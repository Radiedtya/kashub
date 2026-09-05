<template>
  <div class="space-y-6">
    <div v-if="loading" class="flex justify-center items-center h-96">
      <p class="text-slate-400">Memuat data dashboard...</p>
    </div>

    <div v-else-if="dashboardData">
      <!-- Stat cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div class="bg-white rounded-2xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center bg-blue-50"
            >
              <CurrencyDollarIcon class="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-slate-900">
            Rp {{ formatRupiah(dashboardData.statistik.total_kas) }}
          </p>
          <p class="text-slate-400 text-sm mt-1">Total Saldo Kas</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center bg-emerald-50"
            >
              <UsersIcon class="w-5 h-5 text-emerald-500" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-slate-900">
            {{ dashboardData.statistik.total_siswa }}
          </p>
          <p class="text-slate-400 text-sm mt-1">Total Siswa</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center bg-red-50"
            >
              <ExclamationCircleIcon class="w-5 h-5 text-red-500" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-slate-900">
            {{ dashboardData.statistik.siswa_telat }}
          </p>
          <p class="text-slate-400 text-sm mt-1">Siswa Telat Bayar</p>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-5">
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-11 h-11 rounded-xl flex items-center justify-center bg-amber-50"
            >
              <ChartBarIcon class="w-5 h-5 text-amber-500" />
            </div>
          </div>
          <p class="text-2xl font-extrabold text-slate-900">
            {{ dashboardData.statistik.total_iuran_aktif }}
          </p>
          <p class="text-slate-400 text-sm mt-1">Iuran Aktif</p>
        </div>
      </div>

      <!-- Charts row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Revenue chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h2 class="text-lg font-bold text-slate-900">
                Pemasukan vs Pengeluaran
              </h2>
              <p class="text-slate-400 text-sm">6 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-sm">
              <span class="flex items-center gap-1.5 text-slate-500">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span
                >Pemasukan
              </span>
              <span class="flex items-center gap-1.5 text-slate-500">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span
                >Pengeluaran
              </span>
            </div>
          </div>
          <canvas ref="revenueChart" height="120"></canvas>
        </div>

        <!-- Order status donut -->
        <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col">
          <h2 class="text-lg font-bold text-slate-900 mb-1">
            Status Pembayaran
          </h2>
          <p class="text-slate-400 text-sm mb-4">Distribusi semua transaksi</p>
          <div class="flex-1 flex items-center justify-center relative">
            <canvas ref="statusChart" width="180" height="180"></canvas>
            <div
              class="absolute inset-0 flex flex-col items-center justify-center"
            >
              <span class="text-2xl font-extrabold text-slate-900">{{
                totalTransaksi
              }}</span>
              <span class="text-slate-400 text-xs">Total Transaksi</span>
            </div>
          </div>
          <div class="flex flex-col gap-2 mt-4">
            <div class="flex items-center justify-between text-sm">
              <span class="flex items-center gap-2 text-slate-500"
                ><span class="w-2.5 h-2.5 rounded-full bg-slate-300"></span
                >Lunas</span
              >
              <span class="font-semibold text-slate-700">{{
                dashboardData.status_iuran.lunas
              }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="flex items-center gap-2 text-slate-500"
                ><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span
                >Pending</span
              >
              <span class="font-semibold text-slate-700">{{
                dashboardData.status_iuran.pending
              }}</span>
            </div>
            <div class="flex items-center justify-between text-sm">
              <span class="flex items-center gap-2 text-slate-500"
                ><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span
                >Ditolak</span
              >
              <span class="font-semibold text-slate-700">{{
                dashboardData.status_iuran.ditolak
              }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Bottom row -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Top Tunggakan -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-slate-900 mb-1">
            Tunggakan Terbanyak
          </h2>
          <p class="text-slate-400 text-sm mb-5">Siswa yang belum bayar</p>
          <div class="flex flex-col gap-4">
            <div
              v-for="telat in dashboardData.siswa_telat"
              :key="telat.id"
              class="flex items-center gap-3"
            >
              <div
                class="w-11 h-11 rounded-xl flex items-center justify-center bg-red-50 text-red-600 font-semibold shrink-0"
              >
                {{ telat.siswa?.user?.name?.charAt(0) || "?" }}
              </div>
              <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between">
                  <p class="font-semibold text-slate-800 text-sm truncate">
                    {{ telat.siswa?.user?.name || "Nama Kosong" }}
                  </p>
                  <p class="font-semibold text-red-600 text-sm shrink-0 ml-2">
                    Rp {{ formatRupiah(telat.denda) }}
                  </p>
                </div>
                <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2">
                  <div
                    class="h-1.5 rounded-full bg-red-400"
                    :style="{
                      width: Math.min(100, telat.hari_telat * 10) + '%',
                    }"
                  ></div>
                </div>
                <p class="text-slate-400 text-xs mt-1">
                  Telat {{ telat.hari_telat }} hari
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.siswa_telat.length === 0"
              class="text-center text-slate-400 text-sm py-4"
            >
              Tidak ada tunggakan 🎉
            </p>
          </div>
        </div>

        <!-- Iuran Aktif -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <h2 class="text-lg font-bold text-slate-900 mb-1">Iuran Aktif</h2>
          <p class="text-slate-400 text-sm mb-5">Iuran yang sedang berjalan</p>
          <div class="flex flex-col gap-4">
            <div
              v-for="iuran in dashboardData.iuran_aktif"
              :key="iuran.id"
              class="flex items-center gap-3"
            >
              <div
                class="w-11 h-11 rounded-xl flex items-center justify-center bg-blue-50 text-blue-600 shrink-0"
              >
                <CurrencyDollarIcon class="w-6 h-6" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 text-sm truncate">
                  Iuran {{ iuran.bulan }} / {{ iuran.tahun }}
                </p>
                <div class="w-full h-1.5 bg-slate-100 rounded-full mt-2">
                  <div
                    class="h-1.5 rounded-full bg-emerald-400"
                    style="width: 100%"
                  ></div>
                </div>
                <p class="text-slate-400 text-xs mt-1">
                  Rp {{ formatRupiah(iuran.nominal) }}
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.iuran_aktif.length === 0"
              class="text-center text-slate-400 text-sm py-4"
            >
              Tidak ada iuran aktif
            </p>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-5">
            <div>
              <h2 class="text-lg font-bold text-slate-900">
                Transaksi Terbaru
              </h2>
              <p class="text-slate-400 text-sm">Aktivitas terakhir</p>
            </div>
          </div>
          <div class="flex flex-col gap-4">
            <div
              v-for="o in dashboardData.transaksi_terbaru"
              :key="o.id"
              class="flex items-center gap-3"
            >
              <div
                class="w-9 h-9 rounded-full flex items-center justify-center bg-slate-100 text-slate-600 font-semibold text-sm shrink-0"
              >
                {{ o.siswa?.user?.name?.charAt(0) || "?" }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-slate-800 text-sm truncate">
                  {{ o.siswa?.user?.name || "Siswa" }}
                </p>
                <p class="text-slate-400 text-xs">
                  #{{ o.id }} · {{ formatDate(o.created_at) }}
                </p>
              </div>
              <div class="text-right shrink-0">
                <p class="font-semibold text-slate-800 text-sm">
                  Rp {{ formatRupiah(o.jumlah) }}
                </p>
                <p
                  class="text-xs font-medium capitalize"
                  :class="{
                    'text-red-500': o.status === 'pending',
                    'text-emerald-500': o.status === 'confirmed',
                    'text-slate-400': o.status === 'rejected',
                  }"
                >
                  {{ o.status }}
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.transaksi_terbaru.length === 0"
              class="text-center text-slate-400 text-sm py-4"
            >
              Belum ada transaksi
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { Chart, registerables } from "chart.js";
import { useAuthStore } from "@/stores/auth";
import DashboardService from "@/api/dashboard";
import { toast } from "vue3-toastify";
import {
  CurrencyDollarIcon,
  UsersIcon,
  ExclamationCircleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const authStore = useAuthStore();
const dashboardData = ref(null);
const loading = ref(true);
const revenueChart = ref(null);
const statusChart = ref(null);
let revenueChartInstance = null;
let statusChartInstance = null;

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
};

const formatDate = (date) => {
  return dayjs(date).format("DD MMM YYYY");
};

const totalTransaksi = computed(() => {
  if (!dashboardData.value) return 0;
  const s = dashboardData.value.status_iuran;
  return s.lunas + s.pending + s.ditolak;
});

const fetchDashboard = async () => {
  loading.value = true;
  try {
    const response = await DashboardService.getDashboard();
    dashboardData.value = response.data.data;

    // 1. Set loading ke false DULU biar canvas-nya ke-render di DOM
    loading.value = false;

    // 2. Tunggu Vue selesai update DOM (canvas muncul)
    await nextTick();

    // 3. Baru render chart-nya
    if (dashboardData.value?.grafik) {
      renderRevenueChart();
      renderStatusChart();
    }
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat data dashboard");
    loading.value = false; // Pastikan loading tetap false kalau error
  }
};

const renderRevenueChart = () => {
  if (revenueChartInstance) revenueChartInstance.destroy();

  const grafik = dashboardData.value.grafik;
  const labels = grafik.pembayaran_per_bulan.labels;

  revenueChartInstance = new Chart(revenueChart.value, {
    type: "line",
    data: {
      labels: labels,
      datasets: [
        {
          label: "Pemasukan",
          data: grafik.pembayaran_per_bulan.data,
          borderColor: "#1046ee",
          backgroundColor: "rgba(16,70,238,0.08)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderWidth: 3,
        },
        {
          label: "Pengeluaran",
          data: grafik.pengeluaran_per_bulan.data,
          borderColor: "#ef4444",
          backgroundColor: "rgba(239,68,68,0.08)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderWidth: 3,
        },
      ],
    },
    options: {
      responsive: true,
      interaction: { mode: "index", intersect: false },
      plugins: { legend: { display: false } },
      scales: {
        y: {
          grid: { color: "#f1f5f9" },
          ticks: { callback: (v) => "Rp " + v / 1000 + "k" },
        },
        x: { grid: { display: false } },
      },
    },
  });
};

const renderStatusChart = () => {
  if (statusChartInstance) statusChartInstance.destroy();

  const status = dashboardData.value.status_iuran;

  statusChartInstance = new Chart(statusChart.value, {
    type: "doughnut",
    data: {
      labels: ["Lunas", "Pending", "Ditolak"],
      datasets: [
        {
          data: [status.lunas, status.pending, status.ditolak],
          backgroundColor: ["#cbd5e1", "#10b981", "#ef4444"],
          borderWidth: 0,
        },
      ],
    },
    options: {
      cutout: "75%",
      plugins: { legend: { display: false }, tooltip: { enabled: true } },
    },
  });
};

onMounted(() => {
  fetchDashboard();
});
</script>
