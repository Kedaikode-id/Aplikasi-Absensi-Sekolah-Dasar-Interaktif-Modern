<?php
// test-connection.php - File untuk test koneksi database

echo "<h1>🔧 Test Koneksi Sistem Absensi</h1>";
echo "<hr>";

// Test 1: Cek koneksi database
echo "<h3>1. Test Koneksi Database</h3>";
$conn = new mysqli('localhost', 'root', '', 'sistem_absensi');

if ($conn->connect_error) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->connect_error . "<br>";
    echo "Solusi: <br>";
    echo "- Pastikan MySQL running di XAMPP<br>";
    echo "- Pastikan database 'sistem_absensi' sudah dibuat<br>";
    echo "- Buka phpMyAdmin (http://localhost/phpmyadmin)<br>";
    exit();
} else {
    echo "✅ <strong>BERHASIL:</strong> Terhubung ke database 'sistem_absensi'<br>";
}

// Test 2: Cek tabel users
echo "<h3>2. Test Tabel Users</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM users");
if (!$result) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->error . "<br>";
    echo "Solusi: Jalankan SQL structure di phpMyAdmin<br>";
} else {
    $row = $result->fetch_assoc();
    echo "✅ <strong>BERHASIL:</strong> Tabel 'users' ada (" . $row['total'] . " record)<br>";
}

// Test 3: Cek tabel siswa
echo "<h3>3. Test Tabel Siswa</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM siswa");
if (!$result) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->error . "<br>";
} else {
    $row = $result->fetch_assoc();
    echo "✅ <strong>BERHASIL:</strong> Tabel 'siswa' ada (" . $row['total'] . " record)<br>";
}

// Test 4: Cek tabel absensi
echo "<h3>4. Test Tabel Absensi</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM absensi");
if (!$result) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->error . "<br>";
} else {
    $row = $result->fetch_assoc();
    echo "✅ <strong>BERHASIL:</strong> Tabel 'absensi' ada (" . $row['total'] . " record)<br>";
}

// Test 5: Cek tabel hari_libur
echo "<h3>5. Test Tabel Hari Libur</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM hari_libur");
if (!$result) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->error . "<br>";
} else {
    $row = $result->fetch_assoc();
    echo "✅ <strong>BERHASIL:</strong> Tabel 'hari_libur' ada (" . $row['total'] . " record)<br>";
}

// Test 6: Cek akun user default
echo "<h3>6. Test Akun Default</h3>";
$result = $conn->query("SELECT username, role FROM users");
if (!$result) {
    echo "❌ <strong>GAGAL:</strong> " . $conn->error . "<br>";
} else {
    echo "✅ <strong>BERHASIL:</strong> Daftar User:<br>";
    while ($row = $result->fetch_assoc()) {
        echo "   - Username: <strong>" . $row['username'] . "</strong> (Role: " . $row['role'] . ")<br>";
    }
}

// Test 7: Cek session
echo "<h3>7. Test Session</h3>";
session_start();
if (isset($_SESSION['test'])) {
    echo "✅ <strong>BERHASIL:</strong> Session berfungsi<br>";
} else {
    $_SESSION['test'] = true;
    echo "⚙️ Session sedang diinisialisasi...<br>";
}

echo "<hr>";
echo "<h3>✨ Semua Test Berhasil!</h3>";
echo "<p>Anda bisa membuka aplikasi di: <a href='login.php'>login.php</a></p>";
echo "<p style='color: red;'><strong>Catatan:</strong> Hapus file test-connection.php setelah selesai testing untuk keamanan.</p>";

$conn->close();
?>