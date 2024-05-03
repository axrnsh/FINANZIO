# Panduan Penggunaan Aplikasi Web Finanzio
Aplikasi web Finanzio adalah aplikasi manajemen keuangan sederhana yang dibangun dengan HTML, CSS, JavaScript, PHP, dan MySQL. Berikut ini adalah langkah-langkah untuk menjalankan aplikasi web ini.

## Requirement Perangkat Lunak
Untuk menjalankan aplikasi web Finanzio, Anda memerlukan:
- XAMPP atau software serupa yang menyediakan server web Apache dan MySQL.
- PHP versi 8.x atau yang lebih baru.
- Web browser seperti Google Chrome, Mozilla Firefox, atau lainnya.

## Instalasi dan Penggunaan
1. **Persiapan XAMPP:**
   - Pastikan XAMPP telah terpasang di komputer Anda. Jika belum, Anda dapat mengunduhnya dari [situs resmi XAMPP](https://www.apachefriends.org/index.html) dan mengikuti instruksi instalasinya.

2. **Memindahkan File Aplikasi:**
   - Salin atau pindahkan seluruh isi folder aplikasi web Finanzio ke dalam folder `htdocs` di direktori instalasi XAMPP. Biasanya, direktori ini berada di `C:\xampp\htdocs` (Windows).

3. **Menjalankan XAMPP:**
   - Buka aplikasi XAMPP dan pastikan server Apache dan MySQL dijalankan (start).
   - Jika kedua server sudah berjalan, Anda akan melihat lampu indikator yang menyala hijau di nama server.

4. **Membuat Database:**
   - Di dalam folder aplikasi Finanzio, Anda akan menemukan folder database yang di dalamnya terdapat file `finanzio_db.sql`.
   - Buka browser dan akses `http://localhost/phpmyadmin`.
   - Buka phpMyAdmin, pilih tab "Import" dan pilih file `finanzio_db.sql` untuk mengimpor struktur database.

5. **Mengakses Aplikasi Web:**
   - Buka web browser dan akses aplikasi web Finanzio dengan mengetikkan alamat `http://localhost/finanzio/signup.php`.
   - Anda akan diarahkan ke halaman pendaftaran (signup) untuk membuat akun pengguna pertama kali.

6. **Menggunakan Aplikasi:**
   - Setelah membuat akun, Anda dapat masuk (login) ke aplikasi dengan alamat `http://localhost/finanzio/login.php`.
   - Setelah login, Anda dapat menggunakan fitur-fitur aplikasi seperti manajemen pendapatan, pengeluaran, aset, dan lainnya.

## Instruksi Lainnya
- Pastikan username dan password koneksi SQL pada kode sesuai dengan username dan password koneksi SQL Anda agar aplikasi web dapat diakses.
- Jika Anda mengalami kesulitan atau memiliki pertanyaan lebih lanjut, Anda dapat email ke alamat email berikut: `03081220019@student.uph.edu`.
