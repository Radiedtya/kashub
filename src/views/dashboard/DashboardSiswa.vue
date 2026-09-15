<template>
  <div class="space-y-6">
    <!-- Skeleton Loader (Mirip Dashboard Guru) -->
    <div v-if="loading" class="space-y-6 animate-pulse">
      <!-- Skeleton Header -->
      <div
        class="bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4"
      >
        <div class="flex-1 w-full space-y-2">
          <div class="h-6 bg-zinc-200 rounded w-1/3"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/4"></div>
        </div>
        <div
          class="w-full sm:w-48 pt-4 sm:pt-0 border-t sm:border-t-0 sm:border-l border-zinc-100 sm:pl-6 mt-4 sm:mt-0 space-y-2"
        >
          <div class="h-3 bg-zinc-200 rounded w-1/2"></div>
          <div class="h-2 bg-zinc-100 rounded w-full mt-3"></div>
        </div>
      </div>

      <!-- Skeleton Stat Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div
          v-for="i in 4"
          :key="i"
          class="bg-white border border-zinc-200 rounded-xl p-5 h-32"
        >
          <div class="w-10 h-10 rounded-lg bg-zinc-200 mb-4"></div>
          <div class="h-6 bg-zinc-200 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/2"></div>
        </div>
      </div>

      <!-- Skeleton Chart & Status -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div
          class="lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6 h-80"
        >
          <div class="h-5 bg-zinc-200 rounded w-1/4 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/5 mb-6"></div>
          <div class="h-56 bg-zinc-100 rounded-lg flex items-end gap-4 px-4">
            <div class="w-1/6 h-1/2 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-3/4 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-1/3 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-2/3 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-1/2 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-4/5 bg-zinc-200 rounded-t"></div>
          </div>
        </div>
        <div
          class="bg-white border border-zinc-200 rounded-xl p-6 h-80 flex flex-col"
        >
          <div class="h-5 bg-zinc-200 rounded w-1/2 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/3 mb-6"></div>
          <div class="flex-1 flex items-center justify-center">
            <div class="w-20 h-20 bg-zinc-100 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- Skeleton History -->
      <div class="bg-white border border-zinc-200 rounded-xl p-6">
        <div class="h-5 bg-zinc-200 rounded w-1/4 mb-6"></div>
        <div class="space-y-4">
          <div v-for="i in 4" :key="i" class="flex items-center gap-3">
            <div class="w-9 h-9 bg-zinc-200 rounded-lg"></div>
            <div class="flex-1 space-y-2">
              <div class="h-4 bg-zinc-200 rounded w-1/2"></div>
              <div class="h-3 bg-zinc-200 rounded w-1/4"></div>
            </div>
            <div class="space-y-2 text-right">
              <div class="h-4 bg-zinc-200 rounded w-16 ml-auto"></div>
              <div class="h-3 bg-zinc-200 rounded w-10 ml-auto"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Konten Dashboard -->
    <div v-else-if="dashboardData">
      <!-- Header Profil Singkat -->
      <div
        class="siswa-header bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4"
      >
        <div class="flex-1 w-full">
          <h1 class="text-2xl font-bold text-zinc-900">
            {{ dashboardData.profil?.nama }}
          </h1>
          <p class="text-zinc-500 text-sm mt-1">
            NIS: {{ dashboardData.profil?.nis }}
            <span class="mx-2 text-zinc-300">|</span> Kelas:
            {{ dashboardData.profil?.kelas }}
          </p>
        </div>
        <div
          class="w-full sm:w-auto pt-4 sm:pt-0 border-t sm:border-t-0 sm:border-l border-zinc-100 sm:pl-6 mt-4 sm:mt-0"
        >
          <p class="text-xs text-zinc-400 uppercase tracking-wider mb-1">
            Status Iuran Terbaru
          </p>
          <div class="flex items-center gap-2 mb-3">
            <span
              class="w-2 h-2 rounded-full"
              :class="
                dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                  ? 'bg-emerald-500'
                  : dashboardData.status_bayar_bulan_ini.status === 'pending'
                    ? 'bg-amber-500'
                    : !dashboardData.status_bayar_bulan_ini.iuran
                      ? 'bg-zinc-300'
                      : 'bg-red-500'
              "
            ></span>
            <span
              class="font-semibold text-sm capitalize"
              :class="
                dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                  ? 'text-emerald-600'
                  : dashboardData.status_bayar_bulan_ini.status === 'pending'
                    ? 'text-amber-600'
                    : !dashboardData.status_bayar_bulan_ini.iuran
                      ? 'text-zinc-500'
                      : 'text-red-600'
              "
            >
              {{
                dashboardData.status_bayar_bulan_ini.status === "confirmed"
                  ? "Lunas"
                  : dashboardData.status_bayar_bulan_ini.status === "pending"
                    ? "Menunggu Konfirmasi"
                    : !dashboardData.status_bayar_bulan_ini.iuran
                      ? "Belum Ada Iuran"
                      : "Belum Bayar"
              }}
            </span>
          </div>

          <!-- Progress Bar Tagihan (Animated Width) -->
          <div
            class="w-full sm:w-48"
            v-if="dashboardData.statistik.total_iuran_kelas > 0"
          >
            <div class="flex justify-between items-center mb-1">
              <span class="text-[10px] font-medium text-zinc-500"
                >Progress Tagihan</span
              >
              <span class="text-[10px] font-bold text-zinc-700"
                >{{ dashboardData.statistik.total_transaksi }} /
                {{ dashboardData.statistik.total_iuran_kelas || 0 }}</span
              >
            </div>
            <div class="w-full h-2 bg-zinc-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-1000 ease-out"
                :class="
                  paymentProgress === 100 ? 'bg-emerald-500' : 'bg-blue-600'
                "
                :style="{ width: animatedProgress + '%' }"
              ></div>
            </div>
          </div>
          <div class="w-full sm:w-48" v-else>
            <p class="text-[10px] font-medium text-zinc-400 italic">
              Iuran belum dibuat
            </p>
          </div>
        </div>
      </div>

      <!-- Stat Cards Siswa (Scale Up) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-5">
        <div
          class="stat-card group relative overflow-hidden rounded-xl p-5 bg-linear-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/20"
        >
          <CurrencyDollarIcon
            class="absolute -right-4 -bottom-4 w-28 h-28 text-white/15 rotate-[-8deg] pointer-events-none"
          />
          <div class="relative z-10">
            <p class="text-blue-100 text-sm mt-1">Total Sudah Dibayar</p>
            <p class="text-2xl font-bold text-white">
              Rp {{ formatRupiah(animatedBayar) }}
            </p>
          </div>
        </div>

        <div
          class="stat-card group relative overflow-hidden rounded-xl p-5 bg-linear-to-br from-emerald-500 to-emerald-600 shadow-lg shadow-emerald-500/20"
        >
          <CheckCircleIcon
            class="absolute -right-4 -bottom-4 w-28 h-28 text-white/15 rotate-[-8deg] pointer-events-none"
          />
          <div class="relative z-10">
            <p class="text-emerald-100 text-sm mt-1">Transaksi Sukses</p>
            <p class="text-2xl font-bold text-white">{{ animatedTransaksi }}</p>
          </div>
        </div>

        <div
          class="stat-card group relative overflow-hidden rounded-xl p-5 bg-linear-to-br from-amber-500 to-amber-600 shadow-lg shadow-amber-500/20"
        >
          <ClockIcon
            class="absolute -right-4 -bottom-4 w-28 h-28 text-white/15 rotate-[-8deg] pointer-events-none"
          />
          <div class="relative z-10">
            <p class="text-amber-100 text-sm mt-1">Transaksi Pending</p>
            <p class="text-2xl font-bold text-white">{{ animatedPending }}</p>
          </div>
        </div>

        <div
          class="stat-card group relative overflow-hidden rounded-xl p-5 bg-linear-to-br from-red-500 to-red-600 shadow-lg shadow-red-500/20"
        >
          <ExclamationCircleIcon
            class="absolute -right-4 -bottom-4 w-28 h-28 text-white/15 rotate-[-8deg] pointer-events-none"
          />
          <div class="relative z-10">
            <p class="text-red-100 text-sm mt-1">Total Denda</p>
            <p class="text-2xl font-bold text-white">
              Rp {{ formatRupiah(animatedDenda) }}
            </p>
          </div>
        </div>
      </div>

      <!-- Chart & Status Iuran (Slide from Left) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Chart Pembayaran -->
        <div
          class="chart-card lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6"
        >
          <div class="flex items-center justify-between mb-5">
            <div>
              <h2 class="text-base font-semibold text-zinc-800">
                Riwayat Pembayaran
              </h2>
              <p class="text-zinc-400 text-xs">6 bulan terakhir</p>
            </div>
          </div>
          <div class="relative h-75 w-full">
            <canvas ref="paymentChart"></canvas>
          </div>
        </div>

        <!-- Detail Status Bayar -->
        <div
          class="chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Status Iuran Terbaru
          </h2>
          <p
            class="text-zinc-400 text-xs mb-4"
            v-if="dashboardData.status_bayar_bulan_ini.iuran"
          >
            Periode: {{ dashboardData.status_bayar_bulan_ini.iuran }}
          </p>
          <p class="text-zinc-400 text-xs mb-4" v-else>
            Periode: Belum tersedia
          </p>

          <div class="flex-1 flex items-center justify-center">
            <div class="text-center w-full">
              <!-- KONDISI: IURAN BELUM DIBUAT -->
              <template v-if="!dashboardData.status_bayar_bulan_ini.iuran">
                <div
                  class="w-20 h-20 rounded-full flex items-center justify-center shrink-0 mx-auto mb-4 border-4 bg-zinc-50 border-zinc-100"
                >
                  <InboxIcon class="w-10 h-10 text-zinc-400" />
                </div>
                <p class="font-bold text-zinc-800 text-lg">Belum Ada Iuran</p>
                <p class="text-sm text-zinc-500 mt-1">
                  Iuran untuk bulan ini belum dibuat oleh Guru.
                </p>
              </template>

              <!-- KONDISI: IURAN ADA -->
              <template v-else>
                <div
                  class="w-20 h-20 rounded-full flex items-center justify-center shrink-0 mx-auto mb-4 border-4"
                  :class="
                    dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                      ? 'bg-emerald-50 border-emerald-100'
                      : dashboardData.status_bayar_bulan_ini.status ===
                          'pending'
                        ? 'bg-amber-50 border-amber-100'
                        : 'bg-red-50 border-red-100'
                  "
                >
                  <CheckCircleIcon
                    v-if="
                      dashboardData.status_bayar_bulan_ini.status ===
                      'confirmed'
                    "
                    class="w-10 h-10 text-emerald-600"
                  />
                  <ClockIcon
                    v-else-if="
                      dashboardData.status_bayar_bulan_ini.status === 'pending'
                    "
                    class="w-10 h-10 text-amber-600"
                  />
                  <ExclamationCircleIcon
                    v-else
                    class="w-10 h-10 text-red-600"
                  />
                </div>
                <p class="font-bold text-zinc-800 capitalize text-lg">
                  {{
                    dashboardData.status_bayar_bulan_ini.status === "confirmed"
                      ? "Lunas"
                      : dashboardData.status_bayar_bulan_ini.status ===
                          "pending"
                        ? "Menunggu Konfirmasi"
                        : "Belum Bayar"
                  }}
                </p>
                <p
                  class="text-sm text-zinc-500 mt-1"
                  v-if="dashboardData.status_bayar_bulan_ini.tanggal_bayar"
                >
                  Dibayar pada:
                  {{
                    formatDate(
                      dashboardData.status_bayar_bulan_ini.tanggal_bayar,
                    )
                  }}
                </p>
                <p class="text-sm text-zinc-500 mt-1" v-else>
                  Segera lakukan pembayaran sebelum jatuh tempo!
                </p>

                <!-- Tombol Bayar cuma muncul kalau belum bayar atau ditolak -->
                <router-link
                  v-if="
                    ['belum_bayar', 'rejected'].includes(
                      dashboardData.status_bayar_bulan_ini.status,
                    )
                  "
                  to="/iuran"
                  class="mt-6 inline-block px-4 py-2 rounded-lg bg-zinc-900 text-white text-xs font-medium hover:bg-zinc-800 transition"
                >
                  Bayar Sekarang
                </router-link>

                <!-- Kalau statusnya pending, tampilin teks ini -->
                <p
                  v-else-if="
                    dashboardData.status_bayar_bulan_ini.status === 'pending'
                  "
                  class="mt-6 text-xs text-zinc-400 italic"
                >
                  Menunggu konfirmasi pembayaran
                </p>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Transaksi Siswa (Slide from Right) -->
      <div
        class="history-card bg-white border border-zinc-200 rounded-xl p-6 mt-5"
      >
        <div class="flex items-center justify-between mb-4">
          <h2 class="text-base font-semibold text-zinc-800">
            Transaksi Terakhir
          </h2>
          <router-link
            to="/transaksi"
            class="text-xs font-medium text-blue-600 hover:underline"
          >
            Lihat Semua &raquo;
          </router-link>
        </div>
        <div class="flex flex-col divide-y divide-zinc-100">
          <div
            v-for="o in dashboardData.riwayat_transaksi"
            :key="o.id"
            class="flex items-center gap-3 py-3"
          >
            <div
              class="w-9 h-9 rounded-lg flex items-center justify-center bg-zinc-50 text-zinc-600 shrink-0 border border-zinc-100"
            >
              <CurrencyDollarIcon class="w-5 h-5" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-zinc-800 text-sm truncate">
                Iuran {{ o.iuran?.bulan }}/{{ o.iuran?.tahun }}
              </p>
              <p class="text-zinc-400 text-[10px]">
                {{ formatDate(o.created_at) }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-semibold text-zinc-700 text-xs">
                Rp {{ formatRupiah(o.jumlah) }}
              </p>
              <p
                class="text-[10px] font-medium capitalize mt-0.5"
                :class="{
                  'text-amber-500': o.status === 'pending',
                  'text-emerald-500': o.status === 'confirmed',
                  'text-red-500': o.status === 'rejected',
                }"
              >
                {{ o.status }}
              </p>
            </div>
          </div>
          <p
            v-if="dashboardData.riwayat_transaksi.length === 0"
            class="text-center text-zinc-400 text-sm py-8"
          >
            Belum ada riwayat transaksi
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useAuthStore } from "@/stores/auth";
import DashboardService from "@/api/dashboard";
import { toast } from "vue3-toastify";
import {
  CurrencyDollarIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationCircleIcon,
  InboxIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const authStore = useAuthStore();
const dashboardData = ref(null);
const loading = ref(true);
const paymentChart = ref(null);
let chartInstance = null;

// State buat Animasi Count-Up & Progress
const animatedBayar = ref(0);
const animatedTransaksi = ref(0);
const animatedPending = ref(0);
const animatedDenda = ref(0);
const animatedProgress = ref(0);

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
};

const formatDate = (date) => {
  return dayjs(date).format("DD MMM YYYY");
};

// Computed buat ngitung persen progress tagihan siswa
const paymentProgress = computed(() => {
  if (!dashboardData.value) return 0;
  const totalIuran = dashboardData.value.statistik.total_iuran_kelas || 0;
  const paidIuran = dashboardData.value.statistik.total_transaksi || 0;
  if (totalIuran === 0) return 0;
  return Math.min(100, Math.round((paidIuran / totalIuran) * 100));
});

// Animasi Muncul (Variasi Arah)
const triggerAnimations = (anime) => {
  anime({
    targets: ".siswa-header",
    translateY: [-20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuart",
  });

  anime({
    targets: ".stat-card",
    scale: [0.8, 1],
    opacity: [0, 1],
    delay: anime.stagger(100, { start: 200 }),
    duration: 700,
    easing: "easeOutBack",
  });

  anime({
    targets: ".chart-card",
    translateX: [-50, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 400 }),
    duration: 800,
    easing: "easeOutQuart",
  });

  anime({
    targets: ".history-card",
    translateX: [50, 0],
    opacity: [0, 1],
    delay: 600,
    duration: 800,
    easing: "easeOutQuart",
  });
};

// Animasi Angka Naik (Count-Up)
const animateStats = (anime) => {
  const stats = dashboardData.value.statistik;

  const counters = [
    { ref: animatedBayar, target: stats.total_bayar },
    { ref: animatedTransaksi, target: stats.total_transaksi },
    { ref: animatedPending, target: stats.transaksi_pending },
    { ref: animatedDenda, target: stats.total_denda },
  ];

  counters.forEach((counter, index) => {
    const obj = { val: 0 };
    anime({
      targets: obj,
      val: counter.target,
      round: 1,
      duration: 1500,
      delay: 300 + index * 150,
      easing: "easeOutExpo",
      update: () => {
        counter.ref.value = obj.val;
      },
    });
  });

  // Animasi Progress Bar
  anime({
    targets: animatedProgress,
    value: paymentProgress.value,
    round: 1,
    duration: 1500,
    delay: 500,
    easing: "easeOutExpo",
  });
};

// --- Render Chart ---
const renderChart = (Chart) => {
  if (chartInstance) chartInstance.destroy();

  const grafik = dashboardData.value.grafik;

  chartInstance = new Chart(paymentChart.value, {
    type: "bar",
    data: {
      labels: grafik.labels,
      datasets: [
        {
          label: "Pembayaran",
          data: grafik.data,
          backgroundColor: "#2563eb",
          borderRadius: 4,
          barThickness: 20,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: {
          grid: { color: "#f4f4f5" },
          border: { display: false },
          ticks: {
            callback: (v) => "Rp " + v / 1000 + "k",
            color: "#a1a1aa",
            font: { size: 10 },
          },
        },
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: { color: "#a1a1aa", font: { size: 10 } },
        },
      },
    },
  });
};

const fetchDashboard = async () => {
  loading.value = true;
  try {
    const response = await DashboardService.getDashboard();
    dashboardData.value = response.data.data;
    loading.value = false;

    await nextTick(); // Tunggu DOM render

    // Lazy load library berat biar gak nge-block initial load
    const [{ default: anime }, { Chart, registerables }] = await Promise.all([
      import("animejs"),
      import("chart.js"),
    ]);

    Chart.register(...registerables);

    if (dashboardData.value?.grafik) {
      renderChart(Chart);
    }

    triggerAnimations(anime);
    animateStats(anime); // Jalankan count-up & progress bar
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat data dashboard");
    loading.value = false;
  }
};

onMounted(() => {
  fetchDashboard();
});
</script>
