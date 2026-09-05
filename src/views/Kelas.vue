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
      <button
        v-if="authStore.role === 'guru'"
        @click="openCreateModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <PlusIcon class="w-4 h-4" />
        Tambah Kelas
      </button>
    </div>

    <!-- Card Tabel -->
    <div
      class="kelas-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
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
            style="grid-template-columns: 60px 1.5fr 1fr 1.5fr 1fr 100px"
          >
            <div class="text-center">No</div>
            <div>Nama Kelas</div>
            <div>Tahun Ajaran</div>
            <div>Wali Kelas</div>
            <div>Status</div>
            <div class="text-right">Aksi</div>
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
              style="grid-template-columns: 60px 1.5fr 1fr 1.5fr 1fr 100px"
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div class="pr-4 min-w-37.5">
                <span class="font-semibold text-zinc-800">{{
                  k.nama || "-"
                }}</span>
              </div>

              <div class="pr-4 text-zinc-600 text-xs min-w-30">
                {{ k.tahun_ajaran || "-" }}
              </div>

              <div class="pr-4 text-zinc-600 text-xs min-w-37.5">
                {{ k.wali_kelas?.name || "-" }}
              </div>

              <div class="pr-4 min-w-20">
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

              <div class="flex items-center justify-end gap-1 min-w-25">
                <template v-if="authStore.role === 'guru'">
                  <button
                    @click="openEditModal(k)"
                    class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 transition"
                  >
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="confirmDelete(k)"
                    class="w-8 h-8 flex items-center justify-center rounded-md text-red-500 hover:bg-red-50 transition"
                  >
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

    <!-- Modal Form (Create/Edit) -->
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
                    {{ isEditMode ? "Edit Data Kelas" : "Tambah Kelas Baru" }}
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
                      >Nama Kelas</label
                    >
                    <div class="relative mt-1">
                      <AcademicCapIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <input
                        v-model="form.nama"
                        type="text"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: XII RPL 1"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Tahun Ajaran</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.tahun_ajaran"
                        type="text"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: 2024/2025"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Wali Kelas</label
                    >
                    <div class="relative mt-1">
                      <UserIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <select
                        v-model="form.wali_kelas_id"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="">Tidak Ada</option>
                        <option v-for="u in guruList" :key="u.id" :value="u.id">
                          {{ u.name }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="md:col-span-2 flex items-center gap-2 mt-2">
                    <input
                      v-model="form.is_active"
                      type="checkbox"
                      id="is_active_kelas"
                      class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900"
                    />
                    <label
                      for="is_active_kelas"
                      class="text-sm text-zinc-600 font-medium"
                      >Status Aktif (Kelas sedang berjalan)</label
                    >
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
import KelasService from "@/api/kelas";
import UserService from "@/api/user"; // Kita butuh list Guru buat dropdown wali kelas
import anime from "animejs";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  PlusIcon,
  PencilSquareIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  AcademicCapIcon,
  CalendarDaysIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const kelasList = ref([]);
const guruList = ref([]);
const loading = ref(false);
const submitting = ref(false);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editId = ref(null);
const form = reactive({
  nama: "",
  tahun_ajaran: "",
  wali_kelas_id: "",
  is_active: true,
});

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 10;

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
  } catch (error) {
    toast.error("Gagal memuat data kelas");
    loading.value = false;
  }
};

const fetchGuru = async () => {
  try {
    const response = await UserService.getAll();
    // Filter cuma yang rolenya Guru
    guruList.value = (response.data.data || []).filter(
      (u) => u.role?.name === "guru",
    );
  } catch (error) {
    console.error("Gagal memuat data guru", error);
  }
};

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

const openCreateModal = () => {
  isEditMode.value = false;
  Object.assign(form, {
    nama: "",
    tahun_ajaran: "",
    wali_kelas_id: "",
    is_active: true,
  });
  isModalOpen.value = true;
};

const openEditModal = (k) => {
  isEditMode.value = true;
  editId.value = k.id;
  Object.assign(form, {
    nama: k.nama || "",
    tahun_ajaran: k.tahun_ajaran || "",
    wali_kelas_id: k.wali_kelas_id || "",
    is_active: k.is_active,
  });
  isModalOpen.value = true;
};

const closeModal = () => (isModalOpen.value = false);

const submitForm = async () => {
  submitting.value = true;
  try {
    if (isEditMode.value) {
      await KelasService.update(editId.value, form);
      toast.success("Data kelas berhasil diperbarui!");
    } else {
      await KelasService.create(form);
      toast.success("Kelas baru berhasil ditambahkan!");
    }
    closeModal();
    fetchKelas();
  } catch (error) {
    const msg = error.response?.data?.message || "Terjadi kesalahan";
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};

const confirmDelete = (k) => {
  Swal.fire({
    title: "Hapus Kelas?",
    text: `Kamu yakin mau hapus kelas ${k.nama}? Semua siswa & iuran di kelas ini mungkin akan terpengaruh.`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Hapus!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await KelasService.delete(k.id);
        toast.success("Kelas berhasil dihapus");
        fetchKelas();
      } catch (error) {
        const msg = error.response?.data?.message || "Gagal menghapus kelas";
        toast.error(msg);
      }
    }
  });
};

onMounted(() => {
  fetchKelas();
  fetchGuru();
});
</script>
