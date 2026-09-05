<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Card Info Singkat -->
      <div class="profile-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col items-center text-center">
        <div class="w-24 h-24 rounded-full bg-zinc-900 flex items-center justify-center text-white font-bold text-4xl shrink-0 border-4 border-zinc-100 mb-4 overflow-hidden">
          <img v-if="authStore.user?.foto" :src="authStore.user.foto" class="w-full h-full object-cover" alt="foto" />
          <span v-else>{{ authStore.user?.name?.charAt(0) || 'U' }}</span>
        </div>
        <h2 class="text-lg font-bold text-zinc-900">{{ authStore.user?.name }}</h2>
        <p class="text-sm text-zinc-500">{{ authStore.user?.email }}</p>
        <span class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-700 capitalize">
          {{ authStore.role }}
        </span>
      </div>

      <!-- Card Edit Data -->
      <div class="profile-card lg:col-span-2 bg-white border border-zinc-200 rounded-xl p-6">
        <h2 class="text-base font-semibold text-zinc-800 mb-4">Edit Data Diri</h2>
        <form @submit.prevent="submitProfile" class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4">
          <div>
            <label class="text-xs text-zinc-600 font-medium">Nama Lengkap</label>
            <div class="relative mt-1">
              <UserIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.name" type="text" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Email</label>
            <div class="relative mt-1">
              <EnvelopeIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.email" type="email" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">No HP</label>
            <div class="relative mt-1">
              <PhoneIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.no_hp" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Link Foto Profil (URL)</label>
            <div class="relative mt-1">
              <LinkIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="profileForm.foto" type="text" class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" placeholder="https://..." />
            </div>
          </div>
          <div class="md:col-span-2 flex justify-end mt-2">
            <button type="submit" :disabled="savingProfile" class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50">
              {{ savingProfile ? 'Menyimpan...' : 'Simpan Perubahan' }}
            </button>
          </div>
        </form>

        <hr class="my-6 border-zinc-100" />

        <h2 class="text-base font-semibold text-zinc-800 mb-4">Ganti Password</h2>
        <form @submit.prevent="submitPassword" class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4">
          <div>
            <label class="text-xs text-zinc-600 font-medium">Password Lama</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.current_password" type="password" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Password Baru</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.new_password" type="password" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div>
            <label class="text-xs text-zinc-600 font-medium">Konfirmasi Password Baru</label>
            <div class="relative mt-1">
              <LockClosedIcon class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2" />
              <input v-model="passwordForm.new_password_confirmation" type="password" required class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none" />
            </div>
          </div>
          <div class="md:col-span-3 flex justify-end mt-2">
            <button type="submit" :disabled="savingPassword" class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition disabled:opacity-50">
              {{ savingPassword ? 'Menyimpan...' : 'Update Password' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, nextTick } from "vue";
import { toast } from "vue3-toastify";
import { useAuthStore } from "@/stores/auth";
import UserService from "@/api/user";
import anime from "animejs";
import { UserIcon, EnvelopeIcon, PhoneIcon, LinkIcon, LockClosedIcon } from "@heroicons/vue/24/outline";

const authStore = useAuthStore();
const savingProfile = ref(false);
const savingPassword = ref(false);

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

const triggerAnimations = () => {
  anime({
    targets: '.profile-card',
    translateY: [20, 0],
    opacity: [0, 1],
    delay: anime.stagger(100, { start: 0 }),
    duration: 600,
    easing: 'easeOutQuad'
  });
};

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

onMounted(async () => {
  initForm();
  await nextTick();
  triggerAnimations();
});
</script>