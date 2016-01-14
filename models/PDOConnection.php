<?php

class PDOConnection {
    
    const username = "root";
    const pass = "";
	const host = "localhost";
	const db = "test";

	function __construct() {
		
	}
    
    public function getConnection() {
    	$username = self::username;
    	$pass = self::pass;
    	$host = self::host;
    	$db = self::db;
    	try {
	    	$conexion = new PDO("mysql:host=$host;dbname=$db", $username, $pass);
			$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	        return ($conexion);
	    }
	    catch(Exception $e) {
	    	// Si entra por el catch, es para trabajar de manera local con la configuración de los integrantes del grupo
	    	// NOTA: Esto debería modificarse, si el sitio no pudiera conectarse por X razón, y redirigir a una página nuestra del sitio diciendo que hay un error interno (con la BD)
	    	$error = "Hubo un error:".$e;
	    	return $error;
		}
    }

    public function closeConnection($conn) {
    	$conn = null;
    	return (true);
    }

}

?>