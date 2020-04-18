<?php 
//phpinfo();exit;
require_once("configdb.php");
require_once("config.php");


//carrega apenas um usuario
/*$usuario = new Usuario();
$usuario->loadById(1);

echo $usuario;*/

/*$listaUsuarios = Usuario::getAllUsers();

echo json_encode($listaUsuarios);*/

/*$busca = Usuario::busca("dias");

echo json_encode($busca);*/

//$usuario = new Usuario();
//$usuario->login("carlos.dias","123456");
/*$usuario->setLogin("carloseds3@gmail.com");
$usuario->setPassword("1910caca");
$usuario->newUser();*/
//$usuario->loadById(18);
//$usuario->updateUser("carloseds18@gmail.com","1910@caca");
//$usuario->enableDisableUser(18,0);

//echo $usuario;
//$teste2 = new Funcoes();
//$teste = 'seção eduarDo';
//$teste2 = Funcoes::firstUpperCase($teste);

//echo $teste2;

/*$path = 'files/uploads/';
$arquivo = 'https://www.google.com.br/logos/doodles/2020/thank-you-public-transportation-workers-6753651837108759-law.gif';
$imagem = Funcoes::fileDownload($arquivo,$path);

echo '<img src="'.$imagem.'">';*/

/*$cep = '03282-000';

$consulta = Funcoes::viaCep($cep);

print_r($consulta);*/
/*date_default_timezone_set('America/Sao_Paulo');
$cookieName = "VSP_XP";
$arrayData = [
    "empresa" =>"Viasp Tecnologia"
];
$cookieDuration = time()+60;

$setCookie = Funcoes::setCookie($cookieName,$arrayData,$cookieDuration);

if( isset($_COOKIE['VSP']) ){

    var_dump( json_decode($_COOKIE['VSP'],true) );
}*/

/*try{

    throw new Exception("Houve um erro", 400);

} catch (Exception $e){

    echo json_encode(array(
        "message" => $e->getMessage()
        ,"line" => $e->getLine()
        ,"file" => $e->getFile()
        ,"code" => $e->getCode()
    ));

}*/

/*$filename = "error.log";
$title = "ERRO DB";
$path = "logs".DIRECTORY_SEPARATOR;
$content = array(
    "CODIGO_ERRO" => 01
    ,"INFO_ADD" => "APENAS UM TESTE"
);

$teste = Funcoes::setLog($filename,$title ,$path, $content);*/

/*$jwt = new Jwt();
$token = $jwt->create(array(
    "id_user" => 123
    ,"name" => "Carlos Dias"
));

if( !empty($_GET['JWT']) ){
    
    $token = $_GET['JWT'];
    $info = $jwt->validateJwt($token);

    if( $info ){
        
        $dataToken = json_decode( json_encode($info), true );
        print_r( $dataToken);
    
    } else {

        echo "token inválido e/ou não existente";
        exit;

    }

} else {
    echo "token inválido e/ou não existente";
    exit;
}*/
//echo $token;

// eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZF91c2VyIjoxMjMsIm5hbWUiOiJDYXJsb3MgRGlhcyJ9.enCg6b77tsHEa5zzOteX40OQ-r9RoORS4J9iRkCyCXg

$cura = 0;
$dias_quarentena = 15;

while( $cura < $dias_quarentena ){
    
    $dias_quarentena +=$dias_quarentena;
    echo $dias_quarentena.'<br/>';

}