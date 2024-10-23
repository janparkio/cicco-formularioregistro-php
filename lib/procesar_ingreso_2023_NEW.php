<!-- This is just areference of what the previus form looked like, this is not a file that should be edited. -->
 <!-- the original file lives here:https://cicco.conacyt.gov.py/solicitud_registro/procesar_ingreso_2023_NEW.php so the changes should occur somewhere else an not in this file -->

<?php

    session_start();

    /* +++++++++++++++++++++++++++++++++++++++++++++++++  */
    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
    /* +++++++++++++++++++++++++++++++++++++++++++++++++  */

    $accion = 'ninguna';
    $metodo = $_SERVER['REQUEST_METHOD'];


    $MSJ_ERROR = "";
    $URL_INGRESO_ERROR = "https://cicco.conacyt.gov.py/register/register_error_parametros.html";

        if (isset($_POST['captcha_challenge']) && $_POST['captcha_challenge'] == $_SESSION['captcha_text']){


        $separador = "|";

        $fecha_registro    = trim($_POST['et_pb_contact_fecha_ingreso_0']);

	$nombres           = trim($_POST['et_pb_contact_nombres_0']);
        $apellidos         = trim($_POST['et_pb_contact_apellidos_0']);

        $dni               = trim($_POST['et_pb_contact_dni_0']);
	$nacionalidad      = trim($_POST['et_pb_contact_nacionalidad_0']);

        $sexo              = trim($_POST['et_pb_contact_genero_0']);

        $nacimiento        = trim($_POST['et_pb_contact_fecha_nacimiento_0']);

        $telefono          = trim($_POST['et_pb_contact_phone_0']);
        $email             = trim($_POST['et_pb_contact_email_0']);

        $instituciones			= trim($_POST['organizacion']);
        $instituciones_facultad		= trim($_POST['organizacion_facultad']);
        $instituciones_facultad_carrera	= trim($_POST['organizacion_facultad_carrera']);
        $cargo_institucion		= trim($_POST['et_pb_contact_rol_0']);


        $categoria_pronii  = trim($_POST['et_pb_contact_pronii_categoria_0']);
        $contact_orcid     = trim($_POST['et_pb_contact_orcid_0']);
        $contact_scopus    = trim($_POST['et_pb_contact_scopus_0']);
        $contact_wos       = trim($_POST['et_pb_contact_wos_0']);


	$departamento      = trim($_POST['et_pb_contact_departamento_0']);
	$ciudad            = trim($_POST['et_pb_contact_ciudad_0']);

	$ciencias_naturales		= trim($_POST['et_pb_contact_area_investigacion_0_23_0']); 
	$ingenieria_tecnologia  	= trim($_POST['et_pb_contact_area_investigacion_0_23_1']); 
	$ciencias_medicas_salud		= trim($_POST['et_pb_contact_area_investigacion_0_23_2']);
	$ciencias_agricolas_veterinarias= trim($_POST['et_pb_contact_area_investigacion_0_23_3']);
	$ciencias_sociales		= trim($_POST['et_pb_contact_area_investigacion_0_23_4']);
	$humanidades_artes		= trim($_POST['et_pb_contact_area_investigacion_0_23_5']);


        $captcha           = trim($_POST['captcha']);

	/* INICIO VALIDACION DESPUES DEL POST */

	/* ------------------------------------------------------------------------ */
	/* ------------------LISTA INSTITUCIONES----------------------------------- */
	/* ------------------------------------------------------------------------ */

	//$jsonString = file_get_contents('json/INSTITUCIONES_2023.json');
	//$data = json_decode($jsonString, true);
	//$datos = $data['datos'];
	//$lista_instituciones = array();
	//foreach ($datos as $item) {
	//	array_push($lista_instituciones, $item['denominacion']);
	//}

	/* ------------------------------------------------------------------------ */
        //$jsonString = file_get_contents('json/FACULTADES_2023.json');
        //$data = json_decode($jsonString, true);
        //$datos = $data['datos'];
        //$lista_facultades = array();
        //foreach ($datos as $item) {
        //        array_push($lista_facultades, $item['denominacion']);
        //}

	/* ------------------------------------------------------------------------ */

	//$jsonString = file_get_contents('json/CARRERAS_2023.json');
        //$data = json_decode($jsonString, true);
        //$datos = $data['datos'];
        //$lista_carreras = array();
        //foreach ($datos as $item) {
        //        array_push($lista_carreras, $item['denominacion']);
        //}

	/* ------------------------------------------------------------------------ */
        $jsonString = file_get_contents('json/REGION_CIUDAD.json');
        $data = json_decode($jsonString, true);
        $pais = $data['pais'];
        $lista_departamento = array();
        $lista_ciudad  = array();
        foreach ($pais as $item) {
                //echo 'region: ' . $item['nombre_region'] ;
                //echo 'region: ' . $item['nombre_region'] ;
                //echo 'ciudad: ' . print_r($item) ;
                array_push($lista_departamento,  $item['nombre_region']);


                //print_r($item);

                // $item['ciudades'];

                foreach ($item['ciudades']  as $tmp){
                        //echo 'ciudad: ' . $tmp['ciudad'] ;

                        if (strlen($tmp['ciudad']) > 0){
                                array_push($lista_ciudad, $tmp['ciudad']);
                        }
                }


        }

	/* ------------------------------------------------------------------------ */

	$lista_cargo_institucion = array("Investigador", "Investigador PRONII", "Docente universitario", "Estudiante universitario", "Personal administrativo", "Personal técnico", "Consultor/Asesor" );
        $lista_sexo              = array("Masculino", "Femenino");
        $lista_nacionalidad      = array("afgano","alemán","árabe","argentino","australiano","belga","boliviano","brasileño","camboyano","canadiense","chileno","chino","colombiano","coreano","costarricense","cubano","danés","ecuatoriano","egipcio","salvadoreño","escocés","español","estadounidense","estonio","etiope","filipino","finlandés","francés","galés","griego","guatemalteco","haitiano","holandés","hondureño","indonés","inglés","iraquí","iraní","irlandés","israelí","italiano","japonés","jordano","laosiano","letón","letonés","malayo","marroquí","mexicano","nicaragüense","noruego","neozelandés","panameño","paraguayo","peruano","polaco","portugués","puertorriqueño","dominicano","rumano","ruso","sueco","suizo","tailandés","taiwanes","turco","ucraniano","uruguayo","venezolano","vietnamita","afgana","alemana","árabe","argentina","australiana","belga","boliviana","brasileña","camboyana","canadiense","chilena","china","colombiana","coreana","costarricense","cubana","danesa","ecuatoriana","egipcia","salvadoreña","escocesa","española","estadounidense","estonia","etiope","filipina","finlandesa","francesa","galesa","griega","guatemalteca","haitiana","holandesa","hondureña","indonesa","inglesa","iraquí","iraní","irlandesa","israelí","italiana","japonesa","jordana","laosiana","letona","letonesa","malaya","marroquí","mexicana","nicaragüense","noruega","neozelandesa","panameña","paraguaya","peruana","polaca","portuguesa","puertorriqueño","dominicana","rumana","rusa","sueca","suiza","tailandesa","taiwanesa","turca","ucraniana","uruguaya","venezolana","vietnamita");
	//$lista_ciudad            = array("Bahía Negra","Fuerte Olimpo","Isla Margarita","La Victoria","Lagerenza","Puerto Guaraní","Puerto la Esperanza","Ciudad del Este","Domingo Martinez de Irala","Dr. Juan Leon Mallorquin","Hernandarias","Itaquyry","Juan E. Oleary","Los Cedrales","Mbaracayú","Minga Guazu","Minga Pora","Ñacunday","Naranjal","Presidente Franco","Puerto Bertoni","San Alberto","San Cristóbal","Santa Rita","Santa Rosa del Monday","Yguazu","Bella Vista","Capitán Bado","Pedro Juan Caballero","Palacio de Justicia","Correo Central","Mall Excelsior","Tacumbu","Barrio Obrero","Las Mercedes","Barrio Jara","Correo - Corpar","Correo - Corpar","Asociación Coreana","Hipermercado 69","Zeballos Cue","Shopping del Sol","Herrera","Santa Maria","Villa Morra","Shopping Mcal. Lopez","San Pablo","Correo - Corpar","Terminal de Omnibus","Mariano R. Alonso","Luque","Aeropuerto","Campus U.N.A.","Reducto - San Lorenzo","San Lorenzo","Fernando de la Mora","Lambare","Villa Elisa","Capitán Joel Estigarribia","Colonia Neuland","Dr. Pedro P. Peña","Filadelfia","General Eugenio A. Garay","Loma Plata","Mariscal Estigarribia","Teniente Primero Irala Fernandez","3 de Febrero","Caaguazu","Carayao","Cecilio Báez","Colonia Genaro Romero","Coronel Oviedo","Dr. José E. Estigarribia","Dr. Juan Manuel Frutos","José Domingo Ocampos","La Pastora","Mariscal F. Solano Lopez","Nueva Australia","Nueva Londres","R.I. 3 Corrales","Raul A. Oviedo","Repatriación","San Antonio Cordillera","San Joaquin","San José de los Arroyos","Santa Rosa del Mbutu","Simon Bolivar","Yhu","Abaí","Buena Vista","Caazapá","Col. Mayor Nicolas Arguello","Colonia San Cosme","Compañía San Francisco","Dr. Moises Bertoni","Estacion Gral. Patricio Colman","Estación Yuty","General Higinio Morínigo","Isla Saca","Maciel","San Juan Nepomuceno","Santa Barbara","Santa Luisa","Santa Rosa de Lima","Tabai","Yacubo","Yegros","Yuty","Colonia Anahi","Colonia Catuete","Corpus Christi","Curuguaty","General Francisco Alvarez","Itarara","La Paloma","Nueva Esperanza","Salto del Guairá","Ygatimí","Ypé Jhú","Areguá","Capiatá","Fernando de la Mora","Guarambaré","Itá","Itagua","José Augusto Saldivar","Lambaré","Limpio","Loma Pyta","Luque","Mariano Roque Alonso","Ñemby","Nueva Italia","San Antonio","San Lorenzo","Villa Elisa","Villeta","Ypacarai","Ypane","Zeballos Cue","Belén","Concepción","Horqueta","Loreto","Paso Barreto","Paso Mbutú","Puerto Fonciere","San Carlos","San Lázaro","Valle-Mí","Yby - Yau","Alfonso Tranquera","Altos","Arroyos y Esteros","Atyrá","Caacupé","Caraguatay","Col. G. Bernardino Caballero","Compañía San Antonio","Emboscada","Eusebio Ayala","Isla Pucu","Itacurubí de la Cordillera","Itapirú","Juan de Mena","Loma Grande","Mbocayaty del Yhaguy","Nueva Colombia","Piribebuy","Primero de Marzo","San Bernardino","San José Obrero","Santa Elena","Tobatí","Valenzuela","Yaguarete Cua","Barrio Estacion","Borja","Capitan Mauricio José Troche","Colonia Carlos Pfannl","Colonia San Roque González","Coronel Martinez","Dr. Bottrell","Félix Pérez Cardozo","General Eugenio A. Garay","Independencia","Itapé","Iturbe","José Fasardi","Mbocayaty","Natalicio Talavera","Ñumi","Paso Yobay","Pindoyu","San Salvador","Tebicuary","Villarrica","Yataity","Alto Verá","Barrio San Roque","Bella Vista","Cambyretá","Capitán Meza","Capitán Miranda","Carlos Antonio Lopez","Carmen del Parana","Centro de Fronteras","Colonia Federico Chavez","Colonia Samu-u","Colonia Triunfo","Coronel Bogado","Curuñai","Edelira","Encarnación","Fram","General Artigas","General Delgado","Hohenau","Isla Alta","Jesus","José L. Oviedo","La Paz","Mayor Julio Otaño","Natalio","Nueva Alborada","Obligado","Pirapo","San Cosme y Damián","San Dionisio","San Juan del Paraná","San Luis del Paraná","San Pedro del Parana","San Rafael del Parana","Tomas Romero Pereira","Trinidad","Yatytay","Ayolas","Colonia Alejo García","Itayurú","San Ignacio","San Juan Bautista - Misiones","San Miguel","San Patricio","San Ramón","Santa Maria","Santa Rosa","Santiago","Villa Florida","Yabebyry","Yacuti","Alberdi","Barrio Burrerita","Barrio Obrero","Cerrito","Desmochado","General José Eduvigis Díaz","Guazu Cua","Humaitá","Isla Umbú","Laureles","Mayor José de J. Martinez","Paso de Patria","Pilar","San Juan B. de Ñeembucu","Tacuaras","Villa Franca","Villa Oliva","Villalbin","Acahay","Caapucú","Caballero","Carapeguá","Cerro Leon","Colonia G. Cesar Barrientos","Colonia Santa Isabel","Escobar","La Colmena","Mbuyapey","Paraguari","Pirayu","Quiindy","Quyquyho","San Roque Gonzalez","Sapucai","Tebicuary-Mi","Valle Apúa","Yaguarón","Ybycuí","Ybytymí","Benjamín Aceval","Chaco-i","Colonia Falcón","Dr. Francia (Beteretecue)","Fortin Esteban Martinez","Fortín General Bruguez","Fortín General Caballero","Nanawa","Pozo Colorado","Puerto Pinasco","Villa Hayes","25 de Diciembre","Antequera","Chore","Colonia Friesland","Colonia Navidad","Colonia Volendam","General Aquino","General Resquin","Guayaibi","Itacurubi","Lima","Nueva Germania","Puerto Rosario","Puerto Ybapobó","San Estanislao","San José del Rosario","San Pablo","San Pedro","Tacuatí","Unión","Villa del Rosario","Yataity del Norte");
        //$lista_departamento      = array("Alto Paraguay","Alto Paraná","Amambay","Asunción","Boquerón","Caaguazú","Caazapá","Canindeyú","Central","Concepción","Cordillera","Guairá","Itapúa","Misiones","Ñeembucú","Paraguarí","Presidente Hayes","San Pedro");


        if (strlen($nombres) == 0 )  {
            $MSJ_ERROR = $MSJ_ERROR . "Nombres" . $separador ;
        }

        if (strlen($apellidos) == 0 )  {
            $MSJ_ERROR = $MSJ_ERROR . "Apellidos" . $separador ;
        }

        if  ( !( ( strlen($dni)  >= 5 ) && ( strlen($dni)  <= 15 ) && ( is_numeric($dni) )  ) ) {
            $MSJ_ERROR = $MSJ_ERROR . "DNI" . $separador ;
        }

        if  ( !(in_array($nacionalidad, $lista_nacionalidad)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Nacionalidad" . $separador ;
        }

        if  ( !(in_array($sexo, $lista_sexo)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Sexo" . $separador ;
        }

        if ( !(validateDate($nacimiento, 'Y-m-d') ) ){
            $MSJ_ERROR = $MSJ_ERROR . "Fecha Nacimiento" . $separador ;
        }

        if  ( !( (strlen($telefono) >=  6 ) && (strlen($telefono) <=  12 ) && (is_numeric($telefono)) ) )   {
            $MSJ_ERROR = $MSJ_ERROR . "Teléfono" . $separador ;
        }

        if  ( !(filter_var($email, FILTER_VALIDATE_EMAIL)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Email" . $separador ;
        }

        if  ( !(in_array($departamento, $lista_departamento)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Departamento" . $separador ;
        }

        if ( !(in_array($ciudad, $lista_ciudad)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Ciudad" . $separador ;
        }

	/*  +++++++++++++++++  INICIAL OPCIONAL  ++++++++++++++++++   */
/*
        $ciencias_naturales             = trim($_POST['et_pb_contact_area_investigacion_0_23_0']);
        $ingenieria_tecnologia          = trim($_POST['et_pb_contact_area_investigación_0_23_1']);
        $ciencias_medicas_salud         = trim($_POST['et_pb_contact_area_investigación_0_23_2']);
        $ciencias_agricolas_veterinarias= trim($_POST['et_pb_contact_area_investigación_0_23_3']);
        $ciencias_sociales              = trim($_POST['et_pb_contact_area_investigación_0_23_4']);
        $humanidades_artes              = trim($_POST['et_pb_contact_area_investigación_0_23_5']);

 */

        if   ( !( ($ciencias_naturales == "Ciencias Naturales"  ) || ($ingenieria_tecnologia  == "Ingeniería y Tecnología"  ) || ($ciencias_medicas_salud  == "Ciencias Médicas y de la Salud"  ) || ($ciencias_agricolas_veterinarias  == "Ciencias Agrícolas y Veterinarias" ) || ($ciencias_sociales == "Ciencias Sociales" ) || ($humanidades_artes  == "Humanidades y Artes" ) ) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Dominio científico de su interés" . $separador ;
        }

        /*  +++++++++++++++++  FIN OPCIONAL  ++++++++++++++++++   */

	if (isset($_COOKIE["Organizacion"])) {
		$cookieOrganizacion = $_COOKIE["Organizacion"];
		// Use the cookie value as needed
	}

	$comparisonOrganizacion = strcmp($instituciones, $cookieOrganizacion);
        if   ( !($comparisonOrganizacion == 0) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Institución" . $separador ;
        }

        //if   ( !(in_array($instituciones, $lista_instituciones)) ) {
        //    $MSJ_ERROR = $MSJ_ERROR . "Institución" . $separador ;
        //}



        if (isset($_COOKIE["Facultad"])) {
                $cookieFacultad = $_COOKIE["Facultad"];
                // Use the cookie value as needed
        }



	$comparisonFacultad = strcmp($instituciones_facultad, $cookieFacultad);

	if   ( !($comparisonFacultad  == 0) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Facultad" . $separador ;
        }

        //if   ( !(in_array($instituciones_facultad, $lista_facultades)) ) {
        //    $MSJ_ERROR = $MSJ_ERROR . "Facultad" . $separador ;
        //}


	// ********* NO SE VALIDA CARRERA
	//if   ( !(in_array($instituciones_facultad_carrera, $lista_carreras)) ) {
        //    $MSJ_ERROR = $MSJ_ERROR . "Carrera" . $separador ;
        //}
	// ********* NO SE VALIDA CARRERA



        if  ( !(in_array($cargo_institucion, $lista_cargo_institucion)) ) {
            $MSJ_ERROR = $MSJ_ERROR . "Cargo Institución" . $separador ;
        }

        /* FIN VALIDACION DESPUES DEL POST */

        $accion = 'validar_usuarios';
        $arreglo =  array(  "accion"            => $accion,
			    "metodo"            => $metodo,

			    "fecha_registro"    => $fecha_registro,

			    "nombres"           => $nombres,
                            "apellidos"         => $apellidos,

			    "uid"               => 'cona'.$dni,
                            "nacionalidad"      => $nacionalidad,

			    "sexo"              => $sexo,

			    "nacimiento"        => $nacimiento,

			    "telefono"          => $telefono,
			    "email"             => $email,

                            "instituciones"     => $instituciones,
                            "facultad"          => $instituciones_facultad,
                            "carrera"           => $instituciones_facultad_carrera,

			    "cargo_institucion" => $cargo_institucion,
			    "categoria_pronii" 	=> $categoria_pronii,

			    "contact_orcid" 	=> $contact_orcid,
			    "contact_scopus"	=> $contact_scopus,

			    "contact_wos"   	=> $contact_wos,

			    "departamento"      => $departamento,
			    "ciudad"            => $ciudad,

			    "ciencias_naturales" 		=> $ciencias_naturales,          
			    "ingenieria_tecnologia" 		=> $ingenieria_tecnologia,
			    "ciencias_medicas_salud"  		=> $ciencias_medicas_salud,
			    "ciencias_agricolas_veterinarias" 	=> $ciencias_agricolas_veterinarias,
			    "ciencias_sociales" 		=> $ciencias_sociales,
			    "humanidades_artes" 		=> $humanidades_artes,


                            //"agraria_botanica"  => $agraria_botanica,
                            //"ingenieria_mate"   => $ingenieria_mate,
                            //"salud_biologia"    => $salud_biologia,
			    //"humanidades"       => $humanidades,

                            //"comentarios"       => $comentarios,
                            "captcha"           => $captcha);
                            //"submit" => $submit);


        $json = json_encode($arreglo);
        $parametros = '"'.base64_encode($json).'"';

        // print_r(json_decode($json));
        //echo $MSJ_ERROR;

        if (strlen($MSJ_ERROR) == 0 ){

            //echo $parametros;

            //exec('/usr/bin/python3 /var/www/PY/rutina_ingreso.py '.$accion.' '.$parametros, $output, $return);
            exec('/var/www/PY/rutina_ingreso_2023.sh '.$accion.' '.$parametros, $output, $return);

            if ($return == '0') {

                header('Location: '.$output[0], true, 301);

            } else{

                header('Location: '.$URL_INGRESO_ERROR.'?ERROR=Procesamiento', true, 301);
            }

        }else{
            //echo "ERROR ERROR ERROR!!!";
            header('Location: '.$URL_INGRESO_ERROR.'?ERROR='.$MSJ_ERROR, true, 301);

        }


    } else {
        //echo "Error en CAPTCHA";
         header('Location: '.$URL_INGRESO_ERROR.'?ERROR=Captcha', true, 301);
    }
?>

