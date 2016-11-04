<?php

class modelEmpleado {

	protected $mysqli;
	public $attributes = ['identificacion','nombre','apellido'];

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
	public function getEmpleado($id=0){
		$stmt = $this->mysqli->prepare("SELECT * FROM empleado WHERE idempleado = ? ; ");
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
	public function getEmpleados(){
		$result = $this->mysqli->query('SELECT * FROM empleado');
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
		$stmt = $this->mysqli->prepare("INSERT INTO empleado(identificacion,nombre,apellido) VALUES (?,?,?); ");
		$stmt->bind_param('sss', $obj->identificacion, $obj->nombre, $obj->apellido);
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
		$stmt = $this->mysqli->prepare("DELETE FROM empleado WHERE idempleado = ? ; ");
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
			$stmt = $this->mysqli->prepare("UPDATE empleado SET identificacion=?, nombre=?, apellido=? WHERE idempleado = ? ; ");
			$stmt->bind_param('sssi', $obj->identificacion, $obj->nombre, $obj->apellido, $id);
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
		$stmt = $this->mysqli->prepare("SELECT * FROM empleado WHERE idempleado = ?");
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
		$stmt = $this->mysqli->prepare("SELECT * FROM cita WHERE idempleado = ?");
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
