<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');

$d=[];
$d['crops']    =$conn->query("SELECT COUNT(*) c FROM crops WHERE status='growing'")->fetch_assoc()['c'];
$d['crop_sales']=$conn->query("SELECT COALESCE(SUM(total_amount),0) c FROM bills WHERE bill_type='crop' AND MONTH(sale_date)=MONTH(NOW()) AND YEAR(sale_date)=YEAR(NOW())")->fetch_assoc()['c'];
$d['milk_sales']=$conn->query("SELECT COALESCE(SUM(total_amount),0) c FROM bills WHERE bill_type='milk' AND MONTH(sale_date)=MONTH(NOW()) AND YEAR(sale_date)=YEAR(NOW())")->fetch_assoc()['c'];
$d['milk_today']=$conn->query("SELECT COALESCE(SUM(total_litres),0) c FROM milk_production WHERE entry_date=CURDATE()")->fetch_assoc()['c'];
$d['customers'] =$conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];

$sc=$conn->query("SELECT DATE_FORMAT(sale_date,'%b') m,SUM(total_amount) v FROM bills WHERE bill_type='crop' AND sale_date>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY MONTH(sale_date),m ORDER BY MIN(sale_date)");
$d['sc']=[];while($r=$sc->fetch_assoc())$d['sc'][]=$r;

$mc=$conn->query("SELECT DATE_FORMAT(sale_date,'%b') m,SUM(total_amount) v FROM bills WHERE bill_type='milk' AND sale_date>=DATE_SUB(NOW(),INTERVAL 6 MONTH) GROUP BY MONTH(sale_date),m ORDER BY MIN(sale_date)");
$d['mc']=[];while($r=$mc->fetch_assoc())$d['mc'][]=$r;

$rs=$conn->query("SELECT b.*,c.full_name cn FROM bills b LEFT JOIN customers c ON b.customer_id=c.id ORDER BY b.created_at DESC LIMIT 6");
$d['rs']=[];while($r=$rs->fetch_assoc())$d['rs'][]=$r;

echo json_encode($d);
