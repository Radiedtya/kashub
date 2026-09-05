<template>
  <DashboardGuru v-if="authStore.role === 'guru'" />
  <DashboardBendahara v-else-if="authStore.role === 'bendahara'" />
  <DashboardSiswa v-else-if="authStore.role === 'siswa'" />
  <div v-else class="flex items-center justify-center h-96 text-slate-400">
    Role tidak dikenali atau belum ada dashboard.
  </div>
</template>

<script setup>
import { useAuthStore } from "@/stores/auth";
import DashboardGuru from "./DashboardGuru.vue";
import DashboardBendahara from "./DashboardBendahara.vue";
import DashboardSiswa from "./DashboardSiswa.vue";

const authStore = useAuthStore();
</script>
