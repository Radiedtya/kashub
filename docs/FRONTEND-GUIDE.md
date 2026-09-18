# Klasify - Frontend Guide

Dokumen ini berisi panduan lengkap mengenai arsitektur, struktur folder, daftar dependency, aturan UI/UX, dan cara menjalankan aplikasi Frontend Klasify.

## 1. Tech Stack & Dependencies

Aplikasi frontend Klasify dibangun menggunakan ekosistem Vue 3 (Composition API) dengan build tool Vite. Berikut adalah daftar dependency utama dan fungsinya:

### Core Dependencies
| Package | Fungsi |
| --- | --- |
| `vue` | Core framework Vue 3 (Composition API). |
| `vue-router` | Library routing untuk navigasi antar halaman (SPA). |
| `pinia` | Library state management global (Auth, Notifikasi). |
| `axios` | HTTP Client untuk komunikasi dengan API Backend. |

### UI & Styling
| Package | Fungsi |
| --- | --- |
| `tailwindcss` | Framework CSS utility-first untuk styling cepat. |
| `@headlessui/vue` | Komponen UI unstyled (Modal, Dropdown, Transition) yang accessible. |
| `@heroicons/vue` | Kumpulan icon SVG yang mudah diintegrasikan dengan Tailwind. |

### Data Visualization & Animation
| Package | Fungsi |
| --- | --- |
| `chart.js` | Library untuk membuat grafik (Doughnut, Line, Bar) di Dashboard & Laporan. |
| `animejs` | Library animasi JS untuk efek entrance (Count-Up, Stagger, Slide). |

### Utilities
| Package | Fungsi |
| --- | --- |
| `dayjs` | Library manipulasi tanggal & waktu yang ringan. |
| `sweetalert2` | Library untuk membuat popup modal konfirmasi (Approve/Reject). |
| `vue3-toastify` | Library untuk menampilkan notifikasi toast (feedback sukses/error). |

### Dev Dependencies
| Package | Fungsi |
| --- | --- |
| `vite` | Build tool dan dev server yang super cepat. |
| `@vitejs/plugin-vue` | Plugin Vite untuk mengkompilasi file `.vue` (SFC). |
| `@tailwindcss/vite` | Plugin Vite untuk integrasi Tailwind CSS v4. |

## 2. Project Structure

Struktur folder frontend diatur agar terpisah antara logic, view, dan state management.

```text
klasify-frontend/
├── public/                 # File statis (favicon, logo)
├── src/
│   ├── api/                # Konfigurasi Axios instance & endpoint API
│   │   ├── axios.js        # Setup base URL, interceptors (Token, Error 401)
│   │   ├── auth.js         # Endpoint login, logout, profile
│   │   ├── dashboard.js    # Endpoint dashboard
│   │   └── ...             # Endpoint lainnya (siswa, iuran, transaksi, dll)
│   ├── assets/             # Aplikasi level assets
│   │   ├── main.css        # Import Tailwind CSS & custom global styles
│   │   └── images/         # Gambar lokal (logo, hero image)
│   ├── components/          # Komponen UI reusable
│   │   ├── AppHeader.vue   # Header aplikasi (Jam, Notif Dropdown, Profile)
│   │   └── AppSidebar.vue  # Sidebar navigasi (Dinamis berdasarkan Role)
│   ├── layouts/            # Wrapper layout halaman
│   │   └── AppLayout.vue  # Layout utama (Sidebar + Header + <router-view>)
│   ├── router/             # Konfigurasi Vue Router
│   │   └── index.js        # Definisi routes & Navigation Guards (Cek Auth)
│   ├── stores/             # State management Pinia
│   │   ├── auth.js         # State user, token, role, login/logout action
│   │   └── notifikasi.js  # State notifikasi, unread count, fetch notif
│   ├── views/              # Halaman aplikasi (Page Components)
│   │   ├── auth/           # Halaman Login
│   │   ├── dashboard/      # Dashboard Guru, Bendahara, Siswa
│   │   ├── master/         # CRUD Siswa, Kelas, Iuran
│   │   ├── keuangan/       # Transaksi, Pengeluaran, Keterlambatan
│   │   └── other/          # Laporan, Notifikasi, Profile, Pengaturan
│   ├── App.vue             # Root component (Router-view wrapper)
│   └── main.js             # Entry point (Inisialisasi Vue, Pinia, Router)
├── index.html
├── vite.config.js          # Konfigurasi Vite (Proxy API, Plugins)
└── package.json
```

## 3. Environment Setup & Running

### Prasyarat
- Node.js (v18+)
- pnpm (Package Manager)

### Instalasi
1. Pastikan berada di dalam folder `klasify-frontend`.
2. Jalankan perintah install dependency:
   ```bash
   pnpm install
   ```

### Menjalankan Dev Server
Untuk menjalankan aplikasi di mode development (port 5173):
```bash
pnpm dev
```
Aplikasi akan otomatis me-proxy request `/api` ke backend Laravel (pastikan backend berjalan di `http://localhost:8000`).

### Build untuk Production
Untuk mengompilasi aplikasi ke folder `dist/`:
```bash
pnpm build
```

## 4. Konfigurasi Vite (Proxy API)

Karena frontend dan backend berjalan di port berbeda, Vite diatur untuk me-proxy request API agar terhindar dari masalah CORS.

**Contoh `vite.config.js`:**
```javascript
import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import tailwindcss from '@tailwindcss/vite'
import path from 'path'

export default defineConfig({
  plugins: [vue(), tailwindcss()],
  resolve: {
    alias: {
      '@': new URL('./src', import.meta.url).pathname,
    },
  },
  server: {
    port: 5173,
    proxy: {
      '/api': {
        target: 'http://localhost:8000',
        changeOrigin: true,
        secure: false,
      },
    },
  },
})
```

## 5. Aturan UI/UX & Styling

### Tema Warna (Tailwind CSS)
Aplikasi mengadopsi tema "Clean & Corporate" menggunakan palet warna Tailwind:
- **Background Utama**: `bg-zinc-50` (Abu-abu sangat terang)
- **Background Card**: `bg-white` dengan border `border border-zinc-200`
- **Warna Primary**: `bg-zinc-900` (Untuk tombol utama, header sidebar)
- **Warna Aksen**: `text-blue-600` atau `bg-blue-600` (Untuk link aktif, highlight)
- **Warna Status**:
  - Sukses/Lunas: `text-emerald-600` / `bg-emerald-50`
  - Pending: `text-yellow-600` / `bg-yellow-50`
  - Gagal/Tolak: `text-red-600` / `bg-red-50`

### Responsive Design
- Menggunakan sistem grid Tailwind (`grid-cols-1 md:grid-cols-2 lg:grid-cols-4`).
- Sidebar berubah menjadi Drawer (Overlay) pada mode Mobile (`md:hidden` untuk tombol hamburger).

### Animasi (Anime.js)
- **Entrance Animation**: Kartu (Card) muncul dengan variasi arah (Slide dari kiri/kanan, Scale up).
- **Count-Up**: Angka statistik di Dashboard dan Laporan dimutlak dari 0 ke angka aslinya.
- **Stagger**: List item di tabel muncul berurutan dengan delay kecil.

## 6. Best Practices Koding

1. **Composition API**: Gunakan `<script setup>` di semua file `.vue`.
2. **Auto-import Path**: Gunakan alias `@/` untuk import file dari folder `src/` (misal: `import { useAuthStore } from '@/stores/auth'`).
3. **API Service**: Pisahkan logic API ke dalam folder `src/api/` agar mudah di-maintain, jangan taruh `axios.get` langsung di dalam komponen `.vue`.
4. **State Management**: Gunakan Pinia store (`src/stores/`) untuk data global seperti User Auth dan Notifikasi agar bisa diakses di Header dan Halaman.
5. **Format Tanggal & Rupiah**: Gunakan `dayjs` untuk format tanggal dan `Intl.NumberFormat` untuk format Rupiah agar konsisten di seluruh aplikasi.