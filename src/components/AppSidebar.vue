<template>
  <aside
    :class="[
      'w-64 shrink-0 bg-linear-to-b from-[#2a3f8d] to-[#0e39e3] text-white flex flex-col py-8 px-4 rounded-tl-[55px] rounded-br-[55px] overflow-visible transition-transform duration-300 ease-in-out',
      // Mobile: jadi drawer fixed, geser masuk/keluar dari kiri
      'fixed inset-y-0 left-0 z-50',
      isOpen ? 'translate-x-0' : '-translate-x-full',
      // Desktop (md ke atas): balik statis normal, selalu tampil, radius desktop tetap
      'md:static md:translate-x-0 md:z-auto',
    ]"
  >
    <!-- Tombol close, cuma muncul di mobile -->
    <button
      type="button"
      @click="$emit('close')"
      class="md:hidden absolute top-4 right-4 text-white/70 hover:text-white"
    >
      <XMarkIcon class="w-6 h-6" />
    </button>

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
  XMarkIcon,
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

/* Default (mobile-first): style item aktif polos, rounded penuh,
   TANPA efek bleed/curve. Di mobile sidebar itu drawer overlay
   sendirian di atas backdrop gelap, bukan nempel ke bg-zinc-50 —
   jadi efek "nembus ke tepi kanan" bakal keliatan defect/ngambang. */
.nav-active {
  background-color: #fafafa; /* bg-zinc-50 */
  color: #1d4ed8; /* blue-700 */
  font-weight: 600;
  border-radius: 1rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

/* Efek bleed + curve connector HANYA aktif di desktop (md ke atas).
   Di situ sidebar statis nempel langsung ke background utama
   (bg-zinc-50) yang warnanya sama persis, jadi transisi curve-nya
   nyambung mulus - bukan ngambang kayak di mobile. */
@media (min-width: 768px) {
  .nav-active {
    border-radius: 1rem 0 0 1rem;
    margin-right: -1.5rem;
  }

  .nav-active::before,
  .nav-active::after {
    content: "";
    position: absolute;
    right: -1.5rem;
    width: 1.25rem;
    height: 1.25rem;
    pointer-events: none;
  }

  .nav-active::before {
    top: -1.25rem;
    background: radial-gradient(
      circle at 0 0,
      transparent 1.25rem,
      #fafafa 1.26rem
    );
  }

  .nav-active::after {
    bottom: -1.25rem;
    background: radial-gradient(
      circle at 0 100%,
      transparent 1.25rem,
      #fafafa 1.26rem
    );
  }
}
</style>
