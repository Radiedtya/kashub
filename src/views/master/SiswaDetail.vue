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

    <div v-if="loading" class="flex justify-center items-center h-96">
      <p class="text-zinc-400">Memuat data siswa...</p>
    </div>

    <div v-else-if="siswaData" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Kolom Kiri: Info Singkat -->
      <div class="space-y-6">
        <div
          class="bg-white border border-zinc-200 rounded-xl p-6 flex flex-col items-center text-center"
        >
          <div
            class="w-24 h-24 rounded-full bg-zinc-900 flex items-center justify-center text-white font-bold text-4xl shrink-0 border-4 border-zinc-100 mb-4 overflow-hidden"
          >
            <img
              v-if="siswaData.user?.foto"
              :src="siswaData.user.foto"
              class="w-full h-full object-cover"
              alt="foto"
            />
            <UserIcon v-else class="w-12 h-12" />
          </div>
          <h2 class="text-lg font-bold text-zinc-900">
            {{ siswaData.user?.name }}
          </h2>
          <p class="text-sm text-zinc-500">{{ siswaData.user?.email }}</p>

          <div
            class="w-full mt-4 pt-4 border-t border-zinc-100 space-y-2 text-left"
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
                class="font-medium text-zinc-700 px-2 py-0.5 bg-zinc-100 rounded"
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
              <span class="text-zinc-400">Tanggal Lahir</span>
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
        </div>

        <!-- Card Orang Tua -->
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <h3 class="text-sm font-semibold text-zinc-800 mb-3">
            Data Orang Tua
          </h3>
          <div class="space-y-2">
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

      <!-- Kolom Kanan: Riwayat -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Riwayat Transaksi -->
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <h3 class="text-sm font-semibold text-zinc-800 mb-4">
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
                  class="border-b border-zinc-50"
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
        <div class="bg-white border border-zinc-200 rounded-xl p-6">
          <h3 class="text-sm font-semibold text-zinc-800 mb-4">
            Riwayat Keterlambatan & Denda
          </h3>

          <div
            v-if="keterlambatanList.length === 0"
            class="text-center py-6 text-zinc-400 text-sm"
          >
            Tidak ada riwayat keterlambatan. 🎉
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
                  class="border-b border-zinc-50"
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
import { ref, onMounted } from "vue";
import { useRoute } from "vue-router";
import { toast } from "vue3-toastify";
import SiswaService from "@/api/siswa";
import TransaksiService from "@/api/transaksi";
import KeterlambatanService from "@/api/keterlambatan";
import { ChevronLeftIcon, UserIcon } from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const route = useRoute();
const siswaId = route.params.id;

const siswaData = ref(null);
const transaksiList = ref([]);
const keterlambatanList = ref([]);
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

const fetchDetail = async () => {
  loading.value = true;
  try {
    // Fetch Siswa Detail
    const resSiswa = await SiswaService.getById(siswaId);
    siswaData.value = resSiswa.data.data;

    // Fetch Transaksi by Siswa
    const resTrx = await TransaksiService.getBySiswa(siswaId);
    transaksiList.value = resTrx.data.data.transaksi || [];

    // Fetch Keterlambatan by Siswa
    const resTelat = await KeterlambatanService.getBySiswa(siswaId);
    // Karena backend getbySiswa return object { siswa, total_keterlambatan, keterlambatan: [...] }
    keterlambatanList.value = resTelat.data.data.keterlambatan || [];
  } catch (error) {
    console.error(error);
    toast.error("Gagal memuat detail siswa");
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchDetail();
});
</script>
