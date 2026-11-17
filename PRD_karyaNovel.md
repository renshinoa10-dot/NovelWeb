# PRODUCT REQUIREMENTS DOCUMENT (PRD)
## APLIKASI WEB KARYANOVEL — PLATFORM PUBLIKASI NOVEL & ASISTEN PENULIS BERBASIS KECERDASAN BUATAN

---

### IDENTITAS DOKUMEN DAN PROYEK
- **Nama Produk / Sistem** : karyaNovel
- **Versi Produk** : 2.1.0-Production
- **Mata Kuliah** : Rekayasa Perangkat Lunak (UAS RPL)
- **Kelompok** : Kelompok 4
- **Penyusun** :
  1. Ahmad Rian (NIM: 2024001001) — *Lead Developer & AI Integration*
  2. Muhammad Farhan (NIM: 2024001002) — *Full-Stack Developer & Database Engineer*
  3. Siti Aisyah (NIM: 2024001003) — *UI/UX Designer & Software Quality Assurance*
- **Tanggal Dokumen** : 17 November 2026
- **Status Dokumen** : Disetujui (Approved untuk Evaluasi UAS)

---

## 1. PENDAHULUAN
Dokumen *Product Requirements Document* (PRD) ini mendefinisikan secara mendalam visi, tujuan bisnis, karakteristik pengguna, kebutuhan fungsional (*Functional Requirements*), kebutuhan non-fungsional (*Non-Functional Requirements*), serta batasan teknis dari aplikasi **karyaNovel**. Dokumen ini menjadi acuan utama perancangan (*Software Design Document*) dan verifikasi kualitas (*Software Testing Report*) dalam siklus hidup rekayasa perangkat lunak.

---

## 2. LATAR BELAKANG DAN MOTIVASI
Industri literatur digital dan karya fiksi di Indonesia mengalami pertumbuhan yang sangat pesat seiring tingginya adopsi perangkat seluler dan internet. Namun, platform penerbitan novel digital yang ada saat ini menghadapi sejumlah permasalahan mendasar:
1. **Hambatan Kreativitas Penulis (*Writer's Block*)**: Banyak penulis pemula mengalami kebuntuan ide, kesulitan merancang premis naratif, penataan karakter yang dangkal, atau kebingungan dalam memperkaya gaya bahasa sastra.
2. **Keterbatasan Alat Bantu Penulisan Terintegrasi**: Sebagian besar platform web novel hanya menyediakan editor teks dasar (*plain text area*) tanpa integrasi kecerdasan buatan (*AI assistant*) yang selaras dengan kaidah bahasa dan sastra Indonesia.
3. **Kompleksitas Keamanan dan Spam Akun**: Pendaftaran akun pada web novel sering disusupi oleh bot dan akun spam karena minimnya verifikasi identitas dua langkah (*Two-Factor Authentication*) yang terjangkau dan mudah digunakan di Indonesia seperti WhatsApp Gateway.
4. **Minimnya Transparansi Analitik Real-Time**: Pengelola platform (Admin) dan Penulis sering kali kesulitan memperoleh data statistik pembacaan, jumlah naskah, dan metrik operasional yang akurat tanpa harus melakukan kueri basis data manual.

Aplikasi **karyaNovel** hadir sebagai solusi holistik yang mengintegrasikan ekosistem penerbitan naskah novel multi-peran dengan asisten kreatif berbasis kecerdasan buatan (*AI Writing Studio*) dan chatbot operasional cerdas yang tersambung langsung dengan data real-time basis data.

---

## 3. TUJUAN PENGEMBANGAN
Tujuan strategis dari pengembangan karyaNovel adalah:
1. Menyediakan platform publikasi novel digital yang aman, cepat, dan responsif dengan arsitektur Model-View-Controller (MVC) murni tanpa ketergantungan framework berat.
2. Mengintegrasikan teknologi kecerdasan buatan (*Generative AI*) yang adaptif untuk membantu penulis mengatasi kebuntuan naskah melalui 6 modul spesifik: *Generate Idea, Generate Outline, Generate Character, Continue Story, Improve Writing*, dan *Give Suggestions*.
3. Menerapkan sistem verifikasi nomor telepon pengguna menggunakan kode OTP 6-digit via WhatsApp API Gateway (Fonnte) untuk menekan penyalahgunaan akun fiktif.
4. Menghadirkan asisten percakapan (*karyaNovel Assistant Chatbot*) multi-peran yang mampu menjawab pertanyaan pengguna dan menyajikan data analitik operasional berbasis kueri SQL dinamis secara aman.
5. Memenuhi seluruh kriteria rekayasa perangkat lunak standar akademik untuk evaluasi akhir semester (UAS RPL).

---

## 4. RUANG LINGKUP SISTEM
Ruang lingkup sistem karyaNovel meliputi:
- **Pengguna dan Hak Akses**: Pengelolaan tiga tingkatan aktor pengguna (Administrator, Penulis, Pembaca) dengan hak akses terisolasi (*Role-Based Access Control*).
- **Manajemen Naskah dan Bab**: Siklus hidup penerbitan naskah novel mulai dari pembuatan draf, unggah sampul buku (*cover image*), penulisan bab dengan fitur penyimpanan otomatis (*autosave*), penataan urutan bab (*chapter reordering*), hingga publikasi katalog publik.
- **Katalog dan Pembacaan Publik**: Penjelajahan karya berdasarkan genre sastra, penelusuran kata kunci judul/penulis, pembacaan bebas distraksi (*reader mode*), dan navigasi bab berurutan (*prev/next*).
- **Studio Asisten Kecerdasan Buatan**: Fasilitas AI terintegrasi di dalam editor penulisan naskah yang terhubung ke model Gemini / engine bahasa internal cerdas, dilengkapi pembatasan laju kueri (*rate limiting*) dan pencatatan riwayat interaksi (`ai_histories`).
- **Pusat Kendali Administrator**: Pemantauan metrik agregat platform, manajemen akun pengguna, moderasi naskah yang melanggar aturan, dan interaksi analitik melalui chatbot asisten.

---

## 5. DESKRIPSI PRODUK
karyaNovel adalah aplikasi web berbasis PHP 8 native dengan arsitektur MVC berkinerja tinggi, memanfaatkan MariaDB sebagai penyimpanan relasional berintegritas kaskade, Tailwind CSS untuk visual antarmuka modern yang responsif, serta API Fonnte untuk otentikasi pesan instan WhatsApp.

Produk ini dirancang agar dapat diakses secara mulus melalui peramban web modern (*desktop* maupun *mobile*) tanpa memerlukan instalasi aplikasi tambahan oleh pembaca.

---

## 6. PROFIL DAN PERAN PENGGUNA (USER ROLES)

Sistem karyaNovel membedakan secara tegas tiga aktor utama:

### 6.1 Administrator
- **Karakteristik**: Pengelola utama sistem dan penanggung jawab operasional platform.
- **Kewenangan**:
  - Memantau metrik statistik global (total pengguna, rincian per role, total naskah terbit vs draf, total bab, log notifikasi).
  - Melakukan moderasi naskah (menghapus naskah tidak layak beserta kaskade bab terkait).
  - Mengelola akun pengguna (melihat rincian, status verifikasi OTP, hak akses).
  - Berinteraksi dengan Chatbot Administrator untuk memperoleh analitik dan laporan pengguna dalam format tabel Markdown.

### 6.2 Penulis (Author)
- **Karakteristik**: Kreator konten literatur yang memproduksi naskah dan bab cerita.
- **Kewenangan**:
  - Mengakses Dashboard Penulis personal untuk melihat statistik naskah miliknya sendiri.
  - Membuat naskah novel baru, mengunggah sampul, dan menentukan genre sastra.
  - Mengelola bab (menambah, mengubah judul, menulis isi bab dengan autosave, menukar urutan bab, dan beralih status draf/terbit).
  - Menggunakan 6 modul AI Writing Assistant di dalam editor penulisan.
  - Membaca novel karya penulis lain di katalog publik.
  - Berinteraksi dengan Chatbot Asisten dalam konteks kepenulisan naskah personal.

### 6.3 Pembaca (Reader)
- **Karakteristik**: Konsumen konten sastra yang menikmati naskah-naskah terbitan.
- **Kewenangan**:
  - Menjelajahi katalog publik novel berdasarkan genre, kata kunci, dan tingkat popularitas.
  - Membuka halaman detail naskah dan membaca seluruh bab yang berstatus published.
  - Menikmati antarmuka membaca (*reader mode*) yang bersih, nyaman, dan responsif.
  - Berinteraksi dengan Chatbot Asisten untuk panduan membaca dan rekomendasi novel.
  - Dilarang mengakses editor, modul manajemen novel, maupun kueri operasional admin.

---

## 7. KEBUTUHAN FUNGSIONAL (FUNCTIONAL REQUIREMENTS)

Berikut matriks kebutuhan fungsional aplikasi karyaNovel:

| Kode Kebutuhan | Nama Kebutuhan | Deskripsi Fungsional | Aktor Terkait |
| :--- | :--- | :--- | :--- |
| **FR-01** | Registrasi & Verifikasi OTP | Pengguna dapat mendaftar dengan memilih role (*Pembaca* atau *Penulis*), memasukkan nama, email, password, dan nomor WhatsApp. Sistem wajib menerbitkan token OTP 6-digit acak via Fonnte Gateway yang harus diverifikasi sebelum akun dinyatakan aktif. | Calon Pengguna |
| **FR-02** | Autentikasi Login Ganda | Pengguna terdaftar dapat masuk menggunakan kombinasi Alamat Email + Password ATAU Nomor WhatsApp + Password yang diverifikasi melalui fungsi enkripsi `password_verify()`. | Semua Aktor |
| **FR-03** | Gatekeeper Keamanan Rute | Sistem wajib memblokir akses pengguna tamu (tanpa sesi login) pada seluruh rute privat aplikasi dan meredireksi secara otomatis ke halaman login (HTTP 302). | Tamu / Guest |
| **FR-04** | Manajemen Naskah Novel | Penulis dapat membuat naskah novel baru berstatus draf, mengunggah sampul buku, memperbarui metadata (judul, genre, sinopsis), serta mengubah status publikasi (*Draft* $\leftrightarrow$ *Published*). | Penulis, Admin |
| **FR-05** | Editor Bab & Autosave | Penulis dapat menyusun bab cerita pada antarmuka editor yang nyaman, dilengkapi fungsionalitas penyimpanan otomatis (*autosave background API*) ke database untuk mencegah kehilangan teks. | Penulis |
| **FR-06** | Penataan Urutan Bab | Penulis dapat mengatur ulang urutan penomoran bab (*reorder chapter*) secara dinamis melalui antarmuka visual sehingga kronologi baca tetap terjaga. | Penulis |
| **FR-07** | Katalog Publik & Proteksi Draf | Pembaca dapat menjelajahi seluruh novel berstatus `published` dengan fitur pencarian dan paginasi. Sistem wajib memastikan naskah berstatus `draft` terisolasi dan tidak tampil di katalog publik. | Pembaca, Penulis |
| **FR-08** | Reader Mode Responsif | Pembaca dapat membaca bab novel dengan tata letak tipografi yang nyaman, penunjuk estimasi waktu baca, dan tombol navigasi bab sebelumnya (*Previous*) dan bab selanjutnya (*Next*). | Pembaca, Penulis |
| **FR-09** | AI Writing Studio | Penulis dapat memanggil 6 fungsi asisten kecerdasan buatan (*Generate Idea, Outline, Character, Continue Story, Improve Writing, Give Suggestions*) dari dalam editor, dengan batasan rate-limiting 30 request/menit dan pencatatan riwayat interaksi ke tabel `ai_histories`. | Penulis |
| **FR-10** | Chatbot Asisten Berbasis Data | Sistem menyediakan widget chatbot asisten interaktif yang mengenali identitas peran pengguna. Bagi Admin, chatbot menyajikan data analitik operasional riil dan tabel Markdown akun. Bagi peran non-admin, akses terhadap statistik rahasia platform ditolak secara aman. | Semua Aktor |

---

## 8. KEBUTUHAN NON-FUNGSIONAL (NON-FUNCTIONAL REQUIREMENTS)

| Kode Kebutuhan | Parameter | Spesifikasi dan Standar Kualitas |
| :--- | :--- | :--- |
| **NFR-01** | **Kinerja & Responsivitas** | Waktu respons pemuatan halaman rata-rata $\le 1.5$ detik pada jaringan lokal. Kueri agregasi statistik basis data dieksekusi $\le 5$ milidetik melalui optimalisasi indeks database MariaDB. |
| **NFR-02** | **Keamanan Kredensial** | Seluruh kata sandi pengguna wajib di-hash menggunakan algoritma *Blowfish/Bcrypt* (`PASSWORD_BCRYPT`). Kunci rahasia API disimpan terisolasi pada file konfigurasi `.env`. |
| **NFR-03** | **Proteksi Penetrasi Web** | Sistem mengimplementasikan sanitasi input (`htmlspecialchars`, `strip_tags`), parameter binding pada PDO untuk mengeliminasi celah *SQL Injection*, serta proteksi token *CSRF* pada operasi mutasi data penting. |
| **NFR-04** | **Integritas Relasional Data** | Basis data menggunakan engine *InnoDB* dengan integritas kaskade *Foreign Key* (`ON DELETE CASCADE`) untuk memastikan tidak timbul data yatim (*orphan records*) saat naskah atau bab dihapus. |
| **NFR-05** | **Isolasi Multi-Penyewa (*Tenant Isolation*)** | Operasi pembaruan dan penghapusan karya naskah wajib mengunci klausul `WHERE id = :id AND user_id = :user_id` untuk mencegah eskalasi hak akses horizontal antar sesama penulis. |
| **NFR-06** | **Toleransi Kesalahan AI (*Resilience*)** | Jika koneksi atau API key eksternal mengalami kendala kuota/timeout, subsistem AI wajib mengaktifkan mesin generator internal berbasis bahasa sastra Indonesia tanpa menimbulkan kegagalan fatal (*Fatal Error*). |

---

## 9. FITUR KECERDASAN BUATAN (AI CAPABILITIES)

Sistem karyaNovel memanfaatkan dua pilar kecerdasan buatan terintegrasi:

### 9.1 AI Writing Assistant (Asisten Penulisan Naskah)
Tersedia di dalam editor penulisan naskah penulis, terdiri atas 6 kapabilitas:
1. **Generate Idea**: Merumuskan premis naratif kreatif, tema sentral, dan *hook* cerita berdasarkan genre yang dipilih penulis.
2. **Generate Outline**: Menyusun kerangka babak penceritaan (eksposisi, komplikasi, klimaks, antiklimaks, resolusi) menjadi struktur bab yang sistematis.
3. **Generate Character**: Membangun penokohan multidimensi mencakup latar belakang psikologis, kekuatan, cacat kepribadian (*flaw*), dan motivasi tokoh.
4. **Continue Story**: Menjaga momentum narasi dengan melanjutkan adegan cerita yang menggantung selaras dengan nada dan diksi paragraf sebelumnya.
5. **Improve Writing**: Memoles kalimat naskah dengan memperkaya majas, visualisasi pancaindra, dan keindahan diksi sastra.
6. **Give Suggestions**: Memberikan masukan teknis konstruktif mengenai tempo (*pacing*), tensi konflik, dan keefektifan dialog.

### 9.2 Chatbot karyaNovel Assistant (Asisten Operasional Percakapan)
Widget mengambang interaktif (*floating widget*) yang memiliki kesadaran peran (*role-aware*):
- Mengambil data profil dan statistik riil dari sesi server aktif.
- Menyajikan agregasi data pengguna platform, rincian per role, dan katalog naskah bagi Administrator dalam format teks ringkas maupun tabel Markdown yang rapi.
- Menolak kueri operasional sensitif dari pengguna peran Pembaca dan Penulis guna menjaga kerahasiaan platform.
- Mengarahkan kembali pertanyaan yang melenceng (*out-of-context*) ke ranah penulisan dan literatur karyaNovel secara ramah.

---

## 10. BATASAN SISTEM DAN ASUMSI

### 10.1 Batasan Sistem (*System Constraints*)
1. File unggahan sampul novel (*cover*) dibatasi maksimal berukuran 2 MB dengan format gambar `.jpg`, `.jpeg`, atau `.png`.
2. Pengiriman kode OTP WhatsApp bergantung pada ketersediaan koneksi internet dan status operasional layanan pihak ketiga Fonnte Gateway.
3. Permintaan ke AI Writing Assistant dibatasi (*rate-limiting*) maksimal 30 kali per menit untuk setiap akun pengguna guna menjaga stabilitas beban server.
4. Aplikasi dirancang berbasis arsitektur Web Monolitik MVC native PHP tanpa ketergantungan framework pihak ketiga seperti Laravel atau CodeIgniter.

### 10.2 Asumsi Pengembangan (*Assumptions*)
1. Pengguna memiliki peramban web modern yang mendukung JavaScript dan CSS modern (Tailwind).
2. Server hosting menjalankan lingkungan PHP $\ge 8.1$ dengan modul PDO, cURL, OpenSSL, dan mbstring aktif.
3. Layanan database MariaDB/MySQL berjalan stabil pada port lokal default (3306) dengan dukungan engine InnoDB.
