import api from "./axios";

export default {
  get() {
    return api.get("/settings");
  },
  update(data) {
    return api.put("/settings", data);
  },
};
