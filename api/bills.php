<?php
if(session_status()===PHP_SESSION_NONE) session_start();
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_customer_bills'){
    if(!isset($_SESSION['customer'])){echo json_encode(['error'=>'Unauthorized']);exit;}
    $cid=intval($_SESSION['customer']['id']);
    $type=$conn->real_escape_string($_POST['type']??'all');
    $where=$type!=='all'?"AND b.bill_type='$type'":'';
    $r=$conn->query("SELECT b.* FROM bills b WHERE b.customer_id=$cid $where ORDER BY b.sale_date DESC");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}

if($a==='get_bills'){
    $type  =$conn->real_escape_string($_POST['bill_type']??'crop');
    $month =$conn->real_escape_string($_POST['month']??'');
    $custid=intval($_POST['customer_id']??0);
    $where ="WHERE b.bill_type='$type'";
    if($month){
        $ym=explode('-',$month);
        $y=intval($ym[0]);$m=intval($ym[1]);
        $where.=" AND YEAR(b.sale_date)=$y AND MONTH(b.sale_date)=$m";
    }
    if($custid) $where.=" AND b.customer_id=$custid";
    $r=$conn->query("SELECT b.*,c.full_name cn FROM bills b LEFT JOIN customers c ON b.customer_id=c.id $where ORDER BY b.sale_date DESC");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_bill'){
    $id    =intval($_POST['id']??0);
    $type  =$conn->real_escape_string($_POST['bill_type']??'crop');
    $cid   =($_POST['customer_id']??0)?intval($_POST['customer_id']):'NULL';
    $p     =$conn->real_escape_string(trim($_POST['product_name']??''));
    $q     =floatval($_POST['quantity']??0);
    $u     =$conn->real_escape_string($_POST['unit']??'kg');
    $pr    =floatval($_POST['price_per_unit']??0);
    $mn    =$conn->real_escape_string(trim($_POST['market_name']??''));
    $ml    =$conn->real_escape_string(trim($_POST['market_location']??''));
    $sd    =$conn->real_escape_string($_POST['sale_date']??date('Y-m-d'));
    $ps    =$conn->real_escape_string($_POST['payment_status']??'pending');
    $n     =$conn->real_escape_string(trim($_POST['notes']??''));
    if(!$p||!$q||!$pr){echo json_encode(['ok'=>0,'msg'=>'Product, quantity and price required']);exit;}
    if($id)
        $conn->query("UPDATE bills SET bill_type='$type',customer_id=$cid,product_name='$p',quantity=$q,unit='$u',price_per_unit=$pr,market_name='$mn',market_location='$ml',sale_date='$sd',payment_status='$ps',notes='$n' WHERE id=$id");
    else
        $conn->query("INSERT INTO bills(bill_type,customer_id,product_name,quantity,unit,price_per_unit,market_name,market_location,sale_date,payment_status,notes)VALUES('$type',$cid,'$p',$q,'$u',$pr,'$mn','$ml','$sd','$ps','$n')");
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='delete_bill'){
    $conn->query("DELETE FROM bills WHERE id=".intval($_POST['id']??0));
    echo json_encode(['ok'=>1]);exit;
}

if($a==='quick_pay'){
    $id   =intval($_POST['id']??0);
    $mode =$conn->real_escape_string($_POST['mode']??'cash');
    $ref  =$conn->real_escape_string(trim($_POST['reference']??''));
    $today=date('Y-m-d');
    // Get bill details
    $bill=$conn->query("SELECT total_amount,customer_id,bill_type FROM bills WHERE id=$id LIMIT 1")->fetch_assoc();
    if(!$bill){echo json_encode(['ok'=>0,'msg'=>'Bill not found']);exit;}
    $amt   =floatval($bill['total_amount']);
    $custid=$bill['customer_id']?intval($bill['customer_id']):'NULL';
    // Mark bill paid
    $conn->query("UPDATE bills SET payment_status='paid' WHERE id=$id");
    if($conn->error){echo json_encode(['ok'=>0,'msg'=>$conn->error]);exit;}
    // Create payment record so it shows in Payment Records page
    $conn->query("INSERT INTO payments(bill_id,customer_id,amount,payment_mode,payment_date,reference_no,notes)VALUES($id,$custid,$amt,'$mode','$today','$ref','Quick payment')");
    echo json_encode(['ok'=>1,'amount'=>$amt,'mode'=>$mode]);exit;
}

if($a==='get_bill_detail'){
    $id=intval($_POST['id']??0);
    $r=$conn->query("SELECT b.*,c.full_name cn,c.mobile cm,c.address ca FROM bills b LEFT JOIN customers c ON b.customer_id=c.id WHERE b.id=$id LIMIT 1");
    echo json_encode($r->fetch_assoc());exit;
}

echo json_encode(['ok'=>0,'msg'=>'Invalid action']);
