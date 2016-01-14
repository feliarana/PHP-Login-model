<?php

class Session extends Controller {

    public function __construct() {
        if(session_status() == PHP_SESSION_NONE)
            session_start();
    }

    public function index($parametro = '') {
        if(!isset($_SESSION['id'])) { // Si el usuario no esta logueado
            $template = loadTwig("login.twig", $parametro); // Carga el template. Por la configuracion de twigAutoloader.php
            $template->display(array());
        }
        else {
            $this->controlador('Home')->vistaBackend();
        }
    }

    public function login() {
        if($_SERVER["REQUEST_METHOD"] == "POST") {
            $user = $this->model('Usuario_model');
            $errors = $user->checkLogin($_POST["username"], $_POST["password"]);
            if(empty($errors)) {
                $_SESSION['id'] = $user->getId();
                $_SESSION['username'] = $user->getUsername();
                header('Location: ../Home/vistaBackend');
            }
            else {
                $mensaje = "Nombre de usuario o contraseña incorrecta";
                $this->index($mensaje);
            }
        }
        else {
            $this->index();
        }
    }

    public function logout() {
        $_SESSION = array(); // Deja en blanco el array eliminando las variables de sesion
        session_destroy();
        $this->controlador('Home')->vistaFrontend();
    }

}

?>