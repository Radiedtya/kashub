import api from "./axios";

export default {
  getAll() {
    return api.get("/keterlambatan");
  },
  getBySiswa(siswaId) {
    return api.get(`/keterlambatan/siswa/${siswaId}`);
  },
  getMyKeterlambatan() {
    return api.get("/keterlambatan/saya");
  },
  cekKeterlambatan() {
    return api.post("/keterlambatan/cek");
  },
};
