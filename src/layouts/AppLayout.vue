<template>
  <div class="flex h-screen bg-zinc-50 font-sans overflow-hidden px-7 py-4">
    <AppSidebar ref="sidebarRef" />

    <!-- Main Wrapper -->
    <div class="flex-1 flex flex-col overflow-hidden">
      <AppHeader ref="headerRef" />

      <main
        ref="mainContent"
        class="flex-1 overflow-y-auto px-10 pb-10 lenis-scroll"
      >
        <div ref="contentWrapper" class="opacity-0">
          <router-view />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import Lenis from "lenis";
import anime from "animejs";
import AppSidebar from "@/components/AppSidebar.vue";
import AppHeader from "@/components/AppHeader.vue";

const mainContent = ref(null);
const contentWrapper = ref(null);
const sidebarRef = ref(null);
const headerRef = ref(null);

let lenis = null;
let rafId = null;

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

  
  // 1. Sidebar: Slide dari kiri ke kanan
  anime({
    targets: sidebarRef.value?.$el, // Ambil elemen DOM asli dari komponen
    translateX: ['-100%', '0%'],
    opacity: [0, 1],
    easing: 'easeOutCubic',
    duration: 800
  });

  // 2. Header: Slide dari atas ke bawah
  anime({
    targets: headerRef.value?.$el,
    translateY: ['-100%', '0%'],
    opacity: [0, 1],
    easing: 'easeOutCubic',
    duration: 800,
    delay: 200 // Delay 200ms biar nurut sama sidebar
  });

  // 3. Content: Fade in & Slide naik dari bawah
  anime({
    targets: contentWrapper.value,
    translateY: [30, 0],
    opacity: [0, 1],
    easing: 'easeOutCubic',
    duration: 800,
    delay: 400 // Delay 400ms biar muncul terakhir
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