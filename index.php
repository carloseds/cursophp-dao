<?php 

require_once("config.php");

$usuario = new Usuario();
$usuario->loadById(1);

echo $usuario;