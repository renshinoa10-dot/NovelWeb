# karyaNovel — Platform Web Novel & AI Writing Assistant Studio

Dokumentasi Resmi Proyek Tugas Akhir Semester (UAS)  
Mata Kuliah: **Rekayasa Perangkat Lunak (RPL)**  
Tahun Akademik: **2026/2027**

---

## 👥 A. IDENTITAS KELOMPOK

| Komponen | Keterangan |
| :--- | :--- |
| **Nomor Kelompok** | [ Kere Hore |
| **Tema / Topik Proyek** | Sistem Informasi Penulisan & Publikasi Web Novel Berbasis AI |
| **Kelas** | TI-1D Reguler |
| **Fakultas / Universitas** | STMIK IKMI |
| **Dosen Pengampu** | Yudhistira Arie Wijaya, M.kom |

### Daftar Anggota Kelompok:

| No | Nama Mahasiswa | NIM | Peran / Pembagian Tugas |
|:--:| :--- | :---: | :--- |
| 1 | Ketua : Fajar Ma'aruf | 41257191 |  
| 2 | Eggi Alivia Rohmaht | 41257187 |  
| 3 | Daryell Zhafranabil Saren | 41257184 | 
| 4 | Leo Ardiansyah Jailani | 41257201 | 
| 5 | Fakhri Fadlurrohman Rahmadani | 41256192 | 
---

## 📖 B. DESKRIPSI APLIKASI

### 1. Nama & Konsep Aplikasi
**karyaNovel** adalah aplikasi platform web terintegrasi yang mempertemukan penulis dan pembaca naskah digital dalam satu ekosistem interaktif. Dibangun di atas arsitektur **Model-View-Controller (MVC) murni berbahasa PHP**, karyaNovel menggabungkan sistem manajemen naskah berjenjang (*novel and chapter lifecycle*) dengan teknologi kecerdasan buatan (*Artificial Intelligence*) guna membantu proses kreatif penulisan.

### 2. Latar Belakang Pengembangan
Dalam proses kreatif penulisan cerita fiksi, penulis pemula maupun profesional sering mengalami kendala hambatan ide (*writer's block*), inkonsistensi penokohan, dan kesulitan menyusun babak naratif yang berkesinambungan. Di sisi lain, platform publikasi naskah yang ada umumnya hanya berfokus pada distribusi akhir tanpa memberikan fasilitas pendampingan penulisan terintegrasi di ruang kerja (*editor studio*). 

karyaNovel dikembangkan sebagai solusi perangkat lunak yang tidak hanya berfungsi sebagai media penerbitan digital, melainkan juga bertindak sebagai sanggar penulisan pintar (*smart writing studio*).

### 3. Tujuan Pengembangan
1. Menyediakan ruang penulisan naskah yang nyaman, bebas distraksi (*distraction-free editor*), dan terlindungi fitur penyimpanan otomatis (*autosave*).
2. Memfasilitasi penulis dengan asisten berbasis AI untuk mengembangkan alur, karakter, dan kelanjutan adegan tanpa keluar dari ruang kerja editor.
3. Membangun ruang baca publik yang terstruktur dengan perlindungan ketat terhadap karya draf penulis.
4. Menerapkan pengawasan operasional platform yang transparan bagi administrator melalui asisten analitik data (*analytical chatbot assistant*).

### 4. Target Pengguna
- **Penulis Kreatif:** Individu yang ingin menulis, menyusun draf bab, berkonsultasi ide naskah dengan AI, dan memublikasikan ceritanya.
- **Pembaca Digital:** Masyarakat umum penikmat novel yang mencari bahan bacaan terkurasi berdasarkan genre secara fleksibel di perangkat web.
- **Administrator Platform:** Pengelola sistem yang bertugas memoderasi karya, mengelola akun pengguna, dan memantau analitik operasional.

### 5. Permasalahan yang Ingin Diselesaikan
- Mengurangi risiko kehilangan naskah belum tersimpan melalui fitur *auto-save background API*.
- Membantu akselerasi penyelesaian cerita saat mengalami kebuntuan alur naskah.
- Menjamin isolasi data antar penulis (*tenant isolation*) agar karya draf tidak dapat diakses atau disunting pihak lain.
- Memberikan verifikasi identitas akun pengguna menggunakan gerbang OTP WhatsApp resmi demi mencegah akun spam.

---

## 🎭 C. ROLE PENGGUNA & HAK AKSES SISTEM

Sistem karyaNovel membagi hak akses ke dalam **tiga aktor utama** berdasarkan otentikasi sesi server:

```
                  ┌─────────────────────────────────┐
                  │          karyaNovel             │
                  └────────────────┬────────────────┘
                                   │
         ┌─────────────────────────┼─────────────────────────┐
         ▼                         ▼                         ▼
   ┌───────────┐             ┌───────────┐             ┌───────────┐
   │  PEMBACA  │             │  PENULIS  │             │   ADMIN   │
   └───────────┘             └───────────┘             └───────────┘
```

### 1. Pembaca (Reader)
* **Hak Akses:**
  - Menjelajahi katalog novel yang telah berstatus *Published*.
  - Melakukan pencarian novel berdasarkan judul atau sinopsis.
  - Menyaring novel berdasarkan kategori genre.
  - Membaca konten bab yang telah diterbitkan lengkap dengan navigasi bab sebelumnya/berikutnya.
  - Menggunakan widget chatbot untuk rekomendasi novel, panduan membaca, dan info platform.
* **Batasan:**
  - Tidak dapat mengakses dashboard penulisan naskah pribadi.
  - Tidak dapat melihat atau membaca naskah/bab yang masih berstatus *Draft*.
  - Tidak diizinkan mengakses panel administrator maupun data analitik pengguna platform.

### 2. Penulis (Author)
* **Hak Akses:**
  - Memiliki ruang kerja pribadi (*Author Dashboard*) yang memuat statistik naskah mandiri.
  - Melakukan CRUD naskah novel (Judul, Sinopsis, Genre, Cover, Status).
  - Melakukan CRUD bab novel dan mengatur urutan bab (*chapter ordering*).
  - Menggunakan distraction-free editor bab dengan autosave 30 detik.
  - Mengakses bilah **AI Writing Assistant Studio** di editor bab (6 mode bantuan cerita).
  - Mengelola status rilis karya (mengubah dari *Draft* ke *Published* atau sebaliknya).
  - Menggunakan chatbot karyaNovel untuk memantau ringkasan karya pribadi.
* **Batasan:**
  - Hanya dapat mengelola dan mengakses novel miliknya sendiri (*strict tenant isolation*).
  - Dilarang mengakses rute dan menu Administrator.
  - Chatbot menolak jika penulis meminta data global pengguna lain.

### 3. Administrator (System Admin)
* **Hak Akses:**
  - Akses penuh ke panel kendali administrator (`/admin`).
  - Pemantauan metrik operasional sistem (total akun, total novel, total bab, rincian status rilis).
  - Manajemen akun pengguna (mengubah hak akses role pengguna).
  - Moderasi konten naskah dan bab yang melanggar ketentuan.
  - Pemantauan riwayat log pengiriman notifikasi WhatsApp Gateway Fonnte (`/admin/notifications`).
  - Menggunakan **AI Admin Assistant** pada chatbot untuk menjalankan kueri analitik operasional secara langsung dari basis data aktual (daftar pengguna, agregasi status, performa naskah).
* **Batasan:**
  - Tidak dapat melihat kata sandi pengguna (tersimpan dalam bentuk *hash* satu arah).
  - Chatbot admin dilarang menampilkan data rahasia seperti hash password, token CSRF, atau kunci API.

---

## ⚡ D. FITUR-FITUR APLIKASI KARYANOVEL

### 1. Modul Gerbang & Autentikasi (Gatekeeper Authentication)
* **Gatekeeper Routing:** Pengunjung yang belum melakukan login secara otomatis dialihkan menuju halaman `/auth/login`.
* **Pilihan Registrasi Multi-Aktor:** Calon pengguna dapat memilih peran saat mendaftar (*Pembaca*, *Penulis*, atau *Admin* dengan passcode otorisasi khusus).
* **Verifikasi Kode OTP WhatsApp:** Pendaftaran akun mengirimkan 6 digit kode OTP acak ke nomor WhatsApp pengguna via gateway Fonnte. Akun hanya dapat aktif setelah kode diverifikasi.
* **Login Fleksibel:** Mendukung proses login menggunakan **Alamat Email** maupun **Nomor WhatsApp**.
* **Keamanan Sandi:** Hashing sandi menggunakan algoritma bawaan standar industri `PASSWORD_BCRYPT`.

### 2. Modul Manajemen Naskah & Bab (Author Studio)
* **CRUD Novel Terstruktur:** Penulis dapat menambah judul cerita, sinopsis naratif, memilih genre, dan mengunggah gambar sampul.
* **Validasi Unggahan Sampul:** Pengecekan biner tipe berkas (*MIME type inspection* via `finfo`), pembatasan ukuran file (maksimal 2MB), dan pembersihan nama berkas.
* **Chapter Ordering Engine:** Pengaturan urutan bab secara dinamis (*naik/turun*) dengan kalkulasi indeks otomatis.
* **Penyunting Naskah Bersih (Distraction-Free Editor):** Lingkungan penulisan nyaman untuk teks panjang.
* **Autosave Background API:** Naskah tersimpan otomatis ke server setiap 30 detik tanpa memuat ulang halaman (*background AJAX*).
* **Penghitung Kata Real-Time:** Menghitung jumlah kata dan karakter secara langsung saat pengguna mengetik.

### 3. Modul Pembaca Publik (Public Reader)
* **Katalog Jelajah Novel:** Daftar novel terbit yang dilengkapi paginasi dan filter genre.
* **Pencarian Aman:** Kueri pencarian teks bebas judul dan sinopsis dengan perlindungan parameter terisolasi.
* **Reader View:** Tampilan membaca ergonomis dengan navigasi antar-bab (*Previous / Next Chapter*).
* **Draft Content Shield:** Bab atau novel berstatus draf yang dicoba diakses melalui manipulasi URL publik otomatis menghasilkan kode status HTTP 404.

### 4. Modul Administrator & Moderasi
* **Dashboard Statistik Real-Time:** Metrik sistem (jumlah pengguna, novel terbit, novel draf, bab aktif) dihitung secara agregasi langsung dari basis data tanpa nilai tiruan.
* **Manajemen Peran Akun:** Pengubahan hak akses pengguna dari antarmuka tabel administrasi.
* **Log Gateway WhatsApp:** Rekap status pengiriman pesan notifikasi WhatsApp (sukses/gagal, target, isi pesan, waktu kirim).

### 5. Modul Asisten AI & Chatbot Cerdas
* **AI Writing Assistant Studio:** Terpasang di editor bab untuk pendampingan penulisan kreatif naskah.
* **karyaNovel Assistant (Floating Chatbot Widget):** Asisten percakapan responsif di seluruh halaman web dengan format Markdown tabel dan respons berbasis peran.

---

## 🤖 E. DESKRIPSI FITUR KECERDASAN BUATAN (AI)

### 1. Tujuan Integrasi AI
Integrasi AI di karyaNovel bertujuan untuk:
1. Membantu penulis mengatasi hambatan penulisan naskah (*creative writing block*).
2. Membantu administrator mengawasi dan menganalisis operasional sistem secara cepat menggunakan bahasa alami.
3. Membantu pembaca menemukan novel yang sesuai dengan minat dan preferensinya.

### 2. Fungsi AI Writing Assistant Studio (Editor Bab)
Terhubung di bilah samping editor naskah dengan 6 preset instruksi:
- 💡 **Brainstorming Ide Cerita:** Menghasilkan premis dan kaitan konflik cerita berdasarkan genre.
- 📝 **Penyusun Outline Alur:** Merancang struktur alur bab (orientasi, komplikasi, klimaks, resolusi).
- 👤 **Pengembang Karakter:** Membangun profil tokoh, kepribadian, motivasi, dan gaya dialog.
- ⏩ **Lanjutkan Cerita (Continue Writing):** Menganalisis paragraf terakhir tulisan dan memberikan opsi sambungan narasi.
- ✨ **Poles Gaya Bahasa (Improve Writing):** Menyempurnakan diksi, rima emosi, dan kejelasan kalimat.
- 🎯 **Saran & Kritik (Constructive Feedback):** Mengevaluasi tempo narasi (*pacing*) dan celah logika adegan (*plot hole*).

### 3. Fungsi karyaNovel Assistant (Floating Chatbot)
Chatbot mengimplementasikan *Role-Based Context Engine*:
- **Bagi Administrator:**
  - Menjawab pertanyaan total pengguna, total novel, bab, dan status rilis.
  - Mengambil daftar seluruh pengguna dari database dan menyajikannya dalam **Tabel Markdown** yang dirender rapi menjadi tabel HTML dengan badge warna peran.
  - Menjawab pencarian akun pengguna tertentu berdasarkan nama atau role.
- **Bagi Penulis:**
  - Menjawab statistik naskah miliknya sendiri (jumlah bab, status novel, total karya).
  - Memberikan saran teknis kepenulisan.
- **Bagi Pembaca:**
  - Memberikan rekomendasi judul novel berstatus rilis berdasarkan genre favorit.
  - Memberikan panduan cara membaca dan menjelajahi platform.

### 4. Hubungan Chatbot dengan Basis Data (Keamanan Data)
- Model AI **tidak pernah diberikan izin mengeksekusi perintah SQL langsung** ke database (*no direct SQL execution*).
- Backend PHP bertindak sebagai gerbang: kueri agregasi SQL dijalankan oleh backend, lalu hasil data terverifikasi dirangkai dan dikirimkan ke model AI untuk diformat sebagai respons bahasa alami.
- Mencegah teknik *Prompt Injection* dan *Role Escalation*: Chatbot memverifikasi sesi server aktual, bukan klaim pengguna dalam teks percakapan.

### 5. Layanan Eksternal & Batasan Implementasi
- **Layanan Eksternal:** Menggunakan **Google Gemini API** (`gemini-2.5-flash` / `gemini-1.5-flash`) via komunikasi HTTP cURL di sisi server (`app/services/AiService.php`).
- **Batasan Jujur Implementasi:**
  - Kecepatan respons bergantung pada latensi jaringan internet dan kuota harian dari penyedia API.
  - Apabila kunci API tidak diisi atau kuota habis, sistem menerapkan mekanisme cadangan (*graceful fallback simulation*) agar aplikasi tidak mengalami kegagalan fatal (*fatal error*).

---

## 🛠️ F. TEKNOLOGI & LINGKUNGAN PENGEMBANGAN

| Lapisan Sistem | Teknologi / Komponen yang Digunakan |
| :--- | :--- |
| **Bahasa Pemrograman** | PHP 8.2 (Native Custom MVC, Object-Oriented Programming) |
| **Basis Data** | MySQL / MariaDB 10.4 (InnoDB Engine, Foreign Key Cascades) |
| **Driver Basis Data** | PHP Data Objects (PDO) dengan mode *Prepared Statements* |
| **Frontend Styling** | Vanilla CSS3 (Custom Design Tokens, Glassmorphism, Dark Theme) |
| **UI Framework** | Bootstrap v5.3.3 & Bootstrap Icons v1.11.3 |
| **Frontend Scripting** | JavaScript ES6+ (Native Fetch API, DOM Manipulation) |
| **Web Server** | Apache HTTP Server 2.4 (XAMPP Environment dengan `mod_rewrite`) |
| **Integrasi AI** | Google Gemini Generative Language API |
| **Integrasi Pesan** | Fonnte WhatsApp Gateway API |

---

## 📥 G. PANDUAN INSTALASI & SETUP PROYEK

Ikuti petunjuk di bawah ini untuk menjalankan aplikasi karyaNovel pada komputer lokal penguji/dosen:

### 1. Persyaratan Perangkat Lunak
- **PHP:** Versi 8.1 atau lebih baru (ekstensi `pdo_mysql`, `curl`, `mbstring`, `fileinfo` dalam keadaan aktif pada `php.ini`).
- **MySQL / MariaDB:** Versi 10.3+ (tersedia bawaan XAMPP).
- **Web Server:** XAMPP for Windows (Apache & MySQL).
- **Peramban Web:** Google Chrome, Mozilla Firefox, atau Microsoft Edge versi terkini.

### 2. Penempatan Berkas Proyek di XAMPP
Pindahkan atau clone repositori ini ke dalam direktori `htdocs` XAMPP Anda:
```bash
C:\xampp\htdocs\Novelweb
```

### 3. Pengaturan Konfigurasi Lingkungan (`.env`)
Salin berkas template `.env.example` menjadi `.env` pada direktori root proyek:
```bash
# Melalui Command Prompt / PowerShell di dalam folder Novelweb:
copy .env.example .env
```
Buka berkas `.env` dan pastikan konfigurasi basis data sesuai dengan lingkungan lokal Anda:
```ini
APP_NAME=karyaNovel
APP_ENV=development
APP_URL=http://localhost/Novelweb

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=karyanovel_db
DB_USER=root
DB_PASS=
```
*(Opsional: Masukkan API Key Gemini dan Fonnte Anda pada baris `AI_API_KEY` dan `FONNTE_API_KEY` jika ingin menguji panggilan API eksternal secara aktif).*

### 4. Pembuatan & Migrasi Basis Data MySQL
Anda dapat memilih salah satu dari dua cara berikut:

#### Opsi 1: Menggunakan Perintah CLI Otomatis (Direkomendasikan)
Buka terminal/PowerShell di direktori proyek dan jalankan:
```bash
php database/migrate.php
```
*Skrip ini akan secara otomatis membuat database `karyanovel_db`, seluruh tabel relasional, serta data awal pengujian.*

#### Opsi 2: Mengimpor via phpMyAdmin
1. Buka peramban dan akses `http://localhost/phpmyadmin`.
2. Buat database baru bernama `karyanovel_db` dengan collation `utf8mb4_unicode_ci`.
3. Klik tab **Import**, pilih berkas `database/schema.sql` dari proyek ini, lalu klik tombol **Import / Go**.

### 5. Menjalankan Layanan Apache & MySQL
1. Buka aplikasi **XAMPP Control Panel**.
2. Klik tombol **Start** pada modul **Apache** dan modul **MySQL** hingga indikator berwarna hijau.

### 6. Membuka Aplikasi di Browser
Buka peramban Anda dan akses URL berikut:
```
http://localhost/Novelweb
```
*Sistem secara otomatis akan mengarahkan pengunjung ke halaman login gerbang karyaNovel.*

### 7. Akun Uji Coba Siap Pakai (Demo Accounts)
Database seeder telah menyediakan 4 akun uji coba untuk mengevaluasi seluruh fitur:

| Peran Akun | Alamat Email | Kata Sandi | Status Akun | Hak Akses Utama |
| :--- | :--- | :---: | :---: | :--- |
| **Admin** | `admin@karyanovel.id` | `password123` | Terverifikasi | Akses Panel Admin (`/admin`), moderasi, analitik chatbot admin. |
| **Penulis 1** | `penulis1@karyanovel.id` | `password123` | Terverifikasi | Dashboard Penulis, CRUD novel & bab, AI studio, autosave. |
| **Penulis 2** | `penulis2@karyanovel.id` | `password123` | Terverifikasi | Pengujian isolasi naskah antar-penulis (*tenant isolation*). |
| **Pembaca** | `pembaca.test@karyanovel.test` | `password123` | Terverifikasi | Eksplorasi katalog novel terbit, mode membaca bab novel. |

> 🔑 **Passcode Pendaftaran Admin Baru:** Apabila ingin menguji pendaftaran akun baru dengan peran Admin pada form register, gunakan passcode keamanan: `KARYANOVEL2026`.

---

## 🎬 H. VIDEO DEMONSTRASI APLIKASI

Tonton video demonstrasi implementasi dan alur kerja aplikasi karyaNovel pada tautan YouTube di bawah ini:

📺 **Link Video Demonstrasi:**  
[MASUKKAN LINK VIDEO YOUTUBE]

---

## 📁 I. STRUKTUR FOLDER PROYEK

Berikut adalah struktur direktori aplikasi karyaNovel berdasarkan implementasi aktual:

```
Novelweb/
├── app/
│   ├── config/              # Konfigurasi aplikasi & database (config.php)
│   ├── controllers/         # Kontroler logika sistem & otorisasi HTTP
│   │   ├── AdminController.php
│   │   ├── AiController.php
│   │   ├── AuthController.php
│   │   ├── ChapterController.php / ChaptersController.php
│   │   ├── ChatbotController.php
│   │   ├── DashboardController.php
│   │   ├── HomeController.php
│   │   └── NovelController.php / NovelsController.php
│   ├── core/                # Inti kerangka kerja MVC native
│   │   ├── App.php          # Router & URL request dispatcher
│   │   ├── Controller.php   # Base controller, validasi CSRF, otentikasi guard
│   │   ├── Database.php     # Singleton PDO database connector
│   │   ├── Env.php          # Parser berkas .env
│   │   ├── Model.php        # Base model query builder
│   │   └── Session.php      # Session engine, CSRF generator, role helper
│   ├── helpers/             # Fungsi utilitas (helpers.php: sanitasi XSS, flash)
│   ├── models/              # Model data entitas MySQL
│   │   ├── AiHistory.php
│   │   ├── Chapter.php
│   │   ├── NotificationLog.php
│   │   ├── Novel.php
│   │   ├── Setting.php
│   │   └── User.php
│   ├── services/            # Layanan integrasi eksternal
│   │   ├── AiService.php    # Komunikasi cURL backend ke Google Gemini API
│   │   └── FonnteService.php# Komunikasi ke Fonnte WhatsApp Gateway API
│   └── views/               # Tampilan antarmuka pengguna
│       ├── admin/           # Tampilan panel moderasi administrator
│       ├── auth/            # Halaman login, register, dan verifikasi OTP
│       ├── chapter/         # Editor penulisan bab naskah & view reader
│       ├── dashboard/       # Dashboard kerja penulis
│       ├── errors/          # Halaman penanganan galat (404 Not Found)
│       ├── home/            # Beranda & komponen popup chatbot assistant
│       ├── layouts/         # Layout utama, navbar responsif, dan footer
│       └── novel/ & novels/ # Katalog publik dan tabel manajemen novel
├── database/                # Skema dan skrip migrasi database
│   ├── migrate.php          # Skrip migrasi CLI otomatis
│   └── schema.sql           # Berkas skema SQL lengkap & data seeder uji coba
├── public/                  # Berkas statis dan entry point web publik
│   ├── css/                 # Stylesheet antarmuka kustom (style.css)
│   ├── images/              # Aset gambar sampul novel SVG
│   ├── js/                  # Skrip klien (main.js, chatbot.js)
│   ├── uploads/covers/      # Direktori unggahan berkas gambar sampul
│   ├── .htaccess            # Konfigurasi pengalihan Apache mod_rewrite
│   └── index.php            # Front-controller pembuka aplikasi
├── .env.example             # Template variabel lingkungan aman tanpa rahasia
├── .gitignore               # Konfigurasi pengecualian berkas repositori Git
├── AUDIT_UAS_RPL.md         # Laporan audit komprehensif sistem UAS RPL
└── README.md                # Dokumentasi utama proyek karyaNovel
```

---

## 📜 J. LISENSI & PENGEMBANGAN

* **Lisensi:** Proyek ini didistribusikan di bawah lisensi terbuka [MIT License](LICENSE).
* **Tujuan Penggunaan:** Proyek ini dikembangkan secara murni untuk pemenuhan tugas akademik **Ujian Akhir Semester (UAS) Mata Kuliah Rekayasa Perangkat Lunak**.
* **Ucapan Penutup:**  
  Terima kasih kepada Dosen Pengampu mata kuliah Rekayasa Perangkat Lunak atas bimbingan dan arahan teoritis serta praktis selama satu semester ini. Semoga perangkat lunak **karyaNovel** ini dapat memberikan kontribusi pembelajaran yang bermanfaat bagi perancangan perangkat lunak berbasis arsitektur MVC dan integrasi AI modern.

---
**karyaNovel Team — Inovasi Sanggar Penulisan Naskah Digital Berbasis Kecerdasan Buatan.**
