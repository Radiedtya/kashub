import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";
import AppLayout from "@/layouts/AppLayout.vue";

const routes = [
  {
    path: "/login",
    name: "login",
    component: () => import("@/views/auth/Login.vue"),
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
        component: () => import("@/views/dashboard/Dashboard.vue"),
        meta: { title: "Dashboard" },
      },
      {
        path: "profile",
        name: "profile",
        component: () => import("@/views/other/Profile.vue"),
        meta: { title: "Profile Saya" },
      },
      {
        path: "siswa",
        name: "siswa",
        component: () => import("@/views/master/Siswa.vue"),
        meta: { title: "Data Siswa" },
      },
      {
        path: "siswa/:id",
        name: "siswa-detail",
        component: () => import("@/views/master/SiswaDetail.vue"),
        meta: { title: "Detail Siswa" },
      },
      {
        path: "kelas",
        name: "kelas",
        component: () => import("@/views/master/Kelas.vue"),
        meta: { title: "Data Kelas" },
      },
      {
        path: "iuran",
        name: "iuran",
        component: () => import("@/views/master/Iuran.vue"),
        meta: { title: "Iuran" },
      },
      {
        path: "transaksi",
        name: "transaksi",
        component: () => import("@/views/keuangan/Transaksi.vue"),
        meta: { title: "Transaksi" },
      },
      {
        path: "pengeluaran",
        name: "pengeluaran",
        component: () => import("@/views/keuangan/Pengeluaran.vue"),
        meta: { title: "Pengeluaran" },
      },
      {
        path: "notifikasi",
        name: "notifikasi",
        component: () => import("@/views/other/Notifikasi.vue"),
        meta: { title: "Notifikasi" },
      },
      {
        path: "laporan",
        name: "laporan",
        component: () => import("@/views/other/Laporan.vue"),
        meta: { title: "Laporan" },
      },
      {
        path: "pengaturan",
        name: "pengaturan",
        component: () => import("@/views/other/Pengaturan.vue"),
        meta: { title: "Pengaturan" },
      },
      {
        path: "keterlambatan",
        name: "keterlambatan",
        component: () => import("@/views/keuangan/Keterlambatan.vue"),
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