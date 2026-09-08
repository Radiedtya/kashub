<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          {{ filteredKelas.length }} kelas terdaftar
        </p>
      </div>
    </div>

    <!-- Chart Card -->
    <div
      class="kelas-chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6"
    >
      <div class="relative w-40 h-40 shrink-0">
        <canvas ref="kelasChart"></canvas>
        <div
          class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
        >
          <span class="text-xl font-bold text-zinc-900">{{
            kelasList.length
          }}</span>
          <span class="text-zinc-400 text-[10px] uppercase tracking-wide"
            >Total Kelas</span
          >
        </div>
      </div>
      <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Kelas Aktif</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ chartData.data[0] }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-zinc-400"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Kelas Nonaktif</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ chartData.data[1] }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Card Tabel -->
    <div
      class="kelas-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
    >
      <!-- Filter Row -->
      <div
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 p-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <!-- Search (Flex-1) -->
        <div class="relative flex-1 w-full">
          <MagnifyingGlassIcon
            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
          />
          <input
            v-model="searchName"
            type="text"
            placeholder="Cari nama kelas..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option value="true">Aktif</option>
          <option value="false">Nonaktif</option>
        </select>
      </div>

      <!-- Tabel -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <!-- Header Row -->
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            style="grid-template-columns: 60px 1.5fr 1fr 1.5fr 1fr"
          >
            <div class="text-center">No</div>
            <div>Nama Kelas</div>
            <div>Tahun Ajaran</div>
            <div>Wali Kelas</div>
            <div>Status</div>
          </div>

          <!-- States & Rows -->
          <div
            v-if="loading"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Memuat data kelas...
          </div>
          <div
            v-else-if="filteredKelas.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(k, index) in pagedKelas"
              :key="k.id"
              class="kelas-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              style="grid-template-columns: 60px 1.5fr 1fr 1.5fr 1fr"
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div class="pr-4 min-w-45">
                <span class="font-semibold text-zinc-800">{{
                  k.nama || "-"
                }}</span>
              </div>

              <div class="pr-4 text-zinc-600 text-xs min-w-30">
                {{ k.tahun_ajaran || "-" }}
              </div>

              <div class="flex items-center gap-3 pr-4 min-w-45">
                <img
                  v-if="k.wali_kelas?.foto"
                  :src="k.wali_kelas.foto"
                  class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                  alt="foto wali kelas"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-500 flex items-center justify-center font-semibold text-xs shrink-0"
                >
                  {{ k.wali_kelas?.name?.charAt(0) || "?" }}
                </div>
                <span class="text-zinc-600 text-xs">{{
                  k.wali_kelas?.name || "-"
                }}</span>
              </div>

              <div class="pr-4 min-w-25">
                <span
                  class="flex items-center gap-1.5 text-xs font-medium"
                  :class="k.is_active ? 'text-emerald-600' : 'text-zinc-400'"
                >
                  <span
                    class="w-1.5 h-1.5 rounded-full"
                    :class="k.is_active ? 'bg-emerald-500' : 'bg-zinc-400'"
                  ></span>
                  {{ k.is_active ? "Aktif" : "Nonaktif" }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-zinc-100 gap-4"
      >
        <p class="text-zinc-400 text-xs">
          Showing {{ rangeStart }}–{{ rangeEnd }} of {{ filteredKelas.length }}
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition"
          >
            <ChevronLeftIcon class="w-4 h-4" />
          </button>
          <button
            v-for="p in totalPages"
            :key="p"
            @click="currentPage = p"
            class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium transition"
            :class="
              currentPage === p
                ? 'bg-zinc-900 text-white'
                : 'text-zinc-500 hover:bg-zinc-100'
            "
          >
            {{ p }}
          </button>
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition"
          >
            <ChevronRightIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { toast } from "vue3-toastify";
import { useAuthStore } from "@/stores/auth";
import KelasService from "@/api/kelas";
import anime from "animejs";
import { Chart, registerables } from "chart.js";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
} from "@heroicons/vue/24/outline";

Chart.register(...registerables);

const authStore = useAuthStore();
const kelasList = ref([]);
const loading = ref(false);

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 10;

// Chart State
const kelasChart = ref(null);
let chartInstance = null;

// --- Anime.js Stagger Animation ---
const triggerAnimations = () => {
  anime({
    targets: ".kelas-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".kelas-chart-card",
    translateY: [20, 0],
    opacity: [0, 1],
    delay: 100,
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".kelas-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchKelas = async () => {
  loading.value = true;
  try {
    const response = await KelasService.getAll();
    kelasList.value = response.data.data || [];

    loading.value = false;
    await nextTick();
    triggerAnimations();
    renderChart();
  } catch (error) {
    toast.error("Gagal memuat data kelas");
    loading.value = false;
  }
};

// Computed buat Chart Data
const chartData = computed(() => {
  let active = 0,
    inactive = 0;
  kelasList.value.forEach((k) => {
    if (k.is_active) active++;
    else inactive++;
  });
  return {
    labels: ["Aktif", "Nonaktif"],
    data: [active, inactive],
    colors: ["#10b981", "#e4e4e7"],
  };
});

const filteredKelas = computed(() => {
  let list = kelasList.value;
  if (searchName.value) {
    list = list.filter((k) =>
      k.nama?.toLowerCase().includes(searchName.value.toLowerCase()),
    );
  }
  if (filterStatus.value !== "Semua") {
    list = list.filter((k) => String(k.is_active) === filterStatus.value);
  }
  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredKelas.value.length / pageSize)),
);
const pagedKelas = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredKelas.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredKelas.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredKelas.value.length),
);

// --- Render Chart ---
const renderChart = () => {
  if (chartInstance) chartInstance.destroy();

  if (kelasChart.value) {
    chartInstance = new Chart(kelasChart.value, {
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

onMounted(() => {
  fetchKelas();
});
</script>
