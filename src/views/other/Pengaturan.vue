<template>
  <div class="space-y-6">
    <div class="bg-white border border-zinc-200 rounded-xl p-6 max-w-2xl">
      <h2 class="text-base font-semibold text-zinc-800 mb-1">
        Pengaturan Denda Keterlambatan
      </h2>
      <p class="text-zinc-400 text-xs mb-6">
        Atur nominal denda per hari dan maksimal denda yang bisa dikenakan ke
        siswa. Perubahan akan berlaku untuk pengecekan keterlambatan berikutnya.
      </p>

      <div v-if="loading" class="py-8 text-center text-zinc-400 text-sm">
        Memuat data pengaturan...
      </div>

      <form v-else @submit.prevent="submitSettings" class="space-y-6">
        <div>
          <label class="text-xs text-zinc-600 font-medium"
            >Denda Per Hari (Rp)</label
          >
          <div class="relative mt-1">
            <CurrencyDollarIcon
              class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
            />
            <input
              v-model="form.denda_per_hari"
              type="number"
              min="0"
              required
              class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
            />
          </div>
          <p class="text-[11px] text-zinc-400 mt-1">
            Nominal yang akan dikalikan dengan jumlah hari telat.
          </p>
        </div>

        <div>
          <label class="text-xs text-zinc-600 font-medium"
            >Maksimal Denda (Rp)</label
          >
          <div class="relative mt-1">
            <BanknotesIcon
              class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
            />
            <input
              v-model="form.maks_denda"
              type="number"
              min="0"
              required
              class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
            />
          </div>
          <p class="text-[11px] text-zinc-400 mt-1">
            Batas maksimal denda yang bisa ditagihkan untuk 1 iuran.
          </p>
        </div>

        <div class="flex justify-end pt-4 border-t border-zinc-100">
          <button
            type="submit"
            :disabled="saving"
            class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50 flex items-center gap-2"
          >
            <svg
              v-if="saving"
              class="animate-spin h-4 w-4"
              xmlns="http://www.w3.org/2000/svg"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle
                class="opacity-25"
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                stroke-width="4"
              ></circle>
              <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
              ></path>
            </svg>
            {{ saving ? "Menyimpan..." : "Simpan Pengaturan" }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from "vue";
import { toast } from "vue3-toastify";
import SettingService from "@/api/setting";
import { CurrencyDollarIcon, BanknotesIcon } from "@heroicons/vue/24/outline";

const loading = ref(false);
const saving = ref(false);

const form = reactive({
  denda_per_hari: 0,
  maks_denda: 0,
});

const fetchSettings = async () => {
  loading.value = true;
  try {
    const res = await SettingService.get();
    const data = res.data.data;
    form.denda_per_hari = data.denda_per_hari || 5000;
    form.maks_denda = data.maks_denda || 50000;
  } catch (error) {
    toast.error("Gagal memuat pengaturan");
  } finally {
    loading.value = false;
  }
};

const submitSettings = async () => {
  saving.value = true;
  try {
    await SettingService.update(form);
    toast.success("Pengaturan berhasil disimpan!");
  } catch (error) {
    toast.error("Gagal menyimpan pengaturan");
  } finally {
    saving.value = false;
  }
};

onMounted(() => {
  fetchSettings();
});
</script>
