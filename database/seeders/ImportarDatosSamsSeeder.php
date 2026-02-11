<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ImportarDatosSamsSeeder extends Seeder
{
    public function run(): void
    {
        // ==================== EMPRESAS ====================
        DB::table('empresas')->insertOrIgnore([
            ['id' => 1, 'nombre' => 'I.E.T.D.H PREVENTION WORLD QHSE S.A.S.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 14, 'nombre' => 'FERYSEG S.A.S', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==================== SEDES ====================
        DB::table('sedes')->insertOrIgnore([
            ['id' => 1, 'empresa_id' => 1, 'nombre' => 'Sede Administrativa Neiva', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'empresa_id' => 1, 'nombre' => 'Centro de Formación Neiva', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'empresa_id' => 1, 'nombre' => 'Centro de Almacenamiento Fortalecillas', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'empresa_id' => 1, 'nombre' => 'Centro de Formación Barrancabermeja', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 5, 'empresa_id' => 1, 'nombre' => 'Centro de Almacenamiento Granjas', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 6, 'empresa_id' => 1, 'nombre' => 'Principal', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==================== BODEGAS ====================
        DB::table('bodegas')->insertOrIgnore([
            ['id' => 6, 'sede_id' => 4, 'nombre' => 'B-11 - Rescate y Primeros Auxilios BCA', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 18, 'sede_id' => 1, 'nombre' => 'B-14 - General S.AD.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 24, 'sede_id' => 2, 'nombre' => 'B-13- General C.F.N.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 39, 'sede_id' => 5, 'nombre' => 'B-7 - Herramientas', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 44, 'sede_id' => 1, 'nombre' => 'B-3 - Equipos de Oficina y Cómputo', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 53, 'sede_id' => 4, 'nombre' => 'B-16 - General C.F.B.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 60, 'sede_id' => 5, 'nombre' => 'B-9 - Archivos y Otros', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 74, 'sede_id' => 1, 'nombre' => 'B-5 - Ventas - Gerencia', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 90, 'sede_id' => 2, 'nombre' => 'B-2 - Rescate y Primeros Auxilios NVA', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 92, 'sede_id' => 4, 'nombre' => 'B-10 - Alturas BCA', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 97, 'sede_id' => 1, 'nombre' => 'B-4 - Otros y Archivo Actual', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 118, 'sede_id' => 5, 'nombre' => 'B-8 - Andamios', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 121, 'sede_id' => 5, 'nombre' => 'B-15 - General C.A.G.', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 130, 'sede_id' => 5, 'nombre' => 'B-6 - Equipos Dados de Baja y Material Didáctico', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 148, 'sede_id' => 2, 'nombre' => 'B-1 - Alturas NVA', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ==================== CARGOS ====================
        $cargos = [
            'Funcionario', 'Predeterminado', 'Funcionario(a)', 'Gerente', 'Administrador',
            'Coordinador(a) Financiero(a)', 'Gerente Administrativo(a)', 'Coordinador(a) de Formación y Desarrollo',
            'Coordinador(a) de Gestión Humana', 'Auxiliar de Compras e Inventarios', 'Aprendiz', 'Gestor(a)',
            'Coordinador(a) de QHSE', 'Supervisor(a)', 'Mensajero(a)', 'Auxiliar de Servicios Generales',
            'Facilitador(a) QHSE', 'Auxiliar Logístico(a)', 'Facilitador(a) QHSE Externo', 'Apoyo Logístico',
            'Auxiliar de Diseño', 'Digitador(a)', 'Mayordomo', 'Soldador(a)', 'Supervisor de Municipio',
            'Supervisor HSEQ', 'Técnico Instalador', 'Almacenista', 'Auxiliar Contable', 'Instructor SST', 'Gestor Academico'
        ];
        foreach ($cargos as $c) {
            DB::table('cargos')->insertOrIgnore(['nombre' => $c, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== GRUPOS ====================
        $grupos = ['TICs', '(Sin Grupo)', 'Gerencia', 'Administrativa', 'Proyecto', 'Soporte'];
        foreach ($grupos as $g) {
            DB::table('grupos')->insertOrIgnore(['nombre' => $g, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== ESTADO ITEMS ====================
        $estadoItems = ['Material Didáctico', 'De Baja', 'En Uso'];
        foreach ($estadoItems as $e) {
            DB::table('estado_items')->insertOrIgnore(['nombre' => $e, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== ESTADO REMISIONES ====================
        $estadoRemisiones = ['Aprobado', 'Devuelto', 'Rechazado', 'Solicitud'];
        foreach ($estadoRemisiones as $er) {
            DB::table('estado_remisiones')->insertOrIgnore(['nombre' => $er, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== USO ITEMS ====================
        $usoItems = ['Seguridad', 'Rescate', 'Entrenamiento', 'Otros'];
        foreach ($usoItems as $u) {
            DB::table('uso_items')->insertOrIgnore(['nombre' => $u, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== TIPO ITEMS ====================
        DB::table('tipo_items')->insertOrIgnore(['nombre' => 'Equipos de Trabajo', 'created_at' => now(), 'updated_at' => now()]);

        // ==================== FABRICANTES ====================
        $fabricantes = [
            'N/A - NO APLICA', 'STERLING ROPE COMPANY INC.', 'AKT MOTOS', 'I.S.C. LTD - INTERNATIONAL SAF', '3M',
            'Singing Rock CZ', 'CUPRUM', 'Johnson Controls', 'TENDON', 'HONEYWELL INDUSTRIAL SAFETY',
            'Equipos y Mediciones Tecnicas', 'Aludesign S.P.A.', 'RIGGER WALES', 'PERALTEC S.A.S.', 'Zubi-Ola',
            'HUAWEI', 'MSA THE SAFETY COMPANY', 'Zebra Technologies', 'IMS Industrias S.A.S.', 'Lenovo',
            'INSAFE FALL PROTECTION', 'MOTOROLA', 'Panasonic Corp', 'C.M.I. Corporation', 'Werner Co',
            'CORDEN - COURANT SAS', 'Hercules Equipos De Proteccion', 'Lousville Ladder Inc', 'Tendon', 'E.P.I. Ltda',
            'I.E.T.D.H. PREVENTION WORLD QH', 'Bullard', 'DBI SALA', 'Nal Honindustrial. Co, Ltd.', 'Colombiana de Comercio S.A',
            'Honeywell Miller', 'Karcher México S.A. de C.V.', 'CMI CORPORATION', 'LG Electronics', 'WUXI EXANOVO MEDICAL INSTRUMEN',
            'Central De Andamios y Construc', 'AMBU', 'Insafe Fall Portection', 'SPERIAN PROTECTION INC', 'DRäGUER',
            'SUZUKI', 'Canon USA Inc.', 'Magicard', 'Grupo Éxito SA', 'Escaleras De Colombia Ltda', 'TEREX CORPORATION',
            'Yoke Niagara', 'MILLER MIGHTY LITE', 'DJI', 'Lenovo Group Ltd.', 'Beal Pro', 'Shanghai Quick & Strong Scaffo',
            'Rombull Ronets S.L.', 'Dewalt', 'Samsung Electronics', 'Camp Industrial Height Safety', 'Oster', 'Protecta',
            'Hvilog', 'JMC SAS', 'Armadura S.A.S.', 'DINAMIK SAFETY CORP', 'CMC RESCUE INC.', 'SMC Quality Gear ForLife',
            'Ricoh Company Ltd.', 'Petzl', 'ACER AMERICA CORPORATION', 'ALPEN', 'Ikar GMBH', 'Pelican Rope',
            'STANLEY BOSTITCH S.A. DE C.V.', 'Rock Exotica', 'Steelpro Safety', 'CMI', 'CAPITAL SAFETY',
            'SERRANO GOMEZ PRETECOR LTDA', 'Abrasivos Industriales S.A.S.', 'SCHANGHAI', 'ORBIT FALL PROTECTION SYSTEMS',
            'EUSSE', 'Network NDT Solutions', 'SWINLINE'
        ];
        foreach ($fabricantes as $f) {
            DB::table('fabricantes')->insertOrIgnore(['nombre' => $f, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== PROVEEDORES ====================
        $proveedores = [
            'Capital Safety', 'Tarcy S.A.S.', 'Fyrepel OSX', 'DISTRIBUIDORA MOTOCENTRO S.A', 'Homecenter / Sodimac Colombia',
            'Roche', 'Sur Company Ltda', 'Win Tecnology - Nelson Javier', 'NEIVANA DE EXTINTORES', 'COLOMBIA TELECOMUNICACIONES S.',
            'Skedco', 'Papelería Panamericana', 'Aire Neiva Ltda', 'Interspiro', 'OS&H - Ocupational Safety and',
            'Central del Fotocopiadoras', 'WIN TECHNOLOGY', 'Ome Cell', 'Laerdal', 'Diacono S.A.S.', 'E.P.I', 'Ital Desing S.A.S',
            'PRACTI-TRAINER', 'IMS Industrias S.A.S.', 'Homecenter', 'Belltec S.A.S.', 'ECOLIFT', 'Vertikal', 'MSA',
            'FERRETERIA LOS PAISAS', 'Armtex', 'Welch Allyn', 'César Castro Contreras', 'Honeywell Industrial Safety',
            'Tecnimodulares Vidrios y Alumi', 'Air Mask', 'Arseg', 'PRODUCEL INGENIEROS S.A.', 'Industrias Metálicas Cruz',
            'ACTIVEX', 'Tecno - Instrumentación Ltda.', 'Infotech PC', 'Didaclibros Ltda', 'Inyecplas', 'INVERNA-RIEGOS DEL HUILA',
            'Equipos y Mediciones Técnicas', 'ZETTA TIC S.A.S.', 'WJ RESCATE LTDA', 'CPR PROMPT', 'Ascensia Entrust',
            'COLOMBIA TELECOMUNIACIONES S.A', 'CASA DE LA VITRINA', 'QUIRURGICOS DEL HUILA', 'EXTECH INSTRUMENTS',
            'Dotaalturas', 'Survivair', 'Colombiana de Comercio S.A.', 'PEPE GANGA', '3M', 'RIVERA EQUIPOS', 'INGECONSEG SAS',
            'Plastisur del Huila', 'Inversiones Megamotor S.A.S', 'INGENIERÍA VERTICAL EN SEGURIDAD Y SALUD SAS',
            'Tecnoinstrumentación Ltda.', 'BATERCOL S.A.S.', 'DISTRIBUIDOR MATERIALES DE COL', 'Ingecom Colombia',
            'Servi- integrales BC', 'Maxieléctricos Neiva S.A.S.', 'Deporteka Ltda', 'Grupo Éxito S.A.', 'Sperian',
            'Importaciones Power S.A.S.', 'MINISO SAN PEDRO PLAZA', 'Prevencion Tecnica En Segurida', 'TECHNOLOGY STORE 2006 S.A.S.',
            'NELLY CARMENSA FIGUEROA', 'Redline', 'COMPUMAX', 'Imposeg Industrial Ltda', 'Spencer', 'DISEÑO Y PUBLICIDAD AAA',
            'Italdec S.A.S.', 'MARTHA C ROJAS', 'Inversiones MPA', 'CASAPRINT', 'INVERSIONES PROIN S.A.S.',
            'LA CASA DE LAS PINTURAS', 'SERRANO GOMEZ PRETECOR LTDA', 'BETANIA', 'AGROINDUSTRIALES S.A.S.', 'COLGAS',
            'Aga Fano SA', 'Ferralpi Ltda', 'Elkart', 'Tecno Instrumentación Ltda', 'Husqvarna', 'Serrano Gomez Pretecor Ltda',
            'COPY CAESS S.A.S.', 'I.E.T.D.H. PREVENTION WORLD QH', 'Rimax', 'Sumatec S.A.S.', 'Central de Andamios y Construc',
            'Grupo Fla Colombia', 'Equifarmas', 'Seguridad Y Salud Laboral', 'Romak', 'Escaleras De Colombia Ltda',
            'DISTRIELECTRICOS', 'BANCOMEVA SA', 'Industrias Allegro', 'TAMES COLOMBIA S.A.S', 'EUSSE Seguridad',
            'Nomada C.I. Ltda', 'El Palacio Del Aluminio Ltda', 'Asesores Integrales En Salud O', 'Accutrend Plus',
            'TECHNICAL SOLUTIONS SAFETY SAS', 'Hydrajaws Ltd', 'DAVID SALAZAR', 'MUSAPERSEA', 'N/A - NO APLICA'
        ];
        foreach ($proveedores as $p) {
            DB::table('proveedores')->insertOrIgnore(['nombre' => $p, 'created_at' => now(), 'updated_at' => now()]);
        }

        // ==================== TIPO EQUIPOS ====================
        $tipoEquipos = [
            'Eslinga', 'Retráctil', 'Absorbedor', 'Adaptador', 'Alcoholímetro', 'Amortiguador de caídas', 'Anclaje',
            'Anclaje para viga', 'Anclaje portátil', 'Anclaje rotatorio', 'Andamio', 'Andamio colgante', 'Andamio multidireccional',
            'Arnés', 'Arnés multiproposito de 4 argollas', 'Arnés de cuerpo entero multiproposito', 'Arnés helicoportado',
            'Arnés multipropósito de 5 puntos', 'Arnés multipropósito de 4 puntos', 'Arrestador', 'ASAP anticaidas',
            'Ascendedor ASAP LOCK', 'Ascendedor pantin derecho', 'Ascendedor pantin izquierdo', 'Baby Anne', 'BACK - UP',
            'Bala de oxígeno', 'Baliza', 'Bloqueador', 'Bloqueador rope grab', 'Boca siamesa contraincendios', 'Bolsa fine line',
            'Bolso', 'Boquilla', 'BVM', 'Cabeza de intubación', 'Caja plastica', 'Camilla', 'Camilla divan fijo',
            'Camilla para emergencia', 'Carro de desplazamiento en viga', 'Casco', 'Casco mountain', 'Casco ALVEO VENT',
            'Casco VERTEX VENT', 'Chaleco', 'Cinta', 'Cinta cerrada', 'Cinta espectra', 'Cinta plana cerrada',
            'Cinta tubular abierta', 'Cinta tubular cerrada', 'Compresor', 'Conector', 'Cono', 'Cordino', 'Cronómetro',
            'Cuello ortopédico', 'Cuerda', 'Cuerda estatica', 'Cuerda semi estatica', 'Dado con puntos de anclaje',
            'Descendedor', 'Descendedor D4 antipanico', 'Descendedor ID autofrenante', 'Descendedor ocho para rescate',
            'Descendedor RIG PETZL', 'Desfibrilador', 'Desfibrilador didáctico', 'Dorso de RCP', 'Electrocardigrama',
            'Enforcer', 'Equipo de autocontenido', 'Equipos de medición', 'Escalera', 'Eslinga de detención',
            'Eslinga de posicionamiento', 'Eslinga de protección', 'Eslinga rápida', 'Estrangulador', 'Estructura',
            'Extintor', 'Extintor ABC', 'Extintor multipropósito 10 libras', 'Extintor multipropósito 5 libras',
            'Extintores de agua', 'Footape pedal regulable', 'Footer', 'Freno', 'Freno para cuerda', 'Gancho',
            'Glucómetro', 'Grillete crosby', 'Grillete de acero forjado', 'Guantes Petzl', 'Hilo', 'Hondilla',
            'HYDRAJAWS', 'Inmovilizador', 'Inmovilizador de extremidades', 'JINGLE II', 'Kit de derrames',
            'Kit linea de vida vertical', 'Laringoscopio', 'LIFT', 'Linea de llamado', 'Linea de trabajo',
            'Línea de vida', 'Línea de vida autoretráctil', 'Línea de vida fija', 'Línea de vida horizontal',
            'Línea de vida portatíl', 'Línea de vida retráctil', 'Línea de vida vertical', 'Little Anne', 'Maniquí',
            'Manometro', 'Marco de mesa', 'Medidor de luz', 'Mini rope GRAB', 'Mini sujetador', 'Mosqueton',
            'Mosqueton 26 KN', 'Mosqueton en aluminio', 'Mosquetón roscado', 'NFPA 6 Bar Rappel Rack', 'Ocho',
            'Ocho de rescate en acero', 'Otoscopio', 'Paleta pare y siga', 'Pancake', 'Pertiga', 'Pinzas', 'Placa',
            'Placa multianclaje', 'Polea', 'Polea doble fija', 'Polea sencilla en acero', 'Poste', 'Pretales',
            'Primeros Auxilios', 'Protector', 'Protector auditivo', 'Protector de cuerda', 'Puño', 'Puño bloqueador',
            'RACK', 'RANDY', 'Reactor', 'Recipiente', 'Red de seguridad', 'Respirador', 'Resucitador', 'Retractil',
            'ROLL GLISS', 'Silla', 'Silla de suspension', 'Simulador', 'Sistema de rescate asistido', 'Sonómetro',
            'Soporte WINCHE', 'Swivel giratorio', 'TIE OFF', 'Tensiómetro', 'Traje de bombero', 'Tramo de manguera',
            'Trípode', 'TROLLEY', 'Ventilador portable', 'WINCHE', 'Absorbica', 'Multidetector de gases',
            'RESPIRADOR CON SUMINISTRO DE AIRE', 'Radios punto a punto', 'MOSQUETÓN EN ACERO 50 KN',
            'CASCO HT 022 TIPO 1', 'Medidor de gases atmosféricos', 'ARNES PARA ESPACIOS CONFINADOS',
            'cilindro de autocontenido', 'KIT DE EMERGENCIA', 'Obstáculos Izaje de cargas', 'Otro'
        ];
        foreach ($tipoEquipos as $te) {
            DB::table('tipo_equipos')->insertOrIgnore(['nombre' => $te, 'created_at' => now(), 'updated_at' => now()]);
        }

        $this->command->info('✅ Datos importados correctamente desde SAMS antiguo.');
    }
}
