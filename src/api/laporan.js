import api from "./axios";

export default {
  // Yang ini udah ada sebelumnya
  export(format, params) {
    return api.get(`/laporan/export/${format}`, {
      params: params,
      responseType: "blob",
    });
  },
  // Tambahan baru buat ambil data ringkasan
  getKas() {
    return api.get("/laporan/kas");
  },
  getPerBulan(bulan, tahun) {
    return api.get(`/laporan/bulan/${bulan}/${tahun}`);
  },
  getPerKelas(kelasId) {
    return api.get(`/laporan/kelas/${kelasId}`);
  },
};
