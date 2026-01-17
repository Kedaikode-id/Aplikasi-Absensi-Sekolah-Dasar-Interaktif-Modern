<?php
require 'config.php';
check_role(['guru', 'kepala_sekolah']);

// Ambil data dashboard
$total_siswa_per_kelas = [];
for ($i = 1; $i <= 6; $i++) {
    $result = query("SELECT COUNT(*) as total FROM siswa WHERE kelas = $i");
    $row = fetch_row($result);
    $total_siswa_per_kelas[$i] = $row ? $row['total'] : 0;
}

// Ambil data hari libur yang akan datang
$result_libur = query("SELECT * FROM hari_libur WHERE tanggal_mulai >= CURDATE() ORDER BY tanggal_mulai LIMIT 3");
$hari_libur = fetch_all($result_libur);

// Ambil siswa dengan kehadiran rendah (kurang dari 70%)
$sql_absensi = "
    SELECT 
        s.id, 
        s.nama_siswa, 
        s.kelas,
        COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) as total_hadir,
        COUNT(a.id) as total_hari,
        ROUND((COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) / COUNT(a.id) * 100), 1) as persen_hadir
    FROM siswa s
    LEFT JOIN absensi a ON s.id = a.siswa_id
    GROUP BY s.id, s.nama_siswa, s.kelas
    HAVING COUNT(a.id) > 0 AND (COUNT(CASE WHEN a.status = 'Hadir' THEN 1 END) / COUNT(a.id) * 100) < 70
    ORDER BY persen_hadir ASC
";
$hasil_absensi_rendah = query($sql_absensi);
$siswa_jarang_hadir = fetch_all($hasil_absensi_rendah);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistem Absensi</title>
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
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }
        
        .sidebar.collapsed {
            width: 0;
            padding: 0;
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
            display: none;
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
            transition: all 0.3s ease;
        }
        
        .main-content.full {
            margin-left: 0;
        }
        
        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            background: white;
            padding: 10px 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 18px;
        }
        
        .user-details {
            font-size: 13px;
        }
        
        .user-name {
            font-weight: 600;
            color: #333;
        }
        
        .user-role {
            color: #999;
            font-size: 12px;
        }
        
        .logout-btn {
            background: #f05252;
            color: white;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: #d93d3d;
        }
        
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #3b82f6;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }
        
        .card-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .card-title {
            font-size: 13px;
            color: #999;
            margin-bottom: 8px;
            text-transform: uppercase;
            font-weight: 600;
        }
        
        .card-value {
            font-size: 28px;
            font-weight: 700;
            color: #1e3a8a;
        }
        
        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: #1e3a8a;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e7ff;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e0e7ff;
        }
        
        table th {
            padding: 15px;
            text-align: left;
            font-weight: 600;
            color: #1e3a8a;
            font-size: 13px;
            text-transform: uppercase;
        }
        
        table td {
            padding: 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
        }
        
        table tbody tr:hover {
            background: #f9fafb;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
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
        
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                width: 100%;
                height: 100%;
                z-index: 999;
            }
            
            .sidebar.collapsed {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .menu-toggle {
                display: block;
            }
            
            .toggle-btn {
                display: block;
            }
            
            .cards-grid {
                grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
                gap: 15px;
            }
            
            .page-title {
                font-size: 22px;
            }
            
            .content-header {
                margin-bottom: 20px;
            }
            
            .user-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
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
                <li><a href="index.php" class="active"><span>📊</span> Dashboard</a></li>
                
                <?php if ($_SESSION['role'] == 'guru'): ?>
                    <li><a href="tambah-siswa.php"><span>➕</span> Tambah Siswa</a></li>
                    <li><a href="presensi.php"><span>✓</span> Presensi</a></li>
                    <li><a href="laporan.php"><span>📄</span> Laporan</a></li>
                <?php endif; ?>
                
                <?php if ($_SESSION['role'] == 'kepala_sekolah'): ?>
                    <li><a href="laporan.php"><span>📄</span> Laporan</a></li>
                <?php endif; ?>
                
                <li style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.2);">
                    <a href="logout.php" style="color: #fca5a5;"><span>🚪</span> Logout</a>
                </li>
            </ul>
        </aside>
        
        <main class="main-content" id="mainContent">
            <div class="content-header">
                <h1 class="page-title">Dashboard</h1>
                <div class="user-info">
                    <div class="user-avatar"><?php echo strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?></div>
                    <div class="user-details">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                        <div class="user-role">
                            <?php 
                            if ($_SESSION['role'] == 'guru') {
                                echo '👨‍🏫 Guru';
                            } else {
                                echo '👔 Kepala Sekolah';
                            }
                            ?>
                        </div>
                    </div>
                    <form method="POST" action="logout.php" style="margin: 0; margin-left: 20px;">
                        <button type="submit" class="logout-btn">Logout</button>
                    </form>
                </div>
            </div>
            
            <!-- Peringatan Libur -->
            <?php if (count($hari_libur) > 0): ?>
                <div style="background: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; border-radius: 8px; margin-bottom: 30px;">
                    <strong style="color: #92400e;">⚠️ Peringatan Hari Libur</strong>
                    <p style="color: #92400e; font-size: 14px; margin-top: 5px;">
                        <?php 
                        foreach ($hari_libur as $libur) {
                            echo $libur['nama_libur'] . ' (' . date('d M Y', strtotime($libur['tanggal_mulai'])) . ' - ' . date('d M Y', strtotime($libur['tanggal_selesai'])) . ')<br>';
                        }
                        ?>
                    </p>
                </div>
            <?php endif; ?>
            
            <!-- Cards Jumlah Siswa Per Kelas -->
            <div style="margin-bottom: 30px;">
                <h2 class="section-title">Jumlah Siswa Per Kelas</h2>
                <div class="cards-grid">
                    <?php for ($i = 1; $i <= 6; $i++): ?>
                        <div class="card">
                            <div class="card-icon">📚</div>
                            <div class="card-title">Kelas <?php echo $i; ?></div>
                            <div class="card-value"><?php echo $total_siswa_per_kelas[$i]; ?></div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
            
            <!-- Tabel Siswa Jarang Hadir -->
            <?php if (count($siswa_jarang_hadir) > 0): ?>
                <div>
                    <h2 class="section-title">⚠️ Peringatan: Siswa Jarang Sekolah</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Siswa</th>
                                    <th>Kelas</th>
                                    <th>Kehadiran</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siswa_jarang_hadir as $siswa): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($siswa['nama_siswa']); ?></td>
                                        <td><?php echo $siswa['kelas']; ?></td>
                                        <td><?php echo $siswa['total_hadir']; ?>/<?php echo $siswa['total_hari']; ?></td>
                                        <td>
                                            <span style="color: #f05252; font-weight: 600;">
                                                <?php echo $siswa['persen_hadir']; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="empty-state">
                        <p style="font-size: 16px; margin-bottom: 10px;">✓ Semua siswa memiliki kehadiran yang baik!</p>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.getElementById('mainContent');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        menuToggle.addEventListener('click', () => {
            sidebar.classList.remove('collapsed');
        });
        
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.add('collapsed');
        });
        
        // Close sidebar saat klik di main content
        mainContent.addEventListener('click', () => {
            if (window.innerWidth < 768) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
</body>
</html>