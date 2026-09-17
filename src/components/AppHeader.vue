<template>
  <header
    class="px-4 py-4 md:px-10 md:py-6 flex items-center justify-between shrink-0 relative z-30 gap-3"
  >
    <div class="flex items-center gap-3 min-w-0">
      <!-- Tombol Hamburger, cuma muncul di mobile -->
      <button
        type="button"
        @click="$emit('toggle-sidebar')"
        class="md:hidden shrink-0 w-10 h-10 flex items-center justify-center rounded-full bg-white shadow-sm hover:shadow transition"
      >
        <Bars3Icon class="w-5 h-5 text-slate-600" />
      </button>

      <!-- Breadcrumb Dinamis & Judul -->
      <div class="min-w-0">
        <h1 class="text-lg md:text-2xl font-bold text-slate-700 truncate">
          {{ currentTitle }}
        </h1>
        <p
          class="text-slate-400 mt-1 text-xs md:text-base truncate hidden sm:block"
        >
          Selamat datang kembali,
          <span class="font-semibold text-black">{{
            authStore.user?.name
          }}</span>
        </p>
      </div>
    </div>

    <div class="flex items-center gap-3 md:gap-5 shrink-0">
      <!-- Jam & Tanggal, cuma muncul di layar lebih lebar dari mobile -->
      <div class="text-right hidden lg:block mr-2">
        <p class="text-sm font-semibold text-slate-800">
          {{ currentTime }}
        </p>
        <p class="text-xs text-slate-500">{{ currentDate }}</p>
      </div>

      <!-- Dropdown Notifikasi -->
      <Menu as="div" class="relative" v-slot="{ close: closeNotif }">
        <MenuButton
          @click="notifikasiStore.fetchNotifikasi()"
          class="relative w-10 h-10 md:w-11 md:h-11 flex items-center justify-center rounded-full bg-white shadow-sm hover:shadow transition focus:outline-none"
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
            class="fixed sm:absolute left-2 right-2 sm:left-auto sm:right-0 top-16 sm:top-auto mt-0 sm:mt-2 w-auto sm:w-80 md:w-96 origin-top-right bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none divide-y divide-gray-100 z-50"
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
                v-slot="{ active, close }"
              >
                <button
                  type="button"
                  @click="
                    () => {
                      close();
                      handleClickNotif(notif);
                    }
                  "
                  :class="[
                    active ? 'bg-gray-50' : '',
                    'w-full text-left flex items-start gap-3 px-4 py-3 text-sm text-gray-700 border-l-4',
                    notif.tipe === 'danger'
                      ? 'border-red-500'
                      : notif.tipe === 'warning'
                        ? 'border-yellow-500'
                        : notif.tipe === 'success'
                          ? 'border-emerald-500'
                          : 'border-blue-500',
                  ]"
                >
                  <!-- Avatar Pengirim -->
                  <div
                    class="w-8 h-8 rounded-full flex items-center justify-center shrink-0 mt-0.5 overflow-hidden border border-gray-100"
                    :class="
                      !notif.sender ? getIconBg(notif.tipe) : 'bg-gray-100'
                    "
                  >
                    <img
                      v-if="notif.sender?.foto"
                      :src="notif.sender.foto"
                      loading="lazy"
                      class="w-full h-full object-cover"
                      alt="foto"
                    />
                    <div
                      v-else-if="notif.sender"
                      class="w-full h-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-[10px]"
                    >
                      {{ notif.sender?.name?.charAt(0) || "S" }}
                    </div>
                    <BellIcon
                      v-else
                      class="w-4 h-4"
                      :class="getIconColor(notif.tipe)"
                    />
                  </div>

                  <!-- Konten Pesan -->
                  <div class="flex flex-col min-w-0">
                    <span class="font-medium text-gray-800 truncate">{{
                      notif.judul
                    }}</span>
                    <span class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{
                      notif.pesan
                    }}</span>
                    <span
                      v-if="notif.sender"
                      class="text-[10px] text-zinc-400 mt-1"
                    >
                      Dari:
                      <span class="font-medium text-zinc-500">{{
                        notif.sender.name
                      }}</span>
                    </span>
                  </div>
                </button>
              </MenuItem>
            </div>

            <div class="py-2 text-center border-t border-gray-100">
              <router-link
                to="/notifikasi"
                @click="closeNotif"
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
            loading="lazy"
            class="w-10 h-10 md:w-11 md:h-11 rounded-full object-cover border border-slate-200"
            alt="foto profil"
          />
          <!-- Jika tidak ada foto, tampilkan ikon person -->
          <div
            v-else
            class="w-10 h-10 md:w-11 md:h-11 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 border border-slate-200"
          >
            <UserIcon class="w-6 h-6" />
          </div>
          <ChevronDownIcon class="w-4 h-4 text-slate-400 hidden sm:block" />
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
              <MenuItem v-slot="{ active, close }">
                <button
                  type="button"
                  @click="
                    () => {
                      close();
                      router.push('/profile');
                    }
                  "
                  :class="[
                    active ? 'bg-gray-50 text-gray-900' : 'text-gray-700',
                    'w-full flex items-center gap-2 px-4 py-2 text-sm',
                  ]"
                >
                  <UserCircleIcon class="w-5 h-5 text-gray-400" />
                  Profile
                </button>
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
  Bars3Icon,
  BellIcon,
  BellSlashIcon,
  ArrowRightOnRectangleIcon,
  ChevronDownIcon,
  UserCircleIcon,
  UserIcon,
} from "@heroicons/vue/24/outline";

defineEmits(["toggle-sidebar"]);

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

// Helper buat warna ikon notifikasi default (kalau gak ada sender)
const getIconBg = (tipe) => {
  if (tipe === "danger") return "bg-red-50";
  if (tipe === "warning") return "bg-yellow-50";
  if (tipe === "success") return "bg-emerald-50";
  return "bg-blue-50";
};
const getIconColor = (tipe) => {
  if (tipe === "danger") return "text-red-500";
  if (tipe === "warning") return "text-yellow-500";
  if (tipe === "success") return "text-emerald-500";
  return "text-blue-500";
};

onMounted(() => {
  updateTime();
  timer = setInterval(updateTime, 1000);
});

onUnmounted(() => {
  clearInterval(timer);
});
</script>
