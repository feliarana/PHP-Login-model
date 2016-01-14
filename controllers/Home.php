<?php

require_once 'twigAutoloader.php';

class Home extends Controller {

	public function __construct() {
        if(session_status() == PHP_SESSION_NONE)
        	session_start();
    }

	public function vistaFrontend($checkFound = '') {
		if(empty($checkFound))
			$checkFound = "found";
		$mensaje= "Bienvenido al sitio!";
		$template = loadTwig("frontend.twig", $mensaje); // Carga el template. Por la configuracion de twigAutoloader.php
		$template->display(array('isFound' => $checkFound));
	}

	public function vistaBackend($parametro = '') {
		if(isset($_SESSION['id'])) {
	        $template = loadTwig("backend.twig", $parametro); // Carga el template. Por la configuracion de twigAutoloader.php
	        $template->display(array());
		}
		else
			$this->vistaNoEncontrada();
	}

	public function vistaNoEncontrada() {
		$notfound = "notfound";
		$this->vistaFrontend($notfound); // Envio true para que en twig cargue la imagen de error 404
	}

}

?>