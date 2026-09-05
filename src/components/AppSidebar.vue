<template>
  <aside
    class="w-64 shrink-0 bg-linear-to-b from-[#2a3f8d] to-[#0e39e3] text-white flex flex-col py-8 px-4 rounded-tl-[55px] rounded-br-[55px] overflow-visible"
  >
    <!-- Logo / Brand & Badge Role -->
    <div class="flex items-center gap-2.5 px-4 mb-10">
      <div
        class="w-9 h-9 rounded-xl flex items-center justify-center shadow-sm shrink-0"
      >
        <img src="@/assets/k.png" class="w-full h-full object-contain p-1" />
      </div>
      <div class="flex items-center gap-2">
        <span class="text-xl font-bold tracking-tight">Klasify</span>
      </div>
    </div>

    <!-- ==================== MENU UTAMA ==================== -->
    <div
      class="px-4 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
    >
      Menu Utama
    </div>

    <nav class="flex flex-col gap-1.5 px-2">
      <router-link
        to="/"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
        exact-active-class="nav-active"
      >
        <HomeIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Dashboard</span>
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
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <UsersIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Data Siswa</span>
      </router-link>

      <router-link
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        to="/kelas"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
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
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <CurrencyDollarIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Iuran</span>
      </router-link>

      <!-- Transaksi (Semua Role Bisa Lihat) -->
      <router-link
        to="/transaksi"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <ArrowsRightLeftIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Transaksi</span>
      </router-link>

      <router-link
        to="/pengeluaran"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <ArrowTrendingDownIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Pengeluaran</span>
      </router-link>

      <!-- Keterlambatan (Semua Role Bisa Lihat) -->
      <router-link
        to="/keterlambatan"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <ClockIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Keterlambatan</span>
      </router-link>
    </nav>

    <!-- ==================== LAPORAN ==================== -->
    <div
      v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
      class="px-4 mt-6 mb-2 text-[11px] font-semibold uppercase tracking-wider text-white/40"
    >
      Laporan
    </div>

    <nav
      v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
      class="flex flex-col gap-1.5 px-2"
    >
      <router-link
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        to="/laporan"
        class="nav-link relative z-10 flex items-center gap-3 px-4 py-3 rounded-l-2xl transition-all duration-200 text-white/70 hover:bg-white/10 hover:text-white"
        active-class="nav-active"
      >
        <ChartBarIcon class="w-5 h-5 shrink-0" />
        <span class="text-sm">Laporan</span>
      </router-link>
    </nav>

    <!-- Footer Card di Sidebar -->
    <div class="mt-auto px-4">
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
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
</script>

<style scoped>
.nav-link {
  position: relative;
}

/* Item aktif: putih (zinc-50), nembus ke tepi kanan sidebar */
.nav-active {
  background-color: #fafafa; /* bg-zinc-50 */
  color: #1d4ed8; /* blue-700 */
  font-weight: 600;
  border-radius: 1rem 0 0 1rem; /* cuma sisi kiri dibulatkan */
  margin-right: -1.5rem; /* tembus px-4 (aside) + px-2 (nav) */
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Konektor melengkung di atas item aktif */
.nav-active::before,
.nav-active::after {
  content: "";
  position: absolute;
  right: -1.5rem; /* sejajar sama tepi kanan item aktif */
  width: 1.25rem;
  height: 1.25rem;
  pointer-events: none;
}

.nav-active::before {
  top: -1.25rem; /* nongol ke celah SEBELUM item ini */
  background: radial-gradient(
    circle at 0 0,
    transparent 1.25rem,
    #fafafa 1.26rem
  );
}

.nav-active::after {
  bottom: -1.25rem; /* nongol ke celah SETELAH item ini */
  background: radial-gradient(
    circle at 0 100%,
    transparent 1.25rem,
    #fafafa 1.26rem
  );
}
</style>