<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';
$id=intval($_SESSION['admin']['id']);

if($a==='update_profile'){
    $n=$conn->real_escape_string(trim($_POST['full_name']??''));
    $e=$conn->real_escape_string(trim($_POST['email']??''));
    $v=$conn->real_escape_string(trim($_POST['village']??''));
    $tk=$conn->real_escape_string(trim($_POST['taluka']??''));
    $di=$conn->real_escape_string(trim($_POST['district']??''));
    $conn->query("UPDATE admin SET full_name='$n',email='$e',village='$v',taluka='$tk',district='$di' WHERE id=$id");
    $_SESSION['admin']['full_name']=$n;
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='update_mobile'){
    $curpw  = $_POST['current_password']??'';
    $newmob = $conn->real_escape_string(trim($_POST['new_mobile']??''));
    $row    = $conn->query("SELECT password_hash FROM admin WHERE id=$id LIMIT 1")->fetch_assoc();
    if(!password_verify($curpw,$row['password_hash'])){echo json_encode(['ok'=>0,'msg'=>'Current password is wrong']);exit;}
    $exists = $conn->query("SELECT id FROM admin WHERE mobile='$newmob' AND id!=$id LIMIT 1")->fetch_assoc();
    if($exists){echo json_encode(['ok'=>0,'msg'=>'This mobile number is already in use']);exit;}
    $conn->query("UPDATE admin SET mobile='$newmob' WHERE id=$id");
    $_SESSION['admin']['mobile']=$newmob;
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='update_password'){
    $curpw = $_POST['current_password']??'';
    $newpw = $_POST['new_password']??'';
    $row   = $conn->query("SELECT password_hash FROM admin WHERE id=$id LIMIT 1")->fetch_assoc();
    if(!password_verify($curpw,$row['password_hash'])){echo json_encode(['ok'=>0,'msg'=>'Current password is wrong']);exit;}
    if(strlen($newpw)<6){echo json_encode(['ok'=>0,'msg'=>'Password must be at least 6 characters']);exit;}
    $h=$conn->real_escape_string(password_hash($newpw,PASSWORD_DEFAULT));
    $conn->query("UPDATE admin SET password_hash='$h' WHERE id=$id");
    echo json_encode(['ok'=>1]);exit;
}

if($a==='get_lands'){
    $r=$conn->query("SELECT * FROM land_records ORDER BY created_at DESC");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_land'){
    $lid  =intval($_POST['id']??0);
    $plot =$conn->real_escape_string(trim($_POST['plot_number']??''));
    $area =floatval($_POST['area_acres']??0);
    $loc  =$conn->real_escape_string(trim($_POST['location']??''));
    $soil =$conn->real_escape_string($_POST['soil_type']??'');
    $irr  =$conn->real_escape_string($_POST['irrigation_type']??'');
    if(!$plot){echo json_encode(['ok'=>0,'msg'=>'Plot number required']);exit;}
    if($lid) $conn->query("UPDATE land_records SET plot_number='$plot',area_acres=$area,location='$loc',soil_type='$soil',irrigation_type='$irr' WHERE id=$lid");
    else $conn->query("INSERT INTO land_records(plot_number,area_acres,location,soil_type,irrigation_type)VALUES('$plot',$area,'$loc','$soil','$irr')");
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='delete_land'){
    $conn->query("DELETE FROM land_records WHERE id=".intval($_POST['id']??0));
    echo json_encode(['ok'=>1]);exit;
}
echo json_encode(['ok'=>0,'msg'=>'Invalid']);
