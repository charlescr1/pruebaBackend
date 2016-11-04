<?php

class apiCita {

	public function api(){
		header('Content-Type: application/JSON');

		$method = $_SERVER['REQUEST_METHOD'];
		switch ($method) {
		case 'GET'://consulta
			$this->getCita();
			break;
		case 'POST'://inserta
			$this->saveCita();
			break;
		case 'PUT'://actualiza
			$this->updateCita();
			break;
		case 'DELETE'://elimina
			$this->deleteCita();
			break;
		default://metodo NO soportado
			$this->response(405);
			break;
		}
	}

	/**
	 * Respuesta al cliente
	 * @param int $code Codigo de respuesta HTTP
	 * @param String $status indica el estado de la respuesta puede ser "success" o "error"
	 * @param String $message Descripcion de lo ocurrido
	 */
	 function response($code=200, $status="", $message="") {
		http_response_code($code);
		if( !empty($status) && !empty($message) ){
			$response = array("status" => $status ,"message"=>$message);
			echo json_encode($response,JSON_PRETTY_PRINT);
		}
	 }

	/**
	* función que segun el valor de "action" e "id":
	*  - mostrara una array con todos los registros
	*  - mostrara un solo registro
	*  - mostrara un array vacio
	*/
	function getCita(){
		global $funciones;

		if($_GET['action'] == 'cita'){
			$db = new modelCita();
			if(isset($_GET['id'])){//muestra 1 solo registro si es que existiera ID
				$response = $db->getCita($_GET['id']);
				echo json_encode($response,JSON_PRETTY_PRINT);
			}else{ //muestra todos los registros
				$response = $db->getCitas();

				//Formateamos los campos
				foreach($response as $key => $val){
					$response[$key]['fecha'] = $funciones->fechaComp2Espanol($val['fecha']);
				}

				echo json_encode($response,JSON_PRETTY_PRINT);
			}
		}else
			$this->response(400);

	}

	/**
	* metodo para guardar un nuevo registro en la base de datos
	*/
	function saveCita(){
		global $funciones;

		if($_GET['action'] == 'cita'){
			//Decodifica un string de JSON
			$obj = json_decode( file_get_contents('php://input') );
			$objArr = (array)$obj;
			if (empty($objArr)){
				$this->response(422,"error","Entidad no procesable. Revisar json.");
			}else if(isset($obj->idempleado)){
				$model = new modelCita();
				foreach($model->attributes as $val){
					if(!array_key_exists($val,$obj)){
						$this->response(422,"error","La propiedad ".$val." debe estar definida.");
						exit;
					}
				}
				$obj->fecha = $funciones->fechaYYYYDDMM($obj->fecha);

				//Validamos condiciones para ingresar una cita
				$retorno = $model->validate($obj);
				if($retorno !== 1){
					$this->response(422,"error",$retorno);
					exit;
				}

				$model->insert($obj);
				$this->response(200,"success","El registro ha sido agregado con éxito.");
			}else{
				$this->response(422,"error","La propiedad no está definida");
			}
		}else
			$this->response(400);
	}

	/**
	 * elimina registro
	 */
	function deleteCita(){
		if( isset($_GET['action']) && isset($_GET['id']) ){
			if($_GET['action'] == 'cita'){
				$model = new modelCita();
				$model->delete($_GET['id']);
				$this->response(204);
			}else
				$this->response(400);
		}else
			$this->response(400);
	}
}
