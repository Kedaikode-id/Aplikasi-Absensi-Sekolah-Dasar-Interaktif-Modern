<?php
require 'config.php';
check_role(['guru']);

$search = isset($_GET['search']) ? escape($_GET['search']) : '';
$kelas_filter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// Handle Tambah/Edit Siswa
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $nisn = escape($_POST['nisn']);
    $nama_siswa = escape($_POST['nama_siswa']);
    $jenis_kelamin = escape($_POST['jenis_kelamin']);
    $kelas = (int)$_POST['kelas'];
    
    if ($action == 'tambah') {
        $result = query("INSERT INTO siswa (nisn, nama_siswa, jenis_kelamin, kelas) VALUES ('$nisn', '$nama_siswa', '$jenis_kelamin', $kelas)");
        if ($result) {
            $success_msg = 'Siswa berhasil ditambahkan!';
        }
    } elseif ($action == 'edit') {
        $siswa_id = (int)$_POST['siswa_id'];
        $result = query("UPDATE siswa SET nisn='$nisn', nama_siswa='$nama_siswa', jenis_kelamin='$jenis_kelamin', kelas=$kelas WHERE id=$siswa_id");
        if ($result) {
            $success_msg = 'Siswa berhasil diperbarui!';
        }
    }
}

// Handle Hapus Siswa
if (isset($_GET['delete'])) {
    $siswa_id = (int)$_GET['delete'];
    query("DELETE FROM siswa WHERE id=$siswa_id");
    header("Location: tambah-siswa.php");
    exit();
}

// Ambil data siswa
$where = "WHERE 1=1";
if (!empty($search)) {
    $where .= " AND (nama_siswa LIKE '%$search%' OR nisn LIKE '%$search%')";
}
if ($kelas_filter > 0) {
    $where .= " AND kelas = $kelas_filter";
}

$result = query("SELECT * FROM siswa $where ORDER BY kelas ASC, nama_siswa ASC");
$siswa_list = fetch_all($result);

// Ambil data siswa yang akan diedit
$edit_siswa = null;
if (isset($_GET['edit'])) {
    $siswa_id = (int)$_GET['edit'];
    $result = query("SELECT * FROM siswa WHERE id=$siswa_id");
    $edit_siswa = fetch_row($result);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Siswa - Sistem Absensi</title>
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
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 30px;
        }
        
        .content-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .form-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .form-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e7ff;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }
        
        input[type="text"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        input[type="text"]:focus,
        select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .form-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.3s ease;
            flex: 1;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 58, 138, 0.3);
        }
        
        .btn-secondary {
            background: #f0f0f0;
            color: #333;
        }
        
        .btn-secondary:hover {
            background: #e0e0e0;
        }
        
        .siswa-list {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .list-header {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e7ff;
        }
        
        .search-filter {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        
        .search-filter input,
        .search-filter select {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            flex: 1;
            min-width: 150px;
        }
        
        .search-filter button {
            padding: 10px 20px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }
        
        .siswa-item {
            background: #f9fafb;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 3px solid #3b82f6;
        }
        
        .siswa-info {
            flex: 1;
        }
        
        .siswa-name {
            font-weight: 600;
            color: #1e3a8a;
            font-size: 14px;
        }
        
        .siswa-details {
            font-size: 12px;
            color: #999;
            margin-top: 3px;
        }
        
        .siswa-actions {
            display: flex;
            gap: 8px;
        }
        
        .btn-sm {
            padding: 6px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-edit {
            background: #3b82f6;
            color: white;
        }
        
        .btn-edit:hover {
            background: #2563eb;
        }
        
        .btn-delete {
            background: #f05252;
            color: white;
        }
        
        .btn-delete:hover {
            background: #d93d3d;
        }
        
        .success-msg {
            background: #d1fae5;
            color: #065f46;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #999;
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
            z-index: 1000;
        }
        
        @media (max-width: 1024px) {
            .content-wrapper {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                z-index: 999;
                transform: translateX(-100%);
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
            
            .search-filter {
                flex-direction: column;
            }
            
            .search-filter input,
            .search-filter select {
                min-width: 100%;
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
                <li><a href="tambah-siswa.php" class="active"><span>➕</span> Tambah Siswa</a></li>
                <li><a href="presensi.php"><span>✓</span> Presensi</a></li>
                <li><a href="laporan.php"><span>📄</span> Laporan</a></li>
                <li><a href="logout.php" style="color: #fca5a5; margin-top: 20px;"><span>🚪</span> Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content" id="mainContent">
            <h1 class="page-title">Kelola Data Siswa</h1>
            
            <?php if (isset($success_msg)): ?>
                <div class="success-msg">✓ <?php echo $success_msg; ?></div>
            <?php endif; ?>
            
            <div class="content-wrapper">
                <!-- Form -->
                <div class="form-card">
                    <div class="form-title"><?php echo $edit_siswa ? 'Edit Siswa' : 'Tambah Siswa Baru'; ?></div>
                    <form method="POST">
                        <input type="hidden" name="action" value="<?php echo $edit_siswa ? 'edit' : 'tambah'; ?>">
                        <?php if ($edit_siswa): ?>
                            <input type="hidden" name="siswa_id" value="<?php echo $edit_siswa['id']; ?>">
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label>NISN</label>
                            <input type="text" name="nisn" value="<?php echo $edit_siswa ? htmlspecialchars($edit_siswa['nisn']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nama Siswa</label>
                            <input type="text" name="nama_siswa" value="<?php echo $edit_siswa ? htmlspecialchars($edit_siswa['nama_siswa']) : ''; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Jenis Kelamin</label>
                            <select name="jenis_kelamin" required>
                                <option value="">-- Pilih --</option>
                                <option value="Laki-laki" <?php echo $edit_siswa && $edit_siswa['jenis_kelamin'] == 'Laki-laki' ? 'selected' : ''; ?>>Laki-laki</option>
                                <option value="Perempuan" <?php echo $edit_siswa && $edit_siswa['jenis_kelamin'] == 'Perempuan' ? 'selected' : ''; ?>>Perempuan</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Pilih Kelas</label>
                            <select name="kelas" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php for ($i = 1; $i <= 6; $i++): ?>
                                    <option value="<?php echo $i; ?>" <?php echo $edit_siswa && $edit_siswa['kelas'] == $i ? 'selected' : ''; ?>>Kelas <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        
                        <div class="form-buttons">
                            <button type="submit" class="btn btn-primary">
                                <?php echo $edit_siswa ? '💾 Perbarui' : '➕ Tambah'; ?>
                            </button>
                            <?php if ($edit_siswa): ?>
                                <a href="tambah-siswa.php" class="btn btn-secondary" style="text-decoration: none; display: flex; align-items: center; justify-content: center;">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
                
                <!-- Daftar Siswa -->
                <div class="siswa-list">
                    <div class="list-header">Daftar Siswa</div>
                    
                    <div class="search-filter">
                        <input type="text" id="searchInput" placeholder="Cari nama/NISN...">
                        <select id="kelasFilter">
                            <option value="">Semua Kelas</option>
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo $kelas_filter == $i ? 'selected' : ''; ?>>Kelas <?php echo $i; ?></option>
                            <?php endfor; ?>
                        </select>
                        <button onclick="applyFilter()">🔍 Cari</button>
                    </div>
                    
                    <?php if (count($siswa_list) > 0): ?>
                        <?php foreach ($siswa_list as $siswa): ?>
                            <div class="siswa-item">
                                <div class="siswa-info">
                                    <div class="siswa-name"><?php echo htmlspecialchars($siswa['nama_siswa']); ?></div>
                                    <div class="siswa-details">NISN: <?php echo htmlspecialchars($siswa['nisn']); ?> | Kelas <?php echo $siswa['kelas']; ?> | <?php echo $siswa['jenis_kelamin']; ?></div>
                                </div>
                                <div class="siswa-actions">
                                    <a href="?edit=<?php echo $siswa['id']; ?>" class="btn-sm btn-edit">✏️ Edit</a>
                                    <a href="?delete=<?php echo $siswa['id']; ?>" class="btn-sm btn-delete" onclick="return confirm('Yakin hapus siswa ini?');">🗑️ Hapus</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>Belum ada data siswa</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
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
        
        function applyFilter() {
            const search = document.getElementById('searchInput').value;
            const kelas = document.getElementById('kelasFilter').value;
            let url = 'tambah-siswa.php';
            if (search || kelas) {
                url += '?';
                if (search) url += 'search=' + encodeURIComponent(search);
                if (search && kelas) url += '&';
                if (kelas) url += 'kelas=' + kelas;
            }
            window.location.href = url;
        }
        
        // Enter untuk cari
        document.getElementById('searchInput').addEventListener('keypress', (e) => {
            if (e.key === 'Enter') applyFilter();
        });
    </script>
</body>
</html>