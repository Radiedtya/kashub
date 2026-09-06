<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          {{ filteredSiswa.length }} siswa terdaftar
        </p>
      </div>
      <button
        v-if="authStore.role === 'guru'"
        @click="openCreateModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <UserPlusIcon class="w-4 h-4" />
        Tambah Siswa
      </button>
    </div>

    <!-- Card Tabel -->
    <div class="siswa-card bg-white border border-zinc-200 rounded-xl overflow-hidden">
      <!-- Filter Row -->
      <div class="flex flex-col md:flex-row items-stretch md:items-center gap-3 px-6 py-4 border-b border-zinc-100 bg-zinc-50/50">
        <!-- Search -->
        <div class="relative w-full md:w-64">
          <MagnifyingGlassIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10" />
          <input
            v-model="searchName"
            type="text"
            placeholder="Cari nama siswa..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <!-- Filter Kelas -->
        <select
          v-model="filterKelas"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Kelas</option>
          <option v-for="k in kelasOptions" :key="k" :value="k">{{ k }}</option>
        </select>

        <!-- Filter Status -->
        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option value="Aktif">Aktif</option>
          <option value="Nonaktif">Nonaktif</option>
        </select>

        <!-- Tombol Export -->
        <template v-if="authStore.role === 'guru' || authStore.role === 'bendahara'">
          <button
            @click="exportFile('pdf')"
            :disabled="exporting"
            class="w-full md:w-auto flex items-center justify-center gap-1.5 px-3 py-2 border border-zinc-200 rounded-lg text-sm text-red-600 hover:bg-red-50 transition font-medium disabled:opacity-50"
          >
            <DocumentArrowDownIcon class="w-4 h-4" />
            <span>Ekspor PDF</span>
          </button>
          <button
            @click="exportFile('excel')"
            :disabled="exporting"
            class="w-full md:w-auto flex items-center justify-center gap-1.5 px-3 py-2 border border-zinc-200 rounded-lg text-sm text-green-600 hover:bg-green-50 transition font-medium disabled:opacity-50"
          >
            <DocumentArrowDownIcon class="w-4 h-4" />
            <span>Ekspor Excel</span>
          </button>
        </template>
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
            <div class="text-center">Identitas Siswa</div>
            <div>NIS / NISN</div>
            <div>Kelas</div>
            <div>Kontak</div>
            <div>Tgl Lahir</div>
            <div>Alamat</div>
            <div v-if="authStore.role === 'guru'">Orang Tua</div>
            <div>Status</div>
            <div class="text-center">Aksi</div>
          </div>

          <!-- States & Rows -->
          <div v-if="loading" class="px-6 py-16 text-center text-zinc-400 text-sm">
            Memuat data siswa...
          </div>
          <div v-else-if="filteredSiswa.length === 0" class="px-6 py-16 text-center text-zinc-400 text-sm">
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(siswa, index) in pagedSiswa"
              :key="siswa.id"
              class="siswa-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <!-- No -->
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <!-- Nama & Email + Foto Profil -->
              <div class="flex items-center gap-3 pr-4 min-w-37.5">
                <img 
                  v-if="siswa.user?.foto" 
                  :src="siswa.user.foto" 
                  class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100" 
                  alt="foto" 
                />
                <div v-else class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs shrink-0">
                  {{ siswa.user?.name?.charAt(0) || 'S' }}
                </div>
                <div class="flex flex-col">
                  <span class="font-semibold text-zinc-800">{{ siswa.user?.name || "Nama Kosong" }}</span>
                  <span class="text-xs text-zinc-400 truncate">{{ siswa.user?.email || "-" }}</span>
                </div>
              </div>

              <!-- NIS & NISN -->
              <div class="flex flex-col pr-4 min-w-30">
                <span class="text-zinc-700">{{ siswa.nis || "-" }}</span>
                <span class="text-xs text-zinc-400">{{ siswa.nisn || "-" }}</span>
              </div>

              <!-- Kelas -->
              <div class="pr-4 min-w-25">
                <span class="px-2 py-0.5 bg-zinc-100 text-zinc-600 text-xs rounded font-medium">
                  {{ siswa.kelas?.nama || "-" }}
                </span>
              </div>

              <!-- Kontak -->
              <div class="flex flex-col pr-4 min-w-30">
                <span class="text-zinc-600 text-xs">{{ siswa.user?.no_hp || "-" }}</span>
              </div>

              <!-- Tgl Lahir -->
              <div class="pr-4 text-zinc-500 text-xs min-w-30">
                {{ siswa.tempat_lahir || "-" }}<br />
                {{ formatDate(siswa.tanggal_lahir) || "-" }}
              </div>

              <!-- Alamat -->
              <div class="pr-4 text-zinc-500 text-xs truncate max-w-50" :title="siswa.alamat">
                {{ siswa.alamat || "-" }}
              </div>

              <!-- Ortu (Hidden untuk Bendahara) -->
              <div v-if="authStore.role === 'guru'" class="flex flex-col pr-4 min-w-30">
                <span class="text-zinc-700 text-xs font-medium">{{ siswa.nama_ortu || "-" }}</span>
                <span class="text-zinc-400 text-xs">{{ siswa.no_hp_ortu || "-" }}</span>
              </div>

              <!-- Status -->
              <div class="pr-4 min-w-20">
                <span class="flex items-center gap-1.5 text-xs font-medium" :class="siswa.user?.is_active ? 'text-emerald-600' : 'text-zinc-400'">
                  <span class="w-1.5 h-1.5 rounded-full" :class="siswa.user?.is_active ? 'bg-emerald-500' : 'bg-zinc-400'"></span>
                  {{ siswa.user?.is_active ? "Aktif" : "Nonaktif" }}
                </span>
              </div>

              <!-- Action -->
              <div class="flex items-center justify-end gap-1 min-w-25">
                <template v-if="authStore.role === 'guru'">
                  <button @click="openEditModal(siswa)" class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 transition">
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button @click="confirmDelete(siswa)" class="w-8 h-8 flex items-center justify-center rounded-md text-red-500 hover:bg-red-50 transition">
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </template>
                <span v-else class="text-xs text-zinc-300 italic">-</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-zinc-100 gap-4">
        <p class="text-zinc-400 text-xs">Showing {{ rangeStart }}–{{ rangeEnd }} of {{ filteredSiswa.length }}</p>
        <div class="flex items-center gap-1">
          <button @click="currentPage = Math.max(1, currentPage - 1)" :disabled="currentPage === 1" class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition">
            <ChevronLeftIcon class="w-4 h-4" />
          </button>
          <button v-for="p in totalPages" :key="p" @click="currentPage = p" class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium transition" :class="currentPage === p ? 'bg-zinc-900 text-white' : 'text-zinc-500 hover:bg-zinc-100'">
            {{ p }}
          </button>
          <button @click="currentPage = Math.min(totalPages, currentPage + 1)" :disabled="currentPage === totalPages" class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition">
            <ChevronRightIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form (Create/Edit) -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-3xl transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
                <!-- Header Modal -->
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center">
                  <DialogTitle as="h3" class="text-lg font-bold text-zinc-800">
                    {{ isEditMode ? "Edit Data Siswa" : "Tambah Siswa Baru" }}
                  </DialogTitle>
                  <button @click="closeModal" class="text-zinc-400 hover:text-zinc-600">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="submitForm" class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 max-h-[70vh] overflow-y-auto">
                  <!-- Section: Akun -->
                  <div class="md:col-span-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider -mb-3">Informasi Akun</div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Nama Lengkap</label>
                    <div class="relative mt-1">
                      <UserIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.name" type="text" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Email</label>
                    <div class="relative mt-1">
                      <EnvelopeIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.email" type="email" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">
                      Password
                      <span v-if="isEditMode" class="text-zinc-300">(Kosongkan jika tetap)</span>
                    </label>
                    <div class="relative mt-1">
                      <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.password" type="password" :required="!isEditMode" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Kelas</label>
                    <div class="relative mt-1">
                      <AcademicCapIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10" />
                      <select v-model="form.kelas_id" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none">
                        <option value="" disabled>Pilih Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                      </select>
                    </div>
                  </div>

                  <!-- Section: Identitas -->
                  <div class="md:col-span-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider mt-4 -mb-3">Identitas Siswa</div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">NIS</label>
                    <div class="relative mt-1">
                      <IdentificationIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.nis" type="text" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">NISN</label>
                    <div class="relative mt-1">
                      <IdentificationIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.nisn" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">No HP Siswa</label>
                    <div class="relative mt-1">
                      <PhoneIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.no_hp" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Tanggal Lahir</label>
                    <div class="relative mt-1">
                      <CalendarDaysIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.tanggal_lahir" type="date" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium">Tempat Lahir</label>
                    <div class="relative mt-1">
                      <MapPinIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.tempat_lahir" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium">Alamat</label>
                    <div class="relative mt-1">
                      <HomeIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-3" />
                      <textarea v-model="form.alamat" rows="2" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"></textarea>
                    </div>
                  </div>

                  <!-- Section: Orang Tua -->
                  <div class="md:col-span-2 text-xs font-semibold text-zinc-400 uppercase tracking-wider mt-4 -mb-3">Data Orang Tua</div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Nama Orang Tua</label>
                    <div class="relative mt-1">
                      <UsersIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.nama_ortu" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">No HP Orang Tua</label>
                    <div class="relative mt-1">
                      <PhoneIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input v-model="form.no_hp_ortu" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
                    </div>
                  </div>
                </form>

                <!-- Footer Modal -->
                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3">
                  <button type="button" @click="closeModal" class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition">Batal</button>
                  <button @click="submitForm" :disabled="submitting" class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50">
                    {{ submitting ? "Menyimpan..." : "Simpan Data" }}
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
import { ref, computed, onMounted, reactive, nextTick } from "vue";
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import SiswaService from "@/api/siswa";
import KelasService from "@/api/kelas";
import LaporanService from "@/api/laporan";
import anime from "animejs"; // <-- Import animejs
import {
  TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle,
} from "@headlessui/vue";
import {
  UserPlusIcon, PencilSquareIcon, TrashIcon, ChevronLeftIcon, ChevronRightIcon,
  MagnifyingGlassIcon, XMarkIcon, UserIcon, EnvelopeIcon, LockClosedIcon,
  AcademicCapIcon, IdentificationIcon, PhoneIcon, CalendarDaysIcon,
  MapPinIcon, HomeIcon, UsersIcon, DocumentArrowDownIcon
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

const authStore = useAuthStore();
const siswaList = ref([]);
const kelasList = ref([]);
const loading = ref(false);
const submitting = ref(false);
const exporting = ref(false);

// Modal State
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editId = ref(null);
const form = reactive({
  name: "", email: "", password: "", kelas_id: "",
  nis: "", nisn: "", no_hp: "", tanggal_lahir: "", tempat_lahir: "", alamat: "",
  nama_ortu: "", no_hp_ortu: "",
});

// Filter State
const searchName = ref("");
const filterKelas = ref("Semua");
const filterStatus = ref("Semua");

// UI State
const currentPage = ref(1);
const pageSize = 10;

// Dynamic Grid Template (minmax biar responsif)
const gridTemplate = computed(() => {
  if (authStore.role === "guru") {
    return "60px minmax(150px,1.5fr) minmax(120px,1fr) minmax(100px,1fr) minmax(120px,1fr) minmax(120px,1fr) minmax(150px,1.5fr) minmax(120px,1fr) minmax(80px,1fr) minmax(100px,100px)";
  }
  return "60px minmax(150px,1.5fr) minmax(120px,1fr) minmax(100px,1fr) minmax(120px,1fr) minmax(120px,1fr) minmax(150px,1.5fr) minmax(80px,1fr) minmax(100px,100px)";
});

// --- Anime.js Stagger Animation ---
const triggerAnimations = () => {
  anime({
    targets: '.siswa-card',
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: 'easeOutQuad'
  });

  anime({
    targets: '.siswa-row',
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }), // Muncul berurutan tiap 50ms
    duration: 500,
    easing: 'easeOutQuad'
  });
};

const fetchSiswa = async () => {
  loading.value = true;
  try {
    const response = await SiswaService.getAll();
    siswaList.value = response.data.data || [];
    
    loading.value = false;
    await nextTick(); // Tunggu DOM update
    triggerAnimations(); // Jalankan animasi
  } catch (error) {
    toast.error("Gagal memuat data siswa");
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

const kelasOptions = computed(() => {
  const kelas = siswaList.value.map((s) => s.kelas?.nama).filter(Boolean);
  return [...new Set(kelas)];
});

const formatDate = (date) => {
  return date ? dayjs(date).format("DD MMM YYYY") : "-";
};

const filteredSiswa = computed(() => {
  let list = siswaList.value;
  if (searchName.value)
    list = list.filter((s) => s.user?.name?.toLowerCase().includes(searchName.value.toLowerCase()));
  if (filterKelas.value !== "Semua")
    list = list.filter((s) => s.kelas?.nama === filterKelas.value);
  if (filterStatus.value !== "Semua") {
    list = list.filter((s) => (s.user?.is_active ? "Aktif" : "Nonaktif") === filterStatus.value);
  }
  return list;
});

const totalPages = computed(() => Math.max(1, Math.ceil(filteredSiswa.value.length / pageSize)));
const pagedSiswa = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredSiswa.value.slice(start, start + pageSize);
});
const rangeStart = computed(() => filteredSiswa.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1);
const rangeEnd = computed(() => Math.min(currentPage.value * pageSize, filteredSiswa.value.length));

// --- Export Function ---
const exportFile = async (format) => {
  exporting.value = true;
  toast.info("Menyiapkan file export...");
  try {
    const response = await LaporanService.export(format, { type: "siswa_list" });

    if (response.data.type.includes("application/json")) {
      const text = await response.data.text();
      const err = JSON.parse(text);
      throw new Error(err.message || "Gagal export");
    }

    const blob = new Blob([response.data]);
    const link = document.createElement("a");
    link.href = window.URL.createObjectURL(blob);
    link.download = `data_siswa.${format === "pdf" ? "pdf" : "xlsx"}`;
    link.click();
    window.URL.revokeObjectURL(link.href);
    toast.success("File berhasil diunduh!");
  } catch (error) {
    console.error(error);
    toast.error(error.message || "Gagal mengunduh file");
  } finally {
    exporting.value = false;
  }
};

// --- CRUD Functions ---
const openCreateModal = () => {
  isEditMode.value = false;
  Object.assign(form, {
    name: "", email: "", password: "", kelas_id: "",
    nis: "", nisn: "", no_hp: "", tanggal_lahir: "", tempat_lahir: "", alamat: "",
    nama_ortu: "", no_hp_ortu: "",
  });
  isModalOpen.value = true;
};

const openEditModal = (siswa) => {
  isEditMode.value = true;
  editId.value = siswa.id;
  Object.assign(form, {
    name: siswa.user?.name || "",
    email: siswa.user?.email || "",
    password: "",
    kelas_id: siswa.kelas_id || "",
    nis: siswa.nis || "",
    nisn: siswa.nisn || "",
    no_hp: siswa.user?.no_hp || "",
    tanggal_lahir: siswa.tanggal_lahir ? dayjs(siswa.tanggal_lahir).format("YYYY-MM-DD") : "",
    tempat_lahir: siswa.tempat_lahir || "",
    alamat: siswa.alamat || "",
    nama_ortu: siswa.nama_ortu || "",
    no_hp_ortu: siswa.no_hp_ortu || "",
  });
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitForm = async () => {
  submitting.value = true;
  try {
    const payload = { ...form };
    if (isEditMode.value && !payload.password) {
      delete payload.password;
    }

    if (isEditMode.value) {
      await SiswaService.update(editId.value, payload);
      toast.success("Data siswa berhasil diperbarui!");
    } else {
      await SiswaService.create(payload);
      toast.success("Siswa baru berhasil ditambahkan!");
    }
    closeModal();
    fetchSiswa();
  } catch (error) {
    console.error(error);
    const msg = error.response?.data?.message || "Terjadi kesalahan";
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = (siswa) => {
  Swal.fire({
    title: "Hapus Siswa?",
    text: `Kamu yakin mau hapus data ${siswa.user?.name}?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Hapus!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await SiswaService.delete(siswa.id);
        toast.success("Siswa berhasil dihapus");
        fetchSiswa();
      } catch (error) {
        const msg = error.response?.data?.message || "Gagal menghapus siswa";
        toast.error(msg);
      }
    }
  });
};

onMounted(() => {
  fetchSiswa();
  fetchKelas();
});
</script>