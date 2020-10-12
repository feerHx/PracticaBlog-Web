<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, 
        Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once 'database.php';
require_once 'jwt.php';

if($_SERVER['REQUEST_METHOD']=='GET'){
    if(isset($_GET['user']) && isset($_GET['pass'])){
        $u = $_GET['user'];
        $p = $_GET['pass'];
        $users = new DataBase('usuarios');
        $resultado = $users->read( array('user'=>$u, 'pass'=>$p) );
        if($resultado){
            $token = JWT::create(array('user'=>$u,'level'=>$resultado['tipo']),Config::SECRET_JWT);
            $respuesta = array('auth'=>'si', 'token'=>$token, 'level'=>$resultado['tipo']);
        }else{
            $respuesta = array('auth'=>'no', 'token'=>'error');
        }
        echo json_encode($respuesta);
    }else{
        header("HTTP/1.1 401 Bad Request");
    }
}else{
    header("HTTP/1.1 401 Bad Request");
}