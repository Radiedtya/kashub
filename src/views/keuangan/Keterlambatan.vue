<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          <template v-if="authStore.role === 'siswa'">
            Anda memiliki {{ filteredKeterlambatan.length }} data keterlambatan
          </template>
          <template v-else>
            {{ filteredKeterlambatan.length }} siswa memiliki keterlambatan
          </template>
        </p>
      </div>
      <button
        v-if="authStore.role === 'guru'"
        @click="cekKeterlambatan"
        :disabled="checking"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center disabled:opacity-50"
      >
        <ArrowPathIcon
          class="w-4 h-4"
          :class="checking ? 'animate-spin' : ''"
        />
        {{ checking ? "Mengecek..." : "Cek Keterlambatan Manual" }}
      </button>
    </div>

    <!-- Card Tabel -->
    <div
      class="keterlambatan-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
    >
      <!-- Filter Row (Hanya Guru & Bendahara) -->
      <div
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 px-6 py-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <div class="relative w-full md:w-64">
          <MagnifyingGlassIcon
            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
          />
          <input
            v-model="searchName"
            type="text"
            placeholder="Cari nama siswa..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option value="belum_bayar">Belum Bayar</option>
          <option value="lunas">Lunas</option>
        </select>
      </div>

      <!-- Tabel dengan Horizontal Scroll -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            style="
              grid-template-columns:
                60px minmax(150px, 1.5fr) minmax(100px, 1fr)
                minmax(100px, 1fr) minmax(80px, 1fr) minmax(120px, 1fr)
                minmax(100px, 100px);
            "
          >
            <div class="text-center">No</div>
            <div>Siswa</div>
            <div>Kelas</div>
            <div>Iuran</div>
            <div>Telat (Hari)</div>
            <div>Denda</div>
            <div class="text-right">Status</div>
          </div>

          <div
            v-if="loading"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Memuat data keterlambatan...
          </div>
          <div
            v-else-if="filteredKeterlambatan.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Tidak ada data keterlambatan.
          </div>

          <div v-else>
            <div
              v-for="(telat, index) in pagedKeterlambatan"
              :key="telat.id"
              class="keterlambatan-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              style="
                grid-template-columns:
                  60px minmax(150px, 1.5fr) minmax(100px, 1fr)
                  minmax(100px, 1fr) minmax(80px, 1fr) minmax(120px, 1fr)
                  minmax(100px, 100px);
              "
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div class="flex items-center gap-3 pr-4 min-w-37.5">
                <img
                  v-if="telat.siswa?.user?.foto"
                  :src="telat.siswa.user.foto"
                  class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                  alt="foto"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs shrink-0"
                >
                  {{ telat.siswa?.user?.name?.charAt(0) || "S" }}
                </div>
                <div class="flex flex-col">
                  <span class="font-semibold text-zinc-800">{{
                    telat.siswa?.user?.name || "Nama Kosong"
                  }}</span>
                  <span class="text-xs text-zinc-400"
                    >NIS: {{ telat.siswa?.nis || "-" }}</span
                  >
                </div>
              </div>

              <div class="pr-4 min-w-25">
                <span
                  class="px-2 py-0.5 bg-zinc-100 text-zinc-600 text-xs rounded font-medium"
                >
                  {{ telat.siswa?.kelas?.nama || "-" }}
                </span>
              </div>

              <div class="pr-4 text-zinc-600 text-xs min-w-25">
                {{
                  telat.iuran
                    ? `${getMonthName(telat.iuran.bulan)} ${telat.iuran.tahun}`
                    : "-"
                }}
              </div>

              <div class="pr-4 text-zinc-700 font-medium min-w-20">
                {{ telat.hari_telat }} hari
              </div>

              <div class="pr-4 font-semibold text-red-600 min-w-30">
                Rp {{ formatRupiah(telat.denda) }}
              </div>

              <div class="text-right min-w-25">
                <span
                  class="px-2 py-1 text-xs rounded font-medium"
                  :class="
                    telat.status === 'belum_bayar'
                      ? 'bg-red-50 text-red-600'
                      : 'bg-emerald-50 text-emerald-600'
                  "
                >
                  {{ telat.status === "belum_bayar" ? "Belum Bayar" : "Lunas" }}
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
          Showing {{ rangeStart }}–{{ rangeEnd }} of
          {{ filteredKeterlambatan.length }}
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
import KeterlambatanService from "@/api/keterlambatan";
import anime from "animejs";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  ArrowPathIcon,
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const keterlambatanList = ref([]);
const loading = ref(false);
const checking = ref(false);

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 30;

// --- Anime.js Stagger Animation ---
const triggerAnimations = () => {
  anime({
    targets: ".keterlambatan-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".keterlambatan-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchKeterlambatan = async () => {
  loading.value = true;
  try {
    let response;
    // Kalau Siswa, pake endpoint khusus
    if (authStore.role === "siswa") {
      response = await KeterlambatanService.getMyKeterlambatan();
      keterlambatanList.value = response.data.data.keterlambatan || [];
    } else {
      // Kalau Guru/Bendahara, pake endpoint all
      response = await KeterlambatanService.getAll();
      keterlambatanList.value = response.data.data || [];
    }

    loading.value = false;
    await nextTick();
    triggerAnimations();
  } catch (error) {
    toast.error("Gagal memuat data keterlambatan");
    loading.value = false;
  }
};

const cekKeterlambatan = async () => {
  checking.value = true;
  toast.info("Sedang mengecek keterlambatan di server...");
  try {
    const response = await KeterlambatanService.cekKeterlambatan();
    toast.success("Pengecekan keterlambatan berhasil!");
    fetchKeterlambatan(); // Refresh tabel setelah cek
  } catch (error) {
    const msg = error.response?.data?.message || "Gagal menjalankan pengecekan";
    toast.error(msg);
  } finally {
    checking.value = false;
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
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

const filteredKeterlambatan = computed(() => {
  let list = keterlambatanList.value;
  if (searchName.value) {
    list = list.filter((t) =>
      t.siswa?.user?.name
        ?.toLowerCase()
        .includes(searchName.value.toLowerCase()),
    );
  }
  if (filterStatus.value !== "Semua") {
    list = list.filter((t) => t.status === filterStatus.value);
  }
  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredKeterlambatan.value.length / pageSize)),
);
const pagedKeterlambatan = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredKeterlambatan.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredKeterlambatan.value.length === 0
    ? 0
    : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredKeterlambatan.value.length),
);

onMounted(() => {
  fetchKeterlambatan();
});
</script>
