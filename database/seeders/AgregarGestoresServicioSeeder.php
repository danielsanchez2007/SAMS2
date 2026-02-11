<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AgregarGestoresServicioSeeder extends Seeder
{
    public function run(): void
    {
        // Buscar el rol "gestores de servicio" (case-insensitive)
        $rolGestores = Role::whereRaw('LOWER(nombre) LIKE ?', ['%gestor%servicio%'])
            ->orWhereRaw('LOWER(nombre) LIKE ?', ['%gestores%servicio%'])
            ->orWhereRaw('LOWER(nombre) = ?', ['gestores de servicio'])
            ->orWhereRaw('LOWER(nombre) = ?', ['gestor de servicio'])
            ->first();

        if (!$rolGestores) {
            $this->command->error('No se encontró el rol "gestores de servicio". Creando el rol...');
            $rolGestores = Role::create([
                'nombre' => 'Gestores de Servicio',
                'permisos' => []
            ]);
            $this->command->info('Rol "Gestores de Servicio" creado.');
        }

        // Datos de usuarios a crear
        $usuarios = [
            [
                'nombre_completo' => 'ROJAS',
                'correo' => 'supervisorfyd@preventionworld.edu.co',
                'telefono' => '3183120335',
            ],
            [
                'nombre_completo' => 'LEIDY JOHANA CALDERON',
                'correo' => 'g1@preventionworld.edu.co',
                'telefono' => '3176389915',
            ],
            [
                'nombre_completo' => 'TANIA KARINA CHARRY',
                'correo' => 'g2@preventionworld.edu.co',
                'telefono' => '3160264718',
            ],
            [
                'nombre_completo' => 'DIANA MARCELA BUSTOS',
                'correo' => 'g4@preventionworld.edu.co',
                'telefono' => '3176389965',
            ],
            [
                'nombre_completo' => 'KEIDY VALENTINA ROJAS',
                'correo' => 'g5@preventionworld.edu.co',
                'telefono' => '3174380896',
            ],
            [
                'nombre_completo' => 'AZUCENA BECERRA G',
                'correo' => 'g7@preventionworld.edu.co',
                'telefono' => '3182393470',
            ],
            [
                'nombre_completo' => 'JUANITA ALARCON M',
                'correo' => 'g8@preventionworld.edu.co',
                'telefono' => null,
            ],
            [
                'nombre_completo' => 'JUANITA ALARCON M',
                'correo' => 'alicitaciones@preventionworld.edu.co',
                'telefono' => '3165219126',
            ],
            [
                'nombre_completo' => 'LINA YOANA RUIZ',
                'correo' => 'info@preventionworld.edu.co',
                'telefono' => '3177126532',
            ],
        ];

        foreach ($usuarios as $index => $usuarioData) {
            // Separar nombre y apellidos
            $partes = explode(' ', trim($usuarioData['nombre_completo']), 2);
            $nombre = $partes[0];
            $apellidos = isset($partes[1]) ? $partes[1] : '';

            // Generar username desde el correo
            $username = explode('@', $usuarioData['correo'])[0];

            // Generar cédula temporal si no existe (usando un número único)
            $cedula = 'TEMP' . str_pad($index + 1, 6, '0', STR_PAD_LEFT);

            // Verificar si el usuario ya existe por correo o username
            $usuarioExistente = Usuario::where('correo_electronico', $usuarioData['correo'])
                ->orWhere('username', $username)
                ->first();

            if ($usuarioExistente) {
                $this->command->warn("Usuario {$usuarioData['correo']} ya existe. Actualizando...");
                
                // Actualizar datos
                $usuarioExistente->update([
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'correo_electronico' => $usuarioData['correo'],
                    'telefono' => $usuarioData['telefono'],
                    'role_id' => $rolGestores->id,
                    'password' => Hash::make($usuarioData['telefono'] ?? '123456'),
                    'activo' => true,
                ]);
                
                $this->command->info("✓ Usuario {$usuarioData['correo']} actualizado.");
                continue;
            }

            // Crear nuevo usuario
            try {
                Usuario::create([
                    'tipo_documento' => 'Cédula de ciudadanía',
                    'cedula' => $cedula,
                    'nombre' => $nombre,
                    'apellidos' => $apellidos,
                    'fecha_nacimiento' => '1990-01-01', // Fecha por defecto
                    'direccion' => null,
                    'telefono' => $usuarioData['telefono'],
                    'correo_electronico' => $usuarioData['correo'],
                    'tiene_correo_corporativo' => false,
                    'correo_corporativo' => null,
                    'tiene_telefono_corporativo' => false,
                    'telefono_corporativo' => null,
                    'departamento' => null,
                    'departamento_otro' => null,
                    'municipio' => null,
                    'municipio_otro' => null,
                    'tratamiento' => null,
                    'empresa_id' => null,
                    'sede_id' => null,
                    'grupo_id' => null,
                    'cargo_id' => null,
                    'role_id' => $rolGestores->id,
                    'username' => $username,
                    'password' => Hash::make($usuarioData['telefono'] ?? '123456'),
                    'activo' => true,
                    'imagen_usuario' => null,
                    'firma_imagen' => null,
                ]);

                $this->command->info("✓ Usuario {$usuarioData['correo']} creado con username: {$username}");
            } catch (\Exception $e) {
                $this->command->error("✗ Error al crear usuario {$usuarioData['correo']}: " . $e->getMessage());
            }
        }

        $this->command->info("\n¡Proceso completado!");
    }
}
