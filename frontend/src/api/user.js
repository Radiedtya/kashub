import api from "./axios";

export default {
  getAll() {
    return api.get("/users");
  },
  updateProfile(data) {
    return api.put("/profile", data);
  },
  updatePassword(data) {
    return api.put("/password", data);
  },
};
