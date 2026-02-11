<?php

namespace App\Http\Controllers;

use App\Models\EtiquetaAlmacen;
use App\Models\AlmacenImagen;
use App\Models\AlmacenArchivo;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AlmacenController extends Controller
{
    public function index(Request $request): View
    {
        $etiquetasImagen = EtiquetaAlmacen::where('tipo', 'imagen')
            ->with('imagenes.equipo')
            ->orderBy('nombre')
            ->get();
        $etiquetasArchivo = EtiquetaAlmacen::where('tipo', 'archivo')
            ->orderBy('nombre')
            ->get();
        $archivos = AlmacenArchivo::with(['etiqueta', 'equipo'])->orderBy('nombre')->get();
        $equipos = Equipo::orderBy('codigo')->get(['id', 'codigo', 'descripcion', 'imagen_general', 'imagen_etiqueta', 'updated_at']);
        
        // Obtener todas las imágenes del almacén con sus relaciones
        $imagenesAlmacen = AlmacenImagen::with(['etiqueta', 'equipo'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Obtener imágenes de equipos (imagen_general e imagen_etiqueta)
        $imagenesEquipos = collect();
        foreach ($equipos as $equipo) {
            if ($equipo->imagen_general) {
                $rutaImagenGeneral = str_starts_with($equipo->imagen_general, 'storage/') 
                    ? asset('storage/' . str_replace('storage/', '', $equipo->imagen_general))
                    : asset($equipo->imagen_general);
                
                $imagenesEquipos->push((object)[
                    'id' => 'equipo_general_' . $equipo->id,
                    'etiqueta' => null,
                    'equipo' => $equipo,
                    'ruta' => $equipo->imagen_general,
                    'ruta_url' => $rutaImagenGeneral,
                    'nombre_original' => 'Imagen General',
                    'tipo' => 'equipo_general',
                    'created_at' => $equipo->updated_at ?? now(),
                ]);
            }
            if ($equipo->imagen_etiqueta) {
                $rutaImagenEtiqueta = str_starts_with($equipo->imagen_etiqueta, 'storage/') 
                    ? asset('storage/' . str_replace('storage/', '', $equipo->imagen_etiqueta))
                    : asset($equipo->imagen_etiqueta);
                
                $imagenesEquipos->push((object)[
                    'id' => 'equipo_etiqueta_' . $equipo->id,
                    'etiqueta' => null,
                    'equipo' => $equipo,
                    'ruta' => $equipo->imagen_etiqueta,
                    'ruta_url' => $rutaImagenEtiqueta,
                    'nombre_original' => 'Imagen de Etiqueta',
                    'tipo' => 'equipo_etiqueta',
                    'created_at' => $equipo->updated_at ?? now(),
                ]);
            }
        }
        
        // Combinar imágenes del almacén y de equipos, ordenar por fecha
        $imagenes = $imagenesAlmacen->map(function($img) {
            $imgUrl = str_starts_with($img->ruta, 'storage/') 
                ? asset('storage/' . str_replace('storage/', '', $img->ruta)) 
                : asset($img->ruta);
            return (object)[
                'id' => $img->id,
                'etiqueta' => $img->etiqueta,
                'equipo' => $img->equipo,
                'ruta' => $img->ruta,
                'ruta_url' => $imgUrl,
                'nombre_original' => $img->nombre_original,
                'tipo' => 'almacen',
                'created_at' => $img->created_at,
            ];
        })->concat($imagenesEquipos)->sortByDesc('created_at')->values();

        return view('almacen.index', compact('etiquetasImagen', 'etiquetasArchivo', 'archivos', 'equipos', 'imagenes'));
    }

    public function storeEtiqueta(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:imagen,archivo',
        ]);
        EtiquetaAlmacen::create($request->only('nombre', 'tipo'));
        return redirect()->route('almacen.index')->with('success', 'Etiqueta creada.');
    }

    public function storeImagen(Request $request): RedirectResponse
    {
        $request->validate([
            'etiqueta_id' => 'required|exists:etiquetas_almacen,id',
            'imagen' => 'required|image|max:10240',
            'equipo_id' => 'nullable|exists:equipos,id',
        ]);
        $etiqueta = EtiquetaAlmacen::findOrFail($request->etiqueta_id);
        if ($etiqueta->tipo !== 'imagen') {
            return redirect()->route('almacen.index')->with('error', 'La etiqueta debe ser de tipo imagen.');
        }
        $equipoId = $request->equipo_id ?: null;
        // Una imagen por etiqueta (y por equipo si se eligió): reemplazar si ya existe
        $q = AlmacenImagen::where('etiqueta_id', $etiqueta->id);
        if ($equipoId === null) {
            $q->whereNull('equipo_id');
        } else {
            $q->where('equipo_id', $equipoId);
        }
        // Eliminar imágenes anteriores
        $q->each(function ($img) {
            if (file_exists(public_path($img->ruta))) {
                unlink(public_path($img->ruta));
            } elseif (str_starts_with($img->ruta, 'storage/')) {
                Storage::disk('public')->delete(str_replace('storage/', '', $img->ruta));
            }
        });
        $q->delete();
        
        $file = $request->file('imagen');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('img/almacen/imagenes'), $filename);
        $path = 'img/almacen/imagenes/' . $filename;
        
        AlmacenImagen::create([
            'etiqueta_id' => $etiqueta->id,
            'equipo_id' => $equipoId,
            'ruta' => $path,
            'nombre_original' => $file->getClientOriginalName(),
        ]);
        return redirect()->route('almacen.index')->with('success', 'Imagen guardada.');
    }

    public function storeArchivo(Request $request): RedirectResponse
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'archivo' => 'required|file|max:20480',
            'etiqueta_id' => 'nullable|exists:etiquetas_almacen,id',
            'equipo_id' => 'nullable|exists:equipos,id',
        ]);
        $file = $request->file('archivo');
        $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('img/almacen/archivos'), $filename);
        $path = 'img/almacen/archivos/' . $filename;
        
        AlmacenArchivo::create([
            'nombre' => $request->nombre,
            'ruta' => $path,
            'etiqueta_id' => $request->etiqueta_id ?: null,
            'equipo_id' => $request->equipo_id ?: null,
            'mime_type' => $file->getMimeType(),
        ]);
        return redirect()->route('almacen.index')->with('success', 'Archivo guardado.');
    }

    public function downloadImagen(AlmacenImagen $imagen): StreamedResponse
    {
        $path = str_starts_with($imagen->ruta, 'storage/') 
            ? Storage::disk('public')->path(str_replace('storage/', '', $imagen->ruta))
            : public_path($imagen->ruta);
        $nombre = $imagen->nombre_original ?: basename($imagen->ruta);
        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, $nombre, [
            'Content-Type' => 'application/octet-stream',
        ]);
    }

    public function downloadArchivo(AlmacenArchivo $archivo): StreamedResponse
    {
        $path = str_starts_with($archivo->ruta, 'storage/') 
            ? Storage::disk('public')->path(str_replace('storage/', '', $archivo->ruta))
            : public_path($archivo->ruta);
        $nombre = $archivo->nombre . '.' . pathinfo($archivo->ruta, PATHINFO_EXTENSION);
        return response()->streamDownload(function () use ($path) {
            echo file_get_contents($path);
        }, $nombre, [
            'Content-Type' => $archivo->mime_type ?: 'application/octet-stream',
        ]);
    }

    public function destroyImagen(AlmacenImagen $imagen): RedirectResponse
    {
        if (file_exists(public_path($imagen->ruta))) {
            unlink(public_path($imagen->ruta));
        } elseif (str_starts_with($imagen->ruta, 'storage/')) {
            Storage::disk('public')->delete(str_replace('storage/', '', $imagen->ruta));
        }
        $imagen->delete();
        return redirect()->route('almacen.index')->with('success', 'Imagen eliminada.');
    }

    public function destroyArchivo(AlmacenArchivo $archivo): RedirectResponse
    {
        Storage::disk('public')->delete($archivo->ruta);
        $archivo->delete();
        return redirect()->route('almacen.index')->with('success', 'Archivo eliminado.');
    }
}
