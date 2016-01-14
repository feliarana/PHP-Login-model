<?php

class Validar {
	
	public function __construct() {
		
	}

	public function test_input($data) {
		$data = trim($data);
		$data = stripslashes($data);
		$data = htmlspecialchars($data);
		return ($data);
	}

	public function validarEntero($num, $min, $max) {
		if($_SERVER["REQUEST_METHOD"] == "POST") {
			if (!filter_var($num, FILTER_VALIDATE_INT, 
				array("options" => array("min_range"=>$min, "max_range"=>$max)))) {
				return false;
			}
			return true;
		}
		else{
			$goHome= $this->controlador('Home');
			$goHome->vistaNoEncontrada();
			}
	}

	public function validarEmail() {
		if($_SERVER["REQUEST_METHOD"] == "POST") {
			if(empty($_POST["mail"])) {
				$emailError = "Especificar un email valido";
				return ($emailError);
			}
			else {
				$email = $this->test_input($_POST["mail"]);
				if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$emailError = "Formato de mail invalido";
					return ($emailError);
				}
			}
		}
	}

	public function login() {
		if($_SERVER["REQUEST_METHOD"] == "POST") {
			if(empty($_POST["username"])) {
			  	$userError = "El nombre de usuario es requerido";
			} 
			else {
			    $username = $this->test_input($_POST["username"]);
			    if(!preg_match("/^[a-zA-Z ]*$/",$username)){
			        $userError = "Solo letras y espacios son permitidos";
			    }
			    if(strlen($username) < 2 or strlen($username) > 10 )
			        $userError = "Usuario: minimo de 2 carácteres, máximo 10.";
				}

			if(empty($_POST["password"]))
				$passError = "La contraseña es requerida";
			else{
				$password = $this->test_input($_POST["password"]);
			    if(strlen ($password) < 6 or strlen($password) > 30 )
			        $userError = "Contraseña: minimo de 6 carácteres, máximo 30.";				
				}
			$result = array();
			if(isset($userError))
				$result['userError'] = $userError;
			if(isset($passError))
				$result['passError'] = $passError;
			return ($result);
		}

	}

	public function cuota() {
		$result = array();
		if($_SERVER["REQUEST_METHOD"] == "POST") {
			if(count($_POST) != 6 ) 
			  		array_push($result, "Por favor, no deje campos vacíos.");
			else{
				if (!$this->validarEntero($_POST['numero'], 1, 9999))
			  		array_push($result, "Por favor, seleccione un número de cuota correcto.");
				if (!($_POST['tipo'] == 0 or $_POST['tipo'] == 1))
			  		array_push($result, "Por favor, seleccione un tipo de cuota valido.");
				if (!$this->validarEntero($_POST['monto'], 1, 9999))
			  		array_push($result, "Por favor, seleccione un monto válido.");
				if (!$this->validarEntero($_POST['mes'], 1, 12))
			  		array_push($result, "Por favor, seleccione un mes válido.");
				if (!$this->validarEntero($_POST['anio'], 1990, 2050))
			  		array_push($result, "Por favor, seleccione un año válido.");
				if (!$this->validarEntero($_POST['comisionCobrador'], 1, 100))
			  		array_push($result, "Por favor, seleccione un porcentaje válido.");
			}
			return $result;
		}
		$goHome= $this->controlador('Home');
		$goHome->vistaNoEncontrada();
	}
}

?>