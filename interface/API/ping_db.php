<?php
require __DIR__.'/config.php';
$r = db()->query("SELECT DATABASE() db, NOW() t");
$row = $r ? $r->fetch_assoc() : null;
json_out(['ok'=>true,'db'=>$row['db']??null,'time'=>$row['t']??null]);
