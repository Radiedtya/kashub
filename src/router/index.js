import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppLayout from "@/layouts/AppLayout.vue";

// Auth
import Login from "@/views/auth/Login.vue";

// Dashboard Main
import Dashboard from "@/views/dashboard/Dashboard.vue";

// Master
import Profile from "@/views/other/Profile.vue";
import Siswa from "@/views/master/Siswa.vue";
import Kelas from "@/views/master/Kelas.vue";
import Iuran from "@/views/master/Iuran.vue";

// Keuangan
import Pengeluaran from "@/views/keuangan/Pengeluaran.vue";
import Transaksi from "@/views/keuangan/Transaksi.vue";
import Keterlambatan from "@/views/keuangan/Keterlambatan.vue";

// Other
import Notifikasi from "@/views/other/Notifikasi.vue";
import Laporan from "@/views/other/Laporan.vue";

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
