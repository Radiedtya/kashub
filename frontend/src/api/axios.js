import axios from "axios";
import { useAuthStore } from "@/stores/auth";
import router from "@/router";

const api = axios.create({
  baseURL: "/api", // Akan otomatis di-proxy ke localhost:8000/api
  withCredentials: false, // Set false kalau pake Bearer Token, true kalau pake cookie Sanctum
});

// Request Interceptor: Sisipin token ke setiap request
api.interceptors.request.use(
  (config) => {
    const authStore = useAuthStore();
    if (authStore.token) {
      config.headers.Authorization = `Bearer ${authStore.token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  },
);

// Response Interceptor: Tangkap error (misal 401 Unauthorized)
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response && error.response.status === 401) {
      const authStore = useAuthStore();
      authStore.logout(); // Bersihkan token
      router.push({ name: "login" }); // Auto redirect ke login
    }
    return Promise.reject(error);
  },
);

export default api;
