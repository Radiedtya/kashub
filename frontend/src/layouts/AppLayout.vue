<template>
  <div class="flex h-screen bg-zinc-50 font-sans overflow-hidden md:px-12 md:py-8">
    <div
      v-if="isSidebarOpen"
      @click="isSidebarOpen = false"
      class="fixed inset-0 bg-black/40 z-40 md:hidden"
    ></div>

    <AppSidebar
      ref="sidebarRef"
      :is-open="isSidebarOpen"
      @close="isSidebarOpen = false"
    />

    <div class="flex-1 flex flex-col overflow-hidden min-w-0">
      <AppHeader
        ref="headerRef"
        @toggle-sidebar="isSidebarOpen = !isSidebarOpen"
      />

      <main
        ref="mainContent"
        class="flex-1 overflow-y-auto px-4 md:px-10 pb-10 lenis-scroll scrollbar-none"
      >
        <div ref="contentWrapper" class="opacity-0">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from "vue";
import { useRoute } from "vue-router";
import Lenis from "lenis";
import anime from "animejs";
import AppSidebar from "@/components/AppSidebar.vue";
import AppHeader from "@/components/AppHeader.vue";

const route = useRoute();
const mainContent = ref(null);
const contentWrapper = ref(null);
const sidebarRef = ref(null);
const headerRef = ref(null);
const isSidebarOpen = ref(false);

let lenis = null;
let rafId = null;

watch(
  () => route.path,
  () => {
    isSidebarOpen.value = false;
  }
);

onMounted(async () => {
  await nextTick();

  // --- Setup Lenis Smooth Scroll ---
  if (mainContent.value && contentWrapper.value) {
    lenis = new Lenis({
      wrapper: mainContent.value,
      content: contentWrapper.value,
      duration: 1.2,
      easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
    });

    const raf = (time) => {
      lenis?.raf(time);
      rafId = requestAnimationFrame(raf);
    };
    rafId = requestAnimationFrame(raf);
  }

  // Cek breakpoint SEKALI saat mount, buat nentuin apakah sidebar
  // boleh dianimasikan translateX-nya atau tidak.
  const isDesktop = window.matchMedia("(min-width: 768px)").matches;

  // PENTING: animasi translateX buat sidebar HANYA dijalankan di desktop.
  // Alasan: di mobile, transform sidebar itu tugasnya class Tailwind
  // (translate-x-full / translate-x-0) yang dikontrol `isOpen`. Kalau
  // anime.js ikut nulis transform ke elemen yang sama, inline style-nya
  // bakal menang & override class itu terus-terusan -> sidebar sempat
  // kepaksa "kebuka" pas refresh lalu ke-snap nutup lagi (efek ketarik 2x).
  // Di desktop aman dianimasikan karena sidebar memang selalu tampil
  // (md:translate-x-0), jadi hasil akhir animasinya konsisten sama
  // state class yang berlaku.
  if (isDesktop && sidebarRef.value?.$el) {
    anime({
      targets: sidebarRef.value.$el,
      translateX: ["-100%", "0%"],
      opacity: [0, 1],
      easing: "easeOutCubic",
      duration: 800,
    });
  } else if (sidebarRef.value?.$el) {
    // Mobile: skip animasi transform, cukup pastikan opacity normal.
    sidebarRef.value.$el.style.opacity = 1;
  }

  // 2. Header: Slide dari atas ke bawah
  anime({
    targets: headerRef.value?.$el,
    translateY: ["-100%", "0%"],
    opacity: [0, 1],
    easing: "easeOutCubic",
    duration: 800,
    delay: 200,
  });

  // 3. Content: Fade in & Slide naik dari bawah
  anime({
    targets: contentWrapper.value,
    translateY: [30, 0],
    opacity: [0, 1],
    easing: "easeOutCubic",
    duration: 800,
    delay: 400,
  });
});

onUnmounted(() => {
  if (rafId) cancelAnimationFrame(rafId);
  if (lenis) {
    lenis.destroy();
    lenis = null;
  }
});
</script>