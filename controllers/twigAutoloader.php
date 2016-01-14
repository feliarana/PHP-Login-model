<?php

function loadTwig($_plantilla, $vars = '') {
	require_once("lib/Twig/Autoloader.php");
    Twig_Autoloader::register();
    $templateDir = "views";
    $loader = new Twig_Loader_Filesystem($templateDir);
    $twig = new Twig_Environment($loader);
    if(isset($_SESSION))
    	$twig->addGlobal("session", $_SESSION); // Agrega los datos de la sesion para que sean accesibles en las vistas del usuario logueado
    if(isset($vars))
    	$twig->addGlobal("var", $vars); // Agrega variables que pueden ser utilizadas en las vistas para distintos propositos
    $template = $twig->loadTemplate($_plantilla);
    return ($template);
}

?>