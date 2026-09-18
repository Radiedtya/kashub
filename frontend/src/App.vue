<template>
  <router-view />
</template>

<script setup>
import { onMounted } from "vue";
import { useAuthStore } from "@/stores/auth";
import { useNotifikasiStore } from "@/stores/notifikasi";

const authStore = useAuthStore();
const notifikasiStore = useNotifikasiStore();

onMounted(() => {
  const token = localStorage.getItem("token");
  const user = JSON.parse(localStorage.getItem("user") || "null");

  if (token && user) {
    authStore.setAuth(user, token); // Pastikan authStore punya method setAuth
    // Fetch notifikasi setelah login
    notifikasiStore.fetchNotifikasi();
  }
});
</script>
