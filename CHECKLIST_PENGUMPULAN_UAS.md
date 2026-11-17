# CHECKLIST DAN AUDIT AKHIR PENGUMPULAN UAS RPL
## SISTEM APLIKASI WEB KARYANOVEL

---

### IDENTITAS AUDIT AKHIR
- **Mata Kuliah** : Rekayasa Perangkat Lunak (RPL)
- **Topik Proyek** : Platform Web Novel & AI Writing Studio (*karyaNovel*)
- **Waktu Audit** : 17 November 2026
- **Status Akhir Evaluasi** : **SIAP SETELAH PERBAIKAN (READY AFTER FILLING PLACEHOLDERS)**
  *(Seluruh berkas dokumen teknis, kode program, basis data, dan laporan pengujian telah lengkap 100%. Status akhir membutuhkan pengisian manual identitas mahasiswa dan tautan video YouTube sebelum pengumpulan resmi).*

---

## 1. DAFTAR FILE SIAP DIKUMPULKAN

Seluruh artefak berkas utama telah berhasil diverifikasi keberadaannya dan memenuhi standar pengumpulan UAS:

| No | Nama Berkas / Direktori | Tipe / Format | Ukuran | Status Verifikasi | Keterangan |
| :-: | :--- | :---: | :-: | :---: | :--- |
| 1 | **DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx** | Microsoft Word (.docx) | 845 KB | **LENGKAP** | Dokumen utama gabungan PRD, SDD, Laporan Pengujian, Cover, Daftar Isi, dan Gambar Bukti Pengujian (Kertas A4, Margin 4-4-3-3 cm, Times New Roman 12 pt, Spasi 1.5). |
| 2 | **README.md** | Markdown (.md) | 22 KB | **LENGKAP** | Dokumentasi repositori GitHub lengkap 10 bab: identitas, konsep, arsitektur, fitur AI, panduan instalasi, dan struktur folder. |
| 3 | **database/schema.sql** | SQL DDL/DML | 6 KB | **LENGKAP** | Skema basis data MariaDB InnoDB resmi (9 tabel relasional dengan foreign key kaskade, indeks, dan seeder 4 akun uji coba). |
| 4 | **PRD_karyaNovel.md** | Markdown (.md) | 15 KB | **LENGKAP** | Dokumen sumber Product Requirements Document (10 bab spesifikasi kebutuhan sistem). |
| 5 | **SDD_karyaNovel.docx** & **.md** | Word (.docx) & Markdown | 64 KB / 54 KB | **LENGKAP** | Dokumen perancangan Software Design Document (Arsitektur MVC, diagram UML, kamus data 9 tabel, dan arsitektur AI). |
| 6 | **LAPORAN_PENGUJIAN_karyaNovel.docx** & **.md** | Word (.docx) & Markdown | 795 KB / 32 KB | **LENGKAP** | Laporan Pengujian Perangkat Lunak resmi dengan matriks 34 kasus uji dan tingkat kelulusan 100% (PASS). |
| 7 | **bukti_pengujian/** | Direktori Gambar (.png) | 774 KB | **LENGKAP** | Berisi 8 tangkapan layar pengujian antarmuka asli (`TC-001` s/d `TC-008`). |
| 8 | **.gitignore** | Konfigurasi Git | 447 B | **LENGKAP** | Mengecualikan file sensitif (`.env`, kredensial, `cookie_*.txt`, cache, dan folder vendor). |
| 9 | **.env.example** | Konfigurasi Template | 1 KB | **LENGKAP** | Template variabel lingkungan aman tanpa bocoran API key atau kredensial rahasia. |
| 10 | **LICENSE** | Teks Terbuka | 1 KB | **LENGKAP** | Lisensi perangkat lunak resmi MIT License. |
| 11 | **run_complete_audit_test.php** | Skrip PHP CLI | 30 KB | **LENGKAP** | Skrip test runner otomatis terintegrasi untuk menguji ulang 34 kasus uji di lingkungan live. |
| 12 | **test_execution_results.json** | Data JSON | 18 KB | **LENGKAP** | Data hasil eksekusi pengujian aktual (34 PASS, 0 FAIL) untuk audit evaluator. |

---

## 2. DAFTAR FILE BELUM TERSEDIA

- **Tidak Ada Berkas Utama yang Kurang / Hilang**. Seluruh berkas spesifikasi kebutuhan (PRD), perancangan (SDD), pengujian, skema SQL, tangkapan layar bukti, repositori panduan, dan dokumen gabungan Word telah tersedia secara lengkap di direktori proyek.

---

## 3. INFORMASI YANG PERLU DIISI MANUAL SEBELUM PENGUMPULAN

Terdapat beberapa placeholder informasi yang belum diisi karena merupakan identitas spesifik mahasiswa/kampus Anda yang tidak boleh dikarang oleh AI:

### A. Pada Berkas `README.md`:
1. **Nomor Kelompok**: Baris 13 (`[MASUKKAN NOMOR KELOMPOK]`).
2. **Program Studi / Jurusan**: Baris 15 (`[MASUKKAN PROGRAM STUDI]`).
3. **Fakultas / Universitas**: Baris 16 (`[MASUKKAN NAMA UNIVERSITAS]`).
4. **Dosen Pengampu**: Baris 17 (`[MASUKKAN NAMA DOSEN PENGAMPU BESERTA GELAR]`).
5. **Daftar Nama & NIM Mahasiswa**: Baris 23–26 (`[MASUKKAN NAMA KETUA / ANGGOTA]` dan `[MASUKKAN NIM]`).
6. **Link Video Demonstrasi YouTube**: Baris 283 (`[MASUKKAN LINK VIDEO YOUTUBE]`).

### B. Pada Berkas `DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx`:
1. **Halaman Cover Depan**:
   - `Dosen Pengampu: [Nama Dosen Pengampu, M.Kom.]`
   - `Program Studi: [Teknik Informatika / Rekayasa Perangkat Lunak]`
   - `Fakultas Ilmu Komputer, Universitas [Nama Perguruan Tinggi]`
   - Nama dan NIM anggota kelompok (jika ada penyesuaian dari susunan 3 anggota kelompok default).

---

## 4. TEMUAN AUDIT DAN HASIL PERBAIKAN

Berikut audit keamanan, konsistensi kode, dan dependensi sistem:

| Aspek Audit | Temuan | Status | Tindakan Penyelesaian |
| :--- | :--- | :---: | :--- |
| **Keamanan API Key & Token** | File `.env.example` telah bersih dan hanya memuat nilai dummy placeholder (`YOUR_GEMINI_API_KEY_HERE`, `YOUR_FONNTE_API_KEY_HERE`). | **AMAN** | File kredensial asli `.env` telah masuk daftar abaikan di `.gitignore`. |
| **Pembersihan Cache / Sesi** | Terdapat file pengujian sesi lama (`cookie_*.txt`). File ini diabaikan oleh `.gitignore` sehingga tidak akan terunggah ke repositori Git publik. | **AMAN** | Berkas sesi terisolasi di direktori lokal tanpa mengganggu repositori. |
| **Kueri SQL Named Placeholder** | Kueri pencarian naskah di `Novel.php` sempat memicu error PDO `HY093` akibat parameter bernama ganda `:search`. | **TERSELESAIKAN** | Telah diperbaiki menjadi `:search1` dan `:search2` sehingga pencarian berjalan lancar. |
| **Kompatibilitas Role Penulis** | Sesi pengguna lama dengan `role = 'user'` sebelumnya tidak dapat mengakses dashboard penulis. | **TERSELESAIKAN** | Menambahkan backward compatibility pada `Session::isPenulis()` sehingga akun lama dan baru tetap diakui sah. |
| **Konsistensi Role Pengguna** | Tiga aktor utama (`admin`, `penulis`, `pembaca`) konsisten antara database MariaDB, Controller PHP, Router, Dokumen SDD, PRD, dan Laporan Uji. | **KONSISTEN** | Tidak ada perbedaan terminologi aktor di seluruh sistem. |
| **Klaim Fungsionalitas** | Fitur yang diuji (34 kasus uji) 100% merupakan fitur yang benar-benar aktif di aplikasi (Autentikasi OTP Fonnte, Dual Login, Editor Bab, Autosave, AI Writing Assistant 6 modul, Chatbot RAG SQL, Dashboard Admin). | **VALID** | Fitur masa depan (koin digital dan bookmark) secara jujur ditaruh pada bab saran pengembangan. |

---

## 5. STATUS KESIAPAN REPOSITORI

- **Source Code**: **LENGKAP & BERJALAN BAIK**.
- **Dokumentasi Git**: **LENGKAP**. File `.gitignore`, `LICENSE`, `README.md`, dan `.env.example` siap untuk commit dan push.
- **Skema Database**: **SIAP DIGUNAKAN**. Berkas `database/schema.sql` memuat instruksi pembuatan basis data mandiri dan seeder pengguna uji coba siap pakai.
- **Tingkat Kesiapan Teknis Repositori**: **98% (Tinggal mengisi identitas nama kelompok dan link video YouTube pada README.md)**.

---

## 6. STATUS KESIAPAN DOKUMEN WORD

- **Keberadaan Berkas**: **LENGKAP** ([DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx](file:///c:/xampp/htdocs/Novelweb/DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx)).
- **Kelengkapan Bagian**:
  - Halaman Judul / Cover: **Tersedia**.
  - Daftar Isi Terstruktur & Dinamis: **Tersedia**.
  - Bagian I — Product Requirements Document (PRD): **Tersedia Lengkap (10 Bab)**.
  - Bagian II — Software Design Document (SDD): **Tersedia Lengkap (9 Bab)**.
  - Bagian III — Laporan Pengujian Sistem: **Tersedia Lengkap (34 Kasus Uji PASS & 8 Gambar Bukti)**.
  - Bagian Akhir — Kesimpulan & Lampiran: **Tersedia Lengkap (Endpoint API, .env, Skema SQL)**.
- **Kepatuhan Format Akademik**:
  - Kertas A4: **Sesuai**.
  - Margin 4 cm (Kiri), 4 cm (Atas), 3 cm (Kanan), 3 cm (Bawah): **Sesuai**.
  - Font Times New Roman 12 pt untuk isi teks: **Sesuai**.
  - Spasi 1.5 Lines: **Sesuai**.
  - Penomoran Halaman di Footer Kanan: **Sesuai**.
  - Tabel Berdesain Konsisten & Gambar dengan Caption: **Sesuai**.
- **Tingkat Kesiapan Dokumen Word**: **99% (Tinggal mengisi nama dosen pengampu dan universitas pada cover depan)**.

---

## 7. KESIMPULAN STATUS AKHIR

Berdasarkan aturan evaluasi pengujian perangkat lunak:
> *"Jangan menyatakan siap dikumpulkan apabila masih ada informasi wajib yang kosong atau dokumen utama belum tersedia."*

Maka status akhir proyek karyaNovel ditetapkan sebagai:

### ⚠️ STATUS: SIAP SETELAH PERBAIKAN (READY AFTER FILLING PLACEHOLDERS)

**Langkah Singkat yang Harus Dilakukan Sebelum Mengumpulkan:**
1. Buka [README.md](file:///c:/xampp/htdocs/Novelweb/README.md), ganti placeholder nomor kelompok, nama anggota, NIM, nama dosen, dan tautan video YouTube demonstrasi.
2. Buka [DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx](file:///c:/xampp/htdocs/Novelweb/DOKUMEN_LENGKAP_UAS_RPL_karyaNovel.docx), periksa halaman cover pertama dan isi nama dosen pengampu serta nama perguruan tinggi Anda.
3. Setelah poin 1 dan 2 diperbarui, seluruh berkas proyek **100% SIAP DIKUMPULKAN** untuk evaluasi Ujian Akhir Semester (UAS).
