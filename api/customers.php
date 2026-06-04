<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_customers'){
    $cat=$conn->real_escape_string($_POST['category']??'all');
    $type=$conn->real_escape_string($_POST['customer_type']??'all');
    $where='WHERE 1=1';
    if($cat!=='all') $where.=" AND (category='$cat' OR category='both')";
    if($type!=='all') $where.=" AND customer_type='$type'";
    $r=$conn->query("SELECT id,full_name,mobile,address,category,customer_type,COALESCE(retail_cow_rate,0) as retail_cow_rate,COALESCE(retail_buffalo_rate,0) as retail_buffalo_rate,company_name,created_at FROM customers $where ORDER BY full_name");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_customer'){
    $id      =intval($_POST['id']??0);
    $n       =$conn->real_escape_string(trim($_POST['full_name']??''));
    $m       =$conn->real_escape_string(trim($_POST['mobile']??''));
    $ad      =$conn->real_escape_string(trim($_POST['address']??''));
    $cat     =$conn->real_escape_string($_POST['category']??'both');
    $ctype   =$conn->real_escape_string($_POST['customer_type']??'retail');
    $rcowrate =floatval($_POST['retail_cow_rate']??0);
    $rbufrate =floatval($_POST['retail_buffalo_rate']??0);
    $cname   =$conn->real_escape_string(trim($_POST['company_name']??''));
    $pw      =trim($_POST['password']??'');
    if(!$n||!$m){echo json_encode(['ok'=>0,'msg'=>'Name and mobile required']);exit;}
    if($id){
        $extras="full_name='$n',mobile='$m',address='$ad',category='$cat',customer_type='$ctype',retail_cow_rate=$rcowrate,retail_buffalo_rate=$rbufrate,company_name='$cname'";
        if($pw){$h=password_hash($pw,PASSWORD_DEFAULT);$conn->query("UPDATE customers SET $extras,password_hash='$h' WHERE id=$id");}
        else $conn->query("UPDATE customers SET $extras WHERE id=$id");
    } else {
        $h=password_hash($pw?:$m,PASSWORD_DEFAULT);
        $conn->query("INSERT INTO customers(full_name,mobile,address,category,customer_type,retail_cow_rate,retail_buffalo_rate,company_name,password_hash)VALUES('$n','$m','$ad','$cat','$ctype',$rcowrate,$rbufrate,'$cname','$h')");
    }
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='delete_customer'){
    $conn->query("DELETE FROM customers WHERE id=".intval($_POST['id']??0));
    echo json_encode(['ok'=>1]);exit;
}

if($a==='set_all_retail_rates'){
    $rcow =floatval($_POST['retail_cow_rate']??0);
    $rbuf =floatval($_POST['retail_buffalo_rate']??0);
    $conn->query("UPDATE customers SET retail_cow_rate=$rcow, retail_buffalo_rate=$rbuf WHERE customer_type='retail' OR customer_type IS NULL OR customer_type=''");
    $count=$conn->affected_rows;
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1,'count'=>$count]);exit;
}

if($a==='get_companies'){
    $r=$conn->query("SELECT * FROM milk_companies ORDER BY name");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}
echo json_encode(['ok'=>0,'msg'=>'Invalid']);
