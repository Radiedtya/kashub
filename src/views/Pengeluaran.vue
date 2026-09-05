<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          {{ filteredPengeluaran.length }} pengajuan ditemukan
        </p>
      </div>
      <!-- Tombol Ajukan Pengeluaran (Hanya Bendahara) -->
      <button
        v-if="authStore.role === 'bendahara'"
        @click="openCreateModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <PlusIcon class="w-4 h-4" />
        Ajukan Pengeluaran
      </button>
    </div>

    <!-- Card Tabel -->
    <div
      class="pengeluaran-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
    >
      <!-- Filter Row -->
      <div
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 px-6 py-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <div class="relative w-full md:w-64">
          <MagnifyingGlassIcon
            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
          />
          <input
            v-model="searchName"
            type="text"
            placeholder="Cari judul/kategori..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Disetujui (Approved)</option>
          <option value="rejected">Ditolak (Rejected)</option>
        </select>
      </div>

      <!-- Tabel dengan Horizontal Scroll -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            style="
              grid-template-columns: 60px minmax(150px, 1.5fr) minmax(
                  120px,
                  1fr
                ) minmax(100px, 1fr) minmax(80px, 1fr) 80px minmax(
                  120px,
                  1fr
                ) minmax(100px, 1fr) 150px;
            "
          >
            <div class="text-center">No</div>
            <div>Judul & Deskripsi</div>
            <div>Diajukan Oleh</div>
            <div>Tanggal</div>
            <div>Jumlah</div>
            <div class="text-center">Bukti</div>
            <div>Status</div>
            <div>Disetujui Oleh</div>
            <div class="text-right">Aksi</div>
          </div>

          <div
            v-if="loading"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Memuat data pengeluaran...
          </div>
          <div
            v-else-if="filteredPengeluaran.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(p, index) in pagedPengeluaran"
              :key="p.id"
              class="pengeluaran-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              style="
                grid-template-columns: 60px minmax(150px, 1.5fr) minmax(
                    120px,
                    1fr
                  ) minmax(100px, 1fr) minmax(80px, 1fr) 80px minmax(
                    120px,
                    1fr
                  ) minmax(100px, 1fr) 150px;
              "
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div class="pr-4 min-w-37.5">
                <span class="font-semibold text-zinc-800 capitalize">{{
                  p.judul || "Tanpa Judul"
                }}</span>
                <span class="text-xs text-zinc-400 block truncate"
                  >{{ p.kategori || "-" }} - {{ p.deskripsi || "-" }}</span
                >
              </div>

              <div
                class="pr-4 text-zinc-600 text-xs min-w-30 flex flex-col gap-1"
              >
                <span class="font-medium text-zinc-700">{{
                  p.created_by?.name || p.createdBy?.name || "-"
                }}</span>
                <span
                  v-if="p.created_by?.role?.name || p.createdBy?.role?.name"
                  class="inline-flex w-fit items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 capitalize"
                >
                  {{ p.created_by?.role?.name || p.createdBy?.role?.name }}
                </span>
              </div>

              <div class="pr-4 text-zinc-500 text-xs min-w-25">
                {{ formatDate(p.tanggal) }}
              </div>

              <div class="pr-4 font-semibold text-zinc-700 min-w-20">
                Rp {{ formatRupiah(p.jumlah) }}
              </div>

              <!-- Preview Thumbnail di Tabel -->
              <div class="pr-4 text-center min-w-20">
                <a
                  v-if="p.bukti_foto"
                  :href="p.bukti_foto"
                  target="_blank"
                  class="inline-block"
                >
                  <img
                    :src="p.bukti_foto"
                    class="w-10 h-10 rounded-md object-cover border border-zinc-200 mx-auto hover:opacity-80 transition"
                    alt="bukti"
                  />
                </a>
                <PhotoIcon v-else class="w-5 h-5 text-zinc-300 mx-auto" />
              </div>

              <div class="pr-4 min-w-30">
                <span
                  class="px-2 py-1 text-xs rounded font-medium capitalize"
                  :class="getStatusClass(p.status)"
                >
                  {{ p.status }}
                </span>
              </div>

              <div class="pr-4 text-zinc-600 text-xs min-w-25">
                <span v-if="p.approved_by?.name">
                  {{ p.approved_by.name }}
                </span>
                <span v-else-if="p.approvedBy?.name">
                  {{ p.approvedBy.name }}
                </span>
                <span v-else-if="p.approved_by" class="text-zinc-500">
                  ID: {{ p.approved_by }}
                </span>
                <span v-else class="text-zinc-300">-</span>
              </div>

              <div class="flex items-center justify-end gap-1 min-w-37.5">
                <template
                  v-if="p.status === 'pending' && authStore.role === 'guru'"
                >
                  <button
                    @click="confirmAction(p, 'approved')"
                    class="px-2.5 py-1 rounded-md border border-emerald-600 text-emerald-600 bg-white hover:bg-emerald-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <CheckIcon class="w-3.5 h-3.5" /> Approve
                  </button>
                  <button
                    @click="confirmAction(p, 'rejected')"
                    class="px-2.5 py-1 rounded-md border border-red-600 text-red-600 bg-white hover:bg-red-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <XMarkIcon class="w-3.5 h-3.5" /> Reject
                  </button>
                </template>

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
          {{ filteredPengeluaran.length }}
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
            v-for="pg in totalPages"
            :key="pg"
            @click="currentPage = pg"
            class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium transition"
            :class="
              currentPage === pg
                ? 'bg-zinc-900 text-white'
                : 'text-zinc-500 hover:bg-zinc-100'
            "
          >
            {{ pg }}
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

    <!-- Modal Form Ajukan Pengeluaran (Hanya Bendahara) -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
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
                    Ajukan Pengeluaran Baru
                  </DialogTitle>
                  <button
                    @click="closeModal"
                    class="text-zinc-400 hover:text-zinc-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <form
                  @submit.prevent="submitForm"
                  class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 max-h-[70vh] overflow-y-auto"
                >
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Kelas</label
                    >
                    <div class="relative mt-1">
                      <AcademicCapIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <select
                        v-model="form.kelas_id"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Kelas</option>
                        <option
                          v-for="k in kelasList"
                          :key="k.id"
                          :value="k.id"
                        >
                          {{ k.nama }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Judul Pengeluaran</label
                    >
                    <div class="relative mt-1">
                      <CommandLineIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.judul"
                        type="text"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: Beli Alat Tulis"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Jumlah (Rp)</label
                    >
                    <div class="relative mt-1">
                      <CurrencyDollarIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.jumlah"
                        type="number"
                        min="0"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Tanggal</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.tanggal"
                        type="date"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Kategori</label
                    >
                    <div class="relative mt-1">
                      <TagIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.kategori"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: ATK, Kebersihan, Snack"
                      />
                    </div>
                  </div>

                  <!-- Input URL Foto + Preview -->
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Link Foto Bukti/Nota (Opsional)</label
                    >
                    <div class="relative mt-1">
                      <LinkIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.bukti_foto"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="https://..."
                      />
                    </div>
                    <!-- Preview Image -->
                    <div
                      v-if="form.bukti_foto"
                      class="mt-3 flex justify-center border border-zinc-100 rounded-lg p-2 bg-zinc-50"
                    >
                      <img
                        :src="form.bukti_foto"
                        @error="onImgError"
                        class="max-h-40 rounded-md object-contain"
                        alt="preview bukti"
                      />
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Deskripsi</label
                    >
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-3"
                      />
                      <textarea
                        v-model="form.deskripsi"
                        rows="2"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"
                      ></textarea>
                    </div>
                  </div>
                </form>

                <div
                  class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3"
                >
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition"
                  >
                    Batal
                  </button>
                  <button
                    @click="submitForm"
                    :disabled="submitting"
                    class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50"
                  >
                    {{ submitting ? "Menyimpan..." : "Ajukan Sekarang" }}
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
import PengeluaranService from "@/api/pengeluaran";
import KelasService from "@/api/kelas";
import anime from "animejs";
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
  AcademicCapIcon,
  CommandLineIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  TagIcon,
  LinkIcon,
  ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const authStore = useAuthStore();
const pengeluaranList = ref([]);
const kelasList = ref([]);
const loading = ref(false);
const submitting = ref(false);

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 10;

// Modal State
const isModalOpen = ref(false);
const form = reactive({
  kelas_id: "",
  judul: "",
  jumlah: "",
  tanggal: "",
  kategori: "",
  bukti_foto: "",
  deskripsi: "",
});

const triggerAnimations = () => {
  anime({
    targets: ".pengeluaran-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".pengeluaran-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchPengeluaran = async () => {
  loading.value = true;
  try {
    const response = await PengeluaranService.getAll();
    pengeluaranList.value = response.data.data || [];

    loading.value = false;
    await nextTick();
    triggerAnimations();
  } catch (error) {
    toast.error("Gagal memuat data pengeluaran");
    loading.value = false;
  }
};

const fetchKelas = async () => {
  try {
    const response = await KelasService.getAll();
    kelasList.value = response.data.data || [];
  } catch (error) {
    console.error("Gagal memuat kelas", error);
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const formatDate = (date) => (date ? dayjs(date).format("DD MMM YYYY") : "-");
const onImgError = (e) => {
  e.target.style.display = "none"; // Sembunyiin kalau link gambar rusak
};

const getStatusClass = (status) => {
  if (status === "approved") return "bg-emerald-50 text-emerald-600";
  if (status === "pending") return "bg-yellow-50 text-yellow-600";
  if (status === "rejected") return "bg-red-50 text-red-600";
  return "bg-zinc-50 text-zinc-600";
};

const filteredPengeluaran = computed(() => {
  let list = pengeluaranList.value;
  if (searchName.value) {
    const search = searchName.value.toLowerCase();
    list = list.filter(
      (p) =>
        p.judul?.toLowerCase().includes(search) ||
        p.kategori?.toLowerCase().includes(search) ||
        p.deskripsi?.toLowerCase().includes(search),
    );
  }
  if (filterStatus.value !== "Semua") {
    list = list.filter((p) => p.status === filterStatus.value);
  }
  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredPengeluaran.value.length / pageSize)),
);
const pagedPengeluaran = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredPengeluaran.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredPengeluaran.value.length === 0
    ? 0
    : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredPengeluaran.value.length),
);

// --- CRUD Functions ---
const openCreateModal = () => {
  Object.assign(form, {
    kelas_id: "",
    judul: "",
    jumlah: "",
    tanggal: dayjs().format("YYYY-MM-DD"),
    kategori: "",
    bukti_foto: "",
    deskripsi: "",
  });
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitForm = async () => {
  submitting.value = true;
  try {
    await PengeluaranService.create(form);
    toast.success("Pengeluaran berhasil diajukan!");
    closeModal();
    fetchPengeluaran();
  } catch (error) {
    const msg = error.response?.data?.message || "Terjadi kesalahan";
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};

const confirmAction = (p, newStatus) => {
  const statusText =
    newStatus === "approved" ? "Approve (Setujui)" : "Reject (Tolak)";
  Swal.fire({
    title: `${statusText} Pengeluaran?`,
    text: `Kamu yakin mau ${statusText} pengajuan dana "${p.judul}"?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: newStatus === "approved" ? "#10b981" : "#ef4444",
    cancelButtonColor: "#64748b",
    confirmButtonText: `Ya, ${statusText}`,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await PengeluaranService.setujui(p.id, { status: newStatus });
        toast.success(`Pengeluaran berhasil di-${statusText}`);
        fetchPengeluaran();
      } catch (error) {
        const msg =
          error.response?.data?.message || `Gagal ${statusText} pengeluaran`;
        toast.error(msg);
      }
    }
  });
};

onMounted(() => {
  fetchPengeluaran();
  fetchKelas();
});
</script>
