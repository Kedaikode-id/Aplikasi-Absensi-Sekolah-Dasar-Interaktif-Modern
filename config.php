<?php
// config.php - Konfigurasi Database

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistem_absensi');

// Membuat koneksi ke database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die("❌ Koneksi Gagal: " . $conn->connect_error . "<br>");
}

// Set charset
$conn->set_charset("utf8mb4");

// Fungsi untuk escape string
function escape($str) {
    global $conn;
    return $conn->real_escape_string($str);
}

// Fungsi untuk query
function query($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    // Debug: tampilkan error jika query gagal
    if (!$result) {
        die("❌ Query Error: " . $conn->error . "<br>SQL: " . $sql);
    }
    
    return $result;
}

// Fungsi untuk fetch semua data
function fetch_all($result) {
    // Cek apakah result valid
    if (!$result) {
        return array();
    }
    
    $data = array();
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    return $data;
}

// Fungsi untuk fetch satu baris
function fetch_row($result) {
    if (!$result) {
        return null;
    }
    return $result->fetch_assoc();
}

// Fungsi untuk execute query (insert, update, delete)
function execute($sql) {
    global $conn;
    $result = $conn->query($sql);
    
    if (!$result) {
        die("❌ Query Error: " . $conn->error . "<br>SQL: " . $sql);
    }
    
    return $result;
}

// Session start
session_start();

// Fungsi logout
function logout() {
    session_destroy();
    header("Location: login.php");
    exit();
}

// Fungsi cek login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk mengecek role
function check_role($allowed_roles) {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
    
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: index.php");
        exit();
    }
}

// Fungsi untuk count data
function count_data($table, $where = '') {
    global $conn;
    $sql = "SELECT COUNT(*) as total FROM $table";
    if (!empty($where)) {
        $sql .= " WHERE " . $where;
    }
    $result = query($sql);
    $row = fetch_row($result);
    return $row['total'];
}
?>