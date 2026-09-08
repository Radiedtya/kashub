<template>
  <div class="space-y-6">
    <!-- Filter & Export Header -->
    <div
      class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4"
    >
      <div class="flex items-center gap-3 flex-wrap">
        <select
          v-model="filterBulan"
          @change="fetchLaporan"
          class="px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option v-for="n in 12" :key="n" :value="n">
            {{ getMonthName(n) }}
          </option>
        </select>

        <select
          v-model="filterTahun"
          @change="fetchLaporan"
          class="px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
        </select>

        <select
          v-if="authStore.role !== 'guru'"
          v-model="filterKelas"
          @change="fetchLaporan"
          class="px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Kelas</option>
          <option v-for="k in kelasList" :key="k.id" :value="k.id">
            {{ k.nama }}
          </option>
        </select>
      </div>

      <div class="flex items-center gap-2">
        <button
          @click="exportFile('pdf')"
          :disabled="exporting"
          class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-3 py-2 border border-zinc-200 rounded-lg text-sm text-red-600 hover:bg-red-50 transition font-medium disabled:opacity-50"
        >
          <DocumentArrowDownIcon class="w-4 h-4" />
          <span>Export PDF</span>
        </button>
        <button
          @click="exportFile('excel')"
          :disabled="exporting"
          class="flex-1 md:flex-none flex items-center justify-center gap-1.5 px-3 py-2 border border-zinc-200 rounded-lg text-sm text-green-600 hover:bg-green-50 transition font-medium disabled:opacity-50"
        >
          <DocumentArrowDownIcon class="w-4 h-4" />
          <span>Export Excel</span>
        </button>
      </div>
    </div>

    <!-- Chart & Stat Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Chart Doughnut -->
      <div
        class="laporan-chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col items-center justify-center"
      >
        <h2 class="text-base font-semibold text-zinc-800 mb-1 self-start">
          Rasio Keuangan
        </h2>
        <p class="text-zinc-400 text-xs mb-4 self-start">
          Pemasukan vs Pengeluaran
        </p>
        <div class="relative w-40 h-40 my-4">
          <canvas ref="laporanChart"></canvas>
          <div
            class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
          >
            <span class="text-xl font-bold text-zinc-900"
              >{{ persentasePemasukan }}%</span
            >
            <span class="text-zinc-400 text-[10px] uppercase tracking-wide"
              >Pemasukan</span
            >
          </div>
        </div>
        <div class="flex items-center gap-4 text-xs mt-2">
          <span class="flex items-center gap-1.5 text-zinc-500">
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>Masuk
          </span>
          <span class="flex items-center gap-1.5 text-zinc-500">
            <span class="w-2.5 h-2.5 rounded-full bg-red-500"></span>Keluar
          </span>
        </div>
      </div>

      <!-- Stat Cards (Col-span 2) -->
      <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-3 gap-5">
        <div
          class="laporan-stat bg-white border border-zinc-200 rounded-xl p-5 flex flex-col justify-center"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ArrowTrendingUpIcon class="w-5 h-5 text-emerald-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(statistik.total_pemasukan) }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Total Pemasukan</p>
        </div>

        <div
          class="laporan-stat bg-white border border-zinc-200 rounded-xl p-5 flex flex-col justify-center"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <ArrowTrendingDownIcon class="w-5 h-5 text-red-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(statistik.total_pengeluaran) }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Total Pengeluaran</p>
        </div>

        <div
          class="laporan-stat bg-white border border-zinc-200 rounded-xl p-5 flex flex-col justify-center"
        >
          <div class="flex items-center justify-between mb-4">
            <div
              class="w-10 h-10 rounded-lg flex items-center justify-center bg-zinc-50 border border-zinc-100"
            >
              <CurrencyDollarIcon class="w-5 h-5 text-blue-600" />
            </div>
          </div>
          <p class="text-2xl font-bold text-zinc-900">
            Rp {{ formatRupiah(statistik.saldo) }}
          </p>
          <p class="text-zinc-500 text-sm mt-1">Saldo Akhir</p>
        </div>
      </div>
    </div>

    <!-- Detail Transaksi & Pengeluaran -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Detail Pemasukan -->
      <div class="laporan-card bg-white border border-zinc-200 rounded-xl p-6">
        <h2 class="text-base font-semibold text-zinc-800 mb-1">
          Detail Pemasukan
        </h2>
        <p class="text-zinc-400 text-xs mb-4">Transaksi iuran yang lunas</p>
        <div
          class="flex flex-col divide-y divide-zinc-100 max-h-100 overflow-y-auto pr-2"
        >
          <div
            v-for="trx in transaksiList"
            :key="trx.id"
            class="flex items-center gap-3 py-3"
          >
            <!-- Avatar Profile Siswa -->
            <img
              v-if="trx.siswa?.user?.foto"
              :src="trx.siswa.user.foto"
              class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
              alt="foto"
            />
            <div
              v-else
              class="w-8 h-8 rounded-full bg-zinc-50 text-zinc-600 flex items-center justify-center font-semibold text-xs shrink-0 border border-zinc-100"
            >
              {{ trx.siswa?.user?.name?.charAt(0) || "?" }}
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-zinc-800 text-sm truncate">
                {{ trx.siswa?.user?.name || "Siswa" }}
              </p>
              <p class="text-zinc-400 text-[10px]">
                {{ formatDate(trx.tanggal_bayar) }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-semibold text-emerald-600 text-xs">
                + Rp {{ formatRupiah(trx.jumlah) }}
              </p>
            </div>
          </div>
          <p
            v-if="transaksiList.length === 0"
            class="text-center text-zinc-400 text-sm py-8"
          >
            Tidak ada pemasukan
          </p>
        </div>
      </div>

      <!-- Detail Pengeluaran -->
      <div class="laporan-card bg-white border border-zinc-200 rounded-xl p-6">
        <h2 class="text-base font-semibold text-zinc-800 mb-1">
          Detail Pengeluaran
        </h2>
        <p class="text-zinc-400 text-xs mb-4">Pengeluaran yang disetujui</p>
        <div
          class="flex flex-col divide-y divide-zinc-100 max-h-100k overflow-y-auto pr-2"
        >
          <div
            v-for="p in pengeluaranList"
            :key="p.id"
            class="flex items-center gap-3 py-3"
          >
            <!-- Avatar Profile Pengaju -->
            <img
              v-if="p.created_by?.foto"
              :src="p.created_by.foto"
              class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
              alt="foto"
            />
            <div
              v-else
              class="w-8 h-8 rounded-lg flex items-center justify-center bg-red-50 text-red-600 shrink-0 border border-red-100"
            >
              <ArrowTrendingDownIcon class="w-4 h-4" />
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-medium text-zinc-800 text-sm truncate">
                {{ p.judul }}
              </p>
              <p class="text-zinc-400 text-[10px]">
                Oleh: {{ p.created_by?.name || "-" }} ·
                {{ formatDate(p.tanggal) }}
              </p>
            </div>
            <div class="text-right shrink-0">
              <p class="font-semibold text-red-600 text-xs">
                - Rp {{ formatRupiah(p.jumlah) }}
              </p>
            </div>
          </div>
          <p
            v-if="pengeluaranList.length === 0"
            class="text-center text-zinc-400 text-sm py-8"
          >
            Tidak ada pengeluaran
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { toast } from "vue3-toastify";
import LaporanService from "@/api/laporan";
import KelasService from "@/api/kelas";
import anime from "animejs";
import { Chart, registerables } from "chart.js";
import {
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  CurrencyDollarIcon,
  DocumentArrowDownIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const loading = ref(false);
const exporting = ref(false);
const kelasList = ref([]);

const now = new Date();
const filterBulan = ref(now.getMonth() + 1);
const filterTahun = ref(now.getFullYear());
const filterKelas = ref("Semua");
const years = [now.getFullYear(), now.getFullYear() - 1, now.getFullYear() - 2];

const statistik = ref({
  total_pemasukan: 0,
  total_pengeluaran: 0,
  saldo: 0,
});
const transaksiList = ref([]);
const pengeluaranList = ref([]);

// Chart State
const laporanChart = ref(null);
let chartInstance = null;

const triggerAnimations = () => {
  anime({
    targets: ".laporan-stat",
    translateY: [20, 0],
    opacity: [0, 1],
    delay: anime.stagger(100),
    duration: 600,
    easing: "easeOutQuad",
  });
  anime({
    targets: ".laporan-chart-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });
  anime({
    targets: ".laporan-card",
    translateY: [30, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 300 }),
    duration: 700,
    easing: "easeOutQuad",
  });
};

// Computed buat Chart
const chartData = computed(() => {
  return {
    labels: ["Pemasukan", "Pengeluaran"],
    data: [statistik.value.total_pemasukan, statistik.value.total_pengeluaran],
    colors: ["#10b981", "#ef4444"],
  };
});

const persentasePemasukan = computed(() => {
  const total =
    statistik.value.total_pemasukan + statistik.value.total_pengeluaran;
  if (total === 0) return 0;
  return Math.round((statistik.value.total_pemasukan / total) * 100);
});

const fetchLaporan = async () => {
  loading.value = true;
  try {
    const response = await LaporanService.getPerBulan(
      filterBulan.value,
      filterTahun.value,
    );
    const data = response.data.data;

    statistik.value = {
      total_pemasukan: data.total_pemasukan,
      total_pengeluaran: data.total_pengeluaran,
      saldo: data.saldo,
    };
    transaksiList.value = data.transaksi || [];
    pengeluaranList.value = data.pengeluaran || [];

    if (filterKelas.value !== "Semua") {
      transaksiList.value = transaksiList.value.filter(
        (t) => String(t.iuran?.kelas_id) === String(filterKelas.value),
      );
      pengeluaranList.value = pengeluaranList.value.filter(
        (p) => String(p.kelas_id) === String(filterKelas.value),
      );

      const masuk = transaksiList.value.reduce(
        (sum, t) => sum + parseFloat(t.jumlah),
        0,
      );
      const keluar = pengeluaranList.value.reduce(
        (sum, p) => sum + parseFloat(p.jumlah),
        0,
      );
      statistik.value.total_pemasukan = masuk;
      statistik.value.total_pengeluaran = keluar;
      statistik.value.saldo = masuk - keluar;
    }

    loading.value = false;
    await nextTick();
    triggerAnimations();
    renderChart();
  } catch (error) {
    toast.error("Gagal memuat data laporan");
    loading.value = false;
  }
};

const exportFile = async (format) => {
  exporting.value = true;
  toast.info("Menyiapkan file export...");
  try {
    const params = {
      bulan: filterBulan.value,
      tahun: filterTahun.value,
    };
    const response = await LaporanService.export(format, params);

    if (response.data.type.includes("application/json")) {
      const text = await response.data.text();
      const err = JSON.parse(text);
      throw new Error(err.message || "Gagal export");
    }

    const blob = new Blob([response.data]);
    const link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    link.download = `laporan_kas_${getMonthName(filterBulan.value)}_${filterTahun.value}.${format === "pdf" ? "pdf" : "xlsx"}`;
    link.click();
    window.URL.revokeObjectURL(link.href);
    toast.success("File berhasil diunduh!");
  } catch (error) {
    toast.error(error.message || "Gagal mengunduh file");
  } finally {
    exporting.value = false;
  }
};

// --- Render Chart ---
const renderChart = () => {
  if (chartInstance) chartInstance.destroy();

  if (laporanChart.value) {
    chartInstance = new Chart(laporanChart.value, {
      type: "doughnut",
      data: {
        labels: chartData.value.labels,
        datasets: [
          {
            data: chartData.value.data,
            backgroundColor: chartData.value.colors,
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
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const formatDate = (date) => (date ? dayjs(date).format("DD MMM YYYY") : "-");
const getMonthName = (monthNum) => {
  const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return months[monthNum - 1] || "-";
};

onMounted(async () => {
  try {
    const resKelas = await KelasService.getAll();
    kelasList.value = resKelas.data.data || [];

    // Kalau yang login Guru, auto-set filterKelas ke kelasnya dia
    if (authStore.role === "guru" && authStore.user?.kelas_id) {
      filterKelas.value = authStore.user.kelas_id;
    }
  } catch (e) {
    console.error("Gagal ambil kelas");
  }
  fetchLaporan();
});
</script>
