<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';
$isSA=($_SESSION['admin']['role']==='superadmin');

if($a==='get_farmers'){
    $r=$conn->query("SELECT id,full_name,username,mobile,email,village,taluka,district,role,created_at FROM admin ORDER BY role DESC,created_at");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_farmer'){
    if(!$isSA){echo json_encode(['ok'=>0,'msg'=>'Only Farm Director can manage farmers']);exit;}
    $id  =intval($_POST['id']??0);
    $n   =$conn->real_escape_string(trim($_POST['full_name']??''));
    $m   =$conn->real_escape_string(trim($_POST['mobile']??''));
    $e   =$conn->real_escape_string(trim($_POST['email']??''));
    $v   =$conn->real_escape_string(trim($_POST['village']??''));
    $tk  =$conn->real_escape_string(trim($_POST['taluka']??''));
    $di  =$conn->real_escape_string(trim($_POST['district']??''));
    $pw  =trim($_POST['password']??'');
    $role=$conn->real_escape_string($_POST['role']??'farmer');
    if(!in_array($role,['farmer','superadmin']))$role='farmer';
    if(!$n||!$m){echo json_encode(['ok'=>0,'msg'=>'Name and mobile required']);exit;}
    if($id){
        if($pw){$h=password_hash($pw,PASSWORD_DEFAULT);$conn->query("UPDATE admin SET full_name='$n',mobile='$m',email='$e',village='$v',taluka='$tk',district='$di',role='$role',password_hash='$h' WHERE id=$id");}
        else $conn->query("UPDATE admin SET full_name='$n',mobile='$m',email='$e',village='$v',taluka='$tk',district='$di',role='$role' WHERE id=$id");
    } else {
        if(!$pw){echo json_encode(['ok'=>0,'msg'=>'Password required']);exit;}
        $h=password_hash($pw,PASSWORD_DEFAULT);
        $conn->query("INSERT INTO admin(full_name,mobile,email,village,taluka,district,role,password_hash)VALUES('$n','$m','$e','$v','$tk','$di','$role','$h')");
    }
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='delete_farmer'){
    if(!$isSA){echo json_encode(['ok'=>0,'msg'=>'Only Farm Director can delete']);exit;}
    $id=intval($_POST['id']??0);
    $myid=intval($_SESSION['admin']['id']);
    if($id===$myid){echo json_encode(['ok'=>0,'msg'=>'Cannot delete your own account']);exit;}
    $conn->query("DELETE FROM admin WHERE id=$id");
    echo json_encode(['ok'=>1]);exit;
}
echo json_encode(['ok'=>0,'msg'=>'Invalid']);
