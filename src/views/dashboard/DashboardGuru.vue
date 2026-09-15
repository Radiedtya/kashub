<template>
  <div class="space-y-6">
    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-6 animate-pulse">
      <!-- Skeleton Stat Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <div
          v-for="i in 4"
          :key="i"
          class="bg-white border border-zinc-200 rounded-xl p-5 h-31.25"
        >
          <div class="flex items-center justify-between mb-4">
            <div class="w-10 h-10 rounded-lg bg-zinc-200"></div>
          </div>
          <div class="h-6 bg-zinc-200 rounded w-3/4 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/2"></div>
        </div>
      </div>

      <!-- Skeleton Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div
          class="lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6 h-85"
        >
          <div class="h-5 bg-zinc-200 rounded w-1/3 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/4 mb-6"></div>
          <div class="h-55 bg-zinc-100 rounded-lg flex items-end gap-4 px-4">
            <div class="w-1/6 h-1/2 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-3/4 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-1/3 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-2/3 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-1/2 bg-zinc-200 rounded-t"></div>
            <div class="w-1/6 h-4/5 bg-zinc-200 rounded-t"></div>
          </div>
        </div>
        <div
          class="bg-white border border-zinc-200 rounded-xl p-6 h-85 flex flex-col"
        >
          <div class="h-5 bg-zinc-200 rounded w-1/2 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/3 mb-6"></div>
          <div class="flex-1 flex items-center justify-center">
            <div class="w-32 h-32 bg-zinc-100 rounded-full"></div>
          </div>
        </div>
      </div>

      <!-- Skeleton Bottom Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div
          v-for="i in 3"
          :key="i"
          class="bg-white border border-zinc-200 rounded-xl p-6 h-75"
        >
          <div class="h-5 bg-zinc-200 rounded w-1/2 mb-2"></div>
          <div class="h-4 bg-zinc-200 rounded w-1/3 mb-6"></div>
          <div class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-zinc-200 rounded-full shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-zinc-200 rounded w-3/4"></div>
                <div class="h-3 bg-zinc-200 rounded w-1/2"></div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-zinc-200 rounded-full shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-zinc-200 rounded w-2/3"></div>
                <div class="h-3 bg-zinc-200 rounded w-1/3"></div>
              </div>
            </div>
            <div class="flex items-center gap-3">
              <div class="w-9 h-9 bg-zinc-200 rounded-full shrink-0"></div>
              <div class="flex-1 space-y-2">
                <div class="h-4 bg-zinc-200 rounded w-1/2"></div>
                <div class="h-3 bg-zinc-200 rounded w-1/4"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="dashboardData">
      <!-- Stat cards (White Clean, Scale Up Animation) -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Total Saldo Kas"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <CurrencyDollarIcon class="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(animatedKas) }}
          </p>
          <p class="text-zinc-600 text-sm mt-1">Total Saldo Kas</p>
        </section>

        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Total Siswa"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <UsersIcon class="w-5 h-5 text-emerald-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">{{ animatedSiswa }}</p>
          <p class="text-zinc-600 text-sm mt-1">Total Siswa</p>
        </section>

        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Siswa Telat Bayar"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ExclamationCircleIcon class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">{{ animatedTelat }}</p>
          <p class="text-zinc-600 text-sm mt-1">Siswa Telat Bayar</p>
        </section>

        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Iuran Aktif"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ChartBarIcon class="w-5 h-5 text-amber-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">{{ animatedIuran }}</p>
          <p class="text-zinc-600 text-sm mt-1">Iuran Aktif</p>
        </section>
      </div>

      <!-- Charts row (Slide from Left) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Revenue chart -->
        <section
          class="chart-card lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6"
          aria-label="Grafik Pemasukan vs Pengeluaran"
        >
          <div class="flex items-center justify-between mb-5">
            <div>
              <h2 class="text-base font-semibold text-zinc-800">
                Pemasukan vs Pengeluaran
              </h2>
              <p class="text-zinc-500 text-xs">6 bulan terakhir</p>
            </div>
            <div class="flex items-center gap-4 text-xs">
              <span class="flex items-center gap-1.5 text-zinc-600">
                <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span
                >Pemasukan
              </span>
              <span class="flex items-center gap-1.5 text-zinc-600">
                <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span
                >Pengeluaran
              </span>
            </div>
          </div>
          <div class="relative h-75 w-full">
            <canvas
              ref="revenueChart"
              aria-label="Chart Pemasukan"
              role="img"
            ></canvas>
          </div>
        </section>

        <!-- Order status donut -->
        <section
          class="chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col"
          aria-label="Status Pembayaran"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Status Pembayaran
          </h2>
          <p class="text-zinc-500 text-xs mb-4">Distribusi semua transaksi</p>
          <div
            class="flex-1 flex items-center justify-center relative my-4 h-55"
          >
            <canvas
              ref="statusChart"
              aria-label="Chart Status"
              role="img"
            ></canvas>
            <div
              class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
            >
              <span class="text-xl font-bold text-zinc-900">{{
                totalTransaksi
              }}</span>
              <span class="text-zinc-500 text-[10px] uppercase tracking-wide"
                >Total</span
              >
            </div>
          </div>
          <div class="flex flex-col gap-2 mt-4 border-t border-zinc-100 pt-4">
            <div class="flex items-center justify-between text-xs">
              <span class="flex items-center gap-2 text-zinc-600"
                ><span class="w-2 h-2 rounded-full bg-zinc-300"></span
                >Lunas</span
              >
              <span class="font-semibold text-zinc-700">{{
                dashboardData.status_iuran.lunas
              }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="flex items-center gap-2 text-zinc-600"
                ><span class="w-2 h-2 rounded-full bg-emerald-500"></span
                >Pending</span
              >
              <span class="font-semibold text-zinc-700">{{
                dashboardData.status_iuran.pending
              }}</span>
            </div>
            <div class="flex items-center justify-between text-xs">
              <span class="flex items-center gap-2 text-zinc-600"
                ><span class="w-2 h-2 rounded-full bg-red-500"></span
                >Ditolak</span
              >
              <span class="font-semibold text-zinc-700">{{
                dashboardData.status_iuran.ditolak
              }}</span>
            </div>
          </div>
        </section>
      </div>

      <!-- Bottom row (Slide from Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Top Tunggakan -->
        <section
          class="bottom-card bg-white border border-zinc-200 rounded-xl p-6"
          aria-label="Tunggakan Terbanyak"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Tunggakan Terbanyak
          </h2>
          <p class="text-zinc-500 text-xs mb-2">
            Siswa telat bayar & punya denda
          </p>
          <div class="flex flex-col divide-y divide-zinc-100">
            <div
              v-for="telat in dashboardData.siswa_telat"
              :key="telat.id"
              class="flex items-center gap-3 py-3"
            >
              <img
                v-if="telat.siswa?.user?.foto"
                :src="telat.siswa.user.foto"
                loading="lazy"
                width="36"
                height="36"
                class="w-9 h-9 rounded-full object-cover shrink-0 border border-zinc-100"
                alt="Foto Siswa"
                @error="onImgError"
              />
              <div
                v-else
                class="w-9 h-9 rounded-lg flex items-center justify-center bg-red-50 text-red-600 font-semibold text-xs shrink-0"
              >
                {{ telat.siswa?.user?.name?.charAt(0) || "?" }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-zinc-800 text-sm truncate">
                  {{ telat.siswa?.user?.name || "Nama Kosong" }}
                </p>
                <div class="w-full h-1 bg-zinc-100 rounded-full mt-1.5">
                  <div
                    class="h-1 rounded-full bg-red-400"
                    :style="{
                      width: Math.min(100, telat.hari_telat * 10) + '%',
                    }"
                  ></div>
                </div>
              </div>
              <div class="text-right shrink-0">
                <p class="font-semibold text-red-600 text-xs">
                  Rp {{ formatRupiah(telat.denda) }}
                </p>
                <p class="text-zinc-500 text-[10px] mt-0.5">
                  Telat {{ telat.hari_telat }} hari
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.siswa_telat.length === 0"
              class="text-center text-zinc-500 text-sm py-8"
            >
              Tidak ada tunggakan tersedia
            </p>
          </div>
        </section>

        <!-- Iuran Aktif -->
        <section
          class="bottom-card bg-white border border-zinc-200 rounded-xl p-6"
          aria-label="Iuran Aktif"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Iuran Aktif
          </h2>
          <p class="text-zinc-500 text-xs mb-2">Iuran yang sedang berjalan</p>
          <div class="flex flex-col divide-y divide-zinc-100">
            <div
              v-for="iuran in dashboardData.iuran_aktif"
              :key="iuran.id"
              class="flex items-center gap-3 py-3"
            >
              <div
                class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-50 text-blue-600 shrink-0"
              >
                <CurrencyDollarIcon class="w-5 h-5" />
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-zinc-800 text-sm truncate">
                  Iuran {{ iuran.bulan }} / {{ iuran.tahun }}
                </p>
                <div class="w-full h-1 bg-zinc-100 rounded-full mt-1.5">
                  <div
                    class="h-1 rounded-full bg-emerald-400"
                    style="width: 100%"
                  ></div>
                </div>
              </div>
              <div class="text-right shrink-0">
                <p class="font-semibold text-zinc-700 text-xs">
                  Rp {{ formatRupiah(iuran.nominal) }}
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.iuran_aktif.length === 0"
              class="text-center text-zinc-500 text-sm py-8"
            >
              Tidak ada iuran aktif
            </p>
          </div>
        </section>

        <!-- Recent Transactions -->
        <section
          class="bottom-card bg-white border border-zinc-200 rounded-xl p-6"
          aria-label="Transaksi Terbaru"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Transaksi Terbaru
          </h2>
          <p class="text-zinc-500 text-xs mb-2">Aktivitas terakhir</p>
          <div class="flex flex-col divide-y divide-zinc-100">
            <div
              v-for="o in dashboardData.transaksi_terbaru"
              :key="o.id"
              class="flex items-center gap-3 py-3"
            >
              <img
                v-if="o.siswa?.user?.foto"
                :src="o.siswa.user.foto"
                loading="lazy"
                width="36"
                height="36"
                class="w-9 h-9 rounded-full object-cover shrink-0 border border-zinc-100"
                alt="Foto Siswa"
                @error="onImgError"
              />
              <div
                v-else
                class="w-9 h-9 rounded-full flex items-center justify-center bg-zinc-50 text-zinc-600 font-semibold text-xs shrink-0 border border-zinc-100"
              >
                {{ o.siswa?.user?.name?.charAt(0) || "?" }}
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-medium text-zinc-800 text-sm truncate">
                  {{ o.siswa?.user?.name || "Siswa" }}
                </p>
                <p class="text-zinc-500 text-[10px]">
                  #{{ o.id }} · {{ formatDate(o.created_at) }}
                </p>
              </div>
              <div class="text-right shrink-0">
                <p class="font-semibold text-zinc-700 text-xs">
                  Rp {{ formatRupiah(o.jumlah) }}
                </p>
                <p
                  class="text-[10px] font-medium capitalize"
                  :class="{
                    'text-yellow-500': o.status === 'pending',
                    'text-emerald-500': o.status === 'confirmed',
                    'text-red-500': o.status === 'rejected',
                  }"
                >
                  {{ o.status }}
                </p>
              </div>
            </div>
            <p
              v-if="dashboardData.transaksi_terbaru.length === 0"
              class="text-center text-zinc-500 text-sm py-8"
            >
              Belum ada transaksi
            </p>
          </div>
        </section>
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
  UsersIcon,
  ExclamationCircleIcon,
  ChartBarIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const authStore = useAuthStore();
const dashboardData = ref(null);
const loading = ref(true);
const revenueChart = ref(null);
const statusChart = ref(null);
let revenueChartInstance = null;
let statusChartInstance = null;

// State buat Animasi Count-Up
const animatedKas = ref(0);
const animatedSiswa = ref(0);
const animatedTelat = ref(0);
const animatedIuran = ref(0);

const formatRupiah = (angka) => {
  return new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
};

const formatDate = (date) => {
  return dayjs(date).format("DD MMM YYYY");
};

const onImgError = (e) => {
  e.target.style.display = "none";
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

    loading.value = false;
    await nextTick();

    // Dynamic import library berat biar gak nge-block initial load
    const [{ default: anime }, { Chart, registerables }] = await Promise.all([
      import("animejs"),
      import("chart.js"),
    ]);

    Chart.register(...registerables);

    if (dashboardData.value?.grafik) {
      renderRevenueChart(Chart);
      renderStatusChart(Chart);
    }

    triggerAnimations(anime);
    animateStats(anime);
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat data dashboard");
    loading.value = false;
  }
};

// Animasi Muncul
const triggerAnimations = (anime) => {
  anime({
    targets: ".stat-card",
    scale: [0.8, 1],
    opacity: [0, 1],
    delay: anime.stagger(100, { start: 0 }),
    duration: 700,
    easing: "easeOutBack",
  });

  anime({
    targets: ".chart-card",
    translateX: [-50, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 300 }),
    duration: 800,
    easing: "easeOutQuart",
  });

  anime({
    targets: ".bottom-card",
    translateX: [50, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 500 }),
    duration: 800,
    easing: "easeOutQuart",
  });
};

// Animasi Angka Naik
const animateStats = (anime) => {
  const stats = dashboardData.value.statistik;

  const counters = [
    { ref: animatedKas, target: stats.total_kas },
    { ref: animatedSiswa, target: stats.total_siswa },
    { ref: animatedTelat, target: stats.siswa_telat },
    { ref: animatedIuran, target: stats.total_iuran_aktif },
  ];

  counters.forEach((counter, index) => {
    const obj = { val: 0 };
    anime({
      targets: obj,
      val: counter.target,
      round: 1,
      duration: 1500,
      delay: index * 150,
      easing: "easeOutExpo",
      update: () => {
        counter.ref.value = obj.val;
      },
    });
  });
};

const renderRevenueChart = (Chart) => {
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
          borderColor: "#2563eb",
          backgroundColor: "rgba(37,99,235,0.05)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: "Pengeluaran",
          data: grafik.pengeluaran_per_bulan.data,
          borderColor: "#dc2626",
          backgroundColor: "rgba(220,38,38,0.05)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderWidth: 2,
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: "index", intersect: false },
      plugins: { legend: { display: false } },
      scales: {
        y: {
          grid: { color: "#f4f4f5" },
          border: { display: false },
          ticks: {
            callback: (v) => "Rp " + v / 1000 + "k",
            color: "#71717a",
            font: { size: 10 },
          },
        },
        x: {
          grid: { display: false },
          border: { display: false },
          ticks: { color: "#71717a", font: { size: 10 } },
        },
      },
    },
  });
};

const renderStatusChart = (Chart) => {
  if (statusChartInstance) statusChartInstance.destroy();

  const status = dashboardData.value.status_iuran;

  statusChartInstance = new Chart(statusChart.value, {
    type: "doughnut",
    data: {
      labels: ["Lunas", "Pending", "Ditolak"],
      datasets: [
        {
          data: [status.lunas, status.pending, status.ditolak],
          backgroundColor: ["#e4e4e7", "#10b981", "#ef4444"],
          borderWidth: 0,
          hoverOffset: 4,
        },
      ],
    },
    options: {
      cutout: "70%",
      plugins: { legend: { display: false }, tooltip: { enabled: true } },
    },
  });
};

onMounted(() => {
  fetchDashboard();
});
</script>
