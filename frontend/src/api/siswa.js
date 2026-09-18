import api from "./axios";

export default {
  // Ambil semua data siswa
  getAll() {
    return api.get("/siswa");
  },
  // Ambil 1 siswa by ID
  getById(id) {
    return api.get(`/siswa/${id}`);
  },
  // Tambah siswa baru
  create(data) {
    return api.post("/siswa", data);
  },
  // Update data siswa
  update(id, data) {
    return api.put(`/siswa/${id}`, data);
  },
  // Hapus siswa
  delete(id) {
    return api.delete(`/siswa/${id}`);
  },
};
