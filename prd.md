# PRD — SiCepatKeg

## Sistem Informasi Percepatan Kinerja &amp; Kegiatan Instansi

&gt; **Codename:** SiCepatKeg  

&gt; **Platform:** Web Application (Laravel 11)  

&gt; **Versi Dokumen:** 1.2  

&gt; **Terakhir Diperbarui:** 2026-09-22  

&gt; **Status:** Draft — Ready for Development  

&gt; **Referensi Data:** `Data Kegiatan.xlsx` (sheet: Sheet1)

---

## 1. Latar Belakang

Percepatan pelaksanaan anggaran kegiatan merupakan salah satu kunci utama dalam meningkatkan kinerja instansi. Saat ini, proses perencanaan, penginputan, verifikasi, dan pemantauan kegiatan masih dilakukan secara manual menggunakan spreadsheet (contoh: `Data Kegiatan.xlsx`), sehingga:

- Sulit mengetahui kegiatan mana yang akan dilaksanakan dalam waktu dekat.

- Verifikasi &amp; validasi persiapan kegiatan tidak terstruktur.

- PPTK sering kali kurang siap karena tidak ada notifikasi/penanda kegiatan yang mendekati jadwal.

- Pimpinan kesulitan memantau progress secara real-time dan responsif.

- Data antar unit kerja tidak terpusat, rawan duplikasi, dan sulit dilaporkan.

Aplikasi **SiCepatKeg** berbasis **Laravel** akan mendigitalisasi proses tersebut dengan pengelolaan kegiatan, kalender pemantauan, verifikasi berjenjang, serta monitoring yang informatif.

---

## 2. Tujuan Produk

1. Mempercepat pelaksanaan anggaran kegiatan melalui digitalisasi pengelolaan kegiatan.

2. Memberikan kontrol penuh kepada setiap **Seksi**, **Tim**, dan **Sub Bagian** terhadap kegiatan yang akan dilaksanakan.

3. Menyediakan **tampilan kalender** untuk memantau kegiatan ke depan, dengan **penanda khusus kegiatan H-7** agar PPTK dapat bersiap.

4. Menampilkan **informasi kegiatan secara terperinci** (mengacu pada struktur `Data Kegiatan.xlsx`).

5. Menyediakan **verifikasi &amp; validasi berjenjang** terhadap persiapan kegiatan.

6. Menyediakan **dashboard pemantauan responsif &amp; informatif** bagi pimpinan.

7. Menyediakan **migrasi data awal** dari file Excel ke database aplikasi.

---

## 3. Ruang Lingkup

### 3.1 In-Scope

- Manajemen user &amp; role (Admin, Kasi/Sub Bag, PPTK, Verifikator, Pimpinan).

- Manajemen kegiatan (CRUD) sesuai struktur data Excel.

- Kalender kegiatan + penanda H-7.

- Verifikasi &amp; validasi persiapan kegiatan.

- Dashboard monitoring responsif.

- Notifikasi/reminder kegiatan mendatang.

- **Import data dari Excel** `Data Kegiatan.xlsx`).

- **Laporan realisasi anggaran** (Pagu vs Realisasi vs Sisa).

### 3.2 Out-of-Scope (Fase Awal)

- Integrasi langsung dengan SIMKEU/SIPD.

- Modul penganggaran penuh (hanya referensi nomor DPA/SPD).

- Aplikasi mobile native (cukup responsive web).

---

## 4. Target Pengguna &amp; Peran

| Role | Deskripsi | Hak Akses Utama |

|------|-----------|------------------|

| **Admin** | Pengelola sistem | Manajemen user, master data, konfigurasi, import Excel |

| **Kasi / Kasubbag / Ketua Tim** | Penginput kegiatan | Input, edit, ajukan kegiatan untuk verifikasi |

| **PPTK** | Pelaksana teknis kegiatan | Melihat jadwal, menyiapkan kegiatan H-7, update progress |

| **Verifikator** | Verifikasi &amp; validasi | Approve/Reject/revisi persiapan kegiatan |

| **Pimpinan** | Monitoring | Dashboard, laporan, monitoring kalender |

---

## 4A. Master Data Unit Kerja (Seksi &amp; Sub Bagian)

### 4A.1 Daftar Unit Kerja

| Kode | Nama Unit Kerja | Jenis | Singkatan | Warna |

|------|-----------------|-------|-----------|-------|

| SPR | Seksi Pemerintahan | Seksi | Pem | 🔵 `#3B82F6` |

| SPB | Seksi Pembangunan | Seksi | Bang | 🟢 `#10B981` |

| SSB | Seksi Sosial dan Budaya | Seksi | Sosbud | 🟠 `#F59E0B` |

| SPM | Seksi Pemberdayaan Masyarakat | Seksi | PM | 🟣 `#8B5CF6` |

| SKT | Seksi Keamanan dan Ketertiban Umum | Seksi | Trantib | 🔴 `#EF4444` |

| TMV | Tim Monev Kecamatan | Tim | Monev | 🔷 `#06B6D4` |

| SBU | Sub Bagian Umum dan Kepegawaian | Sub Bagian | Umpeg | ⚫ `#6B7280` |

**Total: 7 Unit Kerja** (5 Seksi, 1 Tim, 1 Sub Bagian)

### 4A.2 Struktur Tabel `sections`

```php

Schema::create('sections', function (Blueprint $table) {

    $table-&gt;id();

    $table-&gt;string('code', 10)-&gt;unique();      // SSB, SPM, TMV, SBU, SPB, SPR, SKT

    $table-&gt;string('name');                    // Nama lengkap

    $table-&gt;string('short_name', 30);          // Singkatan

    $table-&gt;enum('type', ['seksi', 'sub_bagian', 'tim']);

    $table-&gt;string('color', 7)-&gt;default('#3B82F6');

    $table-&gt;string('head_name')-&gt;nullable();

    $table-&gt;boolean('is_active')-&gt;default(true);

    $table-&gt;integer('order')-&gt;default(0);

    $table-&gt;timestamps();

});

```

### 4A.3 Seeder Data Awal

```php

Section::insert([

    ['code'=&gt;'SPR','name'=&gt;'Seksi Pemerintahan','short_name'=&gt;'Pem','type'=&gt;'seksi','color'=&gt;'#3B82F6','order'=&gt;1],

    ['code'=&gt;'SPB','name'=&gt;'Seksi Pembangunan','short_name'=&gt;'Bang','type'=&gt;'seksi','color'=&gt;'#10B981','order'=&gt;2],

    ['code'=&gt;'SSB','name'=&gt;'Seksi Sosial dan Budaya','short_name'=&gt;'Sosbud','type'=&gt;'seksi','color'=&gt;'#F59E0B','order'=&gt;3],

    ['code'=&gt;'SPM','name'=&gt;'Seksi Pemberdayaan Masyarakat','short_name'=&gt;'PM','type'=&gt;'seksi','color'=&gt;'#8B5CF6','order'=&gt;4],

    ['code'=&gt;'SKT','name'=&gt;'Seksi Keamanan dan Ketertiban Umum','short_name'=&gt;'Trantib','type'=&gt;'seksi','color'=&gt;'#EF4444','order'=&gt;5],

    ['code'=&gt;'TMV','name'=&gt;'Tim Monev Kecamatan','short_name'=&gt;'Monev','type'=&gt;'tim','color'=&gt;'#06B6D4','order'=&gt;6],

    ['code'=&gt;'SBU','name'=&gt;'Sub Bagian Umum dan Kepegawaian','short_name'=&gt;'Umpeg','type'=&gt;'sub_bagian','color'=&gt;'#6B7280','order'=&gt;7],

]);

```

---

## 5. User Stories

1. **Sebagai Kasi**, saya ingin menginput kegiatan beserta detail persiapannya agar dapat diajukan untuk diverifikasi.

2. **Sebagai Verifikator**, saya ingin memverifikasi &amp; memvalidasi kelengkapan persiapan kegiatan agar kegiatan siap dilaksanakan.

3. **Sebagai PPTK**, saya ingin melihat kalender kegiatan dengan penanda H-7 agar saya dapat mempersiapkan kebutuhan kegiatan.

4. **Sebagai Pimpinan**, saya ingin melihat dashboard responsif agar dapat memantau kinerja seluruh seksi/subbag secara cepat.

5. **Sebagai Admin**, saya ingin mengelola user &amp; role, serta mengimpor data dari Excel agar migrasi data lama tetap terbawa.

6. **Sebagai Pimpinan**, saya ingin melihat laporan realisasi anggaran (Pagu, Realisasi, Sisa) per unit kerja dan per periode.

---

## 6. Kebutuhan Fungsional

### 6.1 Manajemen User &amp; Autentikasi

- **FR-01**: Login/logout dengan email &amp; password.

- **FR-02**: Manajemen user (CRUD) oleh Admin.

- **FR-03**: Role &amp; permission (Spatie Permission).

- **FR-03a**: Setiap user **wajib** terhubung ke **1 unit kerja** `section_id`).

- **FR-03b**: Role default: `kasi`, `pptk`, `staf`, `verifikator`, `pimpinan`, `admin`.

- **FR-04**: Reset password &amp; profil user.

### 6.2 Manajemen Kegiatan — **Mengacu Struktur `Data Kegiatan.xlsx`**

- **FR-05**: CRUD kegiatan dengan field berikut (mapping langsung dari Excel):

| Kolom Excel | Field DB | Tipe | Keterangan |

|-------------|----------|------|------------|

| `Bulan` | `activity_date` | `date` | Tanggal pelaksanaan kegiatan |

| `Bidang` | `section_id` | `FK` | Relasi ke tabel `sections` |

| `Kode Rekening` | `account_code` | `string(30)` | Nomor rekening DPA |

| `Kegiatan` | `program_name` | `text` | Nama program/urusan |

| `Judul Kegiatan` | `title` | `string(255)` | Judul spesifik kegiatan |

| `Kebutuhan Kegiatan` | `requirement_qty` | `integer` | Jumlah kebutuhan |

| `Jumlah` | `total_qty` | `integer` | Jumlah total peserta/sasaran |

| `Satuan` | `unit` | `string(30)` | Contoh: "Orang / Kali", "Orang / Hari" |

| `Pagu` | `budget_pagu` | `decimal(15,2)` | Pagu anggaran |

| `Realisasi` | `budget_realization` | `decimal(15,2)` | Realisasi anggaran |

| `Sisa` | `budget_remaining` | `decimal(15,2)` | Sisa (auto = pagu - realisasi) |

- **FR-05a**: `budget_remaining` **otomatis dihitung** di backend `pagu - realisasi`), tidak diinput manual.

- **FR-05b**: Validasi: `realisasi <= pagu`, `total_qty >= requirement_qty`.

- **FR-06**: Upload dokumen pendukung (TOR, RAB, KAK, dsb).

- **FR-07**: Filter &amp; pencarian kegiatan (per unit kerja, per tanggal, per status, per kode rekening).

- **FR-07a**: Filter kegiatan berdasarkan **7 unit kerja**.

- **FR-07b**: Setiap kegiatan **wajib** memiliki `section_id`.

- **FR-07c**: Filter berdasarkan **bulan/tahun** anggaran.

- **FR-07d**: Pencarian bebas pada `title`, `program_name`, `account_code`.

**Status kegiatan:**

`Draft`, `Diajukan`, `Diverifikasi`, `Disetujui`, `Berjalan`, `Selesai`, `Ditolak`

### 6.2.1 Modul Import Excel (Baru)

- **FR-05c**: Admin dapat mengimpor file `Data Kegiatan.xlsx` (format sesuai sheet yang ada).

- **FR-05d**: Sistem memvalidasi mapping `Bidang` (Excel) → [`sections.name`](http://sections.name) (DB).

- **FR-05e**: Preview hasil import sebelum disimpan (dry-run).

- **FR-05f**: Log import (jumlah sukses, gagal, alasan gagal).

- **FR-05g**: Template Excel untuk import ulang.

**Contoh isi file (dari Data Kegiatan.xlsx):**

| Bulan | Bidang | Kode Rekening | Kegiatan | Judul Kegiatan | Kebutuhan Kegiatan | Jumlah | Satuan | Pagu | Realisasi | Sisa |

|-------|--------|---------------|----------|----------------|--------------------|--------|--------|------|-----------|------|

| 2026-09-23 | Seksi Sosial dan Budaya | 7.01.02.2.04.0003 | Pelaksanaan Urusan Pemerintahan... | Aktifitas Lapangan Kegiatan PORPEMKAB | 50 | 150 | Orang / Kali | 8.100.000 | 0 | 8.100.000 |

| 2026-09-25 | Seksi Pemberdayaan Masyarakat | 7.01.03.2.01.0003 | Peningkatan Efektifitas Kegiatan... | Rapat Optimalisasi PKK dan Posyandu | 70 | 520 | Orang / Kali | 36.920.000 | 13.561.000 | 23.359.000 |

| 2026-09-28 | Tim Monev Kecamatan | 7.01.06.2.01.00003 | Fasilitasi Pengelolaan Keuangan Desa... | Monitoring Evaluasi Desa A | 9 | 189 | Orang / Hari | 32.130.000 | 0 | 32.130.000 |

&gt; Data lengkap: **56 baris kegiatan** dari 7 unit kerja, periode September–Desember 2026.

### 6.3 Verifikasi &amp; Validasi Persiapan Kegiatan

- **FR-08**: Alur verifikasi berjenjang: `Kasi/Kasubbag/Ketua Tim → Verifikator → Pimpinan`

- **FR-09**: Checklist kelengkapan persiapan (dokumen, anggaran, SDM, jadwal).

- **FR-10**: Catatan revisi &amp; riwayat verifikasi (log).

- **FR-11**: Status otomatis berubah sesuai keputusan.

### 6.4 Kalender Kegiatan

- **FR-12**: Tampilan kalender (bulanan, mingguan, harian).

- **FR-13a**: Warna event otomatis mengikuti `section.color`.

- **FR-14**: **Highlight kegiatan H-7** (warna kuning/merah) sebagai penanda kesiapan PPTK.

- **FR-15**: Klik event → muncul detail kegiatan (sesuai field Excel).

- **FR-16**: Filter kalender berdasarkan unit kerja, PPTK, status, bulan.

- **FR-16a**: Legend kalender menampilkan 7 unit kerja + marker H-7.

### 6.5 Detail Informasi Kegiatan

- **FR-17**: Halaman detail kegiatan memuat: Bidang, Kode Rekening, Kegiatan, Judul Kegiatan, Kebutuhan, Jumlah, Satuan, Pagu, Realisasi, Sisa, dokumen, progress, riwayat verifikasi, PPTK.

- **FR-18**: Timeline progress kegiatan (progress bar %).

- **FR-19**: Komentar/catatan antar role.

### 6.6 Dashboard Monitoring Responsif

- **FR-20**: Widget statistik: total kegiatan, kegiatan berjalan, selesai, ditolak, H-7.

- **FR-20a**: Widget **"Kegiatan per Unit Kerja"** menampilkan 7 kartu (jumlah kegiatan &amp; total pagu).

- **FR-21**: Grafik kegiatan per unit kerja.

- **FR-21a**: Grafik batang perbandingan 7 unit kerja (jumlah kegiatan &amp; realisasi).

- **FR-22**: Grafik realisasi anggaran vs pagu (**mengacu kolom Pagu, Realisasi, Sisa**).

- **FR-22a**: Widget **Top 5 kegiatan dengan pagu terbesar**.

- **FR-22b**: Widget **Kegiatan yang belum ada realisasi** (realisasi = 0).

- **FR-23**: List kegiatan terdekat (upcoming 30 hari).

- **FR-24**: Responsif di mobile, tablet, desktop.

### 6.7 Notifikasi

- **FR-25**: Notifikasi in-app untuk kegiatan H-7, verifikasi baru, revisi.

- **FR-26**: (Opsional) Email/WhatsApp gateway.

### 6.8 Laporan

- **FR-27**: Export laporan kegiatan (PDF/Excel) **dengan format kolom sama seperti file Excel asli**.

- **FR-28**: Laporan per unit kerja, per periode (bulan), per status.

- **FR-28a**: Laporan **realisasi anggaran** (Pagu, Realisasi, Sisa, % Realisasi).

- **FR-28b**: Laporan **per kode rekening**.

### 6.9 Manajemen Unit Kerja

- **FR-29**: Admin CRUD unit kerja.

- **FR-30**: Admin mengatur warna &amp; urutan tampilan unit.

- **FR-31**: Setiap unit kerja menampilkan **Kepala Unit** (Kasi/Kasubbag/Ketua Tim).

- **FR-32**: Statistik ringkas per unit di halaman detail unit.

---

## 7. Kebutuhan Non-Fungsional

| Aspek | Kebutuhan |

|-------|-----------|

| **Teknologi** | Laravel 11, PHP 8.2+, MySQL/PostgreSQL |

| **Frontend** | Blade + TailwindCSS / Bootstrap 5, Alpine.js/Livewire |

| **Kalender** | FullCalendar.js |

| **Chart** | Chart.js / ApexCharts |

| **Excel** | Laravel Excel (maatwebsite/excel) untuk import/export |

| **PDF** | barryvdh/laravel-dompdf |

| **Auth** | Laravel Breeze/Jetstream + Spatie Permission |

| **Keamanan** | CSRF, XSS Protection, Password Hashing (bcrypt), Role-based Access |

| **Performa** | Response &lt; 3 detik, caching query dashboard |

| **Responsif** | Mobile-first, min. 360px width |

| **Bahasa** | Indonesia |

| **Audit** | Log aktivitas user (activity log) |

---

## 7A. Matriks Hak Akses per Unit Kerja

| Fitur | Admin | Kasi/Kasubbag | PPTK | Verifikator | Pimpinan |

|-------|:-----:|:-------------:|:----:|:-----------:|:--------:|

| Kelola User | ✅ | ❌ | ❌ | ❌ | ❌ |

| Import Excel | ✅ | ❌ | ❌ | ❌ | ❌ |

| Input Kegiatan (unit sendiri) | ✅ | ✅ | ❌ | ❌ | ❌ |

| Input Kegiatan (semua unit) | ✅ | ❌ | ❌ | ❌ | ❌ |

| Verifikasi Kegiatan | ✅ | ❌ | ❌ | ✅ | ❌ |

| Approve Final | ✅ | ❌ | ❌ | ❌ | ✅ |

| Lihat Kalender (unit sendiri) | ✅ | ✅ | ✅ | ✅ | ✅ |

| Lihat Kalender (semua unit) | ✅ | ❌ | ❌ | ✅ | ✅ |

| Dashboard Global | ✅ | ❌ | ❌ | ✅ | ✅ |

| Update Progress Kegiatan | ✅ | ✅ | ✅ | ❌ | ❌ |

| Lihat Laporan Realisasi | ✅ | ✅ | ❌ | ✅ | ✅ |

---

## 8. Arsitektur &amp; Struktur Database

### 8.1 Tabel Utama

- `users`, `roles`, `permissions`, `model_has_roles`

- `sections` (7 unit kerja)

- `activities` (kegiatan — hasil mapping Excel)

- `activity_documents`

- `activity_checklists`

- `verifications` (riwayat verifikasi)

- `notifications`

- `activity_logs`

- `import_logs` (baru — log import Excel)

### 8.2 Skema Tabel `activities`

```php

Schema::create('activities', function (Blueprint $table) {

    $table-&gt;id();

    $table-&gt;date('activity_date');                    // kolom "Bulan"

    $table-&gt;foreignId('section_id')-&gt;constrained();   // kolom "Bidang"

    $table-&gt;string('account_code', 30);               // kolom "Kode Rekening"

    $table-&gt;text('program_name');                     // kolom "Kegiatan"

    $table-&gt;string('title');                          // kolom "Judul Kegiatan"

    $table-&gt;integer('requirement_qty');               // kolom "Kebutuhan Kegiatan"

    $table-&gt;integer('total_qty');                     // kolom "Jumlah"

    $table-&gt;string('unit', 30);                       // kolom "Satuan"

    $table-&gt;decimal('budget_pagu', 15, 2);            // kolom "Pagu"

    $table-&gt;decimal('budget_realization', 15, 2)-&gt;default(0); // kolom "Realisasi"

    $table-&gt;decimal('budget_remaining', 15, 2)-&gt;storedAs('budget_pagu - budget_realization'); // kolom "Sisa" (generated)

    $table-&gt;string('location')-&gt;nullable();

    $table-&gt;foreignId('pptk_id')-&gt;nullable()-&gt;constrained('users');

    $table-&gt;enum('status', ['draft','diajukan','diverifikasi','disetujui','berjalan','selesai','ditolak'])-&gt;default('draft');

    $table-&gt;integer('progress')-&gt;default(0);          // 0-100

    $table-&gt;text('description')-&gt;nullable();

    $table-&gt;timestamps();

    $table-&gt;index(['activity_date', 'section_id']);

    $table-&gt;index('status');

});

```

### 8.3 Relasi Kunci

- `users` → `sections` (many to one)

- `activities` → `sections`, `users(pptk)`

- `verifications` → `activities`, `users`

- `activity_documents` → `activities`

- `import_logs` → `users`

---

## 9. Alur Proses Utama

```

┌──────────────────────────────────────────────────┐

│  ADMIN: Import Data Kegiatan.xlsx (56 records)  │

└──────────────────────┬───────────────────────────┘

                       ↓

┌──────────────────────────────────────────────────┐

│  INPUT: Kasi/Kasubbag/Ketua Tim (7 unit kerja)  │

│  → Seksi Pemerintahan                           │

│  → Seksi Pembangunan                            │

│  → Seksi Sosial dan Budaya                      │

│  → Seksi Pemberdayaan Masyarakat                │

│  → Seksi Keamanan dan Ketertiban Umum           │

│  → Tim Monev Kecamatan                          │

│  → Sub Bagian Umum dan Kepegawaian              │

└──────────────────────┬───────────────────────────┘

                       ↓

          ┌────────────────────────┐

          │   VERIFIKATOR          │

          └───────────┬────────────┘

                      ↓

             ┌────────────────┐

             │   PIMPINAN     │

             └────────────────┘

                      ↓

        [Kegiatan Disetujui → Muncul di Kalender]

                      ↓

              [H-7 Alert ke PPTK]

                      ↓

        [PPTK Update Progress &amp; Realisasi]

                      ↓

        [Laporan Realisasi Anggaran]

```

---

## 10. Desain UI/UX

- **Dashboard**: 7 kartu unit kerja + grafik + upcoming + statistik pagu/realisasi.

- **Kalender**: FullCalendar dengan legend warna + badge H-7.

- **Detail Kegiatan**: Tab (Info, Dokumen, Verifikasi, Progress, Komentar).

- **Form Input**: Wizard step-by-step, auto-calculate Sisa = Pagu - Realisasi.

- **Halaman Verifikasi**: List antrian + panel detail di samping.

- **Halaman Import Excel**: Upload → Preview → Konfirmasi → Log.

---

## 11. Metrik Keberhasilan (KPI)

1. Waktu input &amp; verifikasi kegiatan turun minimal 50%.

2. 100% kegiatan dari `Data Kegiatan.xlsx` berhasil dimigrasi.

3. 0 kegiatan terlewat tanpa persiapan (H-7 alert berjalan).

4. Kepuasan user (survey) ≥ 80%.

5. Dashboard diakses minimal 3x/minggu oleh pimpinan.

6. Akurasi perhitungan Sisa = Pagu - Realisasi = 100%.

---

## 12. Roadmap Pengembangan

| Fase | Fitur | Estimasi |

|------|-------|----------|

| **Fase 1** | Auth, Role, Master 7 Unit Kerja, CRUD Kegiatan, **Import Excel**, Verifikasi dasar | 4 minggu |

| **Fase 2** | Kalender (warna per unit) + H-7 marker, Dashboard, Notifikasi | 3 minggu |

| **Fase 3** | Laporan per unit kerja + Realisasi, Export Excel/PDF, Activity Log, UI Polish | 2 minggu |

| **Fase 4** | Integrasi SIMKEU/SIPD, Mobile App | TBD |

---

## 13. Risiko &amp; Mitigasi

| Risiko | Mitigasi |

|--------|----------|

| User resisten terhadap sistem baru | Pelatihan + UI sederhana |

| Data Excel tidak konsisten | Validasi import + preview dry-run |

| Duplikasi kegiatan | Unique constraint `account_code + title + activity_date` |

| Kalender penuh event | Filter &amp; zoom level kalender |

| Keamanan data anggaran | Role-based access + enkripsi |

---

## 14. Lampiran

### 14.1 Ringkasan Data Awal (dari `Data Kegiatan.xlsx`)

- **Total kegiatan:** 56 baris

- **Periode:** September – Desember 2026

- **Unit kerja terlibat:** 7 (semua unit)

- **Kegiatan dengan realisasi &gt; 0:** 15 baris

- **Kegiatan dengan realisasi = 0:** 41 baris

- **Total Pagu:** ± Rp 1.008.580.000

- **Total Realisasi:** ± Rp 122.121.000

- **Total Sisa:** ± Rp 886.459.000

### 14.2 Distribusi Kegiatan per Unit Kerja

| Unit Kerja | Jumlah Kegiatan | Total Pagu (Rp) |

|------------|:---------------:|-----------------:|

| Seksi Pemerintahan | 13 | 173.050.000 |

| Seksi Pembangunan | 7 | 132.300.000 |

| Seksi Sosial dan Budaya | 3 | 70.690.000 |

| Seksi Pemberdayaan Masyarakat | 16 | 271.320.000 |

| Seksi Keamanan dan Ketertiban Umum | 3 | 11.096.000 |

| Tim Monev Kecamatan | 13 | 417.690.000 |

| Sub Bagian Umum dan Kepegawaian | 1 | 2.130.000 |

| **TOTAL** | **56** | **1.008.580.000** |

&gt; *Angka di atas adalah estimasi berdasarkan file Excel — akan difinalisasi saat seeding.*

### 14.3 Contoh Mapping Field Excel → DB

```

Excel                     →  DB Column

─────────────────────────────────────────────

Bulan                     →  activity_date

Bidang                    →  section_id (lookup)

Kode Rekening             →  account_code

Kegiatan                  →  program_name

Judul Kegiatan            →  title

Kebutuhan Kegiatan        →  requirement_qty

Jumlah                    →  total_qty

Satuan                    →  unit

Pagu                      →  budget_pagu

Realisasi                 →  budget_realization

Sisa                      →  budget_remaining (generated)

```

### 14.4 Dokumen Tambahan

- Wireframe (Figma)

- ERD ([dbdiagram.io](http://dbdiagram.io))

- Template Excel Import

- Panduan User (dibuat di Fase 3)

---

## 15. Changelog

| Versi | Tanggal | Perubahan |

|-------|---------|-----------|

| 1.0 | 2026-09-22 | Draft awal PRD |

| 1.1 | 2026-09-22 | Tambah Master Data 7 Unit Kerja, Matriks Hak Akses, Alur Verifikasi, Roadmap |

| 1.2 | 2026-09-22 | **Integrasi struktur data `Data Kegiatan.xlsx`**, tambah modul Import Excel, mapping field, laporan realisasi anggaran (Pagu/Realisasi/Sisa), ringkasan 56 kegiatan |

---

**© 2026 — SiCepatKeg**  

*Dokumen ini bersifat internal dan dapat berubah sesuai kebutuhan pengembangan.*