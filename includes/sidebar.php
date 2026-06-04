<?php
$pg   = basename($_SERVER['PHP_SELF'],'.php');
$sub  = strpos($_SERVER['PHP_SELF'],'/pages/')!==false;
$root = $sub?'../':'';
function nav($url,$icon,$key,$pg){
  $a=($pg===basename($url,'.php'))?'active':'';
  return "<a href='$url' class='nav $a'><i class='fa $icon'></i><span data-t='$key'></span></a>";
}
?>
<aside class="sidebar">
  <div class="brand">
    <div class="brand-icon">🌾</div>
    <div><div class="brand-name">Shetkari Mitra</div><div class="brand-sub">शेतकरी मित्र</div></div>
  </div>
  <nav>
    <div class="nav-sec" data-t="nav_overview"></div>
    <?=nav($root.'dashboard.php',        'fa-th-large',   'nav_dashboard',   $pg)?>
    <div class="nav-sec" data-t="nav_modules"></div>
    <?=nav($root.'pages/profile.php',    'fa-user',       'nav_profile',     $pg)?>
    <?=nav($root.'pages/crops.php',      'fa-seedling',   'nav_crops',       $pg)?>
    <?=nav($root.'pages/crop_bills.php', 'fa-store',      'nav_crop_bills',  $pg)?>
    <?=nav($root.'pages/dairy.php',      'fa-cow',        'nav_dairy',       $pg)?>
    <?=nav($root.'pages/milk_bills.php', 'fa-tint',       'nav_milk_bills',  $pg)?>
    <div class="nav-sec" data-t="nav_manage"></div>
    <?=nav($root.'pages/customers.php',  'fa-users',      'nav_customers',   $pg)?>
    <?=nav($root.'pages/rates.php',      'fa-table',      'nav_rates',       $pg)?>
    <?=nav($root.'pages/payments.php',   'fa-money-bill', 'nav_payments',    $pg)?>
  </nav>
  <div class="lang-box">
    <span class="lang-label">भाषा / Language</span>
    <div class="lang-btns">
      <button class="lbtn" data-lang="en">EN</button>
      <button class="lbtn" data-lang="mr">मराठी</button>
    </div>
  </div>
  <div class="user-row">
    <div class="uavatar"><?=strtoupper(substr($admin['full_name'],0,1))?></div>
    <div class="uinfo">
      <div class="uname"><?=htmlspecialchars($admin['full_name'])?></div>
      <div class="urole"><span data-t="role_admin"></span></div>
    </div>
    <a href="<?=$root?>logout.php" class="logout-btn" title="Logout"><i class="fa fa-sign-out-alt"></i></a>
  </div>
</aside>
