<?php

class apiEmpleado {

	public function api(){
		header('Content-Type: application/JSON');

		$method = $_SERVER['REQUEST_METHOD'];
		switch ($method) {
		case 'GET'://consulta
			$this->getEmpleado();
			break;
		case 'POST'://inserta
			$this->saveEmpleado();
			break;
		case 'PUT'://actualiza
			$this->updateEmpleado();
			break;
		case 'DELETE'://elimina
			$this->deleteEmpleado();
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
	function getEmpleado(){
		if($_GET['action'] == 'empleado'){
			$db = new modelEmpleado();
			if(isset($_GET['id'])){//muestra 1 solo registro si es que existiera ID
				$response = $db->getEmpleado($_GET['id']);
				echo json_encode($response,JSON_PRETTY_PRINT);
			}else{ //muestra todos los registros
				$response = $db->getEmpleados();
				echo json_encode($response,JSON_PRETTY_PRINT);
			}
		}else
			$this->response(400);

	}

	/**
	* metodo para guardar un nuevo registro en la base de datos
	*/
	function saveEmpleado(){
		if($_GET['action'] == 'empleado'){
			//Decodifica un string de JSON
			$obj = json_decode( file_get_contents('php://input') );
			$objArr = (array)$obj;
			if (empty($objArr)){
				$this->response(422,"error","Entidad no procesable. Revisar json.");
			}else if(isset($obj->nombre)){
				$model = new modelEmpleado();
				foreach($model->attributes as $val){
					if(!array_key_exists($val,$obj)){
						$this->response(422,"error","La propiedad ".$val." debe estar definida.");
						exit;
					}
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
	* Actualiza un registro
	*/
	function updateEmpleado() {
		if(isset($_GET['action']) && isset($_GET['id'])){
			if($_GET['action'] == 'empleado'){
				$obj = json_decode( file_get_contents('php://input') );
				$objArr = (array)$obj;
				if (empty($objArr)){
					$this->response(422,"error","Entidad no procesable. Revisar json.");
				}else if(isset($obj->nombre)){
					$model = new modelEmpleado();
					foreach($model->attributes as $val){
						if(!array_key_exists($val,$obj)){
							$this->response(422,"error","La propiedad ".$val." debe estar definida.");
							exit;
						}
					}
					$model->update($_GET['id'], $obj);
					$this->response(200,"success","El registro ha sido actualizado con éxito.");
				}else{
					$this->response(422,"error","La propiedad no está definida");
				}
			}
		}else
			$this->response(400);
	}

	/**
	 * elimina registro
	 */
	function deleteEmpleado(){
		if( isset($_GET['action']) && isset($_GET['id']) ){
			if($_GET['action'] == 'empleado'){
				$model = new modelEmpleado();

				//Validamos relaciones
				if($model->checkIDRel($_GET['id'])){
					$this->response(422,"error","No se puede eliminar porque posee relación.");
					exit;
				}

				$model->delete($_GET['id']);
				$this->response(204);
			}else
				$this->response(400);
		}else
			$this->response(400);
	}
}
