<?php
namespace lib;
/**
* Archivo de funciones generales
*
* En este archivo se crean todas las funciones generales del sistema
*
* @access   public
* @package	Funciones
* @category	Generales
*/
class funciones{

	//------------------------------------------------------->
	// Formatea la fecha de un formato aaaa-mm-dd
	// a : dd/mm/aaaa
	//------------------------------------------------------->
	function fechaComp2Espanol($fecha){
		if ($fecha == ""){return "" ;}

		$aaaa = substr($fecha,0,4);
		$mm   = substr($fecha,5,2);
		$dd   = substr($fecha,8,2);
		return $fecha = $dd."/".$mm."/".$aaaa;
	}

	// --------------------------------------------------------------
	// Convierte una fecha dada en DD/MM/YYYY en formato YYYY/MM/DD
	// --------------------------------------------------------------
	function fechaYYYYDDMM($fecha){

		if (!$fecha) return null;

		$temp = explode("-",$fecha);
		$pos = strpos($fecha,"-");

		if ($pos == 0){$temp = explode("/",$fecha);}

		if (strlen($temp[0]) == 1){$temp[0]= "0".$temp[0];}

		if (strlen($temp[1]) == 1){$temp[1]= "0".$temp[1];}

		$fecha = $temp[0]."/".$temp[1]."/".$temp[2];

		$fecha = substr($fecha,6,4)."-".
			 substr($fecha,3,2)."-".
			 substr($fecha,0,2);

		return $fecha;
	}
}
