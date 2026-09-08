<template>
  <div class="space-y-6 relative pb-20">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <p class="text-zinc-400 mt-1 text-sm">Riwayat notifikasi Anda</p>
      </div>
      <div class="flex items-center gap-2 w-full sm:w-auto flex-wrap">
        <button 
          v-if="selectedNotifs.length > 0"
          @click="handleBulkDelete"
          class="flex-1 sm:flex-none bg-red-600 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-red-700 transition flex items-center gap-2 justify-center"
        >
          <TrashIcon class="w-4 h-4" />
          Hapus ({{ selectedNotifs.length }})
        </button>

        <button 
          v-if="notifikasiStore.notifikasi.length > 0"
          @click="handleDeleteAll"
          class="flex-1 sm:flex-none bg-zinc-100 text-zinc-700 px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-200 transition flex items-center gap-2 justify-center"
        >
          <TrashIcon class="w-4 h-4" />
          Hapus Semua
        </button>

        <button 
          v-if="notifikasiStore.unreadCount > 0"
          @click="handleMarkAllRead"
          class="flex-1 sm:flex-none bg-zinc-100 text-zinc-700 px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-200 transition flex items-center gap-2 justify-center"
        >
          <CheckCircleIcon class="w-4 h-4" />
          Tandai Dibaca
        </button>

        <!-- Tombol Kirim Notifikasi (Hanya Guru & Bendahara) -->
        <button 
          v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
          @click="openSendModal"
          class="flex-1 sm:flex-none bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 justify-center shadow-sm"
        >
          <PaperAirplaneIcon class="w-4 h-4" />
          Kirim Notifikasi
        </button>
      </div>
    </div>

    <!-- List Notifikasi -->
    <div class="bg-white rounded-xl border border-zinc-200 shadow-sm divide-y divide-zinc-100">
      <div v-if="notifikasiStore.isLoading" class="px-6 py-16 text-center text-zinc-400 text-sm">Memuat notifikasi...</div>
      
      <div v-else-if="notifikasiStore.notifikasi.length === 0" class="px-6 py-16 text-center text-zinc-400 text-sm">
        <BellSlashIcon class="w-10 h-10 mx-auto mb-3 text-zinc-300" />
        Tidak ada notifikasi.
      </div>

      <!-- Header Checkbox Select All -->
      <div v-else class="flex items-center gap-4 px-6 py-3 bg-zinc-50/50 border-b border-zinc-100">
        <input 
          type="checkbox" 
          :checked="isAllSelected" 
          @change="toggleSelectAll"
          class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900"
        />
        <span class="text-xs text-zinc-500 font-medium">
          {{ isAllSelected ? 'Batal Pilih Semua' : 'Pilih Semua Notifikasi' }}
        </span>
      </div>

      <div 
        v-for="notif in notifikasiStore.notifikasi" 
        :key="notif.id" 
        class="flex items-start gap-4 p-6 hover:bg-zinc-50 transition"
        :class="!notif.is_read ? 'bg-blue-50/30' : ''"
      >
        <!-- Checkbox -->
        <div class="pt-1">
          <input 
            type="checkbox" 
            :value="notif.id" 
            v-model="selectedNotifs"
            class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900"
          />
        </div>

        <!-- Avatar Pengirim / Ikon Sistem -->
        <div 
          class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1 overflow-hidden border border-zinc-100"
          :class="!notif.sender ? getIconBg(notif.tipe) : 'bg-zinc-100'"
        >
          <img v-if="notif.sender?.foto" :src="notif.sender.foto" class="w-full h-full object-cover" alt="foto" />
          <div v-else-if="notif.sender" class="w-full h-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs">
            {{ notif.sender?.name?.charAt(0) || 'S' }}
          </div>
          <BellIcon v-else class="w-5 h-5" :class="getIconColor(notif.tipe)" />
        </div>
        
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-zinc-800">{{ notif.judul }}</p>
            <span v-if="!notif.is_read" class="w-2 h-2 bg-blue-600 rounded-full"></span>
          </div>
          <p class="text-sm text-zinc-500 mt-1">{{ notif.pesan }}</p>
          
          <!-- Info Pengirim & Waktu -->
          <div class="flex items-center gap-2 mt-2 text-xs text-zinc-400">
            <span v-if="notif.sender" class="flex items-center gap-1">
              Dikirim oleh: <span class="font-medium text-zinc-600">{{ notif.sender.name }}</span>
            </span>
            <span v-if="notif.sender" class="text-zinc-300">•</span>
            <span>{{ formatTime(notif.created_at) }}</span>
          </div>
        </div>

        <!-- Tombol Aksi Kanan (Tandai Dibaca & Hapus) -->
        <div class="flex flex-col gap-2 shrink-0 pt-1">
          <!-- Tombol Centang (Tandai Dibaca) -->
          <button 
            @click="handleMarkAsRead(notif)" 
            class="p-1.5 rounded-md transition border"
            :class="notif.is_read ? 'text-emerald-500 border-emerald-200 bg-emerald-50 cursor-default' : 'text-zinc-400 border-zinc-200 hover:bg-zinc-100 hover:text-zinc-700'"
            :disabled="notif.is_read"
            :title="notif.is_read ? 'Sudah dibaca' : 'Tandai sudah dibaca'"
          >
            <CheckCircleIcon class="w-4 h-4" />
          </button>

          <!-- Tombol Hapus -->
          <button 
            @click="handleDelete(notif)" 
            class="p-1.5 rounded-md text-zinc-300 hover:text-red-500 hover:bg-red-50 border border-transparent hover:border-red-200 transition"
            title="Hapus notifikasi"
          >
            <TrashIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Kirim Notifikasi -->
    <TransitionRoot appear :show="isSendModalOpen" as="template">
      <Dialog as="div" @close="closeSendModal" class="relative z-50">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
                <div class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center">
                  <DialogTitle as="h3" class="text-lg font-bold text-zinc-800">
                    Kirim Notifikasi
                  </DialogTitle>
                  <button @click="closeSendModal" class="text-zinc-400 hover:text-zinc-600">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <form @submit.prevent="submitNotif" class="p-6 grid grid-cols-1 gap-y-4 max-h-[70vh] overflow-y-auto">
                  
                  <!-- Pilih Target -->
                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Target Penerima</label>
                    <div class="mt-2 flex items-center gap-4">
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model="targetType" value="kelas" class="w-4 h-4 text-zinc-900 focus:ring-zinc-900" />
                        <span class="text-sm text-zinc-700">Satu Kelas</span>
                      </label>
                      <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" v-model="targetType" value="siswa" class="w-4 h-4 text-zinc-900 focus:ring-zinc-900" />
                        <span class="text-sm text-zinc-700">Siswa Individu</span>
                      </label>
                    </div>
                  </div>

                  <!-- Dropdown Kelas -->
                  <div v-if="targetType === 'kelas'">
                    <label class="text-xs text-zinc-600 font-medium">Pilih Kelas Tujuan</label>
                    <div class="relative mt-1">
                      <AcademicCapIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10" />
                      <select 
                        v-model="notifForm.kelas_id" 
                        required 
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                      </select>
                    </div>
                  </div>

                  <!-- Dropdown Siswa -->
                  <div v-if="targetType === 'siswa'">
                    <label class="text-xs text-zinc-600 font-medium">Pilih Siswa Tujuan</label>
                    <div class="relative mt-1">
                      <UserIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10" />
                      <select 
                        v-model="notifForm.user_id" 
                        required 
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Siswa</option>
                        <option v-for="s in siswaList" :key="s.user_id" :value="s.user_id">
                          {{ s.user?.name }} ({{ s.kelas?.nama || 'Kosong' }})
                        </option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Judul Notifikasi</label>
                    <div class="relative mt-1">
                      <CommandLineIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input 
                        v-model="notifForm.judul" 
                        type="text" 
                        required 
                        placeholder="Contoh: Jatuh Tempo Iuran" 
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" 
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Isi Pesan</label>
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-3" />
                      <textarea 
                        v-model="notifForm.pesan" 
                        rows="3" 
                        required 
                        placeholder="Tulis pesan di sini..." 
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"
                      ></textarea>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium">Tipe Notifikasi</label>
                    <div class="relative mt-1">
                      <TagIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <select 
                        v-model="notifForm.tipe" 
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none capitalize"
                      >
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="danger">Danger</option>
                        <option value="success">Success</option>
                      </select>
                    </div>
                  </div>
                </form>

                <div class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3">
                  <button type="button" @click="closeSendModal" class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition">Batal</button>
                  <button @click="submitNotif" :disabled="sending" class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2">
                    <PaperAirplaneIcon class="w-4 h-4" />
                    {{ sending ? 'Mengirim...' : 'Kirim Sekarang' }}
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
import { ref, onMounted, reactive, computed } from 'vue';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';
import { useAuthStore } from '@/stores/auth';
import { useNotifikasiStore } from '@/stores/notifikasi';
import NotifikasiService from '@/api/notifikasi';
import KelasService from '@/api/kelas';
import SiswaService from '@/api/siswa';
import { 
  TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle 
} from '@headlessui/vue';
import { 
  BellIcon, BellSlashIcon, CheckCircleIcon, TrashIcon, 
  XMarkIcon, AcademicCapIcon, CommandLineIcon, ChatBubbleLeftIcon, 
  TagIcon, PaperAirplaneIcon, UserIcon 
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const authStore = useAuthStore();
const notifikasiStore = useNotifikasiStore();
const kelasList = ref([]);
const siswaList = ref([]);

// Modal & Form State
const isSendModalOpen = ref(false);
const sending = ref(false);
const targetType = ref('kelas'); // 'kelas' atau 'siswa'
const notifForm = reactive({
  kelas_id: '',
  user_id: '',
  judul: '',
  pesan: '',
  tipe: 'info',
});

// Checkbox State
const selectedNotifs = ref([]);

const isAllSelected = computed(() => {
  return notifikasiStore.notifikasi.length > 0 && selectedNotifs.value.length === notifikasiStore.notifikasi.length;
});

const toggleSelectAll = (e) => {
  if (e.target.checked) {
    selectedNotifs.value = notifikasiStore.notifikasi.map(n => n.id);
  } else {
    selectedNotifs.value = [];
  }
};

const fetchKelas = async () => {
  try {
    const response = await KelasService.getAll();
    kelasList.value = response.data.data || [];
  } catch (error) {
    console.error('Gagal memuat kelas', error);
  }
};

const fetchSiswa = async () => {
  try {
    const response = await SiswaService.getAll();
    siswaList.value = response.data.data || [];
  } catch (error) {
    console.error('Gagal memuat siswa', error);
  }
};

const handleMarkAllRead = async () => {
  const result = await Swal.fire({
    title: 'Tandai Semua Dibaca?',
    text: 'Semua notifikasi akan ditandai sudah dibaca.',
    icon: 'question',
    showCancelButton: true,
    confirmButtonColor: '#2563eb',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Tandai',
    cancelButtonText: 'Batal',
  });

  if (result.isConfirmed) {
    await notifikasiStore.markAllAsRead();
    toast.success('Semua notifikasi ditandai dibaca');
  }
};

// --- Logic Baru: Klik tombol centang untuk tandai dibaca ---
const handleMarkAsRead = async (notif) => {
  if (!notif.is_read) {
    try {
      await notifikasiStore.markAsRead(notif.id);
      // toast.success('Notifikasi ditandai dibaca'); // Opsional kalau mau pake toast
    } catch (error) {
      console.error("Gagal mark as read", error);
    }
  }
};

const handleDelete = async (notif) => {
  const result = await Swal.fire({
    title: 'Hapus Notifikasi?',
    text: `Apakah Anda yakin ingin menghapus notifikasi "${notif.judul}"?`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
  });

  if (result.isConfirmed) {
    await notifikasiStore.deleteNotifikasi(notif.id);
    toast.success('Notifikasi dihapus');
  }
};

// Hapus Massal (Bulk Delete)
const handleBulkDelete = async () => {
  const result = await Swal.fire({
    title: 'Hapus Notifikasi Terpilih?',
    text: `${selectedNotifs.value.length} notifikasi akan dihapus permanen.`,
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
  });

  if (result.isConfirmed) {
    let successCount = 0;
    for (const id of selectedNotifs.value) {
      try {
        await notifikasiStore.deleteNotifikasi(id);
        successCount++;
      } catch (error) {
        console.error(`Gagal hapus notif ID: ${id}`, error);
      }
    }
    toast.success(`${successCount} notifikasi berhasil dihapus`);
    selectedNotifs.value = []; // Reset checkbox
  }
};

// Hapus Semua
const handleDeleteAll = async () => {
  const result = await Swal.fire({
    title: 'Hapus SEMUA Notifikasi?',
    text: 'Tindakan ini tidak dapat dibatalkan. Semua riwayat notifikasi akan hilang.',
    icon: 'error',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#6b7280',
    confirmButtonText: 'Ya, Hapus Semua!',
    cancelButtonText: 'Batal',
  });

  if (result.isConfirmed) {
    let successCount = 0;
    // Karena backend belum punya endpoint "delete all without read condition", kita loop aja
    for (const notif of notifikasiStore.notifikasi) {
      try {
        await notifikasiStore.deleteNotifikasi(notif.id);
        successCount++;
      } catch (error) {
        console.error('Gagal hapus notif', error);
      }
    }
    toast.success(`${successCount} notifikasi berhasil dihapus`);
    selectedNotifs.value = [];
  }
};

// --- Send Notif Functions ---
const openSendModal = () => {
  Object.assign(notifForm, { kelas_id: '', user_id: '', judul: '', pesan: '', tipe: 'info' });
  targetType.value = 'kelas';
  isSendModalOpen.value = true;
};

const closeSendModal = () => {
  isSendModalOpen.value = false;
};

const submitNotif = async () => {
  sending.value = true;
  try {
    if (targetType.value === 'kelas') {
      if (!notifForm.kelas_id) {
        toast.error("Silakan pilih kelas tujuan");
        sending.value = false;
        return;
      }
      const response = await NotifikasiService.sendToKelas({
        kelas_id: notifForm.kelas_id,
        judul: notifForm.judul,
        pesan: notifForm.pesan,
        tipe: notifForm.tipe
      });
      toast.success(response.data.message || 'Notifikasi berhasil dikirim ke kelas!');
    } else {
      if (!notifForm.user_id) {
        toast.error("Silakan pilih siswa tujuan");
        sending.value = false;
        return;
      }
      const response = await NotifikasiService.sendManual({
        user_id: notifForm.user_id,
        judul: notifForm.judul,
        pesan: notifForm.pesan,
        tipe: notifForm.tipe
      });
      toast.success(response.data.message || 'Notifikasi berhasil dikirim ke siswa!');
    }
    closeSendModal();
  } catch (error) {
    const msg = error.response?.data?.message || 'Gagal mengirim notifikasi';
    toast.error(msg);
  } finally {
    sending.value = false;
  }
};

const formatTime = (date) => dayjs(date).fromNow();

const getIconBg = (tipe) => {
  if (tipe === 'danger') return 'bg-red-50';
  if (tipe === 'warning') return 'bg-yellow-50';
  if (tipe === 'success') return 'bg-emerald-50';
  return 'bg-blue-50';
};
const getIconColor = (tipe) => {
  if (tipe === 'danger') return 'text-red-500';
  if (tipe === 'warning') return 'text-yellow-500';
  if (tipe === 'success') return 'text-emerald-500';
  return 'text-blue-500';
};

onMounted(() => {
  notifikasiStore.fetchNotifikasi();
  
  // FIX: Cuma fetch data kelas & siswa kalau yang login Guru/Bendahara
  // Kalau siswa, gak usah di-fetch biar gak kena 403 Forbidden
  if (authStore.role === 'guru' || authStore.role === 'bendahara') {
    fetchKelas();
    fetchSiswa();
  }
});
</script>