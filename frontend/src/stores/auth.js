import { defineStore } from "pinia";
import api from "@/api/axios";
import router from "@/router";

export const useAuthStore = defineStore("auth", {
  state: () => ({
    token: localStorage.getItem("token") || null,
    user: JSON.parse(localStorage.getItem("user")) || null,
  }),
  getters: {
    isAuthenticated: (state) => !!state.token,
    // Ambil name dari objek role
    role: (state) => state.user?.role?.name || "guest",
  },
  actions: {
    // Tambahan buat App.vue
    setAuth(user, token) {
      this.user = user;
      this.token = token;
    },
    
    async login(credentials) {
      try {
        const response = await api.post("/login", credentials);

        // Perhatikan response.data.data di sini!
        this.token = response.data.data.token;
        this.user = response.data.data.user;

        localStorage.setItem("token", this.token);
        localStorage.setItem("user", JSON.stringify(this.user));

        return response.data;
      } catch (error) {
        throw error;
      }
    },
    async fetchUser() {
      try {
        // Sesuaikan juga kalau endpoint /me ngembalikan struktur yang sama
        const response = await api.get("/me");
        this.user = response.data.data.user || response.data.data; // Antisipasi struktur
        localStorage.setItem("user", JSON.stringify(this.user));
      } catch (error) {
        this.logout();
      }
    },
    logout() {
      this.token = null;
      this.user = null;
      localStorage.removeItem("token");
      localStorage.removeItem("user");
      router.push({ name: "login", query: { logout: 'true' } });
    },
  },
});