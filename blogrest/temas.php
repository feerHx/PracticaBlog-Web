<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Authorization, Access-Control-Allow-Methods, Access-Control-Allow-Headers, Allow, Access-Control-Allow-Origin");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, HEAD");
header("Allow: GET, POST, PUT, DELETE, OPTIONS, HEAD");
require_once 'database.php';
require_once 'jwt.php';
if($_SERVER['REQUEST_METHOD']=="OPTIONS"){
    exit();
}

$header = apache_request_headers();
$jwt = trim($header['Authorization']);
switch (JWT::verify($jwt, CONFIG::SECRET_JWT)) {
    case 1:
        header("HTTP/1.1 401 Unauthorized");
        echo "El token no es válido";
        exit();
        break;
    case 2:
        header("HTTP/1.1 408 Request Timeout");
        echo "La sesión caduco";
        exit();
        break;
}

switch($_SERVER['REQUEST_METHOD']){
    case "GET":
        if(isset($_GET['id'])){
            $temas = new DataBase('temas');
            $where = array('idtema'=>$_GET['id']);
            $res = $temas->Read($where);
        }else{
            $temas = new DataBase('temas');
            $res = $temas->ReadAll();
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "POST":
        if(isset($_POST['id']) && isset($_POST['nombre'])){
            
            $temas = new DataBase('temas');
            $datos = array(
                'idtema'=>$_POST['id'],
                'nombre'=>$_POST['nombre']
            );
            try{
                $reg = $temas->create($datos);
                $res = array("result"=>"ok","msg"=>"Se guardo el tema", "id"=>$reg);
            }catch(PDOException $e){
                $res = array("result"=>"no","msg"=>$e->getMessage());
            }
        
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "PUT":
        if(isset($_GET['id']) && isset($_GET['nombre'])){
            
            $temas = new DataBase('temas');
            $where = array('idtema'=>$_GET['id']);
            $datos = array('nombre'=>$_GET['nombre']);
            $reg = $temas->update($datos,$where);

            $res = array("result"=>"ok","msg"=>"Se guardo el tema", "num"=>$reg);
        
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    case "DELETE":
        if(isset($_GET['id'])){
            
            $temas = new DataBase('temas');
            $where = array('idtema'=>$_GET['id']);
            $reg = $temas->delete($where);
            $res = array("result"=>"ok","msg"=>"Se elimino el tema", "num"=>$reg);
        
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    default:
        header("HTTP/1.1 401 Bad Request");
}