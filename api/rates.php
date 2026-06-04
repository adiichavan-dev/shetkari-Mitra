<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_rates'){
    $at=$conn->real_escape_string($_POST['animal_type']??'cow');
    $r=$conn->query("SELECT fat_value,snf_value,rate FROM farmer_rates WHERE animal_type='$at' ORDER BY fat_value,snf_value");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode(['rates'=>$d,'has_custom'=>count($d)>0]);exit;
}

if($a==='save_rates'){
    $at   =$conn->real_escape_string($_POST['animal_type']??'cow');
    $rates=json_decode($_POST['rates_json']??'[]',true);
    if(!is_array($rates)){echo json_encode(['ok'=>0,'msg'=>'Invalid data']);exit;}
    $conn->query("DELETE FROM farmer_rates WHERE animal_type='$at'");
    $stmt=$conn->prepare("INSERT INTO farmer_rates(animal_type,fat_value,snf_value,rate)VALUES(?,?,?,?)");
    foreach($rates as $row){
        $fat =floatval($row['fat']??0);
        $snf =floatval($row['snf']??0);
        $rate=floatval($row['rate']??0);
        if($fat>0&&$snf>0&&$rate>0){
            $stmt->bind_param('sdd',$at,$fat,$snf,$rate);
            $stmt->execute();
        }
    }
    echo json_encode(['ok'=>1]);exit;
}

if($a==='reset_rates'){
    $at=$conn->real_escape_string($_POST['animal_type']??'cow');
    $conn->query("DELETE FROM farmer_rates WHERE animal_type='$at'");
    echo json_encode(['ok'=>1]);exit;
}

echo json_encode(['ok'=>0,'msg'=>'Invalid']);
