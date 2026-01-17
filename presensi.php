<?php
require 'config.php';
check_role(['guru']);

$kelas_selected = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;
$tanggal = isset($_GET['tanggal']) ? $_GET['tanggal'] : date('Y-m-d');

// Handle penyimpanan absensi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'hadir_semua') {
        $kelas = (int)$_POST['kelas'];
        $tanggal = escape($_POST['tanggal']);
        
        // Hapus absensi hari ini dulu
        query("DELETE FROM absensi WHERE tanggal_absensi = '$tanggal' AND siswa_id IN (SELECT id FROM siswa WHERE kelas = $kelas)");
        
        // Ambil semua siswa di kelas
        $siswa = fetch_all(query("SELECT id FROM siswa WHERE kelas = $kelas"));
        
        // Insert absensi untuk semua siswa
        foreach ($siswa as $s) {
            $siswa_id = $s['id'];
            $user_id = $_SESSION['user_id'];
            query("INSERT INTO absensi (siswa_id, tanggal_absensi, status, created_by) VALUES ($siswa_id, '$tanggal', 'Hadir', $user_id)");
        }
        
        $success_msg = 'Semua siswa kelas ' . $kelas . ' berhasil diabsen pada ' . date('d M Y', strtotime($tanggal));
    } elseif ($action == 'simpan_absensi') {
        $kelas = (int)$_POST['kelas'];
        $tanggal = escape($_POST['tanggal']);
        
        // Hapus absensi hari ini dulu
        query("DELETE FROM absensi WHERE tanggal_absensi = '$tanggal' AND siswa_id IN (SELECT id FROM siswa WHERE kelas = $kelas)");
        
        // Insert absensi baru
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'siswa_') === 0) {
                $siswa_id = (int)str_replace('siswa_', '', $key);
                $status = escape($value);
                $user_id = $_SESSION['user_id'];
                query("INSERT INTO absensi (siswa_id, tanggal_absensi, status, created_by) VALUES ($siswa_id, '$tanggal', '$status', $user_id)");
            }
        }
        
        $success_msg = 'Absensi berhasil disimpan!';
    }
}

// Ambil siswa berdasarkan kelas yang dipilih
$siswa_list = [];
if ($kelas_selected > 0) {
    $result = query("SELECT * FROM siswa WHERE kelas = $kelas_selected ORDER BY nama_siswa ASC");
    $siswa_list = fetch_all($result);
    
    // Ambil data absensi yang sudah ada
    $absensi_hari_ini = [];
    if ($siswa_list) {
        $result = query("SELECT siswa_id, status FROM absensi WHERE tanggal_absensi = '$tanggal' AND siswa_id IN (" . implode(',', array_map(function($s) { return $s['id']; }, $siswa_list)) . ")");
        $absensi_data = fetch_all($result);
        foreach ($absensi_data as $a) {
            $absensi_hari_ini[$a['siswa_id']] = $a['status'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presensi - Sistem Absensi</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
            color: #333;
        }
        
        .main-container {
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 20px;
            overflow-y: auto;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 100;
            transform: translateX(0);
            transition: transform 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        
        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }
        
        .sidebar-logo {
            font-size: 24px;
        }
        
        .sidebar-title {
            font-size: 18px;
            font-weight: 700;
        }
        
        .toggle-btn {
            background: none;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
        }
        
        .sidebar-menu {
            list-style: none;
        }
        
        .sidebar-menu li {
            margin-bottom: 10px;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            color: white;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 14px;
        }
        
        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255,255,255,0.2);
            transform: translateX(5px);
        }
        
        .sidebar-menu a span:first-child {
            margin-right: 12px;
            font-size: 18px;
        }
        
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            transition: margin-left 0.3s ease;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 30px;
        }
        
        .filter-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .filter-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 15px;
        }
        
        .filter-group {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr auto;
            gap: 15px;
            align-items: flex-end;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }
        
        input[type="date"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        input[type="date"]:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.3);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .presensi-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .presensi-header {
            background: #f8fafc;
            padding: 20px 25px;
            border-bottom: 2px solid #e0e7ff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .presensi-header-title {
            font-size: 16px;
            font-weight: 700;
            color: #1e3a8a;
        }
        
        .presensi-info {
            font-size: 13px;
            color: #999;
        }
        
        .presensi-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-hadir-semua {
            background: #10b981;
            color: white;
            padding: 10px 15px;
        }
        
        .btn-hadir-semua:hover {
            background: #059669;
        }
        
        .presensi-list {
            padding: 25px;
        }
        
        .siswa-item {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #e0e7ff;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }
        
        .siswa-item:hover {
            background: #f9fafb;
        }
        
        .siswa-number {
            width: 35px;
            height: 35px;
            background: #e0e7ff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #1e3a8a;
            margin-right: 15px;
            font-size: 13px;
        }
        
        .siswa-info {
            flex: 1;
        }
        
        .siswa-name {
            font-weight: 600;
            color: #1e3a8a;
            font-size: 14px;
        }
        
        .siswa-nisn {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        
        .status-options {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .status-btn {
            padding: 8px 12px;
            border: 2px solid #e0e0e0;
            background: white;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            color: #333;
        }
        
        .status-btn:hover {
            border-color: #3b82f6;
        }
        
        .status-btn.hadir {
            background: #d1fae5;
            border-color: #10b981;
            color: #065f46;
        }
        
        .status-btn.izin {
            background: #dbeafe;
            border-color: #3b82f6;
            color: #1e3a8a;
        }
        
        .status-btn.alfa {
            background: #fee2e2;
            border-color: #f05252;
            color: #7f1d1d;
        }
        
        .status-btn.sakit {
            background: #fef3c7;
            border-color: #f59e0b;
            color: #92400e;
        }
        
        .status-btn.active {
            border-width: 3px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #999;
        }
        
        .presensi-footer {
            background: #f8fafc;
            padding: 20px 25px;
            border-top: 2px solid #e0e7ff;
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            padding: 12px 30px;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.3);
        }
        
        .btn-reset {
            background: #f0f0f0;
            color: #333;
            padding: 12px 30px;
        }
        
        .menu-toggle {
            display: none;
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1e3a8a;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            z-index: 1001;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                z-index: 999;
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
                padding-top: 60px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .page-title {
                font-size: 22px;
            }
            
            .filter-group {
                grid-template-columns: 1fr;
            }
            
            .presensi-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .siswa-item {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .status-options {
                width: 100%;
                margin-top: 10px;
            }
            
            .presensi-footer {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="main-container">
        <button class="menu-toggle" id="menuToggle">☰</button>
        
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div>
                    <div class="sidebar-logo">📚</div>
                    <div class="sidebar-title">Absensi</div>
                </div>
                <button class="toggle-btn" id="sidebarToggle">✕</button>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="index.php"><span>📊</span> Dashboard</a></li>
                <li><a href="tambah-siswa.php"><span>➕</span> Tambah Siswa</a></li>
                <li><a href="presensi.php" class="active"><span>✓</span> Presensi</a></li>
                <li><a href="laporan.php"><span>📄</span> Laporan</a></li>
                <li><a href="logout.php" style="color: #fca5a5; margin-top: 20px;"><span>🚪</span> Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content" id="mainContent">
            <h1 class="page-title">Input Presensi Siswa</h1>
            
            <?php if (isset($success_msg)): ?>
                <div class="success-msg">✓ <?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <!-- Filter Card -->
            <div class="filter-card">
                <div class="filter-title">Pilih Kelas dan Tanggal</div>
                <div class="filter-group">
                    <div>
                        <label>Kelas</label>
                        <select id="kelasSelect" onchange="updateForm()">
                            <option value="">-- Pilih Kelas --</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $kelas_selected == $i ? 'selected' : ''; ?>>Kelas <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label>Tanggal Absensi</label>
                        <input type="date" id="tanggalInput" value="<?php echo $tanggal; ?>" onchange="updateForm()">
                    </div>
                    
                    <div></div>
                    
                    <button class="btn btn-primary" onclick="updateForm()">🔍 Tampilkan</button>
                </div>
            </div>
            
            <!-- Presensi Form -->
            <?php if ($kelas_selected > 0 && count($siswa_list) > 0): ?>
                <form method="POST" id="presensiForm">
                    <div class="presensi-container">
                        <div class="presensi-header">
                            <div>
                                <div class="presensi-header-title">Absensi Kelas <?php echo $kelas_selected; ?></div>
                                <div class="presensi-info">Tanggal: <?php echo date('d M Y', strtotime($tanggal)); ?> | Total Siswa: <?php echo count($siswa_list); ?></div>
                            </div>
                            <div class="presensi-actions">
                                <button type="button" class="btn btn-hadir-semua" onclick="hAdirSemua()">✓ Hadir Semua</button>
                            </div>
                        </div>
                        
                        <div class="presensi-list">
                            <?php foreach ($siswa_list as $index => $siswa): 
                                $current_status = isset($absensi_hari_ini[$siswa['id']]) ? $absensi_hari_ini[$siswa['id']] : '';
                            ?>
                                <div class="siswa-item">
                                    <div class="siswa-number"><?php echo $index + 1; ?></div>
                                    <div class="siswa-info">
                                        <div class="siswa-name"><?php echo htmlspecialchars($siswa['nama_siswa']); ?></div>
                                        <div class="siswa-nisn">NISN: <?php echo htmlspecialchars($siswa['nisn']); ?></div>
                                    </div>
                                    <div class="status-options">
                                        <button type="button" class="status-btn hadir <?php echo $current_status == 'Hadir' ? 'active' : ''; ?>" onclick="setStatus(this, 'Hadir', <?php echo $siswa['id']; ?>)">Hadir</button>
                                        <button type="button" class="status-btn izin <?php echo $current_status == 'Izin' ? 'active' : ''; ?>" onclick="setStatus(this, 'Izin', <?php echo $siswa['id']; ?>)">Izin</button>
                                        <button type="button" class="status-btn alfa <?php echo $current_status == 'Alfa' ? 'active' : ''; ?>" onclick="setStatus(this, 'Alfa', <?php echo $siswa['id']; ?>)">Alfa</button>
                                        <button type="button" class="status-btn sakit <?php echo $current_status == 'Sakit' ? 'active' : ''; ?>" onclick="setStatus(this, 'Sakit', <?php echo $siswa['id']; ?>)">Sakit</button>
                                        <input type="hidden" name="siswa_<?php echo $siswa['id']; ?>" value="<?php echo $current_status; ?>">
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="presensi-footer">
                            <button type="button" class="btn btn-reset" onclick="resetForm()">↻ Reset</button>
                            <button type="submit" class="btn btn-save">💾 Simpan Absensi</button>
                        </div>
                    </div>
                    
                    <input type="hidden" name="action" value="simpan_absensi">
                    <input type="hidden" name="kelas" value="<?php echo $kelas_selected; ?>">
                    <input type="hidden" name="tanggal" value="<?php echo $tanggal; ?>">
                </form>
            <?php elseif ($kelas_selected > 0): ?>
                <div class="presensi-container">
                    <div class="empty-state">
                        <p>Belum ada siswa di kelas ini</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="presensi-container">
                    <div class="empty-state">
                        <p>Silakan pilih kelas dan tanggal terlebih dahulu</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mainContent = document.getElementById('mainContent');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.remove('collapsed');
        });
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
        });
        
        mainContent.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.add('collapsed');
            }
        });
        
        function updateForm() {
            const kelas = document.getElementById('kelasSelect').value;
            const tanggal = document.getElementById('tanggalInput').value;
            if (kelas) {
                window.location.href = '?kelas=' + kelas + '&tanggal=' + tanggal;
            }
        }
        
        function setStatus(btn, status, siswaId) {
            // Hapus class active dari semua button di container ini
            const parent = btn.parentElement;
            parent.querySelectorAll('.status-btn').forEach(b => b.classList.remove('active'));
            
            // Tambah class active ke button yang diklik
            btn.classList.add('active');
            
            // Set value di hidden input
            const input = parent.querySelector(`input[name="siswa_${siswaId}"]`);
            input.value = status;
        }
        
        function hAdirSemua() {
            if (confirm('Absen semua siswa kelas ini sebagai HADIR?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="hadir_semua">
                    <input type="hidden" name="kelas" value="${document.querySelector('input[name="kelas"]').value}">
                    <input type="hidden" name="tanggal" value="${document.querySelector('input[name="tanggal"]').value}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function resetForm() {
            if (confirm('Reset semua pilihan status?')) {
                document.getElementById('presensiForm').reset();
                document.querySelectorAll('.status-btn.active').forEach(btn => {
                    btn.classList.remove('active');
                });
                document.querySelectorAll('input[type="hidden"][name^="siswa_"]').forEach(input => {
                    input.value = '';
                });
            }
        }
    </script>
</body>
</html>