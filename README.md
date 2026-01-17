# Aplikasi-Absensi-Sekolah-Dasar-Interaktif-Modern
Sistem informasi manajemen kehadiran siswa yang modern, responsif, dan user-friendly untuk Sekolah Dasar Negeri. Dilengkapi dengan fitur lengkap untuk guru dan kepala sekolah dengan antarmuka yang intuitif dan desain yang sesuai dengan tema pendidikan.

## ✨ Fitur Utama

### 👨‍🏫 Akses Guru
- **📊 Dashboard** - Ringkasan kehadiran dan siswa
- **➕ Kelola Siswa** - Tambah, edit, hapus data siswa dengan pencarian
- **✓ Input Presensi** - Input kehadiran harian dengan fitur "Hadir Semua"
- **📄 Laporan Absensi** - Lihat dan export laporan ke Excel

### 👔 Akses Kepala Sekolah
- **📊 Dashboard** - Monitoring kehadiran per kelas
- **📄 Laporan Absensi** - Analisis dan export data untuk kepala sekolah
- **🔐 Akses Terbatas** - Menu terbatas untuk keperluan manajemen

### 🎨 Fitur Teknis
- ✅ **Desain Responsif** - Optimal di desktop, tablet, dan mobile
- ✅ **Dark-Friendly UI** - Sidebar yang dapat disembunyikan
- ✅ **Warna Tema Biru** - Sesuai dengan branding pendidikan
- ✅ **Database MySQL** - Data disimpan langsung ke server (bukan localStorage)
- ✅ **Role-Based Access Control** - Proteksi akses berbeda per role
- ✅ **Error Handling** - Pesan error yang informatif
- ✅ **SQL Injection Protection** - Input sanitization untuk keamanan

## 🛠️ Teknologi yang Digunakan

| Teknologi | Versi | Keterangan |
|-----------|-------|-----------|
| **PHP** | 7.4+ | Backend language |
| **MySQL** | 5.7+ | Database management |
| **HTML5** | Latest | Markup structure |
| **CSS3** | Latest | Styling & responsive design |
| **JavaScript** | ES6+ | Frontend interactivity |
| **XAMPP** | 7.4+ | Local development server |

## 📋 Daftar File

```
sistem-absensi-sd/
├── config.php                 # Konfigurasi database & helper functions
├── login.php                  # Halaman login dengan error handling
├── index.php                  # Dashboard utama
├── tambah-siswa.php           # Kelola data siswa (CRUD)
├── presensi.php               # Input presensi harian
├── laporan.php                # Laporan dengan filter & export
├── logout.php                 # Keluar sistem
├── test-connection.php        # Test koneksi database (opsional)
├── README.md                  # Dokumentasi ini
├── docs/
│   ├── INSTALASI.md           # Panduan instalasi lengkap
│   ├── PENGGUNAAN.md          # Cara menggunakan sistem
│   ├── TROUBLESHOOTING.md     # Solusi error umum
│   └── ROLE_AKSES.md          # Penjelasan hak akses
└── database/
    └── struktur_database.sql  # Script SQL database
```

## 🚀 Instalasi Cepat

### Prasyarat
- XAMPP atau server lokal dengan PHP 7.4+ dan MySQL 5.7+
- Git (opsional)
- Text editor (Sublime Text, VS Code, dll)

### Langkah Instalasi

#### 1. Clone Repository
```bash
git clone https://github.com/yourusername/sistem-absensi-sd.git
cd sistem-absensi-sd
```

#### 2. Copy ke XAMPP
```bash
# Windows
copy -r . "C:\xampp\htdocs\sistem-absensi-sd\"

# Linux/Mac
cp -r . ~/xampp/htdocs/sistem-absensi-sd/
```

#### 3. Setup Database
```bash
# Buka phpMyAdmin
http://localhost/phpmyadmin

#### 4. Verifikasi Koneksi
```bash
# Akses halaman test (opsional)
http://localhost/sistem-absensi-sd/test-connection.php
```

#### 5. Buka Aplikasi
```bash
http://localhost/sistem-absensi-sd/login.php
```

## 🔑 Akun Default

| Role | Username | Password | Nama |
|------|----------|----------|------|
| Guru | `guru1` | `password123` | Ibu Siti Rahman |
| Guru | `guru2` | `password123` | Bapak Budi Santoso |
| Kepala Sekolah | `kepala` | `password123` | Bapak Ahmad Hidayat |

> ⚠️ **Penting:** Ubah password setelah instalasi untuk keamanan!

## 📖 Panduan Penggunaan

### Untuk Guru

#### Input Presensi
```
1. Login dengan akun guru
2. Klik menu "Presensi"
3. Pilih kelas dan tanggal
4. Pilih status siswa (Hadir/Izin/Alfa/Sakit)
5. Atau klik "Hadir Semua" untuk shortcut
6. Klik "Simpan Absensi"
```

#### Kelola Data Siswa
```
1. Klik menu "Tambah Siswa"
2. Isi form data siswa baru
3. Atau cari, edit, dan hapus siswa existing
4. Klik "Tambah" atau "Perbarui"
```

#### Lihat Laporan
```
1. Klik menu "Laporan"
2. Pilih kelas
3. Lihat tabel kehadiran
4. Klik "Export Excel" untuk download
```

### Untuk Kepala Sekolah

#### Monitoring Dashboard
```
1. Login dengan akun kepala
2. Lihat ringkasan kehadiran per kelas
3. Lihat peringatan siswa dengan kehadiran rendah
4. Lihat jadwal hari libur
```

#### Lihat Laporan
```
1. Klik menu "Laporan"
2. Pilih kelas untuk melihat detail
3. Export ke Excel untuk laporan resmi
```

## 🐛 Troubleshooting

### Database Error
```
Error: Connection failed
Solusi:
1. Pastikan MySQL running di XAMPP
2. Cek config.php sudah benar
3. Verifikasi database sudah dibuat
```

### Login Gagal
```
Error: Username tidak ditemukan
Solusi:
1. Periksa username (guru1, guru2, atau kepala)
2. Pastikan data user ada di database
3. Reset password via phpMyAdmin jika perlu
```

### SQL Error
```
Error: Query Error
Solusi:
1. Akses test-connection.php untuk diagnosa
2. Pastikan semua tabel ada
3. Check struktur tabel di phpMyAdmin
```
## 🔒 Keamanan

✅ **Implementasi Keamanan:**
- Password di-hash menggunakan MD5
- Input sanitization untuk SQL injection prevention
- Session management untuk proteksi akses
- Role-based access control (RBAC)
- Escape string untuk semua input database

⚠️ **Best Practices:**
- Ubah password default setelah instalasi
- Gunakan HTTPS saat hosting di server publik
- Backup database secara berkala
- Jangan share akun admin dengan semua orang
- Update PHP dan MySQL secara regular

## 🤝 Kontribusi

Kontribusi sangat diterima! Jika ada saran atau perbaikan:

1. Fork repository ini
2. Buat branch feature (`git checkout -b feature/AmazingFeature`)
3. Commit perubahan (`git commit -m 'Add some AmazingFeature'`)
4. Push ke branch (`git push origin feature/AmazingFeature`)
5. Buka Pull Request

## 📝 Lisensi

Project ini dilisensikan di bawah MIT License - lihat file `LICENSE` untuk detail.

## 📞 Support & Kontak

Jika ada pertanyaan atau butuh bantuan:

- 📧 Email: [your-email@example.com]
- 🐦 Twitter: [@yourhandle]
- 💬 Issues: [GitHub Issues](https://github.com/yourusername/sistem-absensi-sd/issues)

## 👨‍💻 Penulis

**Nama Anda**
- GitHub: [@yourusername](https://github.com/yourusername)
- Email: your-email@example.com

## 🙏 Ucapan Terima Kasih

- Terinspirasi dari kebutuhan sekolah dasar di Indonesia
- Terima kasih kepada semua kontributor yang membantu
- Apresiasi untuk komunitas open source

## 📸 Screenshot

### Halaman Login
![Login Page](#)

### Dashboard Guru
![Dashboard Guru](#)

### Input Presensi
![Presensi](#)

### Laporan Absensi
![Laporan](#)

### Mobile Responsive
![Mobile](#)

---

## ⭐ Jika Proyek Ini Bermanfaat

Jangan lupa untuk:
- ⭐ Star repository ini
- 🍴 Fork untuk pengembangan lebih lanjut
- 💬 Share dengan rekan kerja atau teman

---

**Semoga sistem ini membantu meningkatkan efisiensi manajemen kehadiran siswa di sekolah Anda!** 🎓✨

---

*Last Updated: 2025-01-17*
*Version: 1.0.0*
