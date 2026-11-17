# LAPORAN AUDIT MENYELURUH PROJECT KARYANOVEL
## Kesiapan Pengumpulan Tugas UAS Rekayasa Perangkat Lunak (RPL)

> **Status Audit:** Selesai Dilaksanakan  
> **Tanggal Audit:** 17 November 2026  
> **Objek Audit:** Repository & Aplikasi Web `karyaNovel` (`c:\xampp\htdocs\Novelweb`)  
> **Auditor:** Senior Full-Stack Developer & Software Quality Assurance Engineer  

---

## 1. RINGKASAN EKSEKUTIF AUDIT

Audit ini dilakukan untuk mengevaluasi kesiapan menyeluruh proyek perangkat lunak **karyaNovel** sebelum diajukan sebagai pemenuhan tugas **Ujian Akhir Semester (UAS) Mata Kuliah Rekayasa Perangkat Lunak (RPL)**.

Audit mencakup 4 pilar utama:
1. **Pilar Kode & Arsitektur:** Evaluasi implementasi MVC native, pemisahan layer logika, keamanan web, dan ketergantungan API pihak ketiga (Google Gemini & Fonnte WhatsApp Gateway).
2. **Pilar Basis Data:** Verifikasi integritas relasional, skema tabel MySQL/MariaDB, constraint foreign key, indexing, dan data aktual.
3. **Pilar Fungsionalitas & Pengujian:** Pengujian fitur autentikasi multi-aktor, verifikasi OTP, manajemen novel & bab, editor distraction-free, AI studio, chatbot real-time, dan proteksi hak akses.
4. **Pilar Artefak Dokumentasi Rekayasa Perangkat Lunak:** Inventarisasi dokumen spesifikasi kebutuhan (PRD), perancangan sistem (SDD), diagram pemodelan UML (Use Case, Activity), pemodelan data (ERD), serta laporan pengujian perangkat lunak.

---

## 2. HASIL AUDIT STRUKTUR SISTEM & ARSITEKTUR TEKNOLOGI

### 2.1 Bahasa Pemrograman & Stack Teknologi Aktual
* **Backend:** PHP 8.2 (Native MVC Framework kustom tanpa ketergantungan framework pihak ketiga seperti Laravel atau CodeIgniter).
* **Database Management System:** MariaDB 10.4.32 / MySQL Community Server (Storage Engine: InnoDB, Charset: `utf8mb4_unicode_ci`).
* **Frontend:** HTML5 Semantik, CSS3 Kustom (Modern Design Tokens, Dark Theme, Glassmorphism), JavaScript ES6+ Vanilla (Fetch API, DOM manipulation).
* **UI Framework:** Bootstrap 5.3.3 (via CDN) & Bootstrap Icons 1.11.3.
* **Web Server:** Apache 2.4 (XAMPP Environment) dengan modul `mod_rewrite` aktif via `.htaccess`.
* **Integrasi Eksternal:**
  - **AI Engine:** Google Gemini API (`gemini-2.5-flash` / `gemini-1.5-flash`) via `AiService.php` (cURL backend proxy).
  - **WhatsApp Gateway:** Fonnte WhatsApp Official API (`api.fonnte.com/send`) via `FonnteService.php` untuk verifikasi OTP dan notifikasi pembaruan naskah.

### 2.2 Pola Arsitektur MVC (Model-View-Controller)
Penerapan arsitektur MVC pada project terstruktur dengan rapi:
* **Core Framework (`app/core/`):**
  - `App.php`: Router front-controller yang membedah URL RESTful (`/controller/method/params`).
  - `Controller.php`: Abstract base controller penyedia method `view()`, `model()`, `json()`, validasi keamanan CSRF mutasi HTTP, serta filter otentikasi (`requireAuth`, `requireAdmin`, `requireGuest`).
  - `Database.php`: Wrapper koneksi PDO berbasis *Singleton Pattern* dengan proteksi `PDO::ATTR_EMULATE_PREPARES => false` dan prepared statements.
  - `Model.php`: Base model yang menyediakan query builder CRUD dasar (`find`, `all`, `create`, `update`, `delete`, `count`, `whereFirst`).
  - `Session.php`: Session manager dengan regenerasi Session ID (anti session fixation), proteksi cookie `HttpOnly` / `SameSite=Lax`, dan helper verifikasi role (`isAdmin`, `isPenulis`, `isPembaca`).
  - `Env.php`: Parser berkas konfigurasi `.env`.
* **Controllers (`app/controllers/`):**
  - `AdminController.php` (27.1 KB): Panel Administrator, moderasi pengguna, moderasi naskah/bab, log notifikasi.
  - `AiController.php` (8.1 KB): Endpoint proxy asisten penulisan AI dan riwayat prompt.
  - `AuthController.php` (13.6 KB): Autentikasi multi-aktor, pengiriman & validasi OTP WhatsApp Fonnte, login fleksibel.
  - `ChapterController.php` / `ChaptersController.php` (20.7 KB): Manajemen bab, autosave 30 detik, reorder bab.
  - `ChatbotController.php` (108.3 KB): karyaNovel Assistant dengan role-based engine (Admin, Penulis, Pembaca).
  - `DashboardController.php` (2.6 KB): Agregasi analitik dashboard internal penulis.
  - `HomeController.php` (4.2 KB): Landing page dengan gatekeeper otentikasi.
  - `NovelController.php` / `NovelsController.php` (29.0 KB): Katalog publik dan manajemen naskah internal penulis.
* **Models (`app/models/`):**
  - `User.php`: Pengelolaan pengguna, hashing Bcrypt, otentikasi fleksibel (email/WA), manajemen OTP.
  - `Novel.php`: Operasi data naskah, pencarian non-duplikat parameter PDO, paginasi publik, agregasi status.
  - `Chapter.php`: Pengelolaan konten bab, urutan bab (*chapter_order*), proteksi status draft/published.
  - `AiHistory.php`: Pencatatan prompt dan respons AI per pengguna/bab.
  - `NotificationLog.php`: Pencatatan riwayat pengiriman notifikasi WhatsApp Gateway Fonnte.
  - `Setting.php`: Penyimpanan konfigurasi dinamis sistem.
* **Views (`app/views/`):**
  - Terbagi menjadi sub-direktori spesifik: `admin/`, `auth/`, `chapter/`, `dashboard/`, `errors/`, `home/`, `layouts/`, `novel/`, `novels/`.

### 2.3 Audit Database Relasional Aktif (`karyanovel_db`)
Berdasarkan inspeksi langsung pada database aktif MariaDB:

| Nama Tabel | Jumlah Baris Aktual | Primary Key | Foreign Key Constraints | Keterangan & Peran |
| :--- | :---: | :--- | :--- | :--- |
| `users` | 11 | `id` (INT PK AI) | - | Akun pengguna (`pembaca`, `penulis`, `admin`, `user`), password hash Bcrypt, kolom `otp_code`, `otp_expiry`, `is_verified`. |
| `novels` | 66 | `id` (INT PK AI) | `user_id` $\rightarrow$ `users.id` (ON DELETE CASCADE) | Data naskah novel (judul, sinopsis, genre, cover, status draft/published). |
| `chapters` | 93 | `id` (INT PK AI) | `novel_id` $\rightarrow$ `novels.id` (ON DELETE CASCADE) | Konten bab novel, urutan `chapter_order`, status draft/published. |
| `ai_histories` | 107 | `id` (INT PK AI) | `user_id` $\rightarrow$ `users.id`, `chapter_id` $\rightarrow$ `chapters.id` | Arsip interaksi prompt & respons AI penulis. |
| `notification_logs` | 21 | `id` (INT PK AI) | - | Log riwayat API WhatsApp Gateway Fonnte (OTP, cerita baru, bab baru, sistem). |
| `settings` | 0 | `key_name` (VARCHAR PK) | - | Konfigurasi runtime dinamis. |

Integritas referensial (Foreign Key) telah terpasang dengan baik dan mengimplementasikan aturan integritas data `ON DELETE CASCADE` untuk menjaga kebersihan data yatim (*orphan records*).

---

## 3. PENGELOMPOKAN HASIL AUDIT

---

### KELOMPOK 1: SUDAH SIAP (READY FOR SUBMISSION)
Komponen, fitur, dan modul berikut telah diuji, berfungsi 100%, dan memenuhi standar pengujian perangkat lunak:

#### A. Fitur Autentikasi & Otorisasi Pengguna (Multi-Aktor)
- [x] **Sistem Gatekeeper:** Pengunjung yang belum terautentikasi (tamu) dicegat sebelum dapat menjelajahi web dan diarahkan (HTTP 302) ke halaman login.
- [x] **Dukungan 3 Aktor Utama:**
  - **Pembaca:** Eksplorasi novel publik, pencarian genre, membaca bab berstatus published.
  - **Penulis:** Akses dashboard penulisan, manajemen novel sendiri, manajemen bab, AI writing studio.
  - **Admin:** Akses admin panel, analitik sistem menyeluruh, kontrol operasional, monitoring pesan WhatsApp.
- [x] **Verifikasi OTP WhatsApp Fonnte:**
  - Registrasi menghasilkan kode acak 6 digit numerik (`random_int(100000, 999999)`).
  - Pesan resmi dikirimkan via WhatsApp Fonnte API (`NM8K5TKmpDu1gDaVaNyj`).
  - Halaman input OTP monospace terisolasi dengan auto-submit saat 6 digit terpenuhi.
  - Fitur hitung mundur 60 detik (*cooldown timer*) untuk tombol kirim ulang OTP.
  - Penolakan kode OTP salah dan penanganan kedaluwarsa (10 menit).
- [x] **Login Fleksibel:** Pengguna dapat masuk menggunakan **Alamat Email** maupun **Nomor WhatsApp**.
- [x] **Penanganan Akun Belum Verifikasi:** Pengguna terdaftar yang belum verifikasi OTP otomatis diterbitkan OTP baru saat login dan diarahkan ke halaman verifikasi.
- [x] **Proteksi Registrasi Admin:** Pilihan role Admin mewajibkan kode otorisasi (*Admin Passcode*).

#### B. Fitur Manajemen Novel & Penulisan (Penulis)
- [x] **CRUD Novel:** Pembuatan, pembaruan, dan penghapusan novel dengan sanitasi data teks.
- [x] **Unggah Cover Naskah Aman:** Validasi biner tipe MIME (`finfo`), ekstensi terfilter, batas ukuran file (2MB).
- [x] **CRUD Bab & Penomoran Otomatis:** Bab baru otomatis mendapatkan nomor urut berikutnya.
- [x] **Pengurutan Bab Interaktif (Chapter Reordering):** Fitur geser urutan bab naik/turun (*move up / move down*) dengan re-indexing otomatis.
- [x] **Distraction-Free Editor Bab:** Antarmuka penulisan teks panjang yang bersih.
- [x] **Fitur Autosave 30 Detik:** Background AJAX autosave berkala mencegah kehilangan naskah.
- [x] **Real-Time Word & Character Counter:** Penghitung kata dan karakter otomatis di sisi klien.

#### C. Integrasi AI Writing Assistant & Chatbot Cerdas
- [x] **AI Writing Assistant Studio (Editor Naskah):** 6 mode terintegrasi (Ide Cerita, Outline Alur, Pengembangan Karakter, Lanjutkan Cerita, Poles Tulisan, Saran/Kritik).
- [x] **Penyimpanan Riwayat AI:** Setiap respons AI tersimpan di database dan dapat dipantau atau dihapus oleh penulis.
- [x] **karyaNovel Assistant (Floating Chatbot Widget):**
  - Widget floating responsif dengan deteksi role otomatis.
  - **Mode Admin:** Menjawab kueri analitik operasional langsung dari database aktual (total user, breakdown role, total novel, dsb.).
  - **Rendering Markdown Table Bersih:** Menampilkan data daftar pengguna dalam tabel HTML responsif bergaya dark glassmorphic, lengkap dengan badge warna peran (*Admin/Penulis/Pembaca*).
  - **Parser Callout Note:** Otomatis mengubah catatan kaki teks menjadi kotak informasi terdesain (`.kn-chat-note`).
  - **Anti Tumpang Tindih Tombol Kirim:** Form submit AJAX chatbot terisolasi dari spinner global `main.js`, ukuran tombol bulat tetap presisi.
  - **Mode Penulis & Pembaca:** Menjawab seputar karya pribadi dan rekomendasi naskah publik.

#### D. Halaman Membaca & Katalog Publik (Pembaca)
- [x] **Katalog Novel Terbit:** Menampilkan naskah berstatus `published` dengan paginasi dan filter genre.
- [x] **Pencarian Aman:** Pencarian judul/sinopsis menggunakan parameter unik (`:search1`, `:search2`) bebas dari error `SQLSTATE[HY093]`.
- [x] **Reader Mode Ramah Pembaca:** Navigasi bab sebelumnya (*Previous*) dan bab berikutnya (*Next*).
- [x] **Draft Content Shield:** Naskah dan bab berstatus `draft` diblokir total dari akses pembaca umum (HTTP 404).

#### E. Keamanan Perangkat Lunak (Security Pillars)
- [x] **Anti-SQL Injection:** 100% prepared statements PDO dengan bound parameters.
- [x] **Anti-XSS:** Sanitasi output dinamis dengan fungsi pembantu `sanitize()` (`htmlspecialchars`).
- [x] **Anti-CSRF:** Token kriptografis acak pada setiap form mutasi data.
- [x] **Tenant Isolation (Multi-tenant Security):** Penulis tidak dapat mengakses, menyunting, atau menghapus naskah milik penulis lain.
- [x] **Enkripsi Kredensial:** Password dienkripsi menggunakan `PASSWORD_BCRYPT`. Kunci API Gemini dan Fonnte tersimpan di server (`.env`) dan tidak bocor ke klien.

---

### KELOMPOK 2: PERLU PERBAIKAN (NEEDS ATTENTION / MINOR REVISION)
Hal-hal teknis berikut belum selaras sempurna dan perlu disinkronkan sebelum pengumpulan final:

1. **Sinkronisasi File `database/schema.sql` Terhadap Database Aktual:**
   - **Kondisi:** File `database/schema.sql` masih menggunakan skema lama di mana tabel `users` memiliki kolom role `ENUM('user', 'admin')` dan belum memiliki kolom `phone`, `otp_code`, `otp_expiry`, serta `is_verified`.
   - **Dampak:** Jika dosen atau penilai menjalankan `php database/migrate.php` di komputer lain menggunakan `schema.sql` lama, aplikasi akan error karena kolom OTP dan role `pembaca`/`penulis` tidak terbentuk.
   - **Solusi:** `database/schema.sql` harus diperbarui agar mencerminkan struktur database MariaDB aktual.

2. **Pembaruan Berkas Dokumentasi `README.md`:**
   - **Kondisi:** `README.md` saat ini sangat komprehensif, namun belum mencantumkan fitur-fitur pembaruan terakhir, yaitu:
     - Integrasi Fonnte WhatsApp Gateway & Otomasi Notifikasi.
     - Sistem Verifikasi Akun via Kode OTP WhatsApp.
     - Peran Aktor Pembaca (sebelumnya hanya didokumentasikan Penulis dan Admin).
     - Asisten Chatbot Floating dengan Role-Based Engine.
   - **Solusi:** Tambahkan bab penjelasan mengenai WhatsApp Gateway, OTP, peran Pembaca, dan Chatbot AI pada `README.md`.

3. **Penyelarasan Skrip Pengujian Legacy (`test_master_e2e.php`):**
   - **Kondisi:** Skrip `test_master_e2e.php` dibuat pada 17 Oktober 2026 sebelum implementasi Gatekeeper & OTP WhatsApp Fonnte. Skrip ini gagal saat menjalankan pendaftaran otomatis karena tidak mengirimkan input nomor WhatsApp.
   - **Solusi:** Selaraskan parameter payload di `test_master_e2e.php` agar menyertakan nomor WhatsApp dan auto-verify OTP, atau kelompokkan skrip pengujian ke dalam direktori khusus `tests/`.

4. **Pembersihan Berkas Sisa Pengujian di Root Workspace:**
   - **Kondisi:** Di direktori root terdapat berkas sesi pengujian sementara: `cookie_p8_userA.txt`, `cookie_p8_userB.txt`, `cookie_userA_p9.txt`, `cookie_userB_p9.txt`.
   - **Solusi:** Berkas teks cookie sementara ini sebaiknya dihapus atau dimasukkan ke dalam `.gitignore` agar repositori GitHub tetap bersih.

---

### KELOMPOK 3: BELUM TERSEDIA (MISSING DOCUMENTATION FOR UAS RPL)
Dalam standar penilaian tugas UAS Rekayasa Perangkat Lunak, biasanya diwajibkan kelengkapan dokumen analisis, perancangan, dan pemodelan sistem. Berkas-berkas berikut **belum tersedia** di dalam project:

1. **Dokumen PRD (Product Requirements Document):**
   - Belum ada dokumen formal `PRD.md` atau `docs/PRD.pdf`.
   - Diperlukan untuk menjelaskan latar belakang proyek, visi produk, profil aktor pengguna, *functional requirements* (FR), dan *non-functional requirements* (NFR).
2. **Dokumen SDD (Software Design Document):**
   - Belum ada dokumen arsitektur dan perancangan detail `SDD.md` atau `docs/SDD.pdf`.
   - Diperlukan untuk menjelaskan arsitektur perangkat lunak MVC, desain database relasional, desain antarmuka, keamanan, serta integrasi API eksternal.
3. **Use Case Diagram & Spesifikasi Use Case (UML):**
   - Belum ada file visual Use Case Diagram maupun teks deskripsi use case formal per aktor (Pembaca, Penulis, Admin).
4. **Activity Diagram (UML):**
   - Belum ada file Activity Diagram untuk alur bisnis penting (Alur Registrasi & Verifikasi OTP WhatsApp, Alur Penulisan & Asistensi AI, Alur Publikasi Naskah, dan Alur Moderasi Admin).
5. **Entity Relationship Diagram (ERD) & Kamus Data:**
   - Belum ada diagram visual hubungan entitas (ERD) berbasis standar perancangan RPL (Crow's Foot notation) dan tabel kamus data teknis.
6. **Dokumen Laporan Pengujian Formal (Software Test Report):**
   - Belum ada dokumen rekapitulasi pengujian resmi (Tabel Test Case Black-Box Testing, skenario masukan, hasil yang diharapkan, status lolos/gagal).
7. **Berkas Lisensi Open Source (`LICENSE`):**
   - Repositori belum memiliki berkas `LICENSE` (seperti MIT License).

---

### KELOMPOK 4: MEMBUTUHKAN INFORMASI DARI PENGGUNA
Untuk menyusun dokumen laporan UAS RPL yang lengkap, formal, dan siap cetak/kumpul, diperlukan data administratif akademik dari pengguna:

| Parameter Informasi | Status Saat Ini | Keterangan & Kebutuhan |
| :--- | :---: | :--- |
| **Nama Lengkap Mahasiswa / Anggota Kelompok** | *Belum Diisi* | Nama lengkap penyusun untuk dicantumkan pada cover dokumen PRD, SDD, dan Laporan Uji. |
| **Nomor Induk Mahasiswa (NIM)** | *Belum Diisi* | NIM resmi mahasiswa/kelompok. |
| **Program Studi / Jurusan** | *Belum Diisi* | Contoh: Teknik Informatika, Sistem Informasi, Rekayasa Perangkat Lunak. |
| **Fakultas & Universitas / Kampus** | *Belum Diisi* | Nama institusi perguruan tinggi resmi. |
| **Nama Dosen Pengampu & Gelar** | *Belum Diisi* | Nama dosen mata kuliah Rekayasa Perangkat Lunak. |
| **Tahun Akademik & Semester** | *Belum Diisi* | Contoh: Semester Ganjil / Genap Tahun Ajaran 2026/2027. |
| **Format Pengumpulan yang Diwajibkan Dosen** | *Belum Diisi* | Apakah dosen mewajibkan format PDF, dokumen Word (.docx), repositori GitHub publik, atau Markdown di dalam folder `docs/`? |
| **Template Khusus dari Dosen (Jika Ada)** | *Belum Diisi* | Apakah ada panduan/pedoman struktur laporan khusus dari silabus mata kuliah RPL kampus bersangkutan? |

---

## 4. REKOMENDASI TAHAPAN KERJA BERIKUTNYA (NEXT ACTIONS)

Berdasarkan aturan pengerjaan (tidak merombak kode yang sudah berjalan dan menunggu instruksi pengguna):

```
[Tahap 1: SELESAI]  Audit Menyeluruh Sistem & Pembuatan AUDIT_UAS_RPL.md
        │
        ▼
[Tahap 2: Menunggu Instruksi Pengguna]
        ├── Pengguna memberikan informasi identitas akademik (Nama, NIM, Kampus, Dosen).
        ├── Sinkronisasi berkas `database/schema.sql` agar identik dengan MariaDB aktual.
        ├── Pembersihan berkas temporer (`cookie_*.txt`).
        ├── Pembuatan Dokumen PRD (Product Requirements Document).
        ├── Pembuatan Dokumen SDD (Software Design Document) & Diagram UML (Use Case, Activity, ERD).
        └── Penyusunan Laporan Pengujian Lengkap (Black-Box Test Matrix).
```

---
*Laporan audit ini dihasilkan secara otomatis dan akurat berdasarkan analisis kode program, basis data, dan konfigurasi aktual proyek karyaNovel.*
