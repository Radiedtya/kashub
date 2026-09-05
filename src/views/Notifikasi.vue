<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <p class="text-slate-400 mt-1 text-sm">Riwayat notifikasi Anda</p>
      </div>
      <div class="flex items-center gap-2 w-full sm:w-auto">
        <button 
          v-if="notifikasiStore.unreadCount > 0"
          @click="handleMarkAllRead"
          class="flex-1 sm:flex-none bg-slate-100 text-slate-700 px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-slate-200 transition flex items-center gap-2 justify-center"
        >
          <CheckCircleIcon class="w-4 h-4" />
          Tandai Dibaca
        </button>

        <!-- Tombol Kirim Notifikasi (Hanya Guru & Bendahara) -->
        <button 
          v-if="authStore.role === 'guru' || authStore.role === 'bendahara'"
          @click="openSendModal"
          class="flex-1 sm:flex-none bg-slate-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-slate-800 transition flex items-center gap-2 justify-center shadow-sm"
        >
          <PaperAirplaneIcon class="w-4 h-4" />
          Kirim Notifikasi
        </button>
      </div>
    </div>

    <!-- List Notifikasi -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm divide-y divide-slate-100">
      <div v-if="notifikasiStore.isLoading" class="px-6 py-16 text-center text-slate-400 text-sm">Memuat notifikasi...</div>
      
      <div v-else-if="notifikasiStore.notifikasi.length === 0" class="px-6 py-16 text-center text-slate-400 text-sm">
        <BellSlashIcon class="w-10 h-10 mx-auto mb-3 text-slate-300" />
        Tidak ada notifikasi.
      </div>

      <div 
        v-else
        v-for="notif in notifikasiStore.notifikasi" 
        :key="notif.id" 
        class="flex items-start gap-4 p-6 hover:bg-slate-50 transition cursor-pointer"
        :class="!notif.is_read ? 'bg-blue-50/30' : ''"
        @click="handleClick(notif)"
      >
        <div 
          class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 mt-1"
          :class="getIconBg(notif.tipe)"
        >
          <BellIcon class="w-5 h-5" :class="getIconColor(notif.tipe)" />
        </div>
        
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2">
            <p class="font-semibold text-slate-800">{{ notif.judul }}</p>
            <span v-if="!notif.is_read" class="w-2 h-2 bg-blue-600 rounded-full"></span>
          </div>
          <p class="text-sm text-slate-500 mt-1">{{ notif.pesan }}</p>
          <p class="text-xs text-slate-400 mt-2">{{ formatTime(notif.created_at) }}</p>
        </div>

        <button 
          v-if="notif.is_read" 
          @click.stop="handleDelete(notif)" 
          class="text-slate-300 hover:text-red-500 transition shrink-0"
        >
          <TrashIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Modal Kirim Notifikasi -->
    <TransitionRoot appear :show="isSendModalOpen" as="template">
      <Dialog as="div" @close="closeSendModal" class="relative z-50">
        <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0" enter-to="opacity-100" leave="duration-200 ease-in" leave-from="opacity-100" leave-to="opacity-0">
          <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild as="template" enter="duration-300 ease-out" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100" leave="duration-200 ease-in" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
              <DialogPanel class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all">
                <div class="px-6 py-4 border-b border-slate-100 flex justify-between items-center">
                  <DialogTitle as="h3" class="text-lg font-bold text-slate-800">
                    Kirim Notifikasi ke Kelas
                  </DialogTitle>
                  <button @click="closeSendModal" class="text-slate-400 hover:text-slate-600">
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <form @submit.prevent="submitNotif" class="p-6 grid grid-cols-1 gap-y-4 max-h-[70vh] overflow-y-auto">
                  <div>
                    <label class="text-xs text-slate-600 font-medium">Pilih Kelas Tujuan</label>
                    <div class="relative mt-1">
                      <AcademicCapIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 z-10" />
                      <select 
                        v-model="notifForm.kelas_id" 
                        required 
                        class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Kelas</option>
                        <option v-for="k in kelasList" :key="k.id" :value="k.id">{{ k.nama }}</option>
                      </select>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-slate-600 font-medium">Judul Notifikasi</label>
                    <div class="relative mt-1">
                      <CommandLineIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <input 
                        v-model="notifForm.judul" 
                        type="text" 
                        required 
                        placeholder="Contoh: Jatuh Tempo Iuran" 
                        class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 outline-none" 
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-slate-600 font-medium">Isi Pesan</label>
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon class="w-4 h-4 text-slate-400 absolute left-3 top-3" />
                      <textarea 
                        v-model="notifForm.pesan" 
                        rows="3" 
                        required 
                        placeholder="Tulis pesan di sini..." 
                        class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 outline-none resize-none"
                      ></textarea>
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-slate-600 font-medium">Tipe Notifikasi</label>
                    <div class="relative mt-1">
                      <TagIcon class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                      <select 
                        v-model="notifForm.tipe" 
                        class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-lg text-sm focus:ring-1 focus:ring-slate-900 outline-none bg-white appearance-none capitalize"
                      >
                        <option value="info">Info</option>
                        <option value="warning">Warning</option>
                        <option value="danger">Danger</option>
                        <option value="success">Success</option>
                      </select>
                    </div>
                  </div>
                </form>

                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                  <button type="button" @click="closeSendModal" class="px-4 py-2 rounded-lg bg-white border border-slate-200 text-slate-700 hover:bg-slate-100 text-sm font-medium transition">Batal</button>
                  <button @click="submitNotif" :disabled="sending" class="px-4 py-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2">
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
import { ref, onMounted, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue3-toastify';
import Swal from 'sweetalert2';
import { useAuthStore } from '@/stores/auth';
import { useNotifikasiStore } from '@/stores/notifikasi';
import NotifikasiService from '@/api/notifikasi';
import KelasService from '@/api/kelas';
import { 
  TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogTitle 
} from '@headlessui/vue';
import { 
  BellIcon, BellSlashIcon, CheckCircleIcon, TrashIcon, 
  XMarkIcon, AcademicCapIcon, CommandLineIcon, ChatBubbleLeftIcon, 
  TagIcon, PaperAirplaneIcon 
} from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
dayjs.extend(relativeTime);

const authStore = useAuthStore();
const notifikasiStore = useNotifikasiStore();
const router = useRouter();
const kelasList = ref([]);

// Modal State
const isSendModalOpen = ref(false);
const sending = ref(false);
const notifForm = reactive({
  kelas_id: '',
  judul: '',
  pesan: '',
  tipe: 'info',
});

const fetchKelas = async () => {
  try {
    const response = await KelasService.getAll();
    kelasList.value = response.data.data || [];
  } catch (error) {
    console.error('Gagal memuat kelas', error);
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

const handleClick = async (notif) => {
  if (!notif.is_read) {
    await notifikasiStore.markAsRead(notif.id);
  }
  if (notif.link) {
    router.push(notif.link);
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

// --- Send Notif Functions ---
const openSendModal = () => {
  Object.assign(notifForm, { kelas_id: '', judul: '', pesan: '', tipe: 'info' });
  isSendModalOpen.value = true;
};

const closeSendModal = () => {
  isSendModalOpen.value = false;
};

const submitNotif = async () => {
  sending.value = true;
  try {
    const response = await NotifikasiService.sendToKelas(notifForm);
    toast.success(response.data.message || 'Notifikasi berhasil dikirim!');
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
  // Kita bisa refresh data lagi di sini biar selalu up-to-date saat buka halaman
  notifikasiStore.fetchNotifikasi();
  fetchKelas();
});
</script>