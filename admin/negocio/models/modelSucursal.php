<?php

class modelSucursal {

	protected $mysqli;
	public $attributes = ['nombre','direccion','disponibilidad'];

	/**
	 * Constructor de clase
	 */
	public function __construct() {
		global $bdconfig;

		try{
			//conexión a base de datos
			$this->mysqli = new mysqli($bdconfig['bd_servidor'], $bdconfig['bd_usuario'], $bdconfig['bd_contrasena'], $bdconfig['bd_nombre']);
		}catch (mysqli_sql_exception $e){
			//Si no se puede realizar la conexión
			http_response_code(500);
			exit;
		}
	}

	/**
	 * obtiene un solo registro dado su ID
	 * @param int $id identificador unico de registro
	 * @return Array array con los registros obtenidos de la base de datos
	 */
	public function getSucursal($id=0){
		$stmt = $this->mysqli->prepare("SELECT * FROM sucursal WHERE idsucursal = ? ; ");
		$stmt->bind_param('i', $id);
		$stmt->execute();
		$result = $stmt->get_result();
		$queryId = $result->fetch_all(MYSQLI_ASSOC);
		$stmt->close();
		return $queryId;
	}

	/**
	 * obtiene todos los registros de la tabla
	 * @return Array array con los registros obtenidos de la base de datos
	 */
	public function getSucursals(){
		$result = $this->mysqli->query('SELECT * FROM sucursal');
		$queryId = $result->fetch_all(MYSQLI_ASSOC);
		$result->close();
		return $queryId;
	}

	/**
	 * añade un nuevo registro en la tabla
	 * @param Obj $obj objeto
	 * @return bool TRUE|FALSE
	 */
	public function insert($obj){
		$stmt = $this->mysqli->prepare("INSERT INTO sucursal(nombre,direccion,disponibilidad) VALUES (?,?,?); ");
		$stmt->bind_param('ssi', $obj->nombre, $obj->direccion, $obj->disponibilidad);
		$r = $stmt->execute();
		$stmt->close();
		return $r;
	}

	/**
	 * elimina un registro dado el ID
	 * @param int $id Identificador unico de registro
	 * @return Bool TRUE|FALSE
	 */
	public function delete($id=0) {
		$stmt = $this->mysqli->prepare("DELETE FROM sucursal WHERE idsucursal = ? ; ");
		$stmt->bind_param('i', $id);
		$r = $stmt->execute();
		$stmt->close();
		return $r;
	}

	/**
	 * Actualiza registro dado su ID
	 * @param int $id Description
	 */
	public function update($id,$obj) {
		if($this->checkID($id)){
			$stmt = $this->mysqli->prepare("UPDATE sucursal SET nombre=?, direccion=?, disponibilidad=? WHERE idsucursal = ? ; ");
			$stmt->bind_param('ssii', $obj->nombre, $obj->direccion, $obj->disponibilidad, $id);
			$r = $stmt->execute();
			$stmt->close();
			return $r;
		}
		return false;
	}

	/**
	 * verifica si un ID existe
	 * @param int $id Identificador unico de registro
	 * @return Bool TRUE|FALSE
	 */
	public function checkID($id){
		$stmt = $this->mysqli->prepare("SELECT * FROM sucursal WHERE idsucursal = ?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
			$stmt->store_result();
			if ($stmt->num_rows == 1){
				return true;
			}
		}
		return false;
	}

	/**
	 * verifica si un ID existe en la relacion
	 * @param int $id Identificador unico de registro
	 * @return Bool TRUE|FALSE
	 */
	public function checkIDRel($id){
		$stmt = $this->mysqli->prepare("SELECT * FROM cita WHERE idsucursal = ?");
		$stmt->bind_param("i", $id);
		if($stmt->execute()){
			$stmt->store_result();
			if ($stmt->num_rows > 0){
				return true;
			}
		}
		return false;
	}

}
