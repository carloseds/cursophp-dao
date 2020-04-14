<?php 
//phpinfo();exit;
require_once("config.php");

//carrega apenas um usuario
/*$usuario = new Usuario();
$usuario->loadById(1);

echo $usuario;*/

/*$listaUsuarios = Usuario::getAllUsers();

echo json_encode($listaUsuarios);*/

/*$busca = Usuario::busca("dias");

echo json_encode($busca);*/

$usuario = new Usuario();
//$usuario->login("carlos.dias","123456");
/*$usuario->setLogin("carloseds3@gmail.com");
$usuario->setPassword("1910caca");
$usuario->newUser();*/
$usuario->loadById(18);
$usuario->updateUser("carloseds18@gmail.com","1910@caca");

echo $usuario;