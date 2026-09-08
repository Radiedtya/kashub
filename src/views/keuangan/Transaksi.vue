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
      <!-- Tombol Input Pembayaran (Hanya Guru & Bendahara) -->
      <button
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        @click="openInputModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <PlusIcon class="w-4 h-4" />
        Input Pembayaran
      </button>
    </div>

    <!-- Chart Card -->
    <div
      class="transaksi-chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6"
    >
      <div class="relative w-40 h-40 shrink-0">
        <canvas ref="statusChart"></canvas>
        <div
          class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
        >
          <span class="text-xl font-bold text-zinc-900">{{
            transaksiList.length
          }}</span>
          <span class="text-zinc-400 text-[10px] uppercase tracking-wide"
            >Total Transaksi</span
          >
        </div>
      </div>
      <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Lunas</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.confirmed }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-amber-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Pending</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.pending }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-red-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Ditolak</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.rejected }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Card Tabel -->
    <div
      class="transaksi-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
    >
      <!-- Filter Row (Diubah biar search-nya flex-1 / ngisi full) -->
      <div
        v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 p-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <!-- Search (Diubah jadi flex-1) -->
        <div class="relative flex-1 w-full">
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
            :style="{ gridTemplateColumns: gridTemplate }"
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
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <!-- No -->
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <!-- Siswa + Foto Profil -->
              <div class="flex items-center gap-3 pr-4 min-w-45">
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
              <div class="pr-4 text-zinc-600 text-xs min-w-30">
                {{
                  trx.iuran
                    ? `${getMonthName(trx.iuran.bulan)} ${trx.iuran.tahun}`
                    : "-"
                }}
              </div>

              <!-- Tgl Bayar -->
              <div class="pr-4 text-zinc-500 text-xs min-w-27.5">
                {{ formatDate(trx.tanggal_bayar) || "-" }}
              </div>

              <!-- Jumlah -->
              <div class="pr-4 font-semibold text-zinc-700 min-w-30">
                Rp {{ formatRupiah(trx.jumlah) }}
              </div>

              <!-- Metode Bayar -->
              <div class="pr-4 text-zinc-500 text-xs min-w-22.5 capitalize">
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
              <div class="pr-4 min-w-25">
                <span
                  class="px-2 py-1 text-xs rounded font-medium capitalize"
                  :class="getStatusClass(trx.status)"
                >
                  {{ trx.status }}
                </span>
              </div>

              <!-- Tgl Dikonfirmasi -->
              <div class="pr-4 text-zinc-500 text-xs text-center min-w-27.5">
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

    <!-- Modal Input Pembayaran (Guru/Bendahara) -->
    <TransitionRoot appear :show="isInputModalOpen" as="template">
      <Dialog as="div" @close="closeInputModal" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" />
        </TransitionChild>
        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all"
              >
                <div
                  class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center"
                >
                  <DialogTitle as="h3" class="text-lg font-bold text-zinc-800">
                    Input Pembayaran Siswa
                  </DialogTitle>
                  <button
                    @click="closeInputModal"
                    class="text-zinc-400 hover:text-zinc-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
                <form
                  @submit.prevent="submitInput"
                  class="p-6 grid grid-cols-1 gap-y-4 max-h-[70vh] overflow-y-auto"
                >
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Pilih Siswa</label
                    >
                    <div class="relative mt-1">
                      <UserIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <select
                        v-model="inputForm.siswa_id"
                        required
                        @change="onSiswaChange"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Siswa</option>
                        <option
                          v-for="s in siswaList"
                          :key="s.id"
                          :value="s.id"
                        >
                          {{ s.user?.name }} ({{ s.kelas?.nama }})
                        </option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Pilih Iuran</label
                    >
                    <div class="relative mt-1">
                      <CurrencyDollarIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <select
                        v-model="inputForm.iuran_id"
                        required
                        @change="onIuranChange"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none disabled:opacity-50 disabled:cursor-not-allowed transition"
                        :disabled="!inputForm.siswa_id"
                      >
                        <option value="" disabled>Pilih Iuran</option>
                        <option
                          v-for="i in filteredIuranList"
                          :key="i.id"
                          :value="i.id"
                        >
                          {{ getMonthName(i.bulan) }} {{ i.tahun }} - Rp
                          {{ formatRupiah(i.nominal) }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-xs text-zinc-600 font-medium"
                        >Tanggal Bayar</label
                      >
                      <div class="relative mt-1">
                        <CalendarDaysIcon
                          class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                        />
                        <input
                          v-model="inputForm.tanggal_bayar"
                          type="date"
                          required
                          class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        />
                      </div>
                    </div>
                    <div>
                      <label class="text-xs text-zinc-600 font-medium"
                        >Jumlah (Rp)</label
                      >
                      <div class="relative mt-1">
                        <BanknotesIcon
                          class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                        />
                        <input
                          v-model="inputForm.jumlah"
                          type="number"
                          min="0"
                          required
                          class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        />
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-xs text-zinc-600 font-medium"
                        >Metode Bayar</label
                      >
                      <div class="relative mt-1">
                        <CreditCardIcon
                          class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                        />
                        <select
                          v-model="inputForm.metode"
                          required
                          class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none capitalize"
                        >
                          <option value="cash">Tunai (Cash)</option>
                          <option value="transfer">Transfer Bank</option>
                          <option value="qris">QRIS / E-Wallet</option>
                        </select>
                      </div>
                    </div>
                    <div>
                      <label class="text-xs text-zinc-600 font-medium"
                        >Status Transaksi</label
                      >
                      <div class="relative mt-1">
                        <CheckCircleIcon
                          class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                        />
                        <select
                          v-model="inputForm.status"
                          required
                          class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none capitalize"
                        >
                          <option value="confirmed">
                            Langsung Lunas (Confirm)
                          </option>
                          <option value="pending">
                            Pending (Tunggu Konfirmasi)
                          </option>
                        </select>
                      </div>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Keterangan (Opsional)</label
                    >
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-3"
                      />
                      <textarea
                        v-model="inputForm.keterangan"
                        rows="2"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"
                        placeholder="Contoh: Bayar tunai di kelas"
                      ></textarea>
                    </div>
                  </div>
                </form>
                <div
                  class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3"
                >
                  <button
                    type="button"
                    @click="closeInputModal"
                    class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition"
                  >
                    Batal
                  </button>
                  <button
                    @click="submitInput"
                    :disabled="inputting"
                    class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2"
                  >
                    <PlusIcon class="w-4 h-4" />
                    {{ inputting ? "Menyimpan..." : "Simpan Transaksi" }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, reactive } from "vue";
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import TransaksiService from "@/api/transaksi";
import SiswaService from "@/api/siswa";
import IuranService from "@/api/iuran";
import anime from "animejs";
import { Chart, registerables } from "chart.js";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  CheckIcon,
  XMarkIcon,
  PhotoIcon,
  PlusIcon,
  UserIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  BanknotesIcon,
  CreditCardIcon,
  ChatBubbleLeftIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const authStore = useAuthStore();
const transaksiList = ref([]);
const loading = ref(false);

// State buat Modal Input
const siswaList = ref([]);
const allIuranList = ref([]);
const isInputModalOpen = ref(false);
const inputting = ref(false);

const inputForm = reactive({
  siswa_id: "",
  iuran_id: "",
  tanggal_bayar: dayjs().format("YYYY-MM-DD"),
  jumlah: 0,
  metode: "cash",
  status: "confirmed",
  keterangan: "",
});

// Filter iuran berdasarkan kelas siswa yang dipilih, DAN yang belum dibayar/pending
const filteredIuranList = computed(() => {
  if (!inputForm.siswa_id) return [];
  const siswa = siswaList.value.find((s) => s.id === inputForm.siswa_id);
  if (!siswa) return [];

  const paidIuranIds = transaksiList.value
    .filter(
      (t) =>
        t.siswa_id === inputForm.siswa_id &&
        (t.status === "confirmed" || t.status === "pending"),
    )
    .map((t) => t.iuran_id);

  return allIuranList.value.filter(
    (i) => i.kelas_id === siswa.kelas_id && !paidIuranIds.includes(i.id),
  );
});

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 25;

// Chart State & Computed
const statusChart = ref(null);
let doughnutChartInstance = null;

const statusData = computed(() => {
  let confirmed = 0,
    pending = 0,
    rejected = 0;
  transaksiList.value.forEach((t) => {
    if (t.status === "confirmed") confirmed++;
    else if (t.status === "pending") pending++;
    else if (t.status === "rejected") rejected++;
  });
  return { confirmed, pending, rejected };
});

// Dynamic Grid Template
const gridTemplate = computed(() => {
  return "60px minmax(180px, 1.5fr) minmax(120px, 1fr) minmax(110px, 1fr) minmax(120px, 1fr) minmax(90px, 1fr) 80px minmax(150px, 1.5fr) minmax(100px, 1fr) minmax(110px, 1fr) minmax(150px, 1fr)";
});

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
    targets: ".transaksi-chart-card",
    translateY: [20, 0],
    opacity: [0, 1],
    delay: 100,
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
    if (authStore.role === "siswa") {
      response = await TransaksiService.getMyTransaksi();
      transaksiList.value = response.data.data.transaksi || [];
    } else {
      response = await TransaksiService.getAll();
      transaksiList.value = response.data.data || [];
    }

    loading.value = false;
    await nextTick();
    triggerAnimations();
    renderChart();
  } catch (error) {
    toast.error("Gagal memuat data transaksi");
    loading.value = false;
  }
};

const fetchSiswaForInput = async () => {
  try {
    const res = await SiswaService.getAll();
    siswaList.value = res.data.data || [];
  } catch (error) {
    console.error("Gagal fetch siswa", error);
  }
};

const fetchIuranForInput = async () => {
  try {
    const res = await IuranService.getAll();
    allIuranList.value = res.data.data || [];
  } catch (error) {
    console.error("Gagal fetch iuran", error);
  }
};

const onSiswaChange = () => {
  inputForm.iuran_id = "";
  inputForm.jumlah = 0;
};

const onIuranChange = () => {
  const iuran = allIuranList.value.find((i) => i.id === inputForm.iuran_id);
  if (iuran) {
    inputForm.jumlah = iuran.nominal;
  }
};

const openInputModal = () => {
  Object.assign(inputForm, {
    siswa_id: "",
    iuran_id: "",
    tanggal_bayar: dayjs().format("YYYY-MM-DD"),
    jumlah: 0,
    metode: "cash",
    status: "confirmed",
    keterangan: "",
  });
  isInputModalOpen.value = true;
};

const closeInputModal = () => {
  isInputModalOpen.value = false;
};

const submitInput = async () => {
  inputting.value = true;
  try {
    await TransaksiService.create(inputForm);
    toast.success("Pembayaran berhasil diinput!");
    closeInputModal();
    fetchTransaksi();
  } catch (error) {
    const msg = error.response?.data?.message || "Gagal input pembayaran";
    toast.error(msg);
  } finally {
    inputting.value = false;
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const onImgError = (e) => {
  e.target.style.display = "none";
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

  // FIX: Kalau yang login Guru, filter cuma transaksi siswa di kelasnya
  if (authStore.role === "guru" && authStore.user?.kelas_id) {
    list = list.filter((t) => t.siswa?.kelas_id === authStore.user.kelas_id);
  }

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

// --- Render Chart ---
const renderChart = () => {
  if (doughnutChartInstance) doughnutChartInstance.destroy();

  if (statusChart.value) {
    doughnutChartInstance = new Chart(statusChart.value, {
      type: "doughnut",
      data: {
        labels: ["Lunas", "Pending", "Ditolak"],
        datasets: [
          {
            data: [
              statusData.value.confirmed,
              statusData.value.pending,
              statusData.value.rejected,
            ],
            backgroundColor: ["#10b981", "#f59e0b", "#ef4444"],
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
  fetchTransaksi();
  if (authStore.role === "guru" || authStore.role === "bendahara") {
    fetchSiswaForInput();
    fetchIuranForInput();
  }
});
</script>
