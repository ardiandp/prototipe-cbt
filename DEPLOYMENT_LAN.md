# Panduan Deployment & Operasional Jaringan Lokal (LAN) — CBT School

Dokumen ini memuat panduan komprehensif untuk menyiapkan, menjalankan, dan mendistribusikan aplikasi **CBT School** di lingkungan jaringan lokal sekolah (*Local Area Network / Wi-Fi Intranet*) tanpa ketergantungan pada koneksi internet eksternal.

---

## 1. Persyaratan Lingkungan Server Lokal

Untuk menjalankan komputer server CBT School di laboratorium atau ruang proktor, pastikan komputer server memenuhi spesifikasi berikut:

- **Sistem Operasi**: Windows 10/11 (menggunakan Laragon / PHP CLI), Linux (Ubuntu/Debian), atau macOS.
- **PHP**: Versi **8.2 atau 8.3** (dengan ekstensi `pdo_sqlite`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`).
- **Composer**: Versi 2.x.
- **Database**: **SQLite** (file basis data lokal `database/database.sqlite`, tidak memerlukan instalasi service database MySQL/PostgreSQL tambahan).
- **RAM Minimum**: 4 GB (disarankan 8 GB untuk melayani 30–50 klien serentak).
- **Koneksi Jaringan**: Terhubung ke Switch / Router Wi-Fi yang sama dengan perangkat klien siswa.

---

## 2. Langkah Menjalankan Server Lokal

### Langkah 2.1 — Menyiapkan Database Demo Bersih
Sebelum membuka akses ujian ke siswa, pastikan database sudah terisi skenario data demo:

```bash
# Menjalankan reset database bersih dan pengisian data demo instan
php artisan cbt:reset-demo --force
```

### Langkah 2.2 — Menentukan Alamat IP Lokal Komputer Server
Dapatkan alamat IP komputer server pada jaringan lokal:

- **Pada Windows (Command Prompt / PowerShell)**:
  ```powershell
  ipconfig
  ```
  Cari adaptor jaringan yang aktif (**Wireless LAN adapter Wi-Fi** atau **Ethernet adapter**), lalu catat baris **IPv4 Address**, misalnya:
  `192.168.1.100` atau `10.0.0.15`.

- **Pada Linux / macOS**:
  ```bash
  ip a
  # atau
  ifconfig
  ```

### Langkah 2.3 — Menyesuaikan Konfigurasi `.env`
Buka file `.env` di folder root project CBT School, lalu sesuaikan nilai `APP_URL` menggunakan alamat IP server aktual:

```ini
APP_NAME="CBT School"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://192.168.1.100:8000

DB_CONNECTION=sqlite
```

> **Catatan**: Jika menggunakan port berbeda (misal port 80), sesuaikan port pada `APP_URL`.

### Langkah 2.4 — Menjalankan Server
Jalankan server PHP bawaan Laravel dengan mengikat (*bind*) ke semua antarmuka jaringan (`0.0.0.0`):

```bash
php artisan serve --host=0.0.0.0 --port=8000
```

> [!IMPORTANT]
> **Jangan gunakan `localhost` atau `127.0.0.1` pada parameter `--host`.**  
> Nilai `--host=0.0.0.0` wajib digunakan agar server mendengarkan permintaan (*listen*) yang masuk dari kartu jaringan LAN/Wi-Fi sehingga komputer klien dapat terhubung.

---

## 3. Panduan Akses dari Komputer Klien Siswa & Pengawas

Perangkat klien (laptop siswa, PC lab komputer, Chromebook, tablet, atau smartphone) tidak memerlukan instalasi perangkat lunak khusus, cukup menggunakan web browser modern (Google Chrome, Microsoft Edge, Mozilla Firefox, atau Safari).

### Langkah Menghubungkan Klien:
1. Hubungkan perangkat klien ke jaringan Wi-Fi atau kabel LAN yang sama dengan komputer server.
2. Buka browser di perangkat klien.
3. Ketik alamat URL server pada *address bar*:
   ```
   http://192.168.1.100:8000/login
   ```
   *(Ganti `192.168.1.100` dengan IP komputer server Anda)*.
4. Halaman login CBT School akan tampil. Siswa dan guru dapat masuk menggunakan kredensial masing-masing:
   - **Siswa**: `siswa` / `password`
   - **Guru**: `guru` / `password`
   - **Admin**: `admin` / `password`

---

## 4. Konfigurasi Firewall & Jaringan Sekolah

Jika komputer klien tidak dapat membuka halaman server (muncul pesan *ERR_CONNECTION_TIMED_OUT* atau *Connection Refused*), periksa pengaturan berikut:

### 4.1. Membuka Port pada Windows Defender Firewall
Di komputer server Windows, izinkan lalu lintas masuk (*inbound traffic*) untuk port `8000`:

1. Buka **PowerShell sebagai Administrator**, lalu jalankan perintah:
   ```powershell
   New-NetFirewallRule -DisplayName "CBT School LAN (Port 8000)" -Direction Inbound -LocalPort 8000 -Protocol TCP -Action Allow
   ```
2. Atau melalui GUI Windows:
   - Buka *Windows Defender Firewall with Advanced Security*.
   - Klik **Inbound Rules** -> **New Rule...**
   - Pilih **Port** -> **TCP** -> Masukkan port `8000`.
   - Pilih **Allow the connection** -> Beri nama "CBT School LAN" -> Klik **Finish**.

### 4.2. Membuka Port pada Linux (UFW)
Jika server menggunakan OS Linux:
```bash
sudo ufw allow 8000/tcp
sudo ufw reload
```

### 4.3. Konfigurasi Access Point Wi-Fi (Nonaktifkan AP Isolation)
- Pastikan fitur **AP Isolation** / **Client Isolation** / **Station Isolation** pada router atau access point Wi-Fi sekolah dalam status **DISABLED (Nonaktif)**.
- Jika fitur isolasi aktif, router akan memblokir komunikasi antar-perangkat nirkabel meskipun berada dalam satu SSID yang sama.

### 4.4. Subnet Jaringan yang Sama
- Pastikan IP server dan IP klien berada dalam blok subnet yang identik (misal: Subnet Mask `255.255.255.0` dengan IP server `192.168.1.100` dan IP klien `192.168.1.101` s/d `192.168.1.150`).

---

## 5. Jaminan Asset Lokal Bebas CDN (Offline Guarantee)

Salah satu keunggulan utama arsitektur **CBT School** adalah kemampuannya beroperasi secara penuh tanpa koneksi internet (*zero-internet intranet*):

- **Template UI**: AdminLTE 3 / Bootstrap 5 dimuat 100% dari direktori lokal `public/theme/adminLTE/dist/`.
- **CSS & Ikon**: Menggunakan font dan icon bundle bawaan tema yang tersimpan secara lokal.
- **JavaScript**: Seluruh logika autosave, timer synchronization, dan palette rendering menggunakan vanilla JavaScript murni dan asset lokal.
- **Tidak Ada CDN Eksternal**: Seluruh template Blade menggunakan helper `asset('theme/adminLTE/dist/...')`. Tidak ada pemanggilan domain eksternal seperti `cdn.jsdelivr.net`, `cdnjs.cloudflare.com`, atau `fonts.googleapis.com` pada layout aplikasi.
- **Manfaat**:
  - Ujian tetap berlangsung tanpa kendala saat kabel internet sekolah putus.
  - Waktu muat halaman instan (< 100 milidetik di LAN gigabit).
  - Keamanan jaringan sekolah lebih terjaga dari ancaman eksternal.

---

## 6. Keterbatasan Prototype & Rekomendasi Skala Demo

Aplikasi CBT School dibangun sebagai prototype fungsional tingkat lanjut (*high-fidelity prototype*). Untuk pengujian dan presentasi skala demo, pahami batasan berikut:

| Aspek | Kondisi Prototype Saat Ini | Rekomendasi untuk Skala Produksi Massal |
|---|---|---|
| **Database** | SQLite (`database.sqlite`) | Untuk 1 ruang lab (20–40 klien serentak), SQLite sangat cepat dan stabil. Jika melayani >100 klien bersamaan yang melakukan autosave serentak setiap detik, ganti ke **PostgreSQL** atau **MySQL** untuk menghindari *database table lock*. |
| **Web Server** | PHP Built-in Server (`artisan serve`) | Bersifat single/limited-worker. Cukup untuk demonstrasi 1–10 perangkat. Untuk ujian serentak satu angkatan, gunakan **Nginx + PHP-FPM** atau **Apache MPM-Event**. |
| **Anti-Cheating** | Deteksi tab switch / browser blur visual | Sistem mendeteksi ketika siswa berpindah jendela/tab browser dan mencatatnya. Namun deteksi browser client-side bukan pengamanan mutlak. Pengawasan fisik proktor di laboratorium tetap menjadi pilar utama integritas ujian. |
| **Penyimpanan Media** | Local disk storage (`storage/app/public`) | Gambar soal disimpan di storage lokal server. Cukup jalankan `php artisan storage:link` jika belum aktif. |

---

## 7. Ringkasan Kredensial & Pengujian Cepat

```
URL Login   : http://<IP-SERVER>:8000/login
Password    : password (berlaku untuk seluruh akun)

Akun Siswa  : siswa        (Ahmad Dani - Kelas X RPL 1)
Akun Guru   : guru         (Budi Santoso, S.Pd.)
Akun Admin  : admin        (Admin Sekolah)
Akun Super  : superadmin   (Super Administrator)

Token Ujian Demo: CBT2026 (pada jadwal EXAM-PWPB-01)
```

Untuk petunjuk pengujian skenario lengkap dari awal hingga rekapitulasi nilai dan audit log, silakan baca [DEMO_GUIDE.md](file:///c:/laragon/www/CBT/DEMO_GUIDE.md).

