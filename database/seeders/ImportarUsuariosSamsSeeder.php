<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ImportarUsuariosSamsSeeder extends Seeder
{
    public function run(): void
    {
        // Datos combinados de personas + usuarios del sistema SAMS antiguo
        $usuarios = [
            ['nombre' => 'Cristina', 'apellidos' => 'Campos', 'cedula' => '1077846371', 'username' => 'admin.preventionworld', 'activo' => true, 'cargo' => 'Auxiliar de Compras e Inventarios'],
            ['nombre' => 'Bryan Leonardo', 'apellidos' => 'Sterling Polania', 'cedula' => '451258584', 'username' => 'bryan.sterling', 'activo' => false, 'cargo' => 'Coordinador(a) Financiero(a)'],
            ['nombre' => 'Leidy Cristina', 'apellidos' => 'Campos Sierra', 'cedula' => '10778463711', 'username' => 'lccampos.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar de Compras e Inventarios'],
            ['nombre' => 'Jonathan', 'apellidos' => 'Tovar S', 'cedula' => '0000000001', 'username' => 'jonathan.tovar', 'activo' => true, 'cargo' => 'Coordinador(a) Financiero(a)'],
            ['nombre' => 'Diego Fernando', 'apellidos' => 'Trujillo Valderrama', 'cedula' => '12239182', 'username' => 'gerencia.general', 'activo' => true, 'cargo' => 'Gerente'],
            ['nombre' => 'Ingrid Yesenia', 'apellidos' => 'Narváez Sanmiguel', 'cedula' => '26421960', 'username' => 'gerencia.administrativa', 'activo' => false, 'cargo' => 'Gerente Administrativo(a)'],
            ['nombre' => 'Laura Camila', 'apellidos' => 'Trujillo Restrepo', 'cedula' => '1075304661', 'username' => 'supervisorn.preventionworld', 'activo' => true, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'Wildis', 'apellidos' => 'Ruiz', 'cedula' => '1065900708', 'username' => 'supervisorb.preventionworld', 'activo' => true, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'Adriana', 'apellidos' => 'Galindo', 'cedula' => '1075239948', 'username' => 'agalindo.preventionworld', 'activo' => false, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'C. Neiva', 'apellidos' => 'Neiva', 'cedula' => '1075299984', 'username' => 'afdiaz.preventionworld', 'activo' => true, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Angie Meliza', 'apellidos' => 'Campos Yusunguaira', 'cedula' => '1075302658', 'username' => 'amcampos.preventionworld', 'activo' => false, 'cargo' => 'Gestor(a)'],
            ['nombre' => 'Angie Yolexy', 'apellidos' => 'Valenzuela Esquivel', 'cedula' => '1075311440', 'username' => 'ayvalenzuela.preventionworld', 'activo' => false, 'cargo' => 'Gestor(a)'],
            ['nombre' => 'Carlos Ivan', 'apellidos' => 'Figueroa', 'cedula' => '83258465', 'username' => 'cifigueroa.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Cesar Augusto', 'apellidos' => 'Collazos Palencia', 'cedula' => '1075289209', 'username' => 'cacollazos.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar de Diseño'],
            ['nombre' => 'Cristhian Camilo', 'apellidos' => 'Castillo Trujillo', 'cedula' => '1004251513', 'username' => 'cccastillo.preventionworld', 'activo' => false, 'cargo' => 'Aprendiz'],
            ['nombre' => 'Dainy Julieth', 'apellidos' => 'Castaño Parra', 'cedula' => '1096208055', 'username' => 'djcastaño.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar Logístico(a)'],
            ['nombre' => 'Diana Paola', 'apellidos' => 'Gomez Cuellar', 'cedula' => '26425265', 'username' => 'dpgomez.preventionworld', 'activo' => false, 'cargo' => 'Digitador(a)'],
            ['nombre' => 'Diana Carolina', 'apellidos' => 'Barrera Barrera', 'cedula' => '1096221589', 'username' => 'dcbarrera.preventionworld', 'activo' => false, 'cargo' => 'Aprendiz'],
            ['nombre' => 'Edwin Neftali', 'apellidos' => 'Meneses Mesa', 'cedula' => '1081702083', 'username' => 'edmeneses.preventionworld', 'activo' => false, 'cargo' => 'Mensajero(a)'],
            ['nombre' => 'Faiver Favian', 'apellidos' => 'Contreras Gutierrez', 'cedula' => '7708397', 'username' => 'ffcontreras.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Juan Camilo', 'apellidos' => 'Zuluaga Toro', 'cedula' => '1098733512', 'username' => 'jczuluaga.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Kelly Johanna', 'apellidos' => 'Duque Motta', 'cedula' => '1075281265', 'username' => 'kjduque.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Yeison Arley', 'apellidos' => 'Castro Guevara', 'cedula' => '1075302185', 'username' => 'yacastro.preventionworld', 'activo' => true, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Lina Paola', 'apellidos' => 'Bermeo Bermeo', 'cedula' => '1075222821', 'username' => 'lpbermeo.preventionworld', 'activo' => false, 'cargo' => 'Coordinador(a) de Gestión Humana'],
            ['nombre' => 'Liya Jicela', 'apellidos' => 'Trujillo Osorio', 'cedula' => '26607303', 'username' => 'ljtrujillo.preventionworld', 'activo' => false, 'cargo' => 'Coordinador(a) de QHSE'],
            ['nombre' => 'Marco Antonio', 'apellidos' => 'Mesa Sanchez', 'cedula' => '1083886000', 'username' => 'mamesa.preventionworld', 'activo' => false, 'cargo' => 'Mayordomo'],
            ['nombre' => 'Maria Jimena', 'apellidos' => 'Morales Molano', 'cedula' => '36311681', 'username' => 'mjmorales.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar de Servicios Generales'],
            ['nombre' => 'Michael Alexander', 'apellidos' => 'Portes Ortiz', 'cedula' => '1080291934', 'username' => 'maportes.preventionworld', 'activo' => false, 'cargo' => 'Coordinador(a) Financiero(a)'],
            ['nombre' => 'Mireya', 'apellidos' => 'Salas Amara', 'cedula' => '28020277', 'username' => 'msalas.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar de Servicios Generales'],
            ['nombre' => 'Oscar Ivan', 'apellidos' => 'Agudelo Galvan', 'cedula' => '1096217251', 'username' => 'oiagudelo.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Paola Yolima', 'apellidos' => 'Osorio Alvarado', 'cedula' => '26460176', 'username' => 'pyosorio.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Sandra Socorro', 'apellidos' => 'Camacho Rojas', 'cedula' => '55172945', 'username' => 'sscamacho.preventionworld', 'activo' => false, 'cargo' => 'Gestor(a)'],
            ['nombre' => 'Tania Yulieth', 'apellidos' => 'Ibarra Coronado', 'cedula' => '1075287408', 'username' => 'tyibarra.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Milton Camilo', 'apellidos' => 'Santos Esquivel', 'cedula' => '7689600', 'username' => 'mcsantos.preventionworld', 'activo' => false, 'cargo' => 'Soldador(a)'],
            ['nombre' => 'Saul', 'apellidos' => 'Valenzuela', 'cedula' => '1075269304', 'username' => 'svalenzuela.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar de Compras e Inventarios'],
            ['nombre' => 'Liliana Paola', 'apellidos' => 'Chivata', 'cedula' => '1002387308', 'username' => 'lpchivata.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar Logístico(a)'],
            ['nombre' => 'Maria Nubia', 'apellidos' => 'Tovar Sanmiguel', 'cedula' => '1075311557', 'username' => 'mntovar.preventionworld', 'activo' => false, 'cargo' => 'Gestor(a)'],
            ['nombre' => 'Karol Bibiana', 'apellidos' => 'Rodriguez Jaramillo', 'cedula' => '1075256110', 'username' => 'admin1.preventionworld', 'activo' => true, 'cargo' => 'Auxiliar de Compras e Inventarios'],
            ['nombre' => 'Laura Camila', 'apellidos' => 'Trujillo Restrepo', 'cedula' => '10753046612', 'username' => 'calidad.preventionworld', 'activo' => false, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'Carlos', 'apellidos' => 'Cordoba Mira', 'cedula' => '1039000000', 'username' => 'Carlos.Cordoba', 'activo' => true, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'Jhoana Maria', 'apellidos' => 'Zapata Munera', 'cedula' => '1047396031', 'username' => 'jhoana.zapata', 'activo' => false, 'cargo' => 'Supervisor(a)'],
            ['nombre' => 'Karen Estefania', 'apellidos' => 'Tovar Falla', 'cedula' => '1075238281', 'username' => 'karen.tovar@preventionworld', 'activo' => false, 'cargo' => 'Coordinador(a) de QHSE'],
            ['nombre' => 'Gessica', 'apellidos' => 'Bedoya Andrade', 'cedula' => '1075224816', 'username' => 'gessica.bedoya@preventionworld', 'activo' => false, 'cargo' => 'Instructor SST'],
            ['nombre' => 'Oscar Mauricio', 'apellidos' => 'Torrejano', 'cedula' => '1075238257', 'username' => 'prestadorqhse.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Laura Patricia', 'apellidos' => 'Amaya Quintero', 'cedula' => '1075216026', 'username' => 'instructor.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
            ['nombre' => 'Niny Johanna', 'apellidos' => 'Narvaez Castro', 'cedula' => '28544886', 'username' => 'nini.preventionworld', 'activo' => false, 'cargo' => 'Auxiliar Contable'],
            ['nombre' => 'Oscar Santiago', 'apellidos' => 'Castro Guevara', 'cedula' => '1003908641', 'username' => 'santiago.preventionworld', 'activo' => false, 'cargo' => 'Facilitador(a) QHSE'],
        ];

        // Obtener empresa y sede predeterminadas
        $empresaId = DB::table('empresas')->where('nombre', 'LIKE', '%PREVENTION WORLD%')->value('id') ?? 1;
        $sedeId = DB::table('sedes')->first()?->id ?? 1;

        foreach ($usuarios as $u) {
            // Buscar cargo por nombre
            $cargoId = DB::table('cargos')->where('nombre', $u['cargo'])->value('id');

            // Verificar si ya existe
            $existe = DB::table('usuarios')->where('cedula', $u['cedula'])->exists();
            if ($existe) continue;

            DB::table('usuarios')->insert([
                'tipo_documento' => 'CC',
                'cedula' => $u['cedula'],
                'nombre' => $u['nombre'],
                'apellidos' => $u['apellidos'],
                'fecha_nacimiento' => '1990-01-01',
                'username' => $u['username'],
                'password' => Hash::make('Prevention2026*'),
                'activo' => $u['activo'],
                'empresa_id' => $empresaId,
                'sede_id' => $sedeId,
                'cargo_id' => $cargoId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Usuarios importados correctamente desde SAMS antiguo.');
    }
}
