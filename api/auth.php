<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??'';

if($a==='login_admin'){
    $login   = $conn->real_escape_string(trim($_POST['mobile']??''));
    $password= $_POST['password']??'';
    $r=$conn->query("SELECT * FROM admin WHERE username='$login' OR mobile='$login' LIMIT 1");
    if($row=$r->fetch_assoc()){
        if(password_verify($password,$row['password_hash'])){
            $_SESSION['admin']=$row;
            echo json_encode(['ok'=>1,'type'=>'admin','name'=>$row['full_name']]);
        } else {
            echo json_encode(['ok'=>0,'msg'=>'Wrong password']);
        }
    } else {
        echo json_encode(['ok'=>0,'msg'=>'Username or mobile not found']);
    }
    exit;
}

if($a==='login_customer'){
    $m=$conn->real_escape_string(trim($_POST['mobile']??''));
    $r=$conn->query("SELECT * FROM customers WHERE mobile='$m' LIMIT 1");
    if($row=$r->fetch_assoc()){
        if(password_verify($_POST['password']??'',$row['password_hash']??'')){
            $_SESSION['customer']=$row;
            echo json_encode(['ok'=>1,'type'=>'customer','name'=>$row['full_name']]);
        } else {
            echo json_encode(['ok'=>0,'msg'=>'Wrong password. Default is your mobile number.']);
        }
    } else {
        echo json_encode(['ok'=>0,'msg'=>'Mobile number not found']);
    }
    exit;
}

if($a==='logout'){session_destroy();echo json_encode(['ok'=>1]);exit;}
echo json_encode(['ok'=>0,'msg'=>'Invalid action']);
