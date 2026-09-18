# Dokumentasi Skema Database

## 1. Structure ERD Diagram

```text
┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│                                                                                             │
│  ┌──────────────┐          ┌──────────────┐          ┌──────────────┐                       │
│  │    roles     │          │    users     │          │    kelas     │                       │
│  ├──────────────┤          ├──────────────┤          ├──────────────┤                       │
│  │ id           │◄─────────│ role_id      │          │ id           │                       │
│  │ name         │          │ id           │──┐       │ nama         │                       │
│  │ display_name │          │ name         │  │       │ tahun_ajaran │                       │
│  │ created_at   │          │ email        │  │       │ wali_kelas_id│─────────────────┐     │
│  │ updated_at   │          │ password     │  └──────►│ is_active    │                 │     │
│  └──────────────┘          │ no_hp        │          │ created_at   │                 │     │
│                            │ foto         │          │ updated_at   │                 │     │
│                            │ is_active    │          └──────────────┘                 │     │
│                            │ kelas_id     │────────────────────────────────────────────┘     │
│                            │ created_at   │                                                 │
│                            │ updated_at   │                                                 │
│                            └──────────────┘                                                 │
│                                   │                                                         │
│                                   │ 1                                                       │
│                                   │                                                         │
│                                   │ 1                                                       │
│                                   ▼                                                         │
│                            ┌──────────────┐          ┌──────────────┐                       │
│                            │    siswa     │          │    iuran     │                       │
│                            ├──────────────┤          ├──────────────┤                       │
│                            │ id           │          │ id           │                       │
│                            │ user_id      │◄─────────│ kelas_id     │                       │
│                            │ nis          │          │ bulan        │                       │
│                            │ nisn         │          │ tahun        │                       │
│                            │ kelas_id     │─────────►│ nominal      │                       │
│                            │ tempat_lahir │          │ jatuh_tempo  │                       │
│                            │ tanggal_lahir│          │ is_active    │                       │
│                            │ alamat       │          │ created_by   │──────────────┐        │
│                            │ nama_ortu    │          │ created_at   │              │        │
│                            │ no_hp_ortu   │          │ updated_at   │              │        │
│                            │ created_at   │          └──────────────┘              │        │
│                            │ updated_at   │                   │                    │        │
│                            └──────────────┘                   │                    │        │
│                                   │                           │                    │        │
│                                   │ 1                         │ 1                  │        │
│                                   │                           │                    │        │
│                                   │ M                         │ M                  │        │
│                                   ▼                           ▼                    │        │
│                            ┌──────────────┐          ┌──────────────┐              │        │
│                            │  transaksi   │          │ pengeluaran  │              │        │
│                            ├──────────────┤          ├──────────────┤              │        │
│                            │ id           │          │ id           │              │        │
│                            │ siswa_id     │─────────►│ kelas_id     │──────────────┼──┐     │
│                            │ iuran_id     │◄─────────│ judul        │              │  │     │
│                            │ jumlah       │          │ deskripsi    │              │  │     │
│                            │ tanggal_bayar│          │ jumlah       │              │  │     │
│                            │ metode       │          │ tanggal      │              │  │     │
│                            │ bukti_bayar  │          │ kategori     │              │  │     │
│                            │ status       │          │ bukti_foto   │              │  │     │
│                            │ confirmed_by │──────────│ created_by   │──────────────┼──┘     │
│                            │ confirmed_at │          │ approved_by  │──────────────┼──┐     │
│                            │ keterangan   │          │ approved_at  │              │  │     │
│                            │ created_at   │          │ status       │              │  │     │
│                            │ updated_at   │          │ created_at   │              │  │     │
│                            └──────────────┘          │ updated_at   │              │  │     │
│                                   │                  └──────────────┘              │  │     │
│                                   │                                                │  │     │
│                                   │ 1                                              │  │     │
│                                   │                                                │  │     │
│                                   │ M                                              │  │     │
│                                   ▼                                                │  │     │
│                            ┌──────────────┐                                        │  │     │
│                            │ keterlambatan│                                        │  │     │
│                            ├──────────────┤                                        │  │     │
│                            │ id           │                                        │  │     │
│                            │ siswa_id     │────────────────────────────────────────┘  │     │
│                            │ iuran_id     │───────────────────────────────────────────┘     │
│                            │ hari_telat   │                                                 │
│                            │ denda        │                                                 │
│                            │ status       │                                                 │
│                            │ created_at   │                                                 │
│                            │ updated_at   │                                                 │
│                            └──────────────┘                                                 │
│                                                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────────────────────────────────┐
│  NOTIFIKASI (terpisah - untuk semua user)                                                   │
│                                                                                             │
│  ┌──────────────────┐                                                                       │
│  │    notifikasi    │                                                                       │
│  ├──────────────────┤                                                                       │
│  │ id               │                                                                       │
│  │ user_id          │──────────────► (users.id)                                             │
│  │ judul            │                                                                       │
│  │ pesan            │                                                                       │
│  │ tipe             │                                                                       │
│  │ is_read          │                                                                       │
│  │ link             │                                                                       │
│  │ created_at       │                                                                       │
│  │ updated_at       │                                                                       │
│  └──────────────────┘                                                                       │
│                                                                                             │
└─────────────────────────────────────────────────────────────────────────────────────────────┘

```
### 1.1. Tabel Relasi Lengkap

| No | Relasi | Dari | Ke | Keterangan |
| :--- | :--- | :--- | :--- | :--- |
| 1 | Belongs To | `users.role_id` | `roles.id` | Setiap user punya 1 role |
| 2 | Belongs To | `users.kelas_id` | `kelas.id` | User bisa terkait ke kelas (untuk siswa) |
| 3 | Has One | `users.id` | `siswa.user_id` | 1 user punya 1 data siswa (1-to-1) |
| 4 | Belongs To | `siswa.kelas_id` | `kelas.id` | Siswa berada di 1 kelas |
| 5 | Has Many | `kelas.id` | `siswa.kelas_id` | 1 kelas punya banyak siswa |
| 6 | Has Many | `kelas.id` | `iuran.kelas_id` | 1 kelas punya banyak iuran |
| 7 | Has Many | `kelas.id` | `pengeluaran.kelas_id` | 1 kelas punya banyak pengeluaran |
| 8 | Belongs To | `iuran.created_by` | `users.id` | Iuran dibuat oleh user (guru) |
| 9 | Has Many | `iuran.id` | `transaksi.iuran_id` | 1 iuran punya banyak transaksi |
| 10 | Belongs To | `transaksi.siswa_id` | `siswa.id` | Transaksi milik 1 siswa |
| 11 | Belongs To | `transaksi.iuran_id` | `iuran.id` | Transaksi untuk 1 iuran |
| 12 | Belongs To | `transaksi.confirmed_by` | `users.id` | Transaksi dikonfirmasi oleh user |
| 13 | Belongs To | `pengeluaran.created_by` | `users.id` | Pengeluaran dibuat oleh user |
| 14 | Belongs To | `pengeluaran.approved_by` | `users.id` | Pengeluaran disetujui oleh user |
| 15 | Has Many | `siswa.id` | `keterlambatan.siswa_id` | 1 siswa punya banyak keterlambatan |
| 16 | Belongs To | `keterlambatan.iuran_id` | `iuran.id` | Keterlambatan untuk 1 iuran |
| 17 | Has Many | `users.id` | `notifikasi.user_id` | 1 user punya banyak notifikasi |

---

## 2. Diagram Alur Kardinalitas Entitas
```text
┌─────────────┐    1    ┌─────────────┐    M    ┌─────────────┐
│    roles    │─────────│    users    │─────────│  notifikasi │
└─────────────┘         └─────────────┘         └─────────────┘
                               │ 1
                               │
                               │ 1
                               ▼
                        ┌─────────────┐    M    ┌─────────────┐
                        │    siswa    │─────────│  transaksi  │
                        └─────────────┘         └─────────────┘
                               │ 1                     │
                               │                       │
                               │ M                     │ 1
                               ▼                       ▼
                        ┌─────────────┐    M    ┌─────────────┐
                        │ keterlambatan│─────────│    iuran    │
                        └─────────────┘         └─────────────┘
                                                       │ 1
                                                       │
                                                       │ M
                                                       ▼
                                                ┌─────────────┐
                                                │ pengeluaran │
                                                └─────────────┘

┌─────────────┐    1    ┌─────────────┐
│    kelas    │─────────│    users    │ (sebagai wali_kelas)
└─────────────┘         └─────────────┘
       │ 1
       │
       │ M
       ▼
┌─────────────┐
│    siswa    │
└─────────────┘
```

## 2.1. Ringkasan Foreign Key (FK)

| Foreign Key | Lokasi Tabel | Tabel/Kolom Target | Action On Delete |
| :--- | :--- | :--- | :--- |
| `role_id` | `users` | `roles.id` | `RESTRICT` |
| `kelas_id` | `users` | `kelas.id` | `SET NULL` |
| `user_id` | `siswa` | `users.id` | `CASCADE` |
| `kelas_id` | `siswa` | `kelas.id` | `RESTRICT` |
| `kelas_id` | `iuran` | `kelas.id` | `CASCADE` |
| `created_by` | `iuran` | `users.id` | `RESTRICT` |
| `siswa_id` | `transaksi` | `siswa.id` | `CASCADE` |
| `iuran_id` | `transaksi` | `iuran.id` | `RESTRICT` |
| `confirmed_by` | `transaksi` | `users.id` | `SET NULL` |
| `kelas_id` | `pengeluaran` | `kelas.id` | `CASCADE` |
| `created_by` | `pengeluaran` | `users.id` | `RESTRICT` |
| `approved_by` | `pengeluaran` | `users.id` | `SET NULL` |
| `user_id` | `notifikasi` | `users.id` | `CASCADE` |
| `siswa_id` | `keterlambatan` | `siswa.id` | `CASCADE` |
| `iuran_id` | `keterlambatan` | `iuran.id` | `CASCADE` |

