<?php
header('Content-Type: application/json; charset=utf-8');
require __DIR__.'/config.php';
function out($a,$code=200){http_response_code($code);echo json_encode($a);exit;}
function body(){return json_decode(file_get_contents('php://input'),true)??[];}
function clean($s){return preg_replace('/\D/','',(string)$s);}
function getroom($db,$code){$q=$db->prepare('SELECT * FROM ludo_rooms WHERE room_code=?');$q->execute([$code]);$r=$q->fetch(PDO::FETCH_ASSOC);if(!$r)out(['ok'=>false,'error'=>'रूम नहीं मिला'],404);return $r;}
function unpackstate($r){return json_decode($r['game_state'],true);}
function savegame($db,$code,$s){$q=$db->prepare('UPDATE ludo_rooms SET game_state=?,updated_at=NOW() WHERE room_code=?');$q->execute([json_encode($s),$code]);}
try{
$db=new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8mb4",DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
$in=body();$action=$_GET['action']??'';
if($action==='create'){
 do{$code=(string)random_int(100000,999999);$q=$db->prepare('SELECT id FROM ludo_rooms WHERE room_code=?');$q->execute([$code]);}while($q->fetch());
 $s=['players'=>[['tokens'=>[-1,-1,-1,-1]]],'turn'=>0,'dice'=>null,'last_roll'=>null,'winner'=>null];
 $q=$db->prepare('INSERT INTO ludo_rooms(room_code,game_state,created_at,updated_at) VALUES(?,?,NOW(),NOW())');$q->execute([$code,json_encode($s)]);
 out(['ok'=>true,'room'=>$code,'player'=>0,'state'=>$s]);
}
$code=clean($in['room']??'');if(strlen($code)!==6)out(['ok'=>false,'error'=>'गलत रूम कोड'],400);
$r=getroom($db,$code);$s=unpackstate($r);
if($action==='join'){
 if(count($s['players'])>=2)out(['ok'=>false,'error'=>'यह रूम पहले से भरा हुआ है'],409);
 $s['players'][]=['tokens'=>[-1,-1,-1,-1]];savegame($db,$code,$s);out(['ok'=>true,'room'=>$code,'player'=>1,'state'=>$s]);
}
$p=(int)($in['player']??-1);if(!in_array($p,[0,1],true)||!isset($s['players'][$p]))out(['ok'=>false,'error'=>'खिलाड़ी की जानकारी गलत है'],403);
if($action==='state')out(['ok'=>true,'state'=>$s]);
if($action==='roll'){
 if(count($s['players'])!==2)out(['ok'=>false,'error'=>'दूसरे खिलाड़ी के जुड़ने का इंतज़ार करें']);
 if($s['turn']!==$p||$s['dice']!==null)out(['ok'=>false,'error'=>'अभी आपकी बारी नहीं है']);
 $d=random_int(1,6);$s['dice']=$d;$s['last_roll']=$d;
 savegame($db,$code,$s);out(['ok'=>true,'roll'=>$d,'state'=>$s]);
}
if($action==='move'){
 $t=(int)($in['token']??-1);if($t<0||$t>3||$s['turn']!==$p||$s['dice']===null)out(['ok'=>false,'error'=>'अभी गोटी नहीं चला सकते']);
 $v=$s['players'][$p]['tokens'][$t];$d=$s['dice'];
 if($v<0&&$d!==6)out(['ok'=>false,'error'=>'गोटी निकालने के लिए 6 चाहिए']);
 $s['players'][$p]['tokens'][$t]=$v<0?0:$v+$d;
 if($s['players'][$p]['tokens'][$t]>=57){$s['players'][$p]['tokens'][$t]=57;}
 $s['dice']=null;if($d!==6)$s['turn']=1-$p;
 if(count(array_filter($s['players'][$p]['tokens'],fn($x)=>$x>=57))===4)$s['winner']=$p;
 savegame($db,$code,$s);out(['ok'=>true,'message'=>'चाल चल दी गई।','state'=>$s]);
}
out(['ok'=>false,'error'=>'अमान्य अनुरोध'],400);
}catch(Throwable $e){out(['ok'=>false,'error'=>'सर्वर/डेटाबेस त्रुटि। config.php और SQL setup जाँचें।'],500);}
