<template>
  <header
    class="px-10 py-6 flex items-center justify-between shrink-0 relative z-50"
  >
    <!-- Breadcrumb Dinamis & Judul -->
    <div>
      <h1 class="text-2xl font-bold text-slate-700">
        {{ currentTitle }}
      </h1>
      <p class="text-slate-400 mt-1">
        Selamat datang kembali,
        <span class="font-semibold text-black">{{ authStore.user?.name }}</span>
      </p>
    </div>

    <div class="flex items-center gap-5">
      <!-- Jam & Tanggal -->
      <div class="text-right hidden md:block mr-2">
        <p class="text-sm font-semibold text-slate-800">
          {{ currentTime }}
        </p>
        <p class="text-xs text-slate-500">{{ currentDate }}</p>
      </div>

      <!-- Dropdown Notifikasi -->
      <Menu as="div" class="relative">
        <MenuButton
          @click="notifikasiStore.fetchNotifikasi()"
          class="relative w-11 h-11 flex items-center justify-center rounded-full bg-white shadow-sm hover:shadow transition focus:outline-none"
        >
          <BellIcon class="w-5 h-5 text-slate-500" />
          <span
            v-if="notifikasiStore.unreadCount > 0"
            class="absolute top-1 right-1 min-w-4.5 h-4.5 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full px-1"
          >
            {{
              notifikasiStore.unreadCount > 99
                ? "99+"
                : notifikasiStore.unreadCount
            }}
          </span>
        </MenuButton>
        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <MenuItems
            class="absolute right-0 mt-2 w-80 sm:w-96 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 z-50"
          >
            <div class="p-4 flex justify-between items-center">
              <p class="text-sm font-semibold text-gray-800">
                Notifikasi ({{ notifikasiStore.unreadCount }} baru)
              </p>
              <button
                v-if="notifikasiStore.unreadCount > 0"
                @click="notifikasiStore.markAllAsRead()"
                class="text-xs text-blue-600 hover:underline font-medium"
              >
                Tandai semua dibaca
              </button>
            </div>

            <div class="py-1 max-h-80 overflow-y-auto">
              <div
                v-if="notifikasiStore.notifikasi.length === 0"
                class="px-4 py-8 text-center text-slate-400 text-sm"
              >
                <BellSlashIcon class="w-8 h-8 mx-auto mb-2 text-slate-300" />
                Tidak ada notifikasi.
              </div>

              <MenuItem
                v-for="notif in notifikasiStore.notifikasi.slice(0, 10)"
                :key="notif.id"
                v-slot="{ active }"
              >
                <button
                  type="button"
                  @click="handleClickNotif(notif)"
                  :class="[
                    active ? 'bg-gray-50' : '',
                    'w-full text-left block px-4 py-3 text-sm text-gray-700 border-l-4',
                    notif.tipe === 'danger'
                      ? 'border-red-500'
                      : notif.tipe === 'warning'
                        ? 'border-yellow-500'
                        : notif.tipe === 'success'
                          ? 'border-emerald-500'
                          : 'border-blue-500',
                  ]"
                >
                  <div class="flex flex-col">
                    <span class="font-medium text-gray-800">{{
                      notif.judul
                    }}</span>
                    <span class="text-xs text-gray-500 mt-0.5">{{
                      notif.pesan
                    }}</span>
                  </div>
                </button>
              </MenuItem>
            </div>

            <div class="py-2 text-center border-t border-gray-100">
              <router-link
                to="/notifikasi"
                class="text-xs font-medium text-slate-500 hover:text-slate-700"
              >
                Lihat Semua Notifikasi
              </router-link>
            </div>
          </MenuItems>
        </transition>
      </Menu>

      <!-- Dropdown Profile User -->
      <Menu as="div" class="relative">
        <MenuButton
          class="flex items-center gap-2 cursor-pointer focus:outline-none"
        >
          <!-- Jika ada foto, tampilkan foto -->
          <img
            v-if="authStore.user?.foto"
            :src="authStore.user.foto"
            class="w-11 h-11 rounded-full object-cover border border-slate-200"
            alt="foto profil"
          />
          <!-- Jika tidak ada foto, tampilkan ikon person -->
          <div
            v-else
            class="w-11 h-11 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 border border-slate-200"
          >
            <UserIcon class="w-6 h-6" />
          </div>
          <ChevronDownIcon class="w-4 h-4 text-slate-400" />
        </MenuButton>

        <transition
          enter-active-class="transition ease-out duration-100"
          enter-from-class="transform opacity-0 scale-95"
          enter-to-class="transform opacity-100 scale-100"
          leave-active-class="transition ease-in duration-75"
          leave-from-class="transform opacity-100 scale-100"
          leave-to-class="transform opacity-0 scale-95"
        >
          <MenuItems
            class="absolute right-0 mt-2 w-48 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 z-50"
          >
            <div class="py-1">
              <MenuItem v-slot="{ active }">
                <router-link
                  to="/profile"
                  :class="[
                    active ? 'bg-gray-50 text-gray-900' : 'text-gray-700',
                    'w-full flex items-center gap-2 px-4 py-2 text-sm',
                  ]"
                >
                  <UserCircleIcon class="w-5 h-5 text-gray-400" />
                  Profile
                </router-link>
              </MenuItem>
            </div>
            <div class="py-1">
              <MenuItem v-slot="{ active }">
                <button
                  type="button"
                  @click="authStore.logout()"
                  :class="[
                    active ? 'bg-gray-50 text-red-700' : 'text-red-500',
                    'w-full flex items-center gap-2 px-4 py-2 text-sm',
                  ]"
                >
                  <ArrowRightOnRectangleIcon class="w-5 h-5" />
                  Keluar
                </button>
              </MenuItem>
            </div>
          </MenuItems>
        </transition>
      </Menu>
    </div>
  </header>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Menu, MenuButton, MenuItems, MenuItem } from "@headlessui/vue";
import { useAuthStore } from "@/stores/auth";
import { useNotifikasiStore } from "@/stores/notifikasi";
import dayjs from "dayjs";
import "dayjs/locale/id";
import {
  BellIcon,
  BellSlashIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
  UserCircleIcon,
  UserIcon, // <-- Import UserIcon buat default avatar
} from "@heroicons/vue/24/outline";

dayjs.locale("id");
const authStore = useAuthStore();
const notifikasiStore = useNotifikasiStore();
const route = useRoute();
const router = useRouter();

const currentTitle = computed(() => route.meta.title || "Dashboard");

const currentTime = ref("");
const currentDate = ref("");
let timer;

const updateTime = () => {
  const now = dayjs();
  currentTime.value = now.format("HH:mm:ss");
  currentDate.value = now.format("dddd, DD MMMM YYYY");
};

const handleClickNotif = async (notif) => {
  try {
    await notifikasiStore.markAsRead(notif.id);
    if (notif.link) {
      router.push(notif.link);
    }
  } catch (error) {
    console.error("Gagal mark as read", error);
  }
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>
