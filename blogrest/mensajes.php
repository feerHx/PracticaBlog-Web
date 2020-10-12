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
$tabla = new DataBase('mensajes');
$data = JWT::get_data($jwt, CONFIG::SECRET_JWT);
switch($_SERVER['REQUEST_METHOD']){
    case "GET":
        if(isset($_GET['idtema'])){
            $where = array('idtema'=>$_GET['idtema']);
            $res = $tabla->ReadAll($where);
        }else{
            $res = $tabla->ReadAll();
        }
        header("HTTP/1.1 200 OK");
        echo json_encode($res);
    break;
    case "POST":
        if(isset($_POST['idtema']) && isset($_POST['mensaje'])){
            $datos = array(
                'idtema'=>$_POST['idtema'],
                'mensaje'=>$_POST['mensaje'],
                'user'=>$data['user'],
                'fecha'=> date("Y-m-d H:i:s")
            );
            try{
                $reg = $tabla->create($datos);
                $res = array("result"=>"ok","msg"=>"Se guardo el mensaje", "id"=>$reg);
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
        if(isset($_GET['idmsj']) && isset($_GET['mensaje'])){
            if($data['level']=='A'){
                $where = array('idmsj'=>$_GET['idmsj']);
            }else{
                $where = array('idmsj'=>$_GET['idmsj'], 'user'=>$data['user']);
            }
            $datos = array('mensaje'=>$_GET['mensaje']);
            $reg = $tabla->update($datos,$where);
            $res = array("result"=>"ok","msg"=>"Se guardo el mensaje", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    case "DELETE":
        if(isset($_GET['idmsj'])){
            if($data['level']=='A'){
                $where = array('idmsj'=>$_GET['idmsj']);
            }else{
                $where = array('idmsj'=>$_GET['idmsj'], 'user'=>$data['user']);
            }
            $where = array('idmsj'=>$_GET['idmsj']);
            $reg = $tabla->delete($where);
            $res = array("result"=>"ok","msg"=>"Se elimino el mensaje", "num"=>$reg);
        }else{
            $res = array("result"=>"no","msg"=>"Faltan datos");
        }
        echo json_encode($res);
    break;
    default:
        header("HTTP/1.1 401 Bad Request");
}