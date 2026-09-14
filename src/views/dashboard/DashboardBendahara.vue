<template>
  <div class="space-y-6">
    <!-- Skeleton Loader -->
    <div v-if="loading" class="space-y-6 animate-pulse">
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
      <div class="bg-white border border-zinc-200 rounded-xl p-6 h-75">
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
        </div>
      </div>
    </div>

    <div v-else-if="dashboardData">
      <!-- Stat Cards (White Clean, Scale Up Animation) -->
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
          aria-label="Pemasukan Bulan Ini"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ArrowTrendingUpIcon class="w-5 h-5 text-emerald-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(animatedMasuk) }}
          </p>
          <p class="text-zinc-600 text-sm mt-1">Pemasukan Bulan Ini</p>
        </section>

        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Pengeluaran Bulan Ini"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ArrowTrendingDownIcon class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(animatedKeluar) }}
          </p>
          <p class="text-zinc-600 text-sm mt-1">Pengeluaran Bulan Ini</p>
        </section>

        <section
          class="stat-card bg-white border border-zinc-200 rounded-xl p-5"
          aria-label="Transaksi Perlu Konfirmasi"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ClockIcon class="w-5 h-5 text-amber-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">{{ animatedPending }}</p>
          <p class="text-zinc-600 text-sm mt-1">Transaksi Perlu Konfirmasi</p>
        </section>
      </div>

      <!-- Charts Row (Slide from Left) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mt-5">
        <!-- Revenue Chart -->
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

        <!-- Status Donut Chart -->
        <section
          class="chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col"
          aria-label="Status Pembayaran"
        >
          <h2 class="text-base font-semibold text-zinc-800 mb-1">
            Status Pembayaran
          </h2>
          <p class="text-zinc-500 text-xs mb-4">Distribusi semua transaksi</p>
          <div
            class="flex-1 flex items-center justify-center relative my-4 h-56"
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

      <!-- Antrian Konfirmasi (Slide from Right) -->
      <section
        class="bottom-card bg-white border border-zinc-200 rounded-xl p-6 mt-5"
        aria-label="Antrian Konfirmasi Transaksi"
      >
        <div class="flex items-center justify-between mb-5">
          <div>
            <h2 class="text-base font-semibold text-zinc-800">
              Antrian Konfirmasi Transaksi
            </h2>
            <p class="text-zinc-500 text-xs">
              Siswa yang menunggu pembayaran dikonfirmasi
            </p>
          </div>
        </div>
        <div class="flex flex-col divide-y divide-zinc-100">
          <div
            v-for="trx in dashboardData.transaksi_pending_list"
            :key="trx.id"
            class="flex items-center gap-3 py-3"
          >
            <img
              v-if="trx.siswa?.user?.foto"
              :src="trx.siswa.user.foto"
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
              {{ trx.siswa?.user?.name?.charAt(0) || "?" }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-zinc-800 text-sm truncate">
                {{ trx.siswa?.user?.name || "Siswa" }}
              </p>
              <p class="text-zinc-500 text-[10px]">
                Iuran {{ trx.iuran?.bulan }}/{{ trx.iuran?.tahun }} ·
                {{ formatDate(trx.created_at) }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-semibold text-zinc-700 text-xs">
                Rp {{ formatRupiah(trx.jumlah) }}
              </p>
              <button
                @click="confirmAction(trx)"
                class="text-[10px] font-semibold text-blue-600 hover:underline transition"
              >
                Konfirmasi Sekarang
              </button>
            </div>
          </div>
          <p
            v-if="dashboardData.transaksi_pending_list.length === 0"
            class="text-center text-zinc-500 text-sm py-8"
          >
            Tidak ada antrian transaksi
          </p>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import DashboardService from "@/api/dashboard";
import TransaksiService from "@/api/transaksi";
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import {
  CurrencyDollarIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  ClockIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const dashboardData = ref(null);
const loading = ref(true);
const revenueChart = ref(null);
const statusChart = ref(null);
let revenueChartInstance = null;
let statusChartInstance = null;

// State buat Animasi Count-Up
const animatedKas = ref(0);
const animatedMasuk = ref(0);
const animatedKeluar = ref(0);
const animatedPending = ref(0);

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const formatDate = (date) => dayjs(date).format("DD MMM YYYY");

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

    triggerAnimations(anime);
    animateStats(anime);

    // Delay render Chart biar LCP (Largest Contentful Paint) cepat
    setTimeout(() => {
      if (dashboardData.value?.grafik) {
        renderRevenueChart(Chart);
        renderStatusChart(Chart);
      }
    }, 100);
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat data dashboard");
    loading.value = false;
  }
};

// Animasi Muncul (Variasi Arah)
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

// Animasi Angka Naik (Count-Up)
const animateStats = (anime) => {
  const stats = dashboardData.value.statistik;

  const counters = [
    { ref: animatedKas, target: stats.total_kas },
    { ref: animatedMasuk, target: stats.pemasukan_bulan_ini },
    { ref: animatedKeluar, target: stats.pengeluaran_bulan_ini },
    { ref: animatedPending, target: stats.transaksi_pending },
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

// --- Fungsi Konfirmasi Transaksi ---
const confirmAction = (trx) => {
  Swal.fire({
    title: "Approve (Lunas) Transaksi?",
    text: `Kamu yakin mau Approve pembayaran dari ${trx.siswa?.user?.name}?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: "#10b981",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Approve",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await TransaksiService.konfirmasi(trx.id, { status: "confirmed" });
        toast.success("Transaksi berhasil di-Approve");
        fetchDashboard(); // Refresh dashboard biar antriannya ilang
      } catch (error) {
        const msg = error.response?.data?.message || "Gagal Approve transaksi";
        toast.error(msg);
      }
    }
  });
};

const renderRevenueChart = (Chart) => {
  if (revenueChartInstance) revenueChartInstance.destroy();

  const grafik = dashboardData.value.grafik;

  revenueChartInstance = new Chart(revenueChart.value, {
    type: "line",
    data: {
      labels: grafik.labels,
      datasets: [
        {
          label: "Pemasukan",
          data: grafik.pemasukan,
          borderColor: "#2563eb",
          backgroundColor: "rgba(37,99,235,0.05)",
          fill: true,
          tension: 0.4,
          pointRadius: 0,
          borderWidth: 2,
        },
        {
          label: "Pengeluaran",
          data: grafik.pengeluaran,
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
            color: "#71717a", // Diubah dari a1a1aa biar contrast lebih bagus
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
