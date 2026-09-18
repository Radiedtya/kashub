import api from "./axios";

export default {
  getAll() {
    return api.get("/kelas");
  },
  getById(id) {
    return api.get(`/kelas/${id}`);
  },
  create(data) {
    return api.post("/kelas", data);
  },
  update(id, data) {
    return api.put(`/kelas/${id}`, data);
  },
  delete(id) {
    return api.delete(`/kelas/${id}`);
  },
};
