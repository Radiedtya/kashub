<template>
  <!-- Wrapper buat nengahin Card -->
  <div
    class="min-h-screen flex items-center justify-center bg-zinc-50 p-4 sm:p-8 lg:p-12 font-sans"
  >
    <!-- Card Utama -->
    <div
      class="w-full max-w-5xl bg-white rounded-4xl shadow-xl border border-zinc-200 overflow-hidden grid grid-cols-1 lg:grid-cols-5"
    >
      <!-- Kiri: Form Login (2 Kolom) -->
      <div
        class="lg:col-span-2 flex flex-col justify-center px-6 sm:px-10 lg:px-12 py-10 bg-white"
      >
        <div class="mx-auto w-full max-w-sm">
          <!-- Logo & Heading -->
          <div
            v-motion
            :initial="{ opacity: 0, y: -20 }"
            :enter="{ opacity: 1, y: 0, transition: { duration: 500 } }"
            class="mb-8"
          >
            <div class="flex items-center justify-center gap-3 mb-3">
              <img src="@/assets/kb.png" alt="Logo" class="w-12 h-12" />
              <h1 class="text-3xl font-bold text-zinc-900 tracking-tight">
                KasHub
              </h1>
            </div>
            <p class="text-zinc-500 text-center text-sm">
              Masuk untuk mengelola kas kelas Anda
            </p>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleLogin" class="space-y-5">
            <!-- Error Global (Dari Backend) -->
            <div
              v-if="serverError"
              v-motion
              :initial="{ opacity: 0, x: -20 }"
              :enter="{ opacity: 1, x: 0, transition: { duration: 300 } }"
              class="bg-red-50 border border-red-100 text-red-600 text-xs p-3 rounded-lg flex items-center gap-2"
            >
              <ExclamationCircleIcon class="w-5 h-5 shrink-0" />
              <span>{{ serverError }}</span>
            </div>

            <!-- Input Email -->
            <div
              v-motion
              :initial="{ opacity: 0, y: 20 }"
              :enter="{
                opacity: 1,
                y: 0,
                transition: { delay: 150, duration: 500 },
              }"
            >
              <label
                for="email"
                class="block text-xs font-medium text-zinc-600 mb-1.5"
                >Alamat Email</label
              >
              <div class="relative">
                <EnvelopeIcon
                  class="w-5 h-5 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                />
                <input
                  v-model="form.email"
                  @input="clearErrors"
                  type="email"
                  id="email"
                  class="w-full pl-10 pr-3 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
                  :class="
                    errors.email
                      ? 'border-red-400 bg-red-50/30'
                      : 'border-zinc-200'
                  "
                  placeholder="nama@kb.com"
                  autocomplete="off"
                />
              </div>
              <p v-if="errors.email" class="text-red-500 text-[11px] mt-1 ml-1">
                {{ errors.email }}
              </p>
            </div>

            <!-- Input Password -->
            <div
              v-motion
              :initial="{ opacity: 0, y: 20 }"
              :enter="{
                opacity: 1,
                y: 0,
                transition: { delay: 250, duration: 500 },
              }"
            >
              <label
                for="password"
                class="block text-xs font-medium text-zinc-600 mb-1.5"
                >Password</label
              >
              <div class="relative">
                <LockClosedIcon
                  class="w-5 h-5 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                />
                <input
                  v-model="form.password"
                  @input="clearErrors"
                  :type="showPassword ? 'text' : 'password'"
                  id="password"
                  class="w-full pl-10 pr-10 py-2.5 border rounded-lg text-sm focus:ring-2 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
                  :class="
                    errors.password
                      ? 'border-red-400 bg-red-50/30'
                      : 'border-zinc-200'
                  "
                  placeholder="••••••••"
                />
                <!-- Toggle Show Password -->
                <button
                  type="button"
                  @click="showPassword = !showPassword"
                  class="absolute right-3 top-1/2 -translate-y-1/2 text-zinc-400 hover:text-zinc-600 transition"
                >
                  <EyeIcon v-if="!showPassword" class="w-5 h-5" />
                  <EyeSlashIcon v-else class="w-5 h-5" />
                </button>
              </div>
              <p
                v-if="errors.password"
                class="text-red-500 text-[11px] mt-1 ml-1"
              >
                {{ errors.password }}
              </p>
            </div>

            <!-- Tombol Submit -->
            <div
              v-motion
              :initial="{ opacity: 0, y: 20 }"
              :enter="{
                opacity: 1,
                y: 0,
                transition: { delay: 350, duration: 500 },
              }"
            >
              <button
                type="submit"
                :disabled="loading"
                class="w-full bg-linear-to-b from-[#2a3f8d] to-[#0e39e3] text-white py-2.5 rounded-lg font-semibold text-sm transition flex items-center justify-center gap-2 disabled:bg-zinc-400 disabled:cursor-not-allowed"
              >
                <svg
                  v-if="loading"
                  class="animate-spin h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                <span>{{ loading ? "Memuat..." : "Masuk Sekarang" }}</span>
              </button>
            </div>
          </form>

          <p class="text-center text-xs text-zinc-400 mt-8">
            © 2026 KasHub. Semua hak dilindungi.
          </p>
        </div>
      </div>

      <!-- Kanan: Gambar/Branding (3 Kolom - Lebih Lebar) -->
      <div
        class="hidden lg:block lg:col-span-3 relative bg-zinc-900 overflow-hidden"
      >
        <!-- Background Image -->
        <img
          src="@/assets/kbt.png"
          class="absolute inset-0 h-full w-full object-cover opacity-90"
          alt="background"
        />
        <!-- Overlay Content -->
        <div
          class="relative z-10 flex flex-col justify-end h-full p-10 text-white"
        >
          <div
            v-motion
            :initial="{ opacity: 0, y: 30 }"
            :enter="{
              opacity: 1,
              y: 0,
              transition: { delay: 500, duration: 700 },
            }"
            class="bg-zinc-900/50 backdrop-blur-md border border-white/10 rounded-2xl p-6 max-w-lg"
          >
            <h2 class="text-2xl font-bold mb-2">Selamat Datang Kembali!</h2>
            <p class="text-sm text-zinc-200 leading-relaxed">
              Kelola iuran, pengeluaran, dan laporan kas kelas Anda dengan lebih
              mudah, cepat, dan transparan.
            </p>
            <div class="mt-4 flex items-center gap-2 text-xs text-zinc-300">
              <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
              <span>Transparansi keuangan terjaga</span>
            </div>
            <div class="mt-2 flex items-center gap-2 text-xs text-zinc-300">
              <CheckCircleIcon class="w-4 h-4 text-emerald-400" />
              <span>Notifikasi real-time</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import { toast } from "vue3-toastify";
import { useAuthStore } from "@/stores/auth";
import { useNotifikasiStore } from "@/stores/notifikasi";
import {
  EnvelopeIcon,
  LockClosedIcon,
  EyeIcon,
  EyeSlashIcon,
  ExclamationCircleIcon,
  CheckCircleIcon,
} from "@heroicons/vue/24/outline";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const notifikasiStore = useNotifikasiStore();

const form = reactive({
  email: "",
  password: "",
});

const loading = ref(false);
const showPassword = ref(false);
const serverError = ref("");
const errors = reactive({
  email: "",
  password: "",
});

// --- Notif Selamat Datang / Logout ---
onMounted(() => {
  if (route.query.logout === 'true') {
    toast.success("Anda berhasil keluar. Sampai jumpa lagi!");
  } else {
    toast.info("Halo, Selamat datang di KasHub!");
  }
});

const clearErrors = () => {
  errors.email = "";
  errors.password = "";
  serverError.value = "";
};

const validateForm = () => {
  let isValid = true;
  clearErrors();

  if (!form.email) {
    errors.email = "Email wajib diisi";
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = "Format email tidak valid";
    isValid = false;
  }

  if (!form.password) {
    errors.password = "Password wajib diisi";
    isValid = false;
  } else if (form.password.length < 8) {
    errors.password = "Password minimal 8 karakter";
    isValid = false;
  }

  return isValid;
};

const handleLogin = async () => {
  if (!validateForm()) return;

  loading.value = true;
  try {
    await authStore.login(form);
    await notifikasiStore.fetchNotifikasi();
    toast.success("Login Berhasil!");
    router.push("/");
  } catch (error) {
    console.error(error);
    const msg = error.response?.data?.message || "Terjadi kesalahan jaringan";
    serverError.value = msg;
  } finally {
    loading.value = false;
  }
};
</script>
