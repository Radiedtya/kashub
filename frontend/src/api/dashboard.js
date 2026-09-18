import api from "./axios";

export default {
  getDashboard() {
    return api.get("/dashboard");
  },
};
