# Klasify - Entity Relationship Diagram (ERD)

Dokumen ini menjelaskan struktur database, daftar tabel, atribut (kolom), tipe data, dan relasi (Foreign Key) pada aplikasi Klasify.

## 1. Daftar Tabel

| No | Nama Tabel | Deskripsi |
| --- | --- | --- |
| 1 | `roles` | Menyimpan data role pengguna (guru, bendahara, siswa). |
| 2 | `users` | Menyimpan data akun pengguna untuk autentikasi. |
| 3 | `kelas` | Menyimpan data kelas (misal: XII RPL 1). |
| 4 | `siswas` | Menyimpan data detail siswa (NIS, NISN, Orang Tua). |
| 5 | `iurans` | Menyimpan data tagihan iuran bulanan per kelas. |
| 6 | `transaksis` | Menyimpan data pembayaran iuran oleh siswa. |
| 7 | `pengeluarans` | Menyimpan data pengajuan dan realisasi pengeluaran dana. |
| 8 | `keterlambatans` | Menyimpan data denda keterlambatan pembayaran siswa. |
| 9 | `notifikasis` | Menyimpan data notifikasi untuk setiap user. |
| 10 | `settings` | Menyimpan konfigurasi global sistem (denda per hari, maks denda). |
| 11 | `personal_access_tokens` | Menyimpan token autentikasi Sanctum. |

## 2. Detail Struktur Tabel

### 2.1. `roles`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(255) | Nama role (guru, bendahara, siswa) |
| `display_name` | VARCHAR(255) | Nama tampilan role |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.2. `users`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `role_id` | BIGINT, FK | Foreign Key ke `roles.id` |
| `kelas_id` | BIGINT, FK, NULL | Foreign Key ke `kelas.id` (Wajib diisi untuk Guru) |
| `name` | VARCHAR(100) | Nama lengkap user |
| `email` | VARCHAR(100), UNIQUE | Email untuk login |
| `password` | VARCHAR(255) | Password (Hash Bcrypt) |
| `no_hp` | VARCHAR(15), NULL | Nomor handphone |
| `foto` | VARCHAR(255), NULL | URL foto profil |
| `is_active` | BOOLEAN | Status aktif/nonaktif akun |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.3. `kelas`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `nama` | VARCHAR(50) | Nama kelas (misal: XII RPL 1) |
| `tahun_ajaran` | VARCHAR(20) | Tahun ajaran (misal: 2024/2025) |
| `wali_kelas_id` | BIGINT, FK, NULL | Foreign Key ke `users.id` (Guru wali kelas) |
| `is_active` | BOOLEAN | Status aktif/nonaktif kelas |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.4. `siswas`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `user_id` | BIGINT, FK, UNIQUE | Foreign Key ke `users.id` (Relasi 1:1) |
| `kelas_id` | BIGINT, FK | Foreign Key ke `kelas.id` |
| `nis` | VARCHAR(20), UNIQUE | Nomor Induk Siswa |
| `nisn` | VARCHAR(20), UNIQUE, NULL | Nomor Induk Siswa Nasional |
| `tempat_lahir` | VARCHAR(50), NULL | Tempat lahir siswa |
| `tanggal_lahir` | DATE, NULL | Tanggal lahir siswa |
| `alamat` | TEXT, NULL | Alamat siswa |
| `nama_ortu` | VARCHAR(100), NULL | Nama orang tua siswa |
| `no_hp_ortu` | VARCHAR(15), NULL | Nomor HP orang tua siswa |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.5. `iurans`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `kelas_id` | BIGINT, FK | Foreign Key ke `kelas.id` |
| `bulan` | INTEGER | Bulan iuran (1-12) |
| `tahun` | INTEGER | Tahun iuran (misal: 2026) |
| `nominal` | DECIMAL(15,2) | Nominal iuran |
| `jatuh_tempo` | DATE | Tanggal jatuh tempo pembayaran |
| `is_active` | BOOLEAN | Status iuran aktif (bisa dibayar) |
| `created_by` | BIGINT, FK | Foreign Key ke `users.id` (Guru pembuat) |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.6. `transaksis`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `siswa_id` | BIGINT, FK | Foreign Key ke `siswas.id` |
| `iuran_id` | BIGINT, FK | Foreign Key ke `iurans.id` |
| `jumlah` | DECIMAL(15,2) | Nominal dibayar |
| `tanggal_bayar` | DATE | Tanggal pembayaran |
| `metode` | VARCHAR(255) | Metode bayar (transfer, cash, qris) |
| `bukti_bayar` | VARCHAR(255), NULL | URL foto bukti bayar |
| `status` | VARCHAR(255) | Status transaksi (pending, confirmed, rejected) |
| `confirmed_by` | BIGINT, FK, NULL | Foreign Key ke `users.id` (Guru/Bendahara konfirmator) |
| `confirmed_at` | TIMESTAMP, NULL | Waktu konfirmasi |
| `keterangan` | TEXT, NULL | Keterangan tambahan |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.7. `pengeluarans`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `kelas_id` | BIGINT, FK | Foreign Key ke `kelas.id` |
| `judul` | VARCHAR(200) | Judul pengeluaran |
| `deskripsi` | TEXT, NULL | Deskripsi pengeluaran |
| `jumlah` | DECIMAL(15,2) | Nominal pengeluaran |
| `tanggal` | DATE | Tanggal pengeluaran |
| `kategori` | VARCHAR(50), NULL | Kategori pengeluaran |
| `bukti_foto` | VARCHAR(255), NULL | URL foto bukti/nota |
| `status` | VARCHAR(255) | Status (pending, approved, rejected) |
| `created_by` | BIGINT, FK | Foreign Key ke `users.id` (Bendahara pengaju) |
| `approved_by` | BIGINT, FK, NULL | Foreign Key ke `users.id` (Guru penyetuju) |
| `approved_at` | TIMESTAMP, NULL | Waktu disetujui |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.8. `keterlambatans`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `siswa_id` | BIGINT, FK | Foreign Key ke `siswas.id` |
| `iuran_id` | BIGINT, FK | Foreign Key ke `iurans.id` |
| `hari_telat` | INTEGER | Jumlah hari telat |
| `denda` | DECIMAL(15,2) | Nominal denda |
| `status` | ENUM | Status denda (belum_bayar, sudah_bayar_denda) |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.9. `notifikasis`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `user_id` | BIGINT, FK | Foreign Key ke `users.id` (Penerima) |
| `sender_id` | BIGINT, FK, NULL | Foreign Key ke `users.id` (Pengirim, NULL jika sistem) |
| `judul` | VARCHAR(200) | Judul notifikasi |
| `pesan` | TEXT | Isi pesan |
| `tipe` | VARCHAR(255) | Tipe notif (info, warning, danger, success) |
| `is_read` | BOOLEAN | Status dibaca (false=belum, true=sudah) |
| `link` | VARCHAR(255), NULL | URL tujuan jika notif diklik |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

### 2.10. `settings`
| Nama Kolom | Tipe Data | Keterangan |
| --- | --- | --- |
| `id` | BIGINT, PK, AUTO_INCREMENT | Primary Key |
| `key` | VARCHAR(255), UNIQUE | Key pengaturan (denda_per_hari, maks_denda) |
| `value` | VARCHAR(255) | Nilai pengaturan |
| `created_at` | TIMESTAMP | Waktu dibuat |
| `updated_at` | TIMESTAMP | Waktu diperbarui |

## 3. Skema Relasi (Cardinality)

```text
[roles] 1 --- M [users]      (users.role_id)
[users] 1 --- 1 [siswas]    (siswas.user_id)
[kelas] 1 --- M [siswas]    (siswas.kelas_id)
[kelas] 1 --- M [iurans]    (iurans.kelas_id)
[siswas] 1 --- M [transaksis](transaksis.siswa_id)
[iurans] 1 --- M [transaksis](transaksis.iuran_id)
[users] 1 --- M [transaksis](transaksis.confirmed_by)
[kelas] 1 --- M [pengeluarans](pengeluarans.kelas_id)
[users] 1 --- M [pengeluarans](pengeluarans.created_by)
[users] 1 --- M [pengeluarans](pengeluarans.approved_by)
[siswas] 1 --- M [keterlambatans](keterlambatans.siswa_id)
[iurans] 1 --- M [keterlambatans](keterlambatans.iuran_id)
[users] 1 --- M [notifikasis](notifikasis.user_id)
[users] 1 --- M [notifikasis](notifikasis.sender_id)
[users] 1 --- 1 [kelas]     (kelas.wali_kelas_id)
```

## 4. Aturan Foreign Key (On Delete)

| Tabel Asal | Kolom | Tabel Tujuan | Action On Delete |
| --- | --- | --- | --- |
| `users` | `role_id` | `roles` | RESTRICT |
| `users` | `kelas_id` | `kelas` | SET NULL |
| `siswas` | `user_id` | `users` | CASCADE |
| `siswas` | `kelas_id` | `kelas` | RESTRICT |
| `iurans` | `kelas_id` | `kelas` | CASCADE |
| `iurans` | `created_by` | `users` | RESTRICT |
| `transaksis`| `siswa_id` | `siswas` | CASCADE |
| `transaksis`| `iuran_id` | `iurans` | RESTRICT |
| `transaksis`| `confirmed_by`| `users` | SET NULL |
| `pengeluarans`| `kelas_id` | `kelas` | CASCADE |
| `pengeluarans`| `created_by` | `users` | RESTRICT |
| `pengeluarans`| `approved_by` | `users` | SET NULL |
| `keterlambatans`| `siswa_id` | `siswas` | CASCADE |
| `keterlambatans`| `iuran_id` | `iurans` | CASCADE |
| `notifikasis`| `user_id` | `users` | CASCADE |
| `notifikasis`| `sender_id` | `users` | SET NULL |
| `kelas` | `wali_kelas_id` | `users` | SET NULL |