<?php

require "./../config/bdconfig.php";
require "./lib/funciones.php";

use lib\funciones;
$funciones  = new funciones();

//Cargamos según la acción
if(stripos($_SERVER['QUERY_STRING'], 'sucursal') !== false ){
	require_once "./apis/apiSucursal.php";
	require_once "./models/modelSucursal.php";

	$apiSucursal = new apiSucursal();
	$apiSucursal->api();

}elseif(stripos($_SERVER['QUERY_STRING'], 'servicio') !== false ){
	require_once "./apis/apiServicio.php";
	require_once "./models/modelServicio.php";

	$apiServicio = new apiServicio();
	$apiServicio->api();

}elseif(stripos($_SERVER['QUERY_STRING'], 'empleado') !== false ){
	require_once "./apis/apiEmpleado.php";
	require_once "./models/modelEmpleado.php";

	$apiServicio = new apiEmpleado();
	$apiServicio->api();

}elseif(stripos($_SERVER['QUERY_STRING'], 'cita') !== false ){
	require_once "./apis/apiCita.php";
	require_once "./models/modelCita.php";

	$apiCita = new apiCita();
	$apiCita->api();

}else{
	require_once "./apis/apiCita.php";

	$apiCita = new apiCita();
	$apiCita->response(400);
}

