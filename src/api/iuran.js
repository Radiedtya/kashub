import api from "./axios";

export default {
  getAll() {
    return api.get("/iuran");
  },
  getById(id) {
    return api.get(`/iuran/${id}`);
  },
  getByKelas(kelasId) {
    return api.get(`/iuran/kelas/${kelasId}`);
  },
  create(data) {
    return api.post("/iuran", data);
  },
  update(id, data) {
    return api.put(`/iuran/${id}`, data);
  },
  delete(id) {
    return api.delete(`/iuran/${id}`);
  },
};
