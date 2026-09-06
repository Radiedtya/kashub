<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          {{ filteredTransaksi.length }} transaksi ditemukan
        </p>
      </div>
    </div>

    <!-- Card Tabel -->
    <div
      class="transaksi-card bg-white border border-zinc-200 rounded-xl overflow-hidden px-8 py-3"
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
          <option value="pending">Pending</option>
          <option value="confirmed">Lunas (Confirmed)</option>
          <option value="rejected">Ditolak (Rejected)</option>
        </select>
      </div>

      <!-- Tabel dengan Horizontal Scroll -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <!-- Header Row -->
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            style="
              grid-template-columns:
                60px minmax(150px, 1.5fr) minmax(100px, 1fr)
                minmax(120px, 1fr) minmax(100px, 1fr) minmax(80px, 1fr)
                80px minmax(150px, 1.5fr) minmax(80px, 1fr) minmax(120px, 1fr)
                150px;
            "
          >
            <div class="text-center">No</div>
            <div class="text-center">Siswa</div>
            <div>Iuran</div>
            <div>Tgl Bayar</div>
            <div>Jumlah</div>
            <div>Metode</div>
            <div class="text-center">Bukti</div>
            <div>Keterangan</div>
            <div>Status</div>
            <div>Dikonfirmasi</div>
            <div class="text-center">Aksi</div>
          </div>

          <!-- States & Rows -->
          <div
            v-if="loading"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Memuat data transaksi...
          </div>
          <div
            v-else-if="filteredTransaksi.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(trx, index) in pagedTransaksi"
              :key="trx.id"
              class="transaksi-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              style="
                grid-template-columns:
                  60px minmax(150px, 1.5fr) minmax(100px, 1fr)
                  minmax(120px, 1fr) minmax(100px, 1fr) minmax(80px, 1fr)
                  80px minmax(150px, 1.5fr) minmax(80px, 1fr) minmax(120px, 1fr)
                  150px;
              "
            >
              <!-- No -->
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <!-- Siswa + Foto Profil -->
              <div class="flex items-center gap-3 pr-4 min-w-37.5">
                <img
                  v-if="trx.siswa?.user?.foto"
                  :src="trx.siswa.user.foto"
                  class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                  alt="foto"
                />
                <div
                  v-else
                  class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs shrink-0"
                >
                  {{ trx.siswa?.user?.name?.charAt(0) || "S" }}
                </div>
                <div class="flex flex-col">
                  <span class="font-semibold text-zinc-800">{{
                    trx.siswa?.user?.name || "Siswa"
                  }}</span>
                  <span class="text-xs text-zinc-400">{{
                    trx.siswa?.nis || "-"
                  }}</span>
                </div>
              </div>

              <!-- Iuran -->
              <div class="pr-4 text-zinc-600 text-xs min-w-25">
                {{
                  trx.iuran
                    ? `${getMonthName(trx.iuran.bulan)} ${trx.iuran.tahun}`
                    : "-"
                }}
              </div>

              <!-- Tgl Bayar -->
              <div class="pr-4 text-zinc-500 text-xs min-w-30">
                {{ formatDate(trx.tanggal_bayar) || "-" }}
              </div>

              <!-- Jumlah -->
              <div class="pr-4 font-semibold text-zinc-700 min-w-25">
                Rp {{ formatRupiah(trx.jumlah) }}
              </div>

              <!-- Metode Bayar -->
              <div class="pr-4 text-zinc-500 text-xs min-w-20 capitalize">
                {{ trx.metode || "-" }}
              </div>

              <!-- Bukti Bayar -->
              <div class="pr-4 text-center min-w-20 flex justify-center">
                <a
                  v-if="trx.bukti_bayar"
                  :href="trx.bukti_bayar"
                  target="_blank"
                  class="inline-block"
                >
                  <img
                    :src="trx.bukti_bayar"
                    @error="onImgError"
                    class="w-10 h-10 rounded-md object-cover border border-zinc-200 hover:opacity-80 transition mx-auto"
                    alt="bukti bayar"
                  />
                </a>
                <PhotoIcon v-else class="w-5 h-5 text-zinc-300 mx-auto" />
              </div>

              <!-- Keterangan -->
              <div
                class="pr-4 text-zinc-500 text-xs min-w-37.5 truncate"
                :title="trx.keterangan"
              >
                {{ trx.keterangan || "Tanpa Keterangan" }}
              </div>

              <!-- Status -->
              <div class="pr-4 min-w-20">
                <span
                  class="px-2 py-1 text-xs rounded font-medium capitalize"
                  :class="getStatusClass(trx.status)"
                >
                  {{ trx.status }}
                </span>
              </div>

              <!-- Tgl Dikonfirmasi -->
              <div class="pr-4 text-zinc-500 text-xs text-center min-w-30">
                {{ formatDate(trx.confirmed_at) || "-" }}
              </div>

              <!-- Aksi -->
              <div class="flex items-center justify-end gap-1 min-w-37.5">
                <template
                  v-if="
                    trx.status === 'pending' &&
                    (authStore.role === 'guru' ||
                      authStore.role === 'bendahara')
                  "
                >
                  <button
                    @click="confirmAction(trx, 'confirmed')"
                    class="px-2.5 py-1 rounded-md border border-emerald-600 text-emerald-600 bg-white hover:bg-emerald-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <CheckIcon class="w-3.5 h-3.5" /> Approve
                  </button>
                  <button
                    @click="confirmAction(trx, 'rejected')"
                    class="px-2.5 py-1 rounded-md border border-red-600 text-red-600 bg-white hover:bg-red-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <XMarkIcon class="w-3.5 h-3.5" /> Reject
                  </button>
                </template>

                <!-- Kalau bukan pending, tampilin pesan ini -->
                <span v-else class="text-xs text-zinc-400 italic"
                  >Tidak ada aksi tersedia</span
                >
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
          {{ filteredTransaksi.length }}
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
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import TransaksiService from "@/api/transaksi";
import anime from "animejs"; // <-- Import animejs
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  CheckIcon,
  XMarkIcon,
  PhotoIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const authStore = useAuthStore();
const transaksiList = ref([]);
const loading = ref(false);

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 25; // <-- Diganti jadi 10 biar konsisten

// --- Anime.js Stagger Animation ---
const triggerAnimations = () => {
  anime({
    targets: ".transaksi-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".transaksi-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchTransaksi = async () => {
  loading.value = true;
  try {
    let response;
    // Kalau Siswa, pake endpoint khusus
    if (authStore.role === "siswa") {
      response = await TransaksiService.getMyTransaksi();
      transaksiList.value = response.data.data.transaksi || [];
    } else {
      // Kalau Guru/Bendahara, pake endpoint all
      response = await TransaksiService.getAll();
      transaksiList.value = response.data.data || [];
    }

    loading.value = false;
    await nextTick();
    triggerAnimations();
  } catch (error) {
    toast.error("Gagal memuat data transaksi");
    loading.value = false;
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const onImgError = (e) => {
  e.target.style.display = "none"; // Sembunyiin kalau link gambar rusak
};
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

const getStatusClass = (status) => {
  if (status === "confirmed") return "bg-emerald-50 text-emerald-600";
  if (status === "pending") return "bg-yellow-50 text-yellow-600";
  if (status === "rejected") return "bg-red-50 text-red-600";
  return "bg-zinc-50 text-zinc-600";
};

const filteredTransaksi = computed(() => {
  let list = transaksiList.value;
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
  Math.max(1, Math.ceil(filteredTransaksi.value.length / pageSize)),
);
const pagedTransaksi = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredTransaksi.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredTransaksi.value.length === 0
    ? 0
    : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredTransaksi.value.length),
);

const confirmAction = (trx, newStatus) => {
  const statusText =
    newStatus === "confirmed" ? "Approve (Lunas)" : "Reject (Tolak)";
  Swal.fire({
    title: `${statusText} Transaksi?`,
    text: `Kamu yakin mau ${statusText} pembayaran dari ${trx.siswa?.user?.name}?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: newStatus === "confirmed" ? "#10b981" : "#ef4444",
    cancelButtonColor: "#64748b",
    confirmButtonText: `Ya, ${statusText}`,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await TransaksiService.konfirmasi(trx.id, { status: newStatus });
        toast.success(`Transaksi berhasil di-${statusText}`);
        fetchTransaksi();
      } catch (error) {
        const msg =
          error.response?.data?.message || `Gagal ${statusText} transaksi`;
        toast.error(msg);
      }
    }
  });
};

onMounted(() => {
  fetchTransaksi();
});
</script>
