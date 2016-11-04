<?php

class modelCita {

	protected $mysqli;
	public $attributes = ['idempleado','idservicio','idsucursal','hora','duracion','fecha','observacion'];

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
	public function getCita($id=0){
		$stmt = $this->mysqli->prepare("SELECT * FROM cita WHERE idcita = ? ; ");
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
	public function getCitas(){
		$query = "
				SELECT idcita,s.nombre as servicio,CONCAT(e.nombre,  ' ', e.apellido) as empleado,suc.nombre as sucursal,fecha,hora
				FROM cita as c
					JOIN empleado as e on (e.idempleado = c.idempleado)
					JOIN servicio as s on (s.idservicio = c.idservicio)
					JOIN sucursal as suc on (suc.idsucursal = c.idsucursal)
				";

		$result = $this->mysqli->query($query);
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
		$stmt = $this->mysqli->prepare("INSERT INTO cita(idempleado,idservicio,idsucursal,hora,duracion,fecha,observacion) VALUES (?,?,?,?,?,?,?); ");
		$stmt->bind_param('iiiiiss', $obj->idempleado, $obj->idservicio, $obj->idsucursal, $obj->hora, $obj->duracion, $obj->fecha, $obj->observacion);
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
		$stmt = $this->mysqli->prepare("DELETE FROM cita WHERE idcita = ? ; ");
		$stmt->bind_param('i', $id);
		$r = $stmt->execute();
		$stmt->close();
		return $r;
	}

	/**
	 * valida si el registro de cita puede ser ingresado
	 * @param obj $obj objeto
	 * @return String: 1 en caso de exito, de lo contrario
	 * mensaje de error
	 */
	public function validate($obj){

		//Validamos la disponibilidad del la sucursal, el empleado y horario
		$query = "
				SELECT hora,duracion,disponibilidad
				FROM cita as c
					JOIN sucursal as s on (s.idsucursal = c.idsucursal)
				WHERE idempleado = ?
					AND c.idsucursal = ?
					AND fecha = ?
				";

		$stmt = $this->mysqli->prepare($query);

		$stmt->bind_param('iis', $obj->idempleado, $obj->idsucursal, $obj->fecha);
		$stmt->execute();

		$meta = $stmt->result_metadata();

		$parameters = array();
		while ($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $parameters);

		$horaFin = 0;
		$i = 0;
		$results = array();
		while($stmt->fetch()){

			foreach($row as $key => $val) {
				$x[$key] = $val;
			}
			$results[] = $x;

			$horaFin = $results[$i]['hora'] + $results[$i]['duracion'];

			//Validamos que la cita pueda ser atendida por el empleado, en la sucursal y en el horario dado
			if($obj->hora >= $results[$i]['hora'] && $obj->hora <= $horaFin){
				return $retorno = "La cita no puede ser creada debido a que el empleado ya esta ocupado en el horario dado.";
			}

			$horaFin = 0;

			$i++;
		}

		$cantDemanda = $i + 1;
		if($i > 0){
			//Validamos disponibilidad de la sucursal
			if($cantDemanda > $results[0]['disponibilidad']){
				return $retorno = "La cita no puede ser creada debido a que la sucursal no tiene diponibilidad.";
			}
		}


		//Validamos la disponibilidad general de la sucursal
		$query = "
				SELECT hora,duracion,disponibilidad
				FROM cita as c
					JOIN sucursal as s on (s.idsucursal = c.idsucursal)
				WHERE c.idsucursal = ?
					AND fecha = ?
				";

		$stmt = $this->mysqli->prepare($query);

		$stmt->bind_param('is', $obj->idsucursal, $obj->fecha);
		$stmt->execute();

		$meta = $stmt->result_metadata();

		$parameters = array();
		while ($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $parameters);

		$horaFin = 0;
		$i = 0;
		$results = array();
		while($stmt->fetch()){

			foreach($row as $key => $val) {
				$x[$key] = $val;
			}
			$results[] = $x;

			$horaFin = $results[$i]['hora'] + $results[$i]['duracion'];

			//Validamos que la cita pueda ser atendida en la sucursal y en el horario dado
			if($obj->hora >= $results[$i]['hora'] && $obj->hora <= $horaFin){
				return $retorno = "La cita no puede ser creada. La sucursal está ocupada en el horario dado.";
			}

			$horaFin = 0;

			$i++;
		}

		$cantDemanda = $i + 1;
		if($i > 0){
			//Validamos disponibilidad de la sucursal
			if($cantDemanda > $results[0]['disponibilidad']){
				return $retorno = "La cita no puede ser creada debido a que la sucursal no tiene diponibilidad.";
			}
		}


		//Validamos la disponibilidad general del empleado
		$query = "
				SELECT hora,duracion,disponibilidad
				FROM cita as c
					JOIN sucursal as s on (s.idsucursal = c.idsucursal)
				WHERE c.idempleado = ?
					AND fecha = ?
				";

		$stmt = $this->mysqli->prepare($query);

		$stmt->bind_param('is', $obj->idempleado, $obj->fecha);
		$stmt->execute();

		$meta = $stmt->result_metadata();

		$parameters = array();
		while ($field = $meta->fetch_field()) {
			$parameters[] = &$row[$field->name];
		}

		call_user_func_array(array($stmt, 'bind_result'), $parameters);

		$horaFin = 0;
		$i = 0;
		$results = array();
		while($stmt->fetch()){

			foreach($row as $key => $val) {
				$x[$key] = $val;
			}
			$results[] = $x;

			$horaFin = $results[$i]['hora'] + $results[$i]['duracion'];

			//Validamos que la cita pueda ser atendida en la sucursal y en el horario dado
			if($obj->hora >= $results[$i]['hora'] && $obj->hora <= $horaFin){
				return $retorno = "La cita no puede ser creada. El empleado está ocupado en el horario dado.";
			}

			$horaFin = 0;

			$i++;
		}

		return $retorno = 1;
	}

}
