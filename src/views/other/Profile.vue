<template>
  <div class="space-y-6">
    
    <!-- Header Profile (Full Width) -->
    <div 
      v-motion 
      :initial="{ opacity: 0, y: -20 }" 
      :enter="{ opacity: 1, y: 0, transition: { duration: 500 } }"
      class="bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6"
    >
      <div class="w-24 h-24 rounded-full bg-zinc-900 flex items-center justify-center text-white font-bold text-4xl shrink-0 border-4 border-zinc-100 overflow-hidden">
        <img v-if="authStore.user?.foto" :src="authStore.user.foto" class="w-full h-full object-cover" alt="foto" />
        <UserIcon v-else class="w-12 h-12" />
      </div>
      <div class="flex-1 text-center sm:text-left">
        <div class="flex flex-col sm:flex-row items-center gap-2 mb-1">
          <h2 class="text-xl font-bold text-zinc-900">{{ authStore.user?.name }}</h2>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 capitalize border border-blue-100">
            {{ authStore.role }}
          </span>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-4 text-sm text-zinc-500">
          <span class="flex items-center gap-1.5">
            <EnvelopeIcon class="w-4 h-4" /> {{ authStore.user?.email }}
          </span>
          <span v-if="authStore.user?.no_hp" class="flex items-center gap-1.5">
            <PhoneIcon class="w-4 h-4" /> {{ authStore.user?.no_hp }}
          </span>
        </div>
      </div>
    </div>

    <!-- Grid Bawah: Edit Data & Ganti Password -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      
      <!-- Card Edit Data Diri -->
      <div 
        v-motion 
        :initial="{ opacity: 0, y: 20 }" 
        :enter="{ opacity: 1, y: 0, transition: { delay: 150, duration: 500 } }"
        class="bg-white border border-zinc-200 rounded-xl p-6"
      >
        <h2 class="text-base font-semibold text-zinc-800 mb-1">Edit Data Diri</h2>
        <p class="text-zinc-400 text-xs mb-4">Perbarui informasi dasar akun Anda.</p>
        
        <form @submit.prevent="submitProfile" class="space-y-4">
          <div>
            <label class="text-xs text-zinc-600 font-medium">Nama Lengkap</label>
            <div class="relative mt-1">
              <UserIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.name" type="text" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Email</label>
            <div class="relative mt-1">
              <EnvelopeIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.email" type="email" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">No HP</label>
            <div class="relative mt-1">
              <PhoneIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.no_hp" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Link Foto Profil (URL)</label>
            <div class="relative mt-1">
              <LinkIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.foto" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" placeholder="https://..." />
            </div>
          </div>
          <div class="flex justify-end pt-2">
            <button type="submit" :disabled="savingProfile" class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2">
              <svg v-if="savingProfile" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ savingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Card Ganti Password -->
      <div 
        v-motion 
        :initial="{ opacity: 0, y: 20 }" 
        :enter="{ opacity: 1, y: 0, transition: { delay: 250, duration: 500 } }"
        class="bg-white border border-zinc-200 rounded-xl p-6"
      >
        <h2 class="text-base font-semibold text-zinc-800 mb-1">Ganti Password</h2>
        <p class="text-zinc-400 text-xs mb-4">Pastikan akun Anda menggunakan password yang kuat.</p>
        
        <form @submit.prevent="submitPassword" class="space-y-4">
          <div>
            <label class="text-xs text-zinc-600 font-medium">Password Lama</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.current_password" :type="showOldPass ? 'text' : 'password'" required class="w-full pl-9 pr-10 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
              <button type="button" @click="showOldPass = !showOldPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                <EyeIcon v-if="!showOldPass" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Password Baru</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.new_password" :type="showNewPass ? 'text' : 'password'" required class="w-full pl-9 pr-10 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
              <button type="button" @click="showNewPass = !showNewPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                <EyeIcon v-if="!showNewPass" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Konfirmasi Password Baru</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.new_password_confirmation" :type="showConfPass ? 'text' : 'password'" required class="w-full pl-9 pr-10 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition" />
              <button type="button" @click="showConfPass = !showConfPass" class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600">
                <EyeIcon v-if="!showConfPass" class="w-5 h-5" />
                <EyeSlashIcon v-else class="w-5 h-5" />
              </button>
            </div>
          </div>
          <div class="flex justify-end pt-2">
            <button type="submit" :disabled="savingPassword" class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2">
              <svg v-if="savingPassword" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
              {{ savingPassword ? 'Menyimpan...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { toast } from "vue3-toastify";
import { useAuthStore } from "@/stores/auth";
import UserService from "@/api/user";
import {
  UserIcon, EnvelopeIcon, PhoneIcon, LinkIcon, LockClosedIcon, 
  EyeIcon, EyeSlashIcon 
} from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const savingProfile = ref(false);
const savingPassword = ref(false);

// State buat show/hide password
const showOldPass = ref(false);
const showNewPass = ref(false);
const showConfPass = ref(false);

const profileForm = reactive({
  name: "",
  email: "",
  no_hp: "",
  foto: "",
});

const passwordForm = reactive({
  current_password: "",
  new_password: "",
  new_password_confirmation: "",
});

const initForm = () => {
  profileForm.name = authStore.user?.name || "";
  profileForm.email = authStore.user?.email || "";
  profileForm.no_hp = authStore.user?.no_hp || "";
  profileForm.foto = authStore.user?.foto || "";
};

const submitProfile = async () => {
  savingProfile.value = true;
  try {
    const response = await UserService.updateProfile(profileForm);
    // Update state Pinia & Local Storage biar UI langsung berubah
    authStore.user = response.data.data;
    localStorage.setItem('user', JSON.stringify(authStore.user));
    toast.success("Profile berhasil diperbarui!");
  } catch (error) {
    const msg = error.response?.data?.message || "Gagal memperbarui profile";
    toast.error(msg);
  } finally {
    savingProfile.value = false;
  }
};

const submitPassword = async () => {
  if (passwordForm.new_password !== passwordForm.new_password_confirmation) {
    toast.error("Konfirmasi password baru tidak sesuai");
    return;
  }
  savingPassword.value = true;
  try {
    await UserService.updatePassword(passwordForm);
    toast.success("Password berhasil diubah!");
    passwordForm.current_password = "";
    passwordForm.new_password = "";
    passwordForm.new_password_confirmation = "";
  } catch (error) {
    const msg = error.response?.data?.message || "Gagal mengubah password";
    toast.error(msg);
  } finally {
    savingPassword.value = false;
  }
};

onMounted(() => {
  initForm();
});
</script>