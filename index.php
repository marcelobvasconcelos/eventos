<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();

// Basic routing logic

$url = isset($_GET['url']) ? $_GET['url'] : '';

$url = rtrim($url, '/');

$url = explode('/', $url);

// If accessed via /eventos/..., skip 'eventos'
if (!empty($url[0]) && $url[0] === 'eventos') {
    array_shift($url);
}

// Default controller and action

$controller = !empty($url[0]) ? $url[0] : 'public';

$action = !empty($url[1]) ? $url[1] : 'index';

// Load controller

$controllerFile = 'controllers/' . ucfirst($controller) . 'Controller.php';

if (file_exists($controllerFile)) {

    require_once $controllerFile;

    $controllerClass = ucfirst($controller) . 'Controller';

    if (class_exists($controllerClass)) {

        $controllerInstance = new $controllerClass();

        if (method_exists($controllerInstance, $action)) {

            $controllerInstance->$action();

        } else {

            echo 'Action not found';

        }

    } else {

        echo 'Controller class not found';

    }

} else {

    echo 'Controller file not found';

}

?>