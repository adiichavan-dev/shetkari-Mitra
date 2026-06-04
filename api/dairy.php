<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin'])){http_response_code(401);echo json_encode(['error'=>'Unauthorized']);exit;}
require_once '../includes/db.php';
header('Content-Type: application/json');
$a=$_POST['action']??$_GET['action']??'';

if($a==='get_today_totals'){
    $cow=floatval($conn->query("SELECT COALESCE(SUM(total_litres),0) v FROM milk_production WHERE entry_date=CURDATE() AND animal_type='cow'")->fetch_assoc()['v']);
    $buf=floatval($conn->query("SELECT COALESCE(SUM(total_litres),0) v FROM milk_production WHERE entry_date=CURDATE() AND animal_type='buffalo'")->fetch_assoc()['v']);
    echo json_encode(['cow'=>$cow,'buf'=>$buf]);exit;
}

if($a==='get_livestock'){
    $r=$conn->query("SELECT * FROM livestock LIMIT 1")->fetch_assoc();
    echo json_encode($r?:['cow_count'=>0,'buffalo_count'=>0,'animal_type'=>'both']);exit;
}

if($a==='save_livestock'){
    $c=intval($_POST['cow_count']??0);$b=intval($_POST['buffalo_count']??0);
    $tp=$conn->real_escape_string($_POST['animal_type']??'both');
    $ex=$conn->query("SELECT id FROM livestock LIMIT 1")->fetch_assoc();
    if($ex) $conn->query("UPDATE livestock SET cow_count=$c,buffalo_count=$b,animal_type='$tp' WHERE id={$ex['id']}");
    else     $conn->query("INSERT INTO livestock(cow_count,buffalo_count,animal_type)VALUES($c,$b,'$tp')");
    echo json_encode($conn->error?['ok'=>0,'msg'=>$conn->error]:['ok'=>1]);exit;
}

if($a==='get_milk'){
    $at=$conn->real_escape_string($_POST['animal_type']??'cow');
    $prod=$at==='cow'?'Cow Milk':'Buffalo Milk';
    // Use subquery to avoid duplicate rows from multi-bill join
    $sql="SELECT mp.*,
          c.full_name cn,
          (SELECT b2.id FROM bills b2 WHERE b2.bill_type='milk'
           AND b2.customer_id=mp.customer_id AND b2.sale_date=mp.entry_date
           AND b2.product_name='$prod' LIMIT 1) AS bill_id,
          (SELECT b2.payment_status FROM bills b2 WHERE b2.bill_type='milk'
           AND b2.customer_id=mp.customer_id AND b2.sale_date=mp.entry_date
           AND b2.product_name='$prod' LIMIT 1) AS bill_status
          FROM milk_production mp
          LEFT JOIN customers c ON mp.customer_id=c.id
          WHERE mp.animal_type='$at'
          ORDER BY mp.entry_date DESC, mp.id DESC";
    $r=$conn->query($sql);
    if(!$r){echo json_encode(['error'=>$conn->error]);exit;}
    $d=[];while($row=$r->fetch_assoc())$d[]=$row;
    echo json_encode($d);exit;
}

if($a==='save_milk'){
    $id   =intval($_POST['id']??0);
    $at   =$conn->real_escape_string($_POST['animal_type']??'cow');
    $dt   =$conn->real_escape_string($_POST['entry_date']??date('Y-m-d'));
    $mo   =floatval($_POST['morning_litres']??0);
    $ev   =floatval($_POST['evening_litres']??0);
    $fat  =floatval($_POST['fat_percent']??0);
    $snf  =floatval($_POST['snf_percent']??0);
    $rt   =floatval($_POST['milk_rate']??0);
    $sl   =floatval($_POST['sold_litres']??0);
    $cl   =floatval($_POST['consumed_litres']??0);
    $cid  =($_POST['customer_id']??0)?intval($_POST['customer_id']):'NULL';
    $stype=$conn->real_escape_string($_POST['supply_type']??'retail');
    $n    =$conn->real_escape_string(trim($_POST['notes']??''));

    // FIX: if retail rate not sent, fetch correct column based on animal type
    if($rt<=0 && $cid!=='NULL'){
        $rateCol = $at==='buffalo' ? 'retail_buffalo_rate' : 'retail_cow_rate';
        $cr=$conn->query("SELECT customer_type,COALESCE($rateCol,0) r FROM customers WHERE id=$cid LIMIT 1")->fetch_assoc();
        if($cr && $cr['customer_type']==='retail' && $cr['r']>0) $rt=floatval($cr['r']);
    }

    if($id)
        $conn->query("UPDATE milk_production SET animal_type='$at',entry_date='$dt',morning_litres=$mo,evening_litres=$ev,fat_percent=$fat,snf_percent=$snf,milk_rate=$rt,sold_litres=$sl,consumed_litres=$cl,customer_id=$cid,supply_type='$stype',notes='$n' WHERE id=$id");
    else
        $conn->query("INSERT INTO milk_production(animal_type,entry_date,morning_litres,evening_litres,fat_percent,snf_percent,milk_rate,sold_litres,consumed_litres,customer_id,supply_type,notes)VALUES('$at','$dt',$mo,$ev,$fat,$snf,$rt,$sl,$cl,$cid,'$stype','$n')");

    if($conn->error){echo json_encode(['ok'=>0,'msg'=>$conn->error]);exit;}

    // Create bill when sold > 0 AND rate > 0 AND customer selected
    if($sl>0 && $rt>0 && $cid!=='NULL'){
        $prod=$conn->real_escape_string($at==='cow'?'Cow Milk':'Buffalo Milk');
        if($id){
            $ex=$conn->query("SELECT id FROM bills WHERE bill_type='milk' AND sale_date='$dt' AND customer_id=$cid AND product_name='$prod' LIMIT 1")->fetch_assoc();
            if($ex) $conn->query("UPDATE bills SET quantity=$sl,price_per_unit=$rt,notes='$n' WHERE id={$ex['id']}");
            else     $conn->query("INSERT INTO bills(bill_type,customer_id,product_name,quantity,unit,price_per_unit,sale_date,payment_status,notes)VALUES('milk',$cid,'$prod',$sl,'litre',$rt,'$dt','pending','$n')");
        } else {
            $conn->query("INSERT INTO bills(bill_type,customer_id,product_name,quantity,unit,price_per_unit,sale_date,payment_status,notes)VALUES('milk',$cid,'$prod',$sl,'litre',$rt,'$dt','pending','$n')");
        }
    }
    echo json_encode(['ok'=>1]);exit;
}

if($a==='delete_milk'){
    $conn->query("DELETE FROM milk_production WHERE id=".intval($_POST['id']??0));
    echo json_encode(['ok'=>1]);exit;
}

if($a==='settle_preview'){
    $cid  =trim($_POST['customer_id']??'');
    $month=$conn->real_escape_string(trim($_POST['month']??date('Y-m')));
    $ym=explode('-',$month);$y=intval($ym[0]);$m=intval($ym[1]);
    $cw=$cid?"AND b.customer_id=".intval($cid):'';
    $r=$conn->query("SELECT b.*,c.full_name cn FROM bills b LEFT JOIN customers c ON b.customer_id=c.id WHERE b.bill_type='milk' AND b.payment_status!='paid' AND YEAR(b.sale_date)=$y AND MONTH(b.sale_date)=$m $cw ORDER BY b.sale_date");
    $bills=[];$total=0;
    while($row=$r->fetch_assoc()){$bills[]=$row;$total+=floatval($row['total_amount']);}
    echo json_encode(['bills'=>$bills,'total'=>round($total,2)]);exit;
}

if($a==='settle_confirm'){
    $cid  =trim($_POST['customer_id']??'');
    $month=$conn->real_escape_string(trim($_POST['month']??date('Y-m')));
    $ym=explode('-',$month);$y=intval($ym[0]);$m=intval($ym[1]);
    $cw=$cid?"AND customer_id=".intval($cid):'';
    // Get bills before marking paid
    $bills=$conn->query("SELECT id,customer_id,total_amount FROM bills WHERE bill_type='milk' AND payment_status!='paid' AND YEAR(sale_date)=$y AND MONTH(sale_date)=$m $cw");
    $today=date('Y-m-d');$count=0;
    while($b=$bills->fetch_assoc()){
        $bid=intval($b['id']);$bcust=$b['customer_id']?intval($b['customer_id']):'NULL';$bamt=floatval($b['total_amount']);
        $conn->query("UPDATE bills SET payment_status='paid' WHERE id=$bid");
        // Create payment record automatically
        $conn->query("INSERT INTO payments(bill_id,customer_id,amount,payment_mode,payment_date,notes)VALUES($bid,$bcust,$bamt,'cash','$today','Monthly settlement')");
        $count++;
    }
    echo json_encode(['ok'=>1,'count'=>$count]);exit;
}
echo json_encode(['ok'=>0,'msg'=>'Invalid action']);
