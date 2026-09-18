<template>
  <div class="space-y-6">
    <!-- Tombol Back -->
    <div>
      <router-link
        to="/siswa"
        class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-800 transition"
      >
        <ChevronLeftIcon class="w-4 h-4" />
        Kembali ke Data Siswa
      </router-link>
    </div>

    <!-- Skeleton Loading -->
    <div v-if="loading" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Skeleton Kolom Kiri -->
      <div class="space-y-6">
        <div class="bg-white border border-zinc-200 rounded-xl p-6 flex flex-col items-center text-center">
          <div class="w-24 h-24 rounded-full bg-zinc-200 animate-pulse mb-4 shrink-0"></div>
          <div class="h-5 w-32 bg-zinc-200 animate-pulse rounded mb-2"></div>
          <div class="h-4 w-40 bg-zinc-200 animate-pulse rounded mb-4"></div>
          <div class="w-full bg-zinc-50 rounded-lg p-4 space-y-3 border border-zinc-100">
            <div class="flex justify-between items-center" v-for="i in 6" :key="i">
              <div class="h-3 w-12 bg-zinc-200 animate-pulse rounded"></div>
              <div class="h-3 w-24 bg-zinc-200 animate-pulse rounded"></div>
            </div>
          </div>
          <div class="mt-4 pt-4 border-t border-zinc-100 w-full space-y-2">
            <div class="flex justify-between items-center">
              <div class="h-3 w-20 bg-zinc-200 animate-pulse rounded"></div>
              <div class="h-3 w-12 bg-zinc-200 animate-pulse rounded"></div>
            </div>
            <div class="h-2.5 w-full bg-zinc-200 animate-pulse rounded-full"></div>
          </div>
        </div>
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <div class="h-5 w-32 bg-zinc-200 animate-pulse rounded mb-4"></div>
          <div class="bg-zinc-50 rounded-lg p-4 border border-zinc-100 space-y-3">
            <div class="flex justify-between items-center" v-for="i in 2" :key="i">
              <div class="h-3 w-12 bg-zinc-200 animate-pulse rounded"></div>
              <div class="h-3 w-24 bg-zinc-200 animate-pulse rounded"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Skeleton Kolom Kanan -->
      <div class="lg:col-span-2 space-y-6">
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <div class="h-5 w-48 bg-zinc-200 animate-pulse rounded mb-4"></div>
          <div class="space-y-3">
            <div class="h-8 w-full bg-zinc-200 animate-pulse rounded" v-for="i in 4" :key="i"></div>
          </div>
        </div>
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <div class="h-5 w-48 bg-zinc-200 animate-pulse rounded mb-4"></div>
          <div class="space-y-3">
            <div class="h-8 w-full bg-zinc-200 animate-pulse rounded" v-for="i in 3" :key="i"></div>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="siswaData" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Kolom Kiri: Info Singkat (Slide from Left) -->
      <div class="space-y-6 detail-left-col">
        <div
          class="bg-white border border-zinc-200 border-t-4 border-t-blue-500 rounded-xl p-6 flex flex-col items-center text-center"
        >
          <div
            class="w-24 h-24 rounded-full p-0.75 bg-linear-to-br from-blue-400 to-indigo-600 mb-4 shrink-0 shadow-sm"
          >
            <div class="w-full h-full rounded-full bg-white p-0.5">
              <div
                class="w-full h-full rounded-full bg-zinc-100 flex items-center justify-center text-zinc-400 font-bold text-4xl overflow-hidden"
              >
                <img
                  v-if="siswaData.user?.foto"
                  :src="siswaData.user.foto"
                  class="w-full h-full object-cover"
                  alt="foto"
                />
                <UserIcon v-else class="w-12 h-12" />
              </div>
            </div>
          </div>
          <h2 class="text-lg font-bold text-zinc-900">
            {{ siswaData.user?.name }}
          </h2>
          <p class="text-sm text-zinc-500 mb-3">{{ siswaData.user?.email }}</p>

          <span class="mb-4 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 capitalize">
            {{ authStore.role === 'guru' ? 'Siswa' : '' }}
          </span>

          <div
            class="w-full bg-zinc-50/70 rounded-lg p-4 space-y-2.5 text-left border border-zinc-100"
          >
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">NIS</span>
              <span class="font-medium text-zinc-700">{{
                siswaData.nis || "-"
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">NISN</span>
              <span class="font-medium text-zinc-700">{{
                siswaData.nisn || "-"
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">Kelas</span>
              <span
                class="font-medium text-zinc-700 px-2 py-0.5 bg-white border border-zinc-200 rounded text-xs"
                >{{ siswaData.kelas?.nama || "-" }}</span
              >
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">No HP</span>
              <span class="font-medium text-zinc-700">{{
                siswaData.user?.no_hp || "-"
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">Tgl Lahir</span>
              <span class="font-medium text-zinc-700">{{
                formatDate(siswaData.tanggal_lahir)
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">Alamat</span>
              <span
                class="font-medium text-zinc-700 text-right max-w-37.5 truncate"
                >{{ siswaData.alamat || "-" }}</span
              >
            </div>
          </div>

          <!-- Progress Bar Tagihan Siswa -->
          <div class="mt-4 pt-4 border-t border-zinc-100 w-full">
            <div class="flex justify-between items-center mb-1">
              <span class="text-xs font-medium text-zinc-500"
                >Progress Tagihan Iuran</span
              >
              <span class="text-xs font-bold text-zinc-700"
                >{{ paidIuranCount }} / {{ totalIuranCount }} Lunas</span
              >
            </div>
            <div class="w-full h-2.5 bg-zinc-100 rounded-full overflow-hidden">
              <div
                class="h-full rounded-full transition-all duration-1000 ease-out bg-linear-to-r"
                :class="
                  paymentProgress === 100
                    ? 'from-emerald-400 to-emerald-600'
                    : 'from-blue-400 to-blue-600'
                "
                :style="{ width: paymentProgress + '%' }"
              ></div>
            </div>
            <p class="text-[10px] text-zinc-400 mt-1 text-right">
              {{ paymentProgress }}% Selesai
            </p>
          </div>
        </div>

        <!-- Card Orang Tua (Hanya Guru) -->
        <div
          v-if="authStore.role === 'guru'"
          class="bg-white border border-zinc-200 border-t-4 border-t-zinc-500 rounded-xl p-6"
        >
          <h3 class="text-sm font-semibold text-zinc-800 mb-3 flex items-center gap-2">
            <div class="w-7 h-7 rounded-lg bg-linear-to-br from-zinc-600 to-zinc-700 flex items-center justify-center shadow-sm shadow-zinc-500/20">
              <UsersIcon class="w-4 h-4 text-white" />
            </div>
            Data Orang Tua
          </h3>
          <div class="space-y-2.5 bg-zinc-50/70 rounded-lg p-4 border border-zinc-100">
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">Nama</span>
              <span class="font-medium text-zinc-700">{{
                siswaData.nama_ortu || "-"
              }}</span>
            </div>
            <div class="flex justify-between text-sm">
              <span class="text-zinc-400">No HP</span>
              <span class="font-medium text-zinc-700">{{
                siswaData.no_hp_ortu || "-"
              }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Riwayat (Slide from Right) -->
      <div class="lg:col-span-2 space-y-6 detail-right-col">
        <!-- Riwayat Transaksi -->
        <div class="bg-white border border-zinc-200 border-t-4 border-t-emerald-500 rounded-xl p-6">
          <h3 class="text-sm font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            <div class="w-9 h-9 rounded-lg bg-linear-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-sm shadow-emerald-500/30">
              <CurrencyDollarIcon class="w-5 h-5 text-white" />
            </div>
            Riwayat Pembayaran Iuran
          </h3>

          <div
            v-if="transaksiList.length === 0"
            class="text-center py-6 text-zinc-400 text-sm"
          >
            Belum ada riwayat pembayaran.
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-zinc-50 text-zinc-500 text-xs border-b border-zinc-100"
                >
                  <th class="px-4 py-3 font-medium">Periode Iuran</th>
                  <th class="px-4 py-3 font-medium">Tgl Bayar</th>
                  <th class="px-4 py-3 font-medium">Jumlah</th>
                  <th class="px-4 py-3 font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="text-sm text-zinc-700">
                <tr
                  v-for="trx in transaksiList"
                  :key="trx.id"
                  class="border-b border-zinc-50 hover:bg-zinc-50/50 transition-colors"
                >
                  <td class="px-4 py-3 capitalize">
                    {{ getMonthName(trx.iuran?.bulan) }} {{ trx.iuran?.tahun }}
                  </td>
                  <td class="px-4 py-3 text-zinc-500 text-xs">
                    {{ formatDate(trx.tanggal_bayar) }}
                  </td>
                  <td class="px-4 py-3 font-medium">
                    Rp {{ formatRupiah(trx.jumlah) }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="px-2 py-1 text-xs rounded font-medium capitalize"
                      :class="getStatusClass(trx.status)"
                    >
                      {{ trx.status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Riwayat Keterlambatan -->
        <div class="bg-white border border-zinc-200 border-t-4 border-t-red-500 rounded-xl p-6">
          <h3 class="text-sm font-semibold text-zinc-800 mb-4 flex items-center gap-2">
            <div class="w-9 h-9 rounded-lg bg-linear-to-br from-red-500 to-red-600 flex items-center justify-center shadow-sm shadow-red-500/30">
              <ClockIcon class="w-5 h-5 text-white" />
            </div>
            Riwayat Keterlambatan & Denda
          </h3>

          <div
            v-if="keterlambatanList.length === 0"
            class="text-center py-6 text-zinc-400 text-sm"
          >
            Tidak ada riwayat keterlambatan untuk
            <span class="font-bold">
              {{ siswaData.user?.name }}
            </span>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr
                  class="bg-zinc-50 text-zinc-500 text-xs border-b border-zinc-100"
                >
                  <th class="px-4 py-3 font-medium">Periode Iuran</th>
                  <th class="px-4 py-3 font-medium">Telat</th>
                  <th class="px-4 py-3 font-medium">Denda</th>
                  <th class="px-4 py-3 font-medium">Status</th>
                </tr>
              </thead>
              <tbody class="text-sm text-zinc-700">
                <tr
                  v-for="telat in keterlambatanList"
                  :key="telat.id"
                  class="border-b border-zinc-50 hover:bg-zinc-50/50 transition-colors"
                >
                  <td class="px-4 py-3 capitalize">
                    {{ getMonthName(telat.iuran?.bulan) }}
                    {{ telat.iuran?.tahun }}
                  </td>
                  <td class="px-4 py-3 text-zinc-500">
                    {{ telat.hari_telat }} hari
                  </td>
                  <td class="px-4 py-3 font-medium text-red-600">
                    Rp {{ formatRupiah(telat.denda) }}
                  </td>
                  <td class="px-4 py-3">
                    <span
                      class="px-2 py-1 text-xs rounded font-medium capitalize"
                      :class="
                        telat.status === 'belum_bayar'
                          ? 'bg-red-50 text-red-600'
                          : 'bg-emerald-50 text-emerald-600'
                      "
                    >
                      {{
                        telat.status === "belum_bayar" ? "Belum Bayar" : "Lunas"
                      }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick } from "vue";
import { useRoute } from "vue-router";
import { toast } from "vue3-toastify";
import SiswaService from "@/api/siswa";
import TransaksiService from "@/api/transaksi";
import KeterlambatanService from "@/api/keterlambatan";
import IuranService from "@/api/iuran";
import anime from "animejs";
import {
  ChevronLeftIcon, UserIcon, UsersIcon, CurrencyDollarIcon, ClockIcon
} from "@heroicons/vue/24/outline";
import { useAuthStore } from "@/stores/auth";
import dayjs from "dayjs";

const authStore = useAuthStore();
const route = useRoute();
const siswaId = route.params.id;

const siswaData = ref(null);
const transaksiList = ref([]);
const keterlambatanList = ref([]);
const allIuranList = ref([]);
const loading = ref(true);

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const formatDate = (date) => (date ? dayjs(date).format("DD MMM YYYY") : "-");
const getMonthName = (monthNum) => {
  if (!monthNum) return "-";
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

const getStatusClass = (status) => {
  if (status === "confirmed") return "bg-emerald-50 text-emerald-600";
  if (status === "pending") return "bg-yellow-50 text-yellow-600";
  if (status === "rejected") return "bg-red-50 text-red-600";
  return "bg-zinc-50 text-zinc-600";
};

// Computed buat Progress Bar
const totalIuranCount = computed(() => allIuranList.value.length);

const paidIuranCount = computed(() => {
  const paidIuranIds = transaksiList.value
    .filter((t) => t.status === "confirmed")
    .map((t) => t.iuran_id);
  return [...new Set(paidIuranIds)].length;
});

const paymentProgress = computed(() => {
  if (totalIuranCount.value === 0) return 0;
  return Math.min(
    100,
    Math.round((paidIuranCount.value / totalIuranCount.value) * 100),
  );
});

// Animasi Masuk (Slide kiri & kanan)
const triggerAnimations = () => {
  anime({
    targets: '.detail-left-col',
    translateX: [-50, 0],
    opacity: [0, 1],
    duration: 800,
    easing: 'easeOutQuart'
  });

  anime({
    targets: '.detail-right-col',
    translateX: [50, 0],
    opacity: [0, 1],
    delay: anime.stagger(150, { start: 200 }),
    duration: 800,
    easing: 'easeOutQuart'
  });
};

const fetchDetail = async () => {
  loading.value = true;
  try {
    const resSiswa = await SiswaService.getById(siswaId);
    siswaData.value = resSiswa.data.data;

    const resTrx = await TransaksiService.getBySiswa(siswaId);
    transaksiList.value = resTrx.data.data.transaksi || [];

    const resTelat = await KeterlambatanService.getBySiswa(siswaId);
    keterlambatanList.value = resTelat.data.data.keterlambatan || [];

    if (siswaData.value.kelas_id) {
      const resIuran = await IuranService.getByKelas(siswaData.value.kelas_id);
      allIuranList.value = resIuran.data.data.iuran || [];
    }
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat detail siswa");
  } finally {
    loading.value = false;
    await nextTick();
    triggerAnimations();
  }
};

onMounted(() => {
  fetchDetail();
});
</script>