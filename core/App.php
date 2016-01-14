<?php

class App {
	
	protected $controller = 'Home';
	protected $method = 'index';
	protected $params = [];

	public function __construct() {
		$url = $this->parseUrl();
		if(!isset($url[3]))
			header('Location: /index.php/Home/vistaFrontend');
		if(isset($url[4]))
			header('Location: /index.php/Home/vistaNoEncontrada');
		if(isset($url[2])) {
			if(file_exists('controllers/' . $url[2] . '.php')) { // Si existe archivo que pertenece al controlador enviado en la url
				$this->controller = $url[2]; // Setea la variable de instancia controller con ese valor, sino va por defecto al controlador Home
				unset($url[2]); // Deja en blanco la posicion que contiene al controlador en arreglo $url
			}
			else {
				header('Location: /index.php/Home/vistaNoEncontrada');
			}
		}
		require_once 'controllers/' . $this->controller . '.php';
		$this->controller = new $this->controller();
		if(isset($url[3])) {  // Si existe el metodo en la url
			$param = explode("?", $url[3]);
			if(method_exists($this->controller, $param[0])) { // Si existe el metodo al que se quiere acceder del controlador enviado por url
				$this->method = $param[0]; // Setea la variable de instancia method con el metodo que se ingreso por url, sino va por defecto al index
				unset($param[0]); // Deja en blanco la posicion que contiene el metodo en el arreglo $url
			}
			else {
				// No existe el metodo en el controlador, por lo que redirije al login
				header('Location: /index.php/Home/vistaNoEncontrada');
			}
		}
		// El signo de pregunta es por si se llama un metodo que no requiere parametros. Asi no da error
		$this->params = $url ? array_values($url) : [];
		call_user_func_array([$this->controller, $this->method], $this->params); // Se realiza el llamado al controlador y al metodo correspondiente, puede haber parametros
	}

	public function parseUrl() {
		$serverUrl = $_SERVER['REQUEST_URI'];
		if(strpos($serverUrl, "index.php")) {
			$res = filter_var(rtrim($_SERVER['REQUEST_URI'], '/'), FILTER_SANITIZE_URL);
			return ($url = explode('/', $res));
		}
	}

}

?>