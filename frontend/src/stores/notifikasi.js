import { defineStore } from "pinia";
import { ref } from "vue";
import api from "@/api/axios";

export const useNotifikasiStore = defineStore("notifikasi", () => {
  const notifikasi = ref([]);
  const unreadCount = ref(0);
  const isLoading = ref(false);

  async function fetchNotifikasi() {
    isLoading.value = true;
    try {
      const response = await api.get("/notifikasi");
      if (response.data.success) {
        notifikasi.value = response.data.data.notifikasi || [];
        unreadCount.value = response.data.data.belum_dibaca || 0;
      }
    } catch (error) {
      console.warn("Gagal fetch notifikasi:", error.message);
      notifikasi.value = [];
      unreadCount.value = 0;
    } finally {
      isLoading.value = false;
    }
  }

  async function markAsRead(id) {
    try {
      await api.put(`/notifikasi/${id}/read`);
      const item = notifikasi.value.find((n) => n.id === id);
      if (item && !item.is_read) {
        item.is_read = true;
        unreadCount.value = Math.max(0, unreadCount.value - 1);
      }
    } catch (error) {
      console.warn("Gagal mark as read:", error.message);
    }
  }

  async function markAllAsRead() {
    try {
      await api.put("/notifikasi/read-all");
      notifikasi.value.forEach((n) => (n.is_read = true));
      unreadCount.value = 0;
    } catch (error) {
      console.warn("Gagal mark all as read:", error.message);
    }
  }

  async function deleteNotifikasi(id) {
    try {
      await api.delete(`/notifikasi/${id}`);
      const index = notifikasi.value.findIndex((n) => n.id === id);
      if (index !== -1) {
        const item = notifikasi.value[index];
        notifikasi.value.splice(index, 1);
        if (!item.is_read) {
          unreadCount.value = Math.max(0, unreadCount.value - 1);
        }
      }
    } catch (error) {
      console.warn("Gagal delete notifikasi:", error.message);
    }
  }

  async function deleteAllRead() {
    try {
      await api.delete("/notifikasi/read-all");
      notifikasi.value = notifikasi.value.filter((n) => !n.is_read);
    } catch (error) {
      console.warn("Gagal delete all read:", error.message);
    }
  }

  return {
    notifikasi,
    unreadCount,
    isLoading,
    fetchNotifikasi,
    markAsRead,
    markAllAsRead,
    deleteNotifikasi,
    deleteAllRead,
  };
});
