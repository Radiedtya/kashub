import api from "./axios";

export default {
  getAll() {
    return api.get("/notifikasi");
  },
  getUnread() {
    return api.get("/notifikasi/unread");
  },
  markAsRead(id) {
    return api.put(`/notifikasi/${id}/read`);
  },
  markAllAsRead() {
    return api.put("/notifikasi/read-all");
  },
  deleteNotif(id) {
    return api.delete(`/notifikasi/${id}`);
  },
  deleteAllRead() {
    return api.delete("/notifikasi/read-all");
  },
  // Untuk Guru/Bendahara
  sendManual(data) {
    return api.post("/notifikasi/send", data);
  },
  sendToKelas(data) {
    return api.post("/notifikasi/send-kelas", data);
  },
};
