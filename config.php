<?php 

date_default_timezone_set('America/Sao_Paulo');

spl_autoload_register( function($class_name){

    $dirClass = "class";
    $filename = str_replace("\\", "/", $dirClass . DIRECTORY_SEPARATOR . $class_name . ".php");

    if( file_exists($filename) ){
        require_once($filename);
    }

});

global $config;

$config['dbname']	= 'viasp';
$config['host'] 	= 'localhost';
$config['dbuser']	= 'root';
$config['dbpass']	= '';
$config['charset']	= 'utf8';