import api from "./axios";

export default {
  getAll() {
    return api.get("/transaksi");
  },
  getById(id) {
    return api.get(`/transaksi/${id}`);
  },
  getPending() {
    return api.get("/transaksi/pending");
  },
  getMyTransaksi() {
    return api.get("/transaksi/saya");
  },
  getBySiswa(siswaId) {
    return api.get(`/transaksi/siswa/${siswaId}`);
  },
  create(data) {
    return api.post("/transaksi", data);
  },
  update(id, data) {
    return api.put(`/transaksi/${id}`, data);
  },
  konfirmasi(id, data) {
    return api.put(`/transaksi/${id}/konfirmasi`, data);
  },
  delete(id) {
    return api.delete(`/transaksi/${id}`);
  },
};
