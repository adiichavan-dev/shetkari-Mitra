<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_crops'){
    $r=$conn->query("SELECT c.*,l.plot_number FROM crops c LEFT JOIN land_records l ON c.land_id=l.id ORDER BY c.created_at DESC");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_crop'){
    $id=intval($_POST['id']??0);
    $lid=$_POST['land_id']??0?intval($_POST['land_id']):'NULL';
    $cn=$conn->real_escape_string($_POST['crop_name']??'');
    $v=$conn->real_escape_string($_POST['variety']??'');
    $sd=$conn->real_escape_string($_POST['sowing_date']??'');
    $eh=($_POST['expected_harvest']??'')?("'".$conn->real_escape_string($_POST['expected_harvest'])."'"):'NULL';
    $ah=($_POST['actual_harvest']??'')?("'".$conn->real_escape_string($_POST['actual_harvest'])."'"):'NULL';
    $ar=floatval($_POST['area_acres']??0);
    $sq=floatval($_POST['seed_qty_kg']??0);
    $f=$conn->real_escape_string($_POST['fertilizer_used']??'');
    $w=$conn->real_escape_string($_POST['water_source']??'');
    $st=$conn->real_escape_string($_POST['status']??'growing');
    $n=$conn->real_escape_string($_POST['notes']??'');
    if(!$cn||!$sd){echo json_encode(['ok'=>0,'msg'=>'Crop name and sowing date required']);exit;}
    if($id) $conn->query("UPDATE crops SET land_id=$lid,crop_name='$cn',variety='$v',sowing_date='$sd',expected_harvest=$eh,actual_harvest=$ah,area_acres=$ar,seed_qty_kg=$sq,fertilizer_used='$f',water_source='$w',status='$st',notes='$n' WHERE id=$id");
    else $conn->query("INSERT INTO crops(land_id,crop_name,variety,sowing_date,expected_harvest,actual_harvest,area_acres,seed_qty_kg,fertilizer_used,water_source,status,notes)VALUES($lid,'$cn','$v','$sd',$eh,$ah,$ar,$sq,'$f','$w','$st','$n')");
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='delete_crop'){
    $conn->query("DELETE FROM crops WHERE id=".intval($_POST['id']??0));
    echo json_encode(['ok'=>1]);exit;
}

echo json_encode(['ok'=>0,'msg'=>'Invalid']);
?>
