<?php

require_once 'controllers/twigAutoloader.php';



class Controller {

	public function model($model) {
		require_once 'models/' . $model . '.php';
		return (new $model());
	}

	public function controlador($controller) {
		require_once 'controllers/' . $controller . '.php';
		return (new $controller());
	}



}

?>