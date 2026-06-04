<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_payments'){
    $billId =intval($_POST['bill_id']??0);
    $custId =intval($_POST['customer_id']??0);
    $month  =$conn->real_escape_string($_POST['month']??'');
    $btype  =$conn->real_escape_string($_POST['bill_type']??'');
    $where  ='WHERE 1=1';
    if($billId) $where.=" AND p.bill_id=$billId";
    if($custId) $where.=" AND (p.customer_id=$custId OR b.customer_id=$custId)";
    if($month){$ym=explode('-',$month);$y=intval($ym[0]);$m=intval($ym[1]);$where.=" AND YEAR(p.payment_date)=$y AND MONTH(p.payment_date)=$m";}
    if($btype) $where.=" AND b.bill_type='$btype'";
    $r=$conn->query("SELECT p.*,c.full_name cn,b.product_name prod,b.total_amount bill_amt,b.bill_type FROM payments p LEFT JOIN customers c ON p.customer_id=c.id LEFT JOIN bills b ON p.bill_id=b.id $where ORDER BY p.payment_date DESC,p.id DESC");
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_payment'){
    $id     =intval($_POST['id']??0);
    $billId =intval($_POST['bill_id']??0);
    $custId =($_POST['customer_id']??0)?intval($_POST['customer_id']):'NULL';
    $amt    =floatval($_POST['amount']??0);
    $mode   =$conn->real_escape_string($_POST['payment_mode']??'cash');
    $date   =$conn->real_escape_string($_POST['payment_date']??date('Y-m-d'));
    $ref    =$conn->real_escape_string(trim($_POST['reference_no']??''));
    $notes  =$conn->real_escape_string(trim($_POST['notes']??''));
    if(!$billId||!$amt){echo json_encode(['ok'=>0,'msg'=>'Bill and amount required']);exit;}
    // Auto-fetch customer_id from bill if not provided
    if($custId==='NULL'||$custId==0){
        $brow=$conn->query("SELECT customer_id FROM bills WHERE id=$billId LIMIT 1")->fetch_assoc();
        $custId=$brow&&$brow['customer_id']?intval($brow['customer_id']):'NULL';
    }
    if($id) $conn->query("UPDATE payments SET bill_id=$billId,customer_id=$custId,amount=$amt,payment_mode='$mode',payment_date='$date',reference_no='$ref',notes='$notes' WHERE id=$id");
    else    $conn->query("INSERT INTO payments(bill_id,customer_id,amount,payment_mode,payment_date,reference_no,notes)VALUES($billId,$custId,$amt,'$mode','$date','$ref','$notes')");
    if($conn->error){echo json_encode(['ok'=>0,'msg'=>$conn->error]);exit;}
    // Auto-update bill status based on total paid
    $billTotal=$conn->query("SELECT total_amount FROM bills WHERE id=$billId LIMIT 1")->fetch_assoc()['total_amount']??0;
    $paidTotal=$conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE bill_id=$billId")->fetch_assoc()['s']??0;
    $status='pending';
    if($paidTotal>=$billTotal) $status='paid';
    elseif($paidTotal>0) $status='partial';
    $conn->query("UPDATE bills SET payment_status='$status' WHERE id=$billId");
    echo json_encode(['ok'=>1,'new_status'=>$status]);exit;
}

if($a==='delete_payment'){
    $id    =intval($_POST['id']??0);
    $billId=intval($_POST['bill_id']??0);
    $conn->query("DELETE FROM payments WHERE id=$id");
    if($billId){
        $billTotal=$conn->query("SELECT total_amount FROM bills WHERE id=$billId LIMIT 1")->fetch_assoc()['total_amount']??0;
        $paidTotal=$conn->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE bill_id=$billId")->fetch_assoc()['s']??0;
        $status='pending';
        if($paidTotal>=$billTotal) $status='paid';
        elseif($paidTotal>0) $status='partial';
        $conn->query("UPDATE bills SET payment_status='$status' WHERE id=$billId");
    }
    echo json_encode(['ok'=>1]);exit;
}

if($a==='get_payment_summary'){
    $custId=intval($_POST['customer_id']??0);
    $month =$conn->real_escape_string($_POST['month']??'');
    $btype =$conn->real_escape_string($_POST['bill_type']??'');
    $bw=''; $pmw='';
    if($month){$ym=explode('-',$month);$y=intval($ym[0]);$mo=intval($ym[1]);$bw=" AND YEAR(b.sale_date)=$y AND MONTH(b.sale_date)=$mo";$pmw=" AND YEAR(p.payment_date)=$y AND MONTH(p.payment_date)=$mo";}
    $cw  = $custId ? " AND b.customer_id=$custId" : '';
    $btw = $btype  ? " AND b.bill_type='$btype'" : '';
    $total=$conn->query("SELECT COALESCE(SUM(b.total_amount),0) v FROM bills b WHERE 1=1 $cw $bw $btw")->fetch_assoc()['v'];
    $paid =$conn->query("SELECT COALESCE(SUM(p.amount),0) v FROM payments p LEFT JOIN bills b ON p.bill_id=b.id WHERE 1=1 $cw $pmw $btw")->fetch_assoc()['v'];
    echo json_encode(['total'=>round($total,2),'paid'=>round($paid,2),'balance'=>round($total-$paid,2)]);exit;
}

// Per-customer balance breakdown for the balance grid
if($a==='get_all_customer_balances'){
    $month=$conn->real_escape_string($_POST['month']??'');
    $btype=$conn->real_escape_string($_POST['bill_type']??'');
    $bw=''; $pmw='';
    if($month){$ym=explode('-',$month);$y=intval($ym[0]);$mo=intval($ym[1]);$bw=" AND YEAR(b.sale_date)=$y AND MONTH(b.sale_date)=$mo";$pmw=" AND YEAR(p.payment_date)=$y AND MONTH(p.payment_date)=$mo";}
    $btw = $btype ? " AND b.bill_type='$btype'" : '';
    // Get all customers who have bills this month
    $custs=$conn->query("SELECT DISTINCT c.id,c.full_name FROM customers c JOIN bills b ON b.customer_id=c.id WHERE 1=1 $bw $btw ORDER BY c.full_name");
    $result=[];
    while($c=$custs->fetch_assoc()){
        $cid=intval($c['id']);
        $total=$conn->query("SELECT COALESCE(SUM(b.total_amount),0) v FROM bills b WHERE b.customer_id=$cid $bw $btw")->fetch_assoc()['v'];
        $paid =$conn->query("SELECT COALESCE(SUM(p.amount),0) v FROM payments p LEFT JOIN bills b ON p.bill_id=b.id WHERE b.customer_id=$cid $pmw $btw")->fetch_assoc()['v'];
        $result[]=['id'=>$cid,'name'=>$c['full_name'],'total'=>round($total,2),'paid'=>round($paid,2),'balance'=>round($total-$paid,2)];
    }
    echo json_encode(['customers'=>$result]);exit;
}


// Monthly trend — last 6 months billed vs paid
if($a==='get_monthly_trend'){
    $btype=$conn->real_escape_string($_POST['bill_type']??'');
    $btw = $btype ? "AND bill_type='$btype'" : '';
    $months=[];
    for($i=5;$i>=0;$i--){
        $y=intval(date('Y',strtotime("-$i months")));
        $m=intval(date('m',strtotime("-$i months")));
        $label=date('M Y',strtotime("-$i months"));
        $total=floatval($conn->query("SELECT COALESCE(SUM(total_amount),0) v FROM bills WHERE YEAR(sale_date)=$y AND MONTH(sale_date)=$m $btw")->fetch_assoc()['v']);
        $paid =floatval($conn->query("SELECT COALESCE(SUM(p.amount),0) v FROM payments p LEFT JOIN bills b ON p.bill_id=b.id WHERE YEAR(p.payment_date)=$y AND MONTH(p.payment_date)=$m $btw")->fetch_assoc()['v']);
        $months[]=['label'=>$label,'month'=>"$y-".str_pad($m,2,'0',STR_PAD_LEFT),'total'=>round($total,2),'paid'=>round($paid,2),'balance'=>round($total-$paid,2)];
    }
    echo json_encode(['months'=>$months]);exit;
}

echo json_encode(['ok'=>0,'msg'=>'Invalid action']);
