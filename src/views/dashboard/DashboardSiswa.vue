<template>
  <div class="space-y-6">
    <div v-if="loading" class="flex justify-center items-center h-96">
      <p class="text-zinc-400">Memuat data dashboard...</p>
    </div>

    <div v-else-if="dashboardData">
      <!-- Header Profil Singkat -->
      <div
        class="siswa-header bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4"
      >
        <!-- <div class="w-16 h-16 rounded-xl bg-zinc-900 flex items-center justify-center text-white font-bold text-2xl shrink-0 border border-zinc-800">
          {{ dashboardData.profil?.nama?.charAt(0) || "S" }}
        </div> -->
        <div class="flex-1">
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
          <div class="flex items-center gap-2">
            <span
              class="w-2 h-2 rounded-full"
              :class="
                dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                  ? 'bg-emerald-500'
                  : dashboardData.status_bayar_bulan_ini.status === 'pending'
                    ? 'bg-amber-500'
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
                    : 'text-red-600'
              "
            >
              {{
                dashboardData.status_bayar_bulan_ini.status === "confirmed"
                  ? "Lunas"
                  : dashboardData.status_bayar_bulan_ini.status === "pending"
                    ? "Menunggu Konfirmasi"
                    : "Belum Bayar"
              }}
            </span>
          </div>
        </div>
      </div>

      <!-- Stat Cards Siswa -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5 mt-5">
        <div class="stat-card bg-white border border-zinc-200 rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <CurrencyDollarIcon class="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(dashboardData.statistik.total_bayar) }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Total Sudah Dibayar</p>
        </div>

        <div class="stat-card bg-white border border-zinc-200 rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <CheckCircleIcon class="w-5 h-5 text-emerald-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            {{ dashboardData.statistik.total_transaksi }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Transaksi Sukses</p>
        </div>

        <div class="stat-card bg-white border border-zinc-200 rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ClockIcon class="w-5 h-5 text-amber-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            {{ dashboardData.statistik.transaksi_pending }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Transaksi Pending</p>
        </div>

        <div class="stat-card bg-white border border-zinc-200 rounded-xl p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ExclamationCircleIcon class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(dashboardData.statistik.total_denda) }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Total Denda</p>
        </div>
      </div>

      <!-- Chart & Status Iuran -->
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

          <div class="flex-1 flex items-center justify-center">
            <div class="text-center w-full">
              <div
                class="w-20 h-20 rounded-full flex items-center justify-center shrink-0 mx-auto mb-4 border-4"
                :class="
                  dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                    ? 'bg-emerald-50 border-emerald-100'
                    : dashboardData.status_bayar_bulan_ini.status === 'pending'
                      ? 'bg-amber-50 border-amber-100'
                      : 'bg-red-50 border-red-100'
                "
              >
                <CheckCircleIcon
                  v-if="
                    dashboardData.status_bayar_bulan_ini.status === 'confirmed'
                  "
                  class="w-10 h-10 text-emerald-600"
                />
                <ClockIcon
                  v-else-if="
                    dashboardData.status_bayar_bulan_ini.status === 'pending'
                  "
                  class="w-10 h-10 text-amber-600"
                />
                <ExclamationCircleIcon v-else class="w-10 h-10 text-red-600" />
              </div>
              <p class="font-bold text-zinc-800 capitalize text-lg">
                {{
                  dashboardData.status_bayar_bulan_ini.status === "confirmed"
                    ? "Lunas"
                    : dashboardData.status_bayar_bulan_ini.status === "pending"
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
                  formatDate(dashboardData.status_bayar_bulan_ini.tanggal_bayar)
                }}
              </p>
              <p class="text-sm text-zinc-500 mt-1" v-else>
                Segera lakukan pembayaran sebelum jatuh tempo!
              </p>

              <!-- Tombol Bayar cuma muncul kalau belum bayar atau ditolak -->
              <router-link
                v-if="
                  dashboardData.status_bayar_bulan_ini.status ===
                    'belum_bayar' ||
                  dashboardData.status_bayar_bulan_ini.status === 'rejected'
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
            </div>
          </div>
        </div>
      </div>

      <!-- Riwayat Transaksi Siswa -->
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
import { ref, onMounted, nextTick } from "vue";
import { useAuthStore } from "@/stores/auth";
import DashboardService from "@/api/dashboard";
import { toast } from "vue3-toastify";
import { Chart, registerables } from "chart.js";
import anime from "animejs";
import {
  CurrencyDollarIcon,
  CheckCircleIcon,
  ClockIcon,
  ExclamationCircleIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const authStore = useAuthStore();
const dashboardData = ref(null);
const loading = ref(true);
const paymentChart = ref(null);
let chartInstance = null;

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
};

const formatDate = (date) => {
  return dayjs(date).format("DD MMM YYYY");
};

// --- Anime.js Stagger Animation ---
const triggerAnimations = () => {
  anime({
    targets: ".siswa-header",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".stat-card",
    translateY: [20, 0],
    opacity: [0, 1],
    delay: anime.stagger(100, { start: 100 }),
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".chart-card",
    translateY: [30, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 300 }),
    duration: 700,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".history-card",
    translateY: [30, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 500 }),
    duration: 700,
    easing: "easeOutQuad",
  });
};

// --- Render Chart ---
const renderChart = () => {
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

    triggerAnimations();
    if (dashboardData.value?.grafik) {
      renderChart();
    }
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
