<?php
require 'config.php';
check_role(['guru', 'kepala_sekolah']);

$kelas_filter = isset($_GET['kelas']) ? (int)$_GET['kelas'] : 0;

// Handle export Excel
if (isset($_GET['export']) && $_GET['export'] == 'excel' && $kelas_filter > 0) {
    // Ambil data laporan
    $siswa = fetch_all(query("SELECT id, nisn, nama_siswa FROM siswa WHERE kelas = $kelas_filter ORDER BY nama_siswa ASC"));
    
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="laporan_absensi_kelas_' . $kelas_filter . '_' . date('Y-m-d') . '.xls"');
    
    echo "Laporan Absensi Kelas $kelas_filter\n";
    echo "Tanggal: " . date('d M Y') . "\n\n";
    
    echo "Nama Siswa\tNISN\t";
    // Ambil semua tanggal dengan absensi
    $dates = fetch_all(query("SELECT DISTINCT tanggal_absensi FROM absensi WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas = $kelas_filter) ORDER BY tanggal_absensi ASC"));
    foreach ($dates as $date) {
        echo date('d M Y', strtotime($date['tanggal_absensi'])) . "\t";
    }
    echo "H\tI\tA\tS\n";
    
    // Detail siswa
    foreach ($siswa as $s) {
        echo htmlspecialchars($s['nama_siswa']) . "\t" . htmlspecialchars($s['nisn']) . "\t";
        
        $hadir = 0;
        $izin = 0;
        $alfa = 0;
        $sakit = 0;
        
        foreach ($dates as $date) {
            $result = query("SELECT status FROM absensi WHERE siswa_id = " . $s['id'] . " AND tanggal_absensi = '" . $date['tanggal_absensi'] . "'");
            if ($result->num_rows > 0) {
                $absensi = fetch_row($result);
                $status = $absensi['status'];
                echo $status[0] . "\t";
                
                if ($status == 'Hadir') $hadir++;
                elseif ($status == 'Izin') $izin++;
                elseif ($status == 'Alfa') $alfa++;
                elseif ($status == 'Sakit') $sakit++;
            } else {
                echo "-\t";
            }
        }
        
        echo "$hadir\t$izin\t$alfa\t$sakit\n";
    }
    
    exit();
}

// Ambil data untuk ditampilkan
$siswa = [];
$laporan = [];

if ($kelas_filter > 0) {
    $siswa = fetch_all(query("SELECT id, nisn, nama_siswa FROM siswa WHERE kelas = $kelas_filter ORDER BY nama_siswa ASC"));
    
    // Ambil semua tanggal dengan absensi
    $dates = fetch_all(query("SELECT DISTINCT tanggal_absensi FROM absensi WHERE siswa_id IN (SELECT id FROM siswa WHERE kelas = $kelas_filter) ORDER BY tanggal_absensi ASC"));
    
    foreach ($siswa as $s) {
        $hadir = 0;
        $izin = 0;
        $alfa = 0;
        $sakit = 0;
        $data_harian = [];
        
        foreach ($dates as $date) {
            $result = query("SELECT status FROM absensi WHERE siswa_id = " . $s['id'] . " AND tanggal_absensi = '" . $date['tanggal_absensi'] . "'");
            if ($result->num_rows > 0) {
                $absensi = fetch_row($result);
                $status = $absensi['status'];
                $data_harian[$date['tanggal_absensi']] = $status;
                
                if ($status == 'Hadir') $hadir++;
                elseif ($status == 'Izin') $izin++;
                elseif ($status == 'Alfa') $alfa++;
                elseif ($status == 'Sakit') $sakit++;
            } else {
                $data_harian[$date['tanggal_absensi']] = '-';
            }
        }
        
        $laporan[] = [
            'nama_siswa' => $s['nama_siswa'],
            'nisn' => $s['nisn'],
            'hadir' => $hadir,
            'izin' => $izin,
            'alfa' => $alfa,
            'sakit' => $sakit,
            'data_harian' => $data_harian
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Sistem Absensi</title>
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
            display: flex;
            gap: 15px;
            align-items: flex-end;
            flex-wrap: wrap;
        }
        
        .filter-group {
            display: flex;
            flex-direction: column;
        }
        
        label {
            margin-bottom: 6px;
            color: #333;
            font-weight: 600;
            font-size: 13px;
        }
        
        select {
            padding: 10px 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            transition: all 0.3s ease;
            min-width: 200px;
        }
        
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
        
        .btn-excel {
            background: #10b981;
            color: white;
        }
        
        .btn-excel:hover {
            background: #059669;
        }
        
        .table-container {
            background: white;
            border-radius: 15px;
            overflow-x: auto;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        
        table thead {
            background: #f8fafc;
            border-bottom: 2px solid #e0e7ff;
        }
        
        table th {
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            color: #1e3a8a;
            font-size: 12px;
            text-transform: uppercase;
            border-right: 1px solid #e0e7ff;
        }
        
        table td {
            padding: 12px 15px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 13px;
            border-right: 1px solid #f0f0f0;
        }
        
        table tbody tr:hover {
            background: #f9fafb;
        }
        
        .status-h {
            background: #d1fae5;
            color: #065f46;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
        }
        
        .status-i {
            background: #dbeafe;
            color: #1e3a8a;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
        }
        
        .status-a {
            background: #fee2e2;
            color: #7f1d1d;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
        }
        
        .status-s {
            background: #fef3c7;
            color: #92400e;
            padding: 4px 8px;
            border-radius: 4px;
            text-align: center;
            font-weight: 600;
        }
        
        .status-dash {
            color: #999;
            text-align: center;
        }
        
        .summary-row {
            background: #f8fafc;
            font-weight: 600;
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
            
            .filter-card {
                flex-direction: column;
                align-items: stretch;
            }
            
            .filter-group {
                width: 100%;
            }
            
            select {
                min-width: 100%;
            }
            
            .btn {
                width: 100%;
            }
            
            table {
                font-size: 12px;
            }
            
            table th,
            table td {
                padding: 8px 10px;
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
                
                <?php if ($_SESSION['role'] == 'guru'): ?>
                    <li><a href="tambah-siswa.php"><span>➕</span> Tambah Siswa</a></li>
                    <li><a href="presensi.php"><span>✓</span> Presensi</a></li>
                <?php endif; ?>
                
                <li><a href="laporan.php" class="active"><span>📄</span> Laporan</a></li>
                <li><a href="logout.php" style="color: #fca5a5; margin-top: 20px;"><span>🚪</span> Logout</a></li>
            </ul>
        </aside>
        
        <main class="main-content" id="mainContent">
            <h1 class="page-title">Laporan Absensi</h1>
            
            <!-- Filter Card -->
            <div class="filter-card">
                <div class="filter-group">
                    <label>Pilih Kelas</label>
                    <select id="kelasSelect" onchange="filterLaporan()">
                        <option value="">-- Semua Kelas --</option>
                        <?php for ($i = 1; $i <= 6; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo $kelas_filter == $i ? 'selected' : ''; ?>>Kelas <?php echo $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <?php if ($kelas_filter > 0): ?>
                    <a href="?kelas=<?php echo $kelas_filter; ?>&export=excel" class="btn btn-excel">📥 Export Excel</a>
                <?php endif; ?>
            </div>
            
            <!-- Tabel Laporan -->
            <?php if ($kelas_filter > 0 && count($laporan) > 0): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Nama Siswa</th>
                                <th>NISN</th>
                                <?php 
                                // Tampilkan header tanggal
                                if (!empty($laporan[0]['data_harian'])) {
                                    foreach (array_keys($laporan[0]['data_harian']) as $date) {
                                        echo '<th>' . date('d M', strtotime($date)) . '</th>';
                                    }
                                }
                                ?>
                                <th style="background: #e0e7ff;">H</th>
                                <th style="background: #e0e7ff;">I</th>
                                <th style="background: #e0e7ff;">A</th>
                                <th style="background: #e0e7ff;">S</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($laporan as $item): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($item['nama_siswa']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['nisn']); ?></td>
                                    <?php 
                                    foreach ($item['data_harian'] as $status) {
                                        if ($status == 'Hadir') {
                                            echo '<td><span class="status-h">H</span></td>';
                                        } elseif ($status == 'Izin') {
                                            echo '<td><span class="status-i">I</span></td>';
                                        } elseif ($status == 'Alfa') {
                                            echo '<td><span class="status-a">A</span></td>';
                                        } elseif ($status == 'Sakit') {
                                            echo '<td><span class="status-s">S</span></td>';
                                        } else {
                                            echo '<td><span class="status-dash">-</span></td>';
                                        }
                                    }
                                    ?>
                                    <td style="background: #e0e7ff;"><strong><?php echo $item['hadir']; ?></strong></td>
                                    <td style="background: #e0e7ff;"><strong><?php echo $item['izin']; ?></strong></td>
                                    <td style="background: #e0e7ff;"><strong><?php echo $item['alfa']; ?></strong></td>
                                    <td style="background: #e0e7ff;"><strong><?php echo $item['sakit']; ?></strong></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php elseif ($kelas_filter > 0): ?>
                <div class="table-container">
                    <div class="empty-state">
                        <p>Belum ada data absensi untuk kelas ini</p>
                    </div>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <div class="empty-state">
                        <p>Silakan pilih kelas terlebih dahulu untuk melihat laporan</p>
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
        
        function filterLaporan() {
            const kelas = document.getElementById('kelasSelect').value;
            window.location.href = 'laporan.php' + (kelas ? '?kelas=' + kelas : '');
        }
    </script>
</body>
</html>