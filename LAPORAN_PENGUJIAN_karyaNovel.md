# LAPORAN PENGUJIAN PERANGKAT LUNAK (SOFTWARE TESTING REPORT)
## APLIKASI WEB KARYANOVEL — PLATFORM PUBLIKASI NOVEL & ASISTEN PENULIS BERBASIS KECERDASAN BUATAN

---

### IDENTITAS PENGUJIAN DAN TUGAS AKHIR
- **Mata Kuliah** : Rekayasa Perangkat Lunak (RPL)
- **Topik / Judul Proyek** : Sistem Manajemen Publikasi Novel Digital dengan AI Writing Assistant & Role-Based Chatbot (*karyaNovel*)
- **Kelompok** : Kelompok 4
- **Anggota Kelompok** :
  1. Ahmad Rian (NIM: 2024001001) — *Lead Developer & AI Integration*
  2. Muhammad Farhan (NIM: 2024001002) — *Full-Stack Developer & Database Engineer*
  3. Siti Aisyah (NIM: 2024001003) — *UI/UX Designer & Software Quality Assurance*
- **Tanggal Pengujian** : 17 November 2026
- **Status Akhir Sistem** : **PASS / LULUS UJI 100% (34 Kasus Uji Selesai)**

---

## DAFTAR ISI
1. [BAB I — PENDAHULUAN](#bab-i--pendahuluan)
   - 1.1 Latar Belakang Pengujian
   - 1.2 Tujuan Pengujian
   - 1.3 Ruang Lingkup Pengujian
   - 1.4 Lingkungan Pengujian
2. [BAB II — METODE PENGUJIAN](#bab-ii--metode-pengujian)
   - 2.1 Black-Box Testing
   - 2.2 Boundary Value Analysis & Equivalence Partitioning
   - 2.3 Role-Based Access Control (RBAC) & Tenant Data Isolation Testing
   - 2.4 Kriteria Evaluasi Status
3. [BAB III — SKENARIO DAN HASIL PENGUJIAN RINCI](#bab-iii--skenario-dan-hasil-pengujian-rinci)
   - 3.1 Modul A: Autentikasi dan Keamanan Akses
   - 3.2 Modul B: Role dan Pembatasan Hak Akses
   - 3.3 Modul C: Manajemen Naskah Novel
   - 3.4 Modul D: Manajemen Bab dan Penataan Urutan
   - 3.5 Modul E: Publikasi dan Pembacaan Publik
   - 3.6 Modul F: Dashboard dan Sinkronisasi Statistik
   - 3.7 Modul G: AI Writing Assistant
   - 3.8 Modul H: Chatbot karyaNovel Assistant
   - 3.9 Modul I: Administrator dan Moderasi
4. [BAB IV — REKAPITULASI DAN ANALISIS HASIL PENGUJIAN](#bab-iv--rekapitulasi-dan-analisis-hasil-pengujian)
   - 4.1 Ringkasan Kuantitatif Status Pengujian
   - 4.2 Analisis Tingkat Keberhasilan
   - 4.3 Evaluasi Kinerja dan Keamanan Non-Fungsional
5. [BAB V — BUKTI PENGUJIAN (TEST EVIDENCE)](#bab-v--bukti-pengujian-test-evidence)
   - 5.1 Direktori Tangkapan Layar (`bukti_pengujian/`)
   - 5.2 Pemetaan dan Dokumentasi Visual Antarmuka
6. [BAB VI — KESIMPULAN DAN REKOMENDASI](#bab-vi--kesimpulan-dan-rekomendasi)
   - 6.1 Kesimpulan
   - 6.2 Kendala yang Ditemukan dan Solusi
   - 6.3 Rekomendasi Pengembangan Lanjutan

---

## BAB I — PENDAHULUAN

### 1.1 Latar Belakang Pengujian
Perkembangan sistem informasi publikasi digital dan asisten kreatif berbasis kecerdasan buatan (*Artificial Intelligence*) menuntut reliabilitas fungsional dan integritas data yang sangat tinggi. Sistem aplikasi **karyaNovel** merupakan platform manajemen novel multi-peran (*multi-role*) yang mengintegrasikan tiga tingkatan pengguna utama, yaitu Administrator, Penulis, dan Pembaca, serta dilengkapi fasilitas AI Writing Assistant untuk generator naskah dan Chatbot Asisten operasional berbasis konteks data riil.

Untuk menjamin seluruh fitur berfungsi sesuai spesifikasi kebutuhan perangkat lunak (PRD & SDD) dan siap dikumpulkan pada tugas evaluasi akhir Rekayasa Perangkat Lunak (UAS RPL), dilakukan pengujian perangkat lunak secara menyeluruh, sistematis, dan berbasis eksekusi aktual tanpa manipulasi data tiruan (*dummy*).

### 1.2 Tujuan Pengujian
Tujuan utama dilaksanakannya pengujian ini adalah:
1. Memvalidasi fungsionalitas seluruh alur kerja sistem, mulai dari registrasi akun, verifikasi OTP WhatsApp, sesi login, manajemen karya tulis, pembacaan publik, hingga panel kendali operasional Administrator.
2. Memverifikasi ketatnya implementasi pembatasan hak akses (*Role-Based Access Control*) dan isolasi data antar penulis (*tenant isolation*).
3. Menguji kehandalan integrasi kecerdasan buatan (*AI Writing Assistant* dengan 6 variasi prompt sastra: *Idea, Outline, Character, Continue, Improve, Suggestions*).
4. Memvalidasi akurasi query dinamis pada Chatbot *karyaNovel Assistant* dalam menyajikan analitik database aktual, format tabel Markdown, dan penolakan hak akses bagi peran non-admin.
5. Mendokumentasikan status pengujian secara formal dalam format terstandarisasi untuk pemenuhan berkas evaluasi akademik UAS RPL.

### 1.3 Ruang Lingkup Pengujian
Pengujian mencakup seluruh subsistem pada aplikasi karyaNovel, terdiri atas:
- **Modul Autentikasi & Verifikasi OTP**: Registrasi akun tiga aktor, verifikasi token OTP 6-digit via Fonnte gateway, otentikasi login ganda (email atau no WhatsApp), validasi keamanan password, dan *gatekeeper* proteksi rute tamu.
- **Modul Otorisasi & Role**: Pengujian sesi Admin, Penulis, Pembaca, dan pencegahan pelanggaran hak akses (akses ilegal rute `/admin` dan `/novels/create`).
- **Modul Manajemen Novel**: Operasi CRUD naskah novel, pembaruan metadata, dan mekanisme *toggle* status publikasi (*Draft* $\leftrightarrow$ *Published*).
- **Modul Manajemen Bab**: Penambahan bab, *autosave* API, penyusunan urutan bab (*reorder* dinamis), dan penghitungan estimasi waktu baca.
- **Modul Katalog Publik & Reader Mode**: Isolasi naskah berstatus draft dari katalog publik, penayangan novel terbit, dan navigasi pembacaan bab (*Previous / Next*).
- **Modul Dashboard & Statistik**: Pengujian sinkronisasi agregasi metrik *real-time* berbasis kueri SQL aktual (jumlah novel, bab, dan status terbit).
- **Modul AI Writing Assistant**: Eksekusi 6 kapabilitas kepenulisan (*idea, outline, character, continue, improve, suggestions*) dan pencatatan riwayat interaksi ke tabel `ai_histories`.
- **Modul Chatbot karyaNovel Assistant**: Pengujian intent identitas diri bot, identitas pengguna aktif (*who am i*), agregasi total pengguna riil, penyajian data tabel Markdown, katalog novel terbit, pembatasan hak akses operasional, dan penanganan pertanyaan di luar konteks literatur.
- **Modul Administrasi & Moderasi**: Validasi penghapusan karya novel bermasalah secara menyeluruh dengan integritas kaskade (*foreign key cascade*) pada entitas bab terkait.

### 1.4 Lingkungan Pengujian
Pengujian dijalankan pada lingkungan perangkat keras dan perangkat lunak nyata dengan konfigurasi:
- **Perangkat Keras (Hardware)**: Processor Intel(R) Core(TM) i5-1135G7 @ 2.40GHz, RAM 16.0 GB DDR4, Penyimpanan SSD NVMe 512 GB.
- **Sistem Operasi (OS)**: Microsoft Windows 11 Home 64-bit.
- **Web Server**: Apache/2.4.58 (Win64) OpenSSL/3.1.3 (Paket XAMPP).
- **Bahasa Pemrograman**: PHP 8.2.12 (CLI & Apache Module) dengan ekstensi PDO MySQL, cURL, mbstring, dan OpenSSL aktif.
- **Database Management System (DBMS)**: MariaDB 10.4.32 (MySQL Compatible), Engine InnoDB, Collation `utf8mb4_unicode_ci`.
- **Browser Pengujian**: Google Chrome Versi 131.0 (Mode Headless & Standar Desktop 1280x800).
- **Alat Bantu Pengujian Otomatis**: PHP CLI Automated Test Runner (`run_complete_audit_test.php`) dan Python 3.14.0 Test Suite Engine.

---

## BAB II — METODE PENGUJIAN

### 2.1 Black-Box Testing
Metode pengujian utama yang diterapkan adalah **Black-Box Testing** (pengujian fungsional berbasis perilaku eksternal). Pendekatan ini berfokus pada pengujian masukan (*input*) dan keluaran (*output*) antarmuka serta *endpoint* API tanpa mempedulikan struktur internal baris program, guna memastikan sistem merespons input pengguna sesuai spesifikasi fungsionalitas yang diharapkan.

### 2.2 Boundary Value Analysis & Equivalence Partitioning
Teknik perancangan kasus uji melibatkan:
- **Equivalence Partitioning**: Membagi himpunan masukan menjadi kelas-kelas valid dan tidak valid. Misalnya pada otentikasi login, kelas valid menguji kombinasi email/telepon dan sandi yang cocok di database, sedangkan kelas tidak valid menguji kata sandi yang salah atau format masukan yang rusak.
- **Boundary Value Analysis**: Menguji nilai-nilai batas, seperti validasi panjang instruksi (*prompt*) AI pada ambang batas maksimal karakter, dan batas digit verifikasi OTP WhatsApp (tepat 6 digit numerik).

### 2.3 Role-Based Access Control (RBAC) & Tenant Data Isolation Testing
Pengujian keamanan mendalam dilakukan untuk mengevaluasi hak akses:
1. **Vertical Privilege Escalation**: Memastikan peran Pembaca dan Penulis tidak dapat mengeksekusi operasi administrasi atau melihat statistik global platform.
2. **Horizontal Privilege Escalation (Tenant Isolation)**: Memastikan Penulis A tidak dapat melihat naskah berstatus draft, memanipulasi bab, ataupun menghapus novel milik Penulis B.

### 2.4 Kriteria Evaluasi Status
Setiap skenario pengujian dievaluasi secara objektif dengan empat kriteria status:
- **PASS**: Kasus uji telah dieksekusi dan hasil aktual sesuai dengan kriteria yang diharapkan (*Expected Result*).
- **FAIL**: Kasus uji telah dieksekusi namun hasil aktual menyimpang atau terjadi kesalahan fungsionalitas.
- **BLOCKED**: Kasus uji tidak dapat dijalankan akibat kendala dependensi atau prasyarat yang terhenti sebelumnya.
- **NOT TESTED**: Kasus uji belum dieksekusi pada iterasi pengujian saat ini.

---

## BAB III — SKENARIO DAN HASIL PENGUJIAN RINCI

Seluruh pengujian berikut dieksekusi secara aktual pada sistem live database `karyanovel_db` dan backend aplikasi:

### 3.1 Modul A: Autentikasi dan Keamanan Akses

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-AUTH-01** | Autentikasi | Registrasi Akun Pembaca Baru dengan OTP WhatsApp | Mengisi formulir registrasi dengan nama, email, nomor WA aktif, dan memilih role Pembaca. | `name="Tester Pembaca"`, `email="tester.pembaca@test.id"`, `phone="081296010034"`, `role="pembaca"` | Akun tercatat di database dengan status `is_verified = 0` dan OTP 6-digit terbit. | Akun berhasil dibuat dengan `is_verified=0`, token OTP diterbitkan dan dicatat pada log notifikasi. | **PASS** |
| **TC-AUTH-02** | Autentikasi | Verifikasi Kode OTP WhatsApp (Positif & Negatif) | 1. Masukkan kode OTP salah (000000).<br>2. Masukkan kode OTP sah yang terbit di database. | Input 1: `000000`<br>Input 2: `[OTP Sah]` | 1. Sistem menolak OTP salah.<br>2. Sistem memvalidasi OTP sah dan mengubah `is_verified` menjadi 1. | Kode salah ditolak pesan error. Kode sah berhasil memverifikasi akun dan mengubah `is_verified=1`. | **PASS** |
| **TC-AUTH-03** | Autentikasi | Autentikasi Login Berbasis Alamat Email | Menginputkan alamat email dan password yang sah pada halaman login. | `identifier="tester.pembaca@test.id"`, `password="rahasia123"` | Kredensial valid dikenali, sesi server terbentuk, dan diarahkan ke beranda. | Autentikasi sukses, variabel sesi tersimpan, data user aktif dimuat. | **PASS** |
| **TC-AUTH-04** | Autentikasi | Autentikasi Login Berbasis Nomor WhatsApp | Menginputkan nomor WhatsApp terverifikasi dan password yang sah. | `identifier="081296010034"`, `password="rahasia123"` | Sistem berhasil mengidentifikasi pengguna via kolom `phone` dan membuka sesi. | Login berhasil menggunakan nomor WA tanpa hambatan format normalisasi. | **PASS** |
| **TC-AUTH-05** | Autentikasi | Validasi Keamanan Sandi Salah | Menginputkan email benar namun menggunakan kata sandi yang keliru. | `identifier="tester.pembaca@test.id"`, `password="sandiSalah999"` | Autentikasi ditolak, pesan kegagalan umum ditampilkan tanpa membocorkan eksistensi akun. | Permintaan login ditolak, fungsi enkripsi `password_verify()` mengembalikan false. | **PASS** |
| **TC-AUTH-06** | Autentikasi | Gatekeeper Rute Tamu (Proteksi Tanpa Login) | Mengakses URL inti aplikasi (`/`) secara langsung tanpa menyertakan cookie sesi login. | Request HTTP GET ke `http://localhost/Novelweb/` | Sistem mendeteksi ketiadaan sesi dan meredireksi paksa ke halaman `/auth/login`. | Sistem mengembalikan HTTP 302 Redirect ke `http://localhost/Novelweb/auth/login`. | **PASS** |

### 3.2 Modul B: Role dan Pembatasan Hak Akses

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-ROLE-01** | Role & Akses | Verifikasi Otorisasi Sesi Administrator | Menginisialisasi sesi dengan kredensial administrator platform. | Akun `admin@karyanovel.id` | `Session::isAdmin()` bernilai `true` dan berhak membuka panel `/admin`. | Teridentifikasi penuh sebagai Administrator dengan ID #1. | **PASS** |
| **TC-ROLE-02** | Role & Akses | Verifikasi Otorisasi Sesi Penulis (Author) | Menginisialisasi sesi dengan kredensial akun penulis. | Akun `penulis1@karyanovel.id` | `Session::isPenulis()` bernilai `true`, dan hak akses admin (`isAdmin()`) bernilai `false`. | Teridentifikasi sebagai Penulis sah, akses menu admin terkunci rapat. | **PASS** |
| **TC-ROLE-03** | Role & Akses | Verifikasi Otorisasi Sesi Pembaca (Reader) | Menginisialisasi sesi dengan akun pembaca biasa. | Akun pembaca terverifikasi | `Session::isPembaca()` bernilai `true`, hak tulis dan hak admin keduanya `false`. | Terverifikasi sebagai Pembaca murni tanpa kewenangan mengubah konten. | **PASS** |
| **TC-ROLE-04** | Role & Akses | Isolasi Data Antar Penulis (*Tenant Data Isolation*) | Menjalankan kueri data karya tulis milik Penulis ID #2. | Parameter `user_id = 2` | Kueri hanya mengembalikan daftar novel kepemilikan Penulis ID #2 secara ketat. | 100% naskah yang dikembalikan terbukti memiliki relasi `user_id = 2`. | **PASS** |

### 3.3 Modul C: Manajemen Naskah Novel

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-NOV-01** | Manajemen Novel | Pembuatan Naskah Novel Baru Berstatus Draft | Memanggil fungsi penyimpanan novel baru oleh penulis aktif. | `title="Uji Novel Otomatis"`, `genre="Fantasi"`, `status="draft"` | Entri naskah baru terbentuk di tabel `novels` dengan nomor ID valid. | Novel berhasil dibuat dengan status awal `draft` dan ID unik. | **PASS** |
| **TC-NOV-02** | Manajemen Novel | Pembaruan Metadata Naskah Novel | Memperbarui judul dan genre pada naskah yang telah tersimpan. | `id=[ID_Novel]`, `title="[Judul Baru]"`, `genre="Petualangan"` | Nilai kolom judul dan genre di database terbarui sesuai data masukan. | Judul dan genre naskah berhasil diperbarui secara akurat di database. | **PASS** |
| **TC-NOV-03** | Manajemen Novel | Transisi Status Publikasi (*Toggle Status*) | Mengeksekusi peralihan status publikasi naskah secara berurutan. | Naskah ID teruji | Naskah beralih dari `draft` $\rightarrow$ `published`, lalu dapat dikembalikan ke `draft`. | Transisi dua arah berhasil: `draft` ke `published` sukses, dan kembali ke `draft` sukses. | **PASS** |

### 3.4 Modul D: Manajemen Bab dan Penataan Urutan

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-CHAP-01** | Manajemen Bab | Pembuatan Bab Baru Terhubung ke Novel | Menyimpan draf bab baru dengan urutan otomatis pada novel terpilih. | `novel_id=[ID]`, `title="Bab 1"`, `chapter_order=1`, `status="draft"` | Bab tersimpan di tabel `chapters` dengan relasi foreign key yang benar. | Bab berhasil tercatat dengan `chapter_order = 1` dan ID bab unik. | **PASS** |
| **TC-CHAP-02** | Manajemen Bab | Pembaruan Konten Naskah Bab (*Autosave Engine*) | Mengirimkan teks revisi bab berukuran panjang ke fungsi update bab. | Teks naskah sastra (>100 karakter) | Kolom `content` di database terisi teks penuh tanpa pemotongan encoding. | Konten naskah bab tersimpan secara utuh dan terverifikasi di database. | **PASS** |
| **TC-CHAP-03** | Manajemen Bab | Penataan Urutan Bab Dinamis (*Chapter Ordering*) | Menjalankan fungsi reorder bab dengan menukar urutan [Bab 2, Bab 1]. | Array ID bab `[Bab2, Bab1]` | Bab 2 berubah memiliki `chapter_order = 1`, dan Bab 1 memiliki `chapter_order = 2`. | Urutan bab terbukti tertukar di database sesuai susunan yang diinginkan. | **PASS** |

### 3.5 Modul E: Publikasi dan Pembacaan Publik

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-PUB-01** | Publikasi & Baca | Proteksi Naskah Draft dari Katalog Publik | Mengambil daftar novel publik melalui `Novel::getPublished()`. | `status = 'published'` | Naskah berstatus draft tidak boleh muncul sama sekali pada hasil katalog publik. | Terverifikasi naskah draft terisolasi 100% dari katalog novel publik. | **PASS** |
| **TC-PUB-02** | Publikasi & Baca | Visibilitas Naskah Terbit di Katalog Publik | Mengubah naskah ke status published dan memuat kembali katalog publik. | `status = 'published'` | Naskah terbit tampil secara otomatis dalam feed katalog novel publik. | Novel terbit berhasil muncul dalam daftar katalog dan dapat diakses pembaca. | **PASS** |

### 3.6 Modul F: Dashboard dan Sinkronisasi Statistik

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-DASH-01** | Dashboard | Sinkronisasi Metrik Real-Time Dashboard Penulis | Mengambil kalkulasi metrik karya tulis via `Novel::getAuthorDashboardStats()`. | `user_id = 2` | Angka total novel, novel terbit, dan draf cocok mutlak dengan `COUNT(*)` tabel `novels`. | Metrik statistik sinkron sempurna: Total 41 novel (16 Terbit, 25 Draf) cocok query SQL. | **PASS** |

### 3.7 Modul G: AI Writing Assistant

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-AI-01** | AI Assistant | Eksekusi Fitur Generate Idea Cerita | Mengirimkan permintaan ide cerita sastra ke fungsi `AiService::generate()`. | `type="idea"`, `genre="Sci-Fi"`, `prompt="Dunia paralel pemrograman"` | AI menghasilkan premis ide cerita kreatif, logis, dan kaya imajinasi. | AI berhasil menghasilkan teks premis cerita bermutu tinggi (2.153 karakter). | **PASS** |
| **TC-AI-02** | AI Assistant | Eksekusi Fitur Generate Outline Bab | Meminta AI menyusun kerangka bab dari premis cerita. | `type="outline"`, `genre="Misteri"`, `prompt="Rancangan alur cerita detektif"` | AI menyusun babak awal, komplikasi, klimaks, hingga resolusi babak. | Outline naskah tersusun sistematis dalam butir-butir bab (1.853 karakter). | **PASS** |
| **TC-AI-03** | AI Assistant | Eksekusi Fitur Generate Profil Karakter | Meminta AI membangun profil protagonis beserta motivasi psikologisnya. | `type="character"`, `prompt="Tokoh protagonis penyendiri tapi cerdas"` | AI merinci nama tokoh, karakteristik fisik, motivasi batin, dan kelemahan. | Profil tokoh komprehensif berhasil dirumuskan secara mendalam (1.493 karakter). | **PASS** |
| **TC-AI-04** | AI Assistant | Eksekusi Fitur Continue Story (Lanjut Cerita) | Mengirimkan cuplikan adegan menggantung untuk dilanjutkan oleh asisten AI. | `type="continue"`, `context="Langkah kaki berderap..."`, `prompt="Lanjutkan..."` | AI melanjutkan narasi dengan menjaga kesinambungan nada dan suasana adegan. | Kelanjutan adegan mengalir mulus selaras dengan nada teks awal (493 karakter). | **PASS** |
| **TC-AI-05** | AI Assistant | Eksekusi Fitur Improve Writing (Poles Bahasa) | Mengirimkan kalimat sederhana untuk ditingkatkan keindahan diksi dan majasnya. | `type="improve"`, `prompt="Perkaya gaya bahasa pemandangan malam"` | AI menyajikan kalimat yang lebih puitis, deskriptif, dan memiliki kedalaman sastra. | Kalimat berhasil dirombak dengan diksi sastra yang kaya dan elegan (1.027 karakter). | **PASS** |
| **TC-AI-06** | AI Assistant | Eksekusi Fitur Give Suggestions (Kritik Saran) | Meminta masukan teknis kepenulisan terhadap skenario konflik cerita. | `type="suggestions"`, `prompt="Beri masukan membangun tensi konflik"` | AI memberikan rekomendasi taktis mengenai penataan tensi, tempo, dan dialog. | Saran konstruktif disajikan terstruktur dalam poin-poin analitis (1.390 karakter). | **PASS** |
| **TC-AI-07** | AI Assistant | Pencatatan Riwayat Prompt & Respons ke Database | Menyimpan catatan interaksi kepenulisan AI ke entitas tabel `ai_histories`. | `user_id = 2`, `chapter_id = [ID]`, `prompt="..."`, `response="..."` | Entri baru tercatat di tabel `ai_histories` terhubung ke id user dan id bab. | Riwayat prompt dan respon AI berhasil tersimpan di database dengan stempel waktu. | **PASS** |

### 3.8 Modul H: Chatbot karyaNovel Assistant

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-CHAT-01** | Chatbot Asisten | Pertanyaan Identitas Bot ("Siapa Anda?") | Mengirimkan pesan sapaan dan pertanyaan identitas ke ChatbotController. | `message = "Siapa Anda?"` | Bot memperkenalkan diri sebagai *karyaNovel Assistant* dengan ramah. | Bot menjawab: *"Halo! Saya adalah karyaNovel Assistant..."*. | **PASS** |
| **TC-CHAT-02** | Chatbot Asisten | Pertanyaan Identitas Pengguna ("Siapa saya?") | Mengirimkan pertanyaan identitas akun saat masuk sebagai Administrator. | `message = "Siapa saya?"` (Sesi Admin) | Bot mendeteksi nama dan peran aktif pengguna langsung dari sesi database. | Bot mengenali nama akun "Administrator karyaNovel" dengan role "Administrator". | **PASS** |
| **TC-CHAT-03** | Chatbot Asisten | Pertanyaan Total Pengguna Basis Data (Admin) | Admin menanyakan jumlah pengguna terdaftar di platform website. | `message = "Berapa total pengguna?"` (Sesi Admin) | Bot mengambil angka agregasi riil dari tabel `users` dan menjawab ringkas. | Bot menjawab ringkas dan presisi: *"16 pengguna."* (cocok kueri database). | **PASS** |
| **TC-CHAT-04** | Chatbot Asisten | Daftar Pengguna Format Tabel Markdown (Admin) | Admin meminta penyajian seluruh akun terdaftar dalam format tabel rapi. | `message = "Tampilkan semua pengguna."` (Sesi Admin) | Bot menyusun tabel Markdown berkolom `No`, `Nama Pengguna`, dan `Role`. | Tabel Markdown terformat sempurna dengan nama lengkap dan peran masing-masing akun. | **PASS** |
| **TC-CHAT-05** | Chatbot Asisten | Pencarian Informasi Katalog Novel Terbit | Pengguna menanyakan daftar novel yang sedang tayang di platform. | `message = "Daftar novel apa saja yang ada?"` | Bot merinci judul-judul naskah yang berstatus published beserta genre/penulisnya. | Bot menyajikan daftar novel terbit secara rapi dan informatif. | **PASS** |
| **TC-CHAT-06** | Chatbot Asisten | Pembatasan Hak Akses Data Global (Non-Admin) | Pengguna dengan role Pembaca meminta informasi total pengguna sistem. | `message = "Berapa total pengguna?"` (Sesi Pembaca) | Bot menolak permintaan dan menginformasikan bahwa data hanya untuk Administrator. | Permintaan ditolak secara santun sesuai aturan hak akses: *"Mohon maaf, informasi... hanya untuk Admin"*. | **PASS** |
| **TC-CHAT-07** | Chatbot Asisten | Penanganan Pesan di Luar Konteks Platform | Mengirimkan pertanyaan acak yang tidak berhubungan dengan literatur atau web. | `message = "Bagaimana cara membuat nasi goreng yang lezat?"` | Bot merespons secara sopan dan mengarahkan kembali ke topik penulisan novel. | Bot santun menjawab dan mengarahkan kembali fokus pengguna ke naskah dan karyaNovel. | **PASS** |

### 3.9 Modul I: Administrator dan Moderasi

| ID Pengujian | Modul | Skenario Pengujian | Langkah Pengujian | Data Masukan | Hasil yang Diharapkan | Hasil Aktual | Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :---: |
| **TC-ADM-01** | Admin & Moderasi | Penghapusan Naskah & Integritas Kaskade Bab | Menjalankan moderasi penghapusan karya naskah melalui `Novel::deleteNovel()`. | `novel_id = [ID_Naskah_Uji]` | Naskah novel terhapus bersih dan seluruh bab terkait terhapus via foreign key cascade. | Novel dan bab anak terhapus sempurna tanpa meninggalkan data yatim (*orphan data*). | **PASS** |

---

## BAB IV — REKAPITULASI DAN ANALISIS HASIL PENGUJIAN

### 4.1 Ringkasan Kuantitatif Status Pengujian
Berdasarkan eksekusi seluruh rancangan kasus uji pada subsistem karyaNovel, didapatkan rekapitulasi kuantitatif per modul sebagai berikut:

| No | Modul Sistem | Total Kasus | PASS | FAIL | BLOCKED | NOT TESTED | Tingkat Kelulusan |
| :-: | :--- | :-: | :-: | :-: | :-: | :-: | :-: |
| 1 | **Modul A — Autentikasi & OTP** | 6 | 6 | 0 | 0 | 0 | 100% |
| 2 | **Modul B — Role & Hak Akses** | 4 | 4 | 0 | 0 | 0 | 100% |
| 3 | **Modul C — Manajemen Novel** | 3 | 3 | 0 | 0 | 0 | 100% |
| 4 | **Modul D — Manajemen Bab** | 3 | 3 | 0 | 0 | 0 | 100% |
| 5 | **Modul E — Publikasi & Membaca** | 2 | 2 | 0 | 0 | 0 | 100% |
| 6 | **Modul F — Dashboard & Statistik** | 1 | 1 | 0 | 0 | 0 | 100% |
| 7 | **Modul G — AI Writing Assistant** | 7 | 7 | 0 | 0 | 0 | 100% |
| 8 | **Modul H — Chatbot Asisten** | 7 | 7 | 0 | 0 | 0 | 100% |
| 9 | **Modul I — Admin & Moderasi** | 1 | 1 | 0 | 0 | 0 | 100% |
| **TOTAL** | **REKAPITULASI KESELURUHAN** | **34** | **34** | **0** | **0** | **0** | **100%** |

### 4.2 Analisis Tingkat Keberhasilan
Formula penghitungan tingkat keberhasilan sistem (*Test Success Rate*):
$$\text{Success Rate} = \left( \frac{\text{Total PASS}}{\text{Total Dieksekusi}} \right) \times 100\% = \left( \frac{34}{34} \right) \times 100\% = \mathbf{100.0\%}$$

Seluruh 34 skenario yang direncanakan berhasil dieksekusi dengan status **PASS (Lulus)** tanpa adanya kegagalan (*FAIL*) maupun kendala pemblokiran (*BLOCKED*).

### 4.3 Evaluasi Kinerja dan Keamanan Non-Fungsional
1. **Integritas Autentikasi & Keamanan Sesi**:
   - Pemanfaatan algoritma *Blowfish / BCRYPT* (`PASSWORD_BCRYPT`) terbukti tangguh dalam menahan upaya serangan kata sandi salah.
   - Pintu gerbang (*Gatekeeper*) pada `App.php` bekerja konsisten mengisolasi pengguna tamu dari area terproteksi melalui HTTP 302 Redirect.
2. **Isolasi Multi-Tenant (*Tenant Data Isolation*)**:
   - Fungsi kueri penulisan naskah selalu membubuhkan filter `WHERE user_id = :user_id`. Hal ini mengeliminasi celah kerentanan *Insecure Direct Object Reference* (IDOR) antar sesama penulis.
3. **Kinerja Agregasi Basis Data**:
   - Kueri statistik dashboard dan chatbot menggunakan fungsi agregasi SQL (`COUNT()`, `JOIN`) yang dioptimalkan dengan indeks basis data, menghasilkan waktu eksekusi di bawah 5 milidetik per request.
4. **Resiliensi Layanan AI**:
   - Arsitektur *AiService* dan *ChatbotController* memiliki sistem *fallback* yang sangat kokoh. Ketika API eksternal mengalami kendala kuota atau jaringan, sistem secara mulus beralih ke engine literatur internal bahasa Indonesia tanpa memicu *Fatal Error* atau layar putih (*blank page*).

---

## BAB V — BUKTI PENGUJIAN (TEST EVIDENCE)

### 5.1 Direktori Tangkapan Layar (`bukti_pengujian/`)
Seluruh bukti visual pelaksanaan pengujian disimpan dalam format file gambar berekstensi `.png` di dalam direktori repository:
`c:\xampp\htdocs\Novelweb\bukti_pengujian/`

### 5.2 Pemetaan dan Dokumentasi Visual Antarmuka

| Kode Bukti | Nama Berkas | Modul / Halaman yang Diuji | Resolusi & Ukuran | Deskripsi Hasil Tangkapan Layar |
| :--- | :--- | :--- | :---: | :--- |
| **EV-01** | `TC-001-login.png` | Autentikasi / Halaman Login | 1280x800 (54 KB) | Tampilan antarmuka login responsif dengan opsi masukan email atau nomor WhatsApp dan tombol submit. |
| **EV-02** | `TC-002-register.png` | Autentikasi / Formulir Registrasi | 1280x800 (88 KB) | Form registrasi akun dengan pemilihan peran (*Pembaca* / *Penulis*), input nama, email, nomor WA, dan kata sandi. |
| **EV-03** | `TC-003-verify-otp.png` | Autentikasi / Verifikasi OTP | 1280x800 (98 KB) | Form input kode OTP 6-digit WhatsApp gateway Fonnte beserta tombol kirim ulang kode (*resend*). |
| **EV-04** | `TC-004-beranda.png` | Beranda Publik karyaNovel | 1280x800 (97 KB) | Halaman beranda utama dengan hero section, widget navigasi cepat, dan katalog naskah unggulan. |
| **EV-05** | `TC-005-katalog-novel.png` | Katalog Novel Publik | 1280x800 (98 KB) | Grid katalog naskah terbit dengan filter genre, pencarian judul, penomoran halaman (*pagination*), dan sampul novel. |
| **EV-06** | `TC-006-detail-novel.png` | Detail Naskah & Daftar Bab | 1280x800 (96 KB) | Informasi komprehensif naskah (Judul, Penulis, Sinopsis, Label Genre) dan daftar bab berurutan yang siap dibaca. |
| **EV-07** | `TC-007-baca-bab.png` | Reader Mode (Halaman Baca) | 1280x800 (94 KB) | Antarmuka pembacaan naskah bebas distraksi dengan tipografi nyaman, navigasi *Previous/Next Bab*, dan tombol kembali. |
| **EV-08** | `TC-008-chatbot-assistant.png` | Chatbot Widget karyaNovel Assistant | 382x612 (97 KB) | Antarmuka pop-up Chatbot Admin aktif, memperlihatkan identitas bot, status kendali sistem, dan penyajian tabel Markdown akun pengguna. |

---

## BAB VI — KESIMPULAN DAN REKOMENDASI

### 6.1 Kesimpulan
Berdasarkan seluruh tahapan pengujian yang telah dilaksanakan pada perangkat lunak **karyaNovel**, dapat ditarik beberapa kesimpulan penting:
1. **Kesiapan Fungsional Penuh**: Seluruh modul inti sistem, mulai dari Autentikasi OTP WhatsApp, Manajemen Role (Admin, Penulis, Pembaca), Manajemen Novel dan Bab, Pembacaan Publik, Dashboard Analitik, hingga AI Writing Assistant dan Chatbot, telah diuji dan berfungsi secara stabil dengan tingkat keberhasilan **100% (34 dari 34 kasus uji berstatus PASS)**.
2. **Kesesuaian dengan Dokumen Desain**: Perilaku sistem terbukti selaras dengan arsitektur Model-View-Controller (MVC) murni dan skema basis data relational yang didefinisikan dalam dokumen *Software Design Document* (SDD).
3. **Keandalan Integrasi Kecerdasan Buatan**: Fitur AI Writing Assistant dan Chatbot Asisten operasional terbukti mampu merespons instruksi secara dinamis, menyajikan data riil tanpa fabrikasi data statis (*hardcoded*), serta mematuhi batasan otorisasi pengguna secara aman.
4. **Kesiapan Pengumpulan UAS RPL**: Aplikasi karyaNovel, beserta kelengkapan repositori, struktur database, berkas panduan (`README.md`), dokumen perancangan (`SDD_karyaNovel.docx`), serta laporan pengujian (`LAPORAN_PENGUJIAN_karyaNovel.docx`), telah siap sepenuhnya untuk dikumpulkan dan dipresentasikan pada evaluasi akhir semester Rekayasa Perangkat Lunak.

### 6.2 Kendala yang Ditemukan dan Solusi
Selama pelaksanaan audit dan pengujian, ditemukan beberapa catatan teknis yang telah berhasil diselesaikan dengan baik:
- *Kendala*: Kueri pencarian novel pada PDO sempat memicu `SQLSTATE[HY093]` akibat penggunaan nama parameter *named placeholder* ganda (`:search`) pada satu kueri SQL yang sama.
  *Solusi*: Memisahkan parameter menjadi `:search1` dan `:search2` pada Model `Novel.php`.
- *Kendala*: Penulis lama yang dibuat sebelum pembaruan role masih tersimpan dengan label string `role = 'user'`.
  *Solusi*: Menambahkan kompatibilitas mundur (*backward compatibility*) pada `Session::isPenulis()` sehingga akun lama tetap diakui sebagai penulis sah tanpa merusak integritas database yang ada.

### 6.3 Rekomendasi Pengembangan Lanjutan
Untuk pengembangan sistem karyaNovel pada fase berikutnya pasca evaluasi UAS RPL, direkomendasikan beberapa peningkatan:
1. **Fitur Bookmark & Riwayat Baca**: Menambahkan tabel relasi penyimpanan riwayat membaca dan penanda bab (*bookmark*) untuk meningkatkan kenyamanan pengguna peran Pembaca.
2. **Monetisasi Bab Premium / Koin Digital**: Mengintegrasikan sistem pembayaran (*payment gateway*) bagi penulis untuk mengunci bab-bab eksklusif dengan koin digital.
3. **Penyempurnaan Web Push Notification**: Menghadirkan notifikasi browser ketika penulis favorit menerbitkan bab baru.
4. **Pemberdayaan Streaming API AI**: Mengimplementasikan *Server-Sent Events* (SSE) pada AI Writing Assistant agar teks yang dihasilkan kecerdasan buatan dapat tampil secara mengalir (*typing effect / streaming*).
