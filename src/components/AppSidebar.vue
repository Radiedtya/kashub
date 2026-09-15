<template>
  <aside
    :class="[
      'w-64 shrink-0 bg-linear-to-b from-[#2a3f8d] to-[#0e39e3] text-white flex flex-col py-8 rounded-tl-[55px] rounded-br-[55px] transition-transform duration-300 ease-in-out',
      // Mobile: jadi drawer fixed, geser masuk/keluar dari kiri
      'fixed inset-y-0 left-0 z-50',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      // Desktop (md ke atas): balik statis normal, selalu tampil
      'md:static md:translate-x-0 md:z-auto',
    ]"
  >
    <!-- Tombol close, cuma muncul di mobile -->
    <button
      type="button"
      @click="$emit('close')"
      class="md:hidden absolute top-4 right-4 text-white/70 hover:text-white z-10"
    >
      <XMarkIcon class="w-6 h-6" />
    </button>

    <!-- Logo / Brand — fixed di atas, tidak ikut ke-scroll -->
    <div class="flex items-center gap-2.5 px-8 mb-10 shrink-0">
      <div
        class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0"
      >
        <img src="@/assets/k.png" class="w-full h-full object-contain p-1" />
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xl font-bold tracking-tight">Klasify</span>
      </div>
    </div>

    <!-- Area menu yang bisa di-scroll -->
    <div class="flex-1 overflow-y-auto px-4 sidebar-scroll">
      <!-- ==================== MENU UTAMA ==================== -->
      <div
        class="px-4 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
      >
        Menu Utama
      </div>

      <nav class="flex flex-col gap-1.5 px-2">
        <router-link
          to="/"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
          exact-active-class="nav-active"
        >
          <HomeIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Dashboard</span>
        </router-link>

        <router-link
          to="/notifikasi"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <BellIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Notifikasi</span>
        </router-link>
      </nav>

      <!-- ==================== AKADEMIK ==================== -->
      <div
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="px-4 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
      >
        Akademik
      </div>

      <nav
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="flex flex-col gap-1.5 px-2"
      >
        <router-link
          v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
          to="/siswa"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <UsersIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Data Siswa</span>
        </router-link>

        <router-link
          v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
          to="/kelas"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <AcademicCapIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Data Kelas</span>
        </router-link>
      </nav>

      <!-- ==================== KEUANGAN ==================== -->
      <div
        class="px-4 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
      >
        Keuangan
      </div>

      <nav class="flex flex-col gap-1.5 px-2">
        <router-link
          to="/iuran"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <CurrencyDollarIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Iuran</span>
        </router-link>

        <!-- Transaksi (Semua Role Bisa Lihat) -->
        <router-link
          to="/transaksi"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <ArrowsRightLeftIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Transaksi</span>
        </router-link>

        <router-link
          to="/pengeluaran"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <ArrowTrendingDownIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Pengeluaran</span>
        </router-link>

        <!-- Keterlambatan (Semua Role Bisa Lihat) -->
        <router-link
          to="/keterlambatan"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <ClockIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Keterlambatan</span>
        </router-link>
      </nav>

      <!-- ==================== Lainnya ==================== -->
      <div
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="px-4 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
      >
        Lainnya
      </div>

      <nav
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="flex flex-col gap-1.5 px-2 pb-2"
      >
        <router-link
          v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
          to="/laporan"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <ChartBarIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Laporan</span>
        </router-link>

        <router-link
          v-if="authStore.role === 'guru'"
          to="/pengaturan"
          class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
          active-class="nav-active"
        >
          <Cog6ToothIcon class="w-5 h-5 shrink-0" />
          <span class="text-sm">Pengaturan Denda</span>
        </router-link>
      </nav>
    </div>

    <!-- Footer Card di Sidebar — fixed di bawah, tidak ikut ke-scroll -->
    <div class="px-4 shrink-0">
      <div class="p-4 text-center">
        <p class="text-xs text-white/70">Klasify</p>
        <p class="text-[10px] text-white/40 mt-1">© 2026 Klasify Team</p>
      </div>
    </div>
  </aside>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
import {
  HomeIcon,
  UsersIcon,
  CurrencyDollarIcon,
  ArrowTrendingDownIcon,
  ChartBarIcon,
  ClockIcon,
  ArrowsRightLeftIcon,
  AcademicCapIcon,
  XMarkIcon,
  BellIcon,
  Cog6ToothIcon,
} from "@heroicons/vue/24/outline";

defineProps({
  isOpen: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["close"]);

const authStore = useAuthStore();
</script>

<style scoped>
.nav-link {
  position: relative;
}

/* Item aktif: rounded biasa, tidak nembus/nyambung ke konten */
.nav-active {
  background-color: #fafafa; /* bg-zinc-50 */
  color: #1d4ed8; /* blue-700 */
  font-weight: 600;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Area scroll menu — paksa LTR biar scrollbar selalu di kanan,
   nggak kebalik walau ada parent yang set direction: rtl */
.sidebar-scroll {
  direction: ltr;
  scrollbar-width: thin;
  scrollbar-color: #38bdf8 rgba(255, 255, 255, 0.08);
}

/* Konten di dalamnya dipastikan balik LTR normal
   (jaga-jaga kalau ada text/komponen yang butuh arah baca normal) */
.sidebar-scroll > * {
  direction: ltr;
}

.sidebar-scroll::-webkit-scrollbar {
  width: 7px;
}

.sidebar-scroll::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.08);
  border-radius: 9999px;
  margin-block: 4px;
}

.sidebar-scroll::-webkit-scrollbar-thumb {
  background: linear-gradient(180deg, #38bdf8 0%, #818cf8 50%, #f472b6 100%);
  border-radius: 9999px;
  border: 1px solid rgba(255, 255, 255, 0.15);
}

.sidebar-scroll::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(180deg, #7dd3fc 0%, #a5b4fc 50%, #f9a8d4 100%);
}
</style>
