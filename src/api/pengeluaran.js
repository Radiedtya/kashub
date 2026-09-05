import api from "./axios";

export default {
  getAll() {
    return api.get("/pengeluaran");
  },
  getById(id) {
    return api.get(`/pengeluaran/${id}`);
  },
  create(data) {
    return api.post("/pengeluaran", data);
  },
  update(id, data) {
    return api.put(`/pengeluaran/${id}`, data);
  },
  setujui(id, data) {
    return api.put(`/pengeluaran/${id}/setujui`, data); // Buat approve/reject
  },
  delete(id) {
    return api.delete(`/pengeluaran/${id}`);
  },
};
