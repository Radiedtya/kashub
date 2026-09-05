import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppLayout from "@/layouts/AppLayout.vue";

import Login from "@/views/Login.vue";
import Dashboard from "@/views/Dashboard.vue";
import Profile from "@/views/Profile.vue";
// Bikin file kosong dulu di folder src/views: Siswa.vue, Iuran.vue, Pengeluaran.vue, Laporan.vue
// Isi cuma: <template><div>Halaman ...</div></template>
import Siswa from "@/views/Siswa.vue";
import Kelas from "@/views/Kelas.vue";
import Iuran from "@/views/Iuran.vue";
import Pengeluaran from "@/views/Pengeluaran.vue";
import Notifikasi from "@/views/Notifikasi.vue";
import Laporan from "@/views/Laporan.vue";
import Keterlambatan from "@/views/Keterlambatan.vue";
import Transaksi from "@/views/Transaksi.vue";

const routes = [
  {
    path: "/login",
    name: "login",
    component: Login,
    meta: { requiresAuth: false },
  },
  {
    path: "/",
    component: AppLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: "",
        name: "dashboard",
        component: Dashboard,
        meta: { title: "Dashboard" },
      },
      {
        path: "profile",
        name: "profile",
        component: Profile,
        meta: { title: "Profile Saya" },
      },
      {
        path: "siswa",
        name: "siswa",
        component: Siswa,
        meta: { title: "Data Siswa" },
      },
      {
        path: "kelas",
        name: "kelas",
        component: Kelas,
        meta: { title: "Data Kelas" },
      },
      {
        path: "iuran",
        name: "iuran",
        component: Iuran,
        meta: { title: "Iuran" },
      },
      {
        path: "transaksi",
        name: "transaksi",
        component: Transaksi,
        meta: { title: "Transaksi" },
      },
      {
        path: "pengeluaran",
        name: "pengeluaran",
        component: Pengeluaran,
        meta: { title: "Pengeluaran" },
      },
      {
        path: "notifikasi",
        name: "notifikasi",
        component: Notifikasi,
        meta: { title: "Notifikasi" },
      },
      {
        path: "laporan",
        name: "laporan",
        component: Laporan,
        meta: { title: "Laporan" },
      },
      {
        path: "keterlambatan",
        name: "keterlambatan",
        component: Keterlambatan,
        meta: { title: "Keterlambatan" },
      },
    ],
  },
  // Route buat nangkep URL yang gak ada (auto redirect ke Dashboard)
  {
    path: "/:pathMatch(.*)*",
    redirect: "/",
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: "login" };
  }

  if (to.name === "login" && authStore.isAuthenticated) {
    return { name: "dashboard" };
  }

  return true;
});

export default router;
