import { createApp } from "vue";
import { createPinia } from "pinia";
import { MotionPlugin } from "@vueuse/motion"; // <-- Import ini
import App from "./App.vue";
import router from "./router";

import "./assets/main.css";
import "vue3-toastify/dist/index.css";

const app = createApp(App);

app.use(createPinia());
app.use(router);
app.use(MotionPlugin); // <-- Daftarin ini

app.mount("#app");
