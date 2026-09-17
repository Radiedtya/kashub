<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          <template v-if="loading">
            <span
              class="inline-block h-4 w-32 bg-zinc-200 rounded animate-pulse align-middle"
            ></span>
          </template>
          <template v-else>
            {{ filteredPengeluaran.length }} pengajuan ditemukan
          </template>
        </p>
      </div>
      <!-- Tombol Ajukan Pengeluaran (Hanya Bendahara) -->
      <button
        v-if="authStore.role === 'bendahara'"
        @click="openCreateModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <PlusIcon class="w-4 h-4" />
        Ajukan Pengeluaran
      </button>
    </div>

    <!-- Chart Card Skeleton -->
    <div
      v-if="loading"
      class="bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6 animate-pulse"
    >
      <div class="w-40 h-40 rounded-full bg-zinc-200 shrink-0"></div>
      <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div
          v-for="n in 3"
          :key="n"
          class="h-16 bg-zinc-100 rounded-lg"
        ></div>
      </div>
    </div>

    <!-- Chart Card -->
    <div
      v-else
      class="pengeluaran-chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6"
    >
      <div class="relative w-40 h-40 shrink-0">
        <canvas ref="statusChart"></canvas>
        <div
          class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
        >
          <span class="text-xl font-bold text-zinc-900">{{
            pengeluaranList.length
          }}</span>
          <span class="text-zinc-400 text-[10px] uppercase tracking-wide"
            >Total Pengajuan</span
          >
        </div>
      </div>
      <div class="flex-1 w-full grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Disetujui</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.approved }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-amber-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Pending</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.pending }}
            </p>
          </div>
        </div>
        <div class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg">
          <span class="w-3 h-3 rounded-full bg-red-500"></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">Ditolak</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ statusData.rejected }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Card Tabel -->
    <div
      class="pengeluaran-card bg-white border border-zinc-200 rounded-xl overflow-hidden p-5"
    >
      <!-- Filter Row (Diubah biar search-nya flex-1) -->
      <div
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 p-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <div class="relative flex-1 w-full">
          <MagnifyingGlassIcon
            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
          />
          <input
            v-model="searchName"
            type="text"
            placeholder="Cari judul/kategori..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option value="pending">Pending</option>
          <option value="approved">Disetujui (Approved)</option>
          <option value="rejected">Ditolak (Rejected)</option>
        </select>
      </div>

      <!-- Tabel dengan Horizontal Scroll -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            :style="{ gridTemplateColumns: gridTemplate }"
          >
            <div class="text-center">No</div>
            <div>Judul & Deskripsi</div>
            <div>Diajukan Oleh</div>
            <div>Tanggal</div>
            <div>Jumlah</div>
            <div class="text-center">Bukti</div>
            <div>Status</div>
            <div>Disetujui Oleh</div>
            <div class="text-center">Aksi</div>
          </div>

          <!-- Skeleton Loading Rows -->
          <div v-if="loading">
            <div
              v-for="n in 6"
              :key="'skeleton-' + n"
              class="grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 text-sm animate-pulse"
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <!-- No -->
              <div class="flex justify-center">
                <div class="h-4 w-4 bg-zinc-200 rounded"></div>
              </div>

              <!-- Judul & Deskripsi -->
              <div class="pr-4 min-w-50 space-y-2">
                <div class="h-4 w-3/4 bg-zinc-200 rounded"></div>
                <div class="h-3 w-1/2 bg-zinc-100 rounded"></div>
              </div>

              <!-- Diajukan Oleh -->
              <div class="pr-4 min-w-45">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-zinc-200 shrink-0"></div>
                  <div class="space-y-2 flex-1">
                    <div class="h-3 w-2/3 bg-zinc-200 rounded"></div>
                    <div class="h-2.5 w-12 bg-zinc-100 rounded"></div>
                  </div>
                </div>
              </div>

              <!-- Tanggal -->
              <div class="pr-4 min-w-27.5">
                <div class="h-3 w-20 bg-zinc-200 rounded"></div>
              </div>

              <!-- Jumlah -->
              <div class="pr-4 min-w-30">
                <div class="h-4 w-24 bg-zinc-200 rounded"></div>
              </div>

              <!-- Bukti -->
              <div class="pr-4 flex justify-center">
                <div class="w-10 h-10 rounded-md bg-zinc-200"></div>
              </div>

              <!-- Status -->
              <div class="pr-4 min-w-25">
                <div class="h-6 w-16 bg-zinc-200 rounded-full"></div>
              </div>

              <!-- Disetujui Oleh -->
              <div class="pr-4 min-w-37.5">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-zinc-100 shrink-0"></div>
                  <div class="space-y-2 flex-1">
                    <div class="h-3 w-2/3 bg-zinc-100 rounded"></div>
                    <div class="h-2.5 w-10 bg-zinc-100 rounded"></div>
                  </div>
                </div>
              </div>

              <!-- Aksi -->
              <div class="flex items-center justify-end gap-2 min-w-45">
                <div class="h-6 w-20 bg-zinc-200 rounded-md"></div>
                <div class="h-6 w-20 bg-zinc-100 rounded-md"></div>
              </div>
            </div>
          </div>

          <div
            v-else-if="filteredPengeluaran.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(p, index) in pagedPengeluaran"
              :key="p.id"
              class="pengeluaran-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div class="pr-4 min-w-50">
                <span class="font-semibold text-zinc-800 capitalize">{{
                  p.judul || "Tanpa Judul"
                }}</span>
                <span class="text-xs text-zinc-400 block truncate"
                  >{{ p.kategori || "-" }} - {{ p.deskripsi || "-" }}</span
                >
              </div>

              <!-- Diajukan Oleh (Avatar + Role) -->
              <div class="pr-4 min-w-45">
                <div class="flex items-center gap-3">
                  <img
                    v-if="p.created_by?.foto || p.createdBy?.foto"
                    :src="p.created_by?.foto || p.createdBy?.foto"
                    class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                    alt="foto"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-semibold text-xs shrink-0"
                  >
                    {{
                      (p.created_by?.name || p.createdBy?.name)?.charAt(0) ||
                      "?"
                    }}
                  </div>
                  <div class="flex flex-col gap-1">
                    <span class="font-medium text-zinc-700 text-xs">{{
                      p.created_by?.name || p.createdBy?.name || "-"
                    }}</span>
                    <span
                      v-if="p.created_by?.role?.name || p.createdBy?.role?.name"
                      class="inline-flex w-fit items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 capitalize"
                    >
                      {{ p.created_by?.role?.name || p.createdBy?.role?.name }}
                    </span>
                  </div>
                </div>
              </div>

              <div class="pr-4 text-zinc-500 text-xs min-w-27.5">
                {{ formatDate(p.tanggal) }}
              </div>

              <div class="pr-4 font-semibold text-zinc-700 min-w-30">
                Rp {{ formatRupiah(p.jumlah) }}
              </div>

              <!-- Bukti Bayar -->
              <div class="pr-4 text-center min-w-20 flex justify-center">
                <a
                  v-if="p.bukti_foto"
                  :href="p.bukti_foto"
                  target="_blank"
                  class="inline-block"
                >
                  <img
                    :src="p.bukti_foto"
                    @error="onImgError"
                    class="w-10 h-10 rounded-md object-cover border border-zinc-200 mx-auto hover:opacity-80 transition"
                    alt="bukti"
                  />
                </a>
                <PhotoIcon v-else class="w-5 h-5 text-zinc-300 mx-auto" />
              </div>

              <div class="pr-4 min-w-25">
                <span
                  class="px-2 py-1 text-xs rounded font-medium capitalize"
                  :class="getStatusClass(p.status)"
                >
                  {{ p.status }}
                </span>
              </div>

              <!-- Disetujui Oleh (Avatar + Role) -->
              <div class="pr-4 min-w-37.5">
                <div
                  v-if="p.approved_by?.name || p.approvedBy?.name"
                  class="flex items-center gap-3"
                >
                  <img
                    v-if="p.approved_by?.foto || p.approvedBy?.foto"
                    :src="p.approved_by?.foto || p.approvedBy?.foto"
                    class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                    alt="foto"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center font-semibold text-xs shrink-0"
                  >
                    {{
                      (p.approved_by?.name || p.approvedBy?.name)?.charAt(0) ||
                      "?"
                    }}
                  </div>
                  <div class="flex flex-col gap-1">
                    <span class="font-medium text-zinc-700 text-xs">{{
                      p.approved_by?.name || p.approvedBy?.name
                    }}</span>
                    <span
                      v-if="
                        p.approved_by?.role?.name || p.approvedBy?.role?.name
                      "
                      class="inline-flex w-fit items-center px-1.5 py-0.5 rounded text-[10px] font-medium bg-zinc-100 text-zinc-600 capitalize"
                    >
                      {{
                        p.approved_by?.role?.name || p.approvedBy?.role?.name
                      }}
                    </span>
                  </div>
                </div>
                <span v-else class="text-zinc-300 text-xs">-</span>
              </div>

              <div class="flex items-center justify-end gap-2 min-w-45">
                <template
                  v-if="p.status === 'pending' && authStore.role === 'guru'"
                >
                  <button
                    @click="confirmAction(p, 'approved')"
                    class="px-2.5 py-1 rounded-md border border-emerald-600 text-emerald-600 bg-white hover:bg-emerald-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <CheckIcon class="w-3.5 h-3.5" /> Approve
                  </button>
                  <button
                    @click="confirmAction(p, 'rejected')"
                    class="px-2.5 py-1 rounded-md border border-red-600 text-red-600 bg-white hover:bg-red-50 text-xs font-semibold transition flex items-center gap-1"
                  >
                    <XMarkIcon class="w-3.5 h-3.5" /> Reject
                  </button>
                </template>

                <span v-else class="text-xs text-zinc-400 italic"
                  >Tidak ada aksi tersedia</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Pagination -->
      <div
        class="flex flex-col sm:flex-row items-center justify-between px-6 py-4 border-t border-zinc-100 gap-4"
      >
        <p class="text-zinc-400 text-xs">
          <template v-if="loading">
            <span
              class="inline-block h-3 w-28 bg-zinc-200 rounded animate-pulse align-middle"
            ></span>
          </template>
          <template v-else>
            Showing {{ rangeStart }}–{{ rangeEnd }} of
            {{ filteredPengeluaran.length }}
          </template>
        </p>

        <!-- Pagination Skeleton -->
        <div v-if="loading" class="flex items-center gap-1">
          <div class="w-8 h-8 bg-zinc-200 rounded-md animate-pulse"></div>
          <div class="w-8 h-8 bg-zinc-100 rounded-md animate-pulse"></div>
          <div class="w-8 h-8 bg-zinc-100 rounded-md animate-pulse"></div>
          <div class="w-8 h-8 bg-zinc-200 rounded-md animate-pulse"></div>
        </div>

        <div v-else class="flex items-center gap-1">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition"
          >
            <ChevronLeftIcon class="w-4 h-4" />
          </button>
          <button
            v-for="pg in totalPages"
            :key="pg"
            @click="currentPage = pg"
            class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium transition"
            :class="
              currentPage === pg
                ? 'bg-zinc-900 text-white'
                : 'text-zinc-500 hover:bg-zinc-100'
            "
          >
            {{ pg }}
          </button>
          <button
            @click="currentPage = Math.min(totalPages, currentPage + 1)"
            :disabled="currentPage === totalPages"
            class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition"
          >
            <ChevronRightIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </div>

    <!-- Modal Form Ajukan Pengeluaran (Hanya Bendahara) -->
    <TransitionRoot appear :show="isModalOpen" as="template">
      <Dialog as="div" @close="closeModal" class="relative z-50">
        <TransitionChild
          as="template"
          enter="duration-300 ease-out"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="duration-200 ease-in"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <div class="fixed inset-0 bg-zinc-900/50 backdrop-blur-sm" />
        </TransitionChild>

        <div class="fixed inset-0 overflow-y-auto">
          <div class="flex min-h-full items-center justify-center p-4">
            <TransitionChild
              as="template"
              enter="duration-300 ease-out"
              enter-from="opacity-0 scale-95"
              enter-to="opacity-100 scale-100"
              leave="duration-200 ease-in"
              leave-from="opacity-100 scale-100"
              leave-to="opacity-0 scale-95"
            >
              <DialogPanel
                class="w-full max-w-lg transform overflow-hidden rounded-2xl bg-white text-left align-middle shadow-xl transition-all"
              >
                <div
                  class="px-6 py-4 border-b border-zinc-100 flex justify-between items-center"
                >
                  <DialogTitle as="h3" class="text-lg font-bold text-zinc-800">
                    Ajukan Pengeluaran Baru
                  </DialogTitle>
                  <button
                    @click="closeModal"
                    class="text-zinc-400 hover:text-zinc-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>

                <form
                  @submit.prevent="submitForm"
                  class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 max-h-[70vh] overflow-y-auto"
                >
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Kelas</label
                    >
                    <div class="relative mt-1">
                      <AcademicCapIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <select
                        v-model="form.kelas_id"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none"
                      >
                        <option value="" disabled>Pilih Kelas</option>
                        <option
                          v-for="k in kelasList"
                          :key="k.id"
                          :value="k.id"
                        >
                          {{ k.nama }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Judul Pengeluaran</label
                    >
                    <div class="relative mt-1">
                      <CommandLineIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.judul"
                        type="text"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: Beli Alat Tulis"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Jumlah (Rp)</label
                    >
                    <div class="relative mt-1">
                      <CurrencyDollarIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.jumlah"
                        type="number"
                        min="0"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>

                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Tanggal</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.tanggal"
                        type="date"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Kategori</label
                    >
                    <div class="relative mt-1">
                      <TagIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.kategori"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="Contoh: ATK, Kebersihan, Snack"
                      />
                    </div>
                  </div>

                  <!-- Input URL Foto + Preview -->
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Link Foto Bukti/Nota (Opsional)</label
                    >
                    <div class="relative mt-1">
                      <LinkIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.bukti_foto"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="https://..."
                      />
                    </div>
                    <!-- Preview Image -->
                    <div
                      v-if="form.bukti_foto"
                      class="mt-3 flex justify-center border border-zinc-100 rounded-lg p-2 bg-zinc-50"
                    >
                      <img
                        :src="form.bukti_foto"
                        @error="onImgError"
                        class="max-h-40 rounded-md object-contain"
                        alt="preview bukti"
                      />
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Deskripsi</label
                    >
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-3"
                      />
                      <textarea
                        v-model="form.deskripsi"
                        rows="2"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"
                      ></textarea>
                    </div>
                  </div>
                </form>

                <div
                  class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3"
                >
                  <button
                    type="button"
                    @click="closeModal"
                    class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition"
                  >
                    Batal
                  </button>
                  <button
                    @click="submitForm"
                    :disabled="submitting"
                    class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50"
                  >
                    {{ submitting ? "Menyimpan..." : "Ajukan Sekarang" }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, nextTick, reactive } from "vue";
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import PengeluaranService from "@/api/pengeluaran";
import KelasService from "@/api/kelas";
import anime from "animejs";
import { Chart, registerables } from "chart.js";
import {
  TransitionRoot,
  TransitionChild,
  Dialog,
  DialogPanel,
  DialogTitle,
} from "@headlessui/vue";
import {
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  CheckIcon,
  XMarkIcon,
  PhotoIcon,
  PlusIcon,
  AcademicCapIcon,
  CommandLineIcon,
  CurrencyDollarIcon,
  CalendarDaysIcon,
  TagIcon,
  LinkIcon,
  ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const authStore = useAuthStore();
const pengeluaranList = ref([]);
const kelasList = ref([]);
const loading = ref(false);
const submitting = ref(false);

const searchName = ref("");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 10;

// Chart State
const statusChart = ref(null);
let chartInstance = null;

// Dynamic Grid Template
const gridTemplate = computed(() => {
  return "60px minmax(250px, 1.5fr) minmax(200px, 1fr) minmax(120px, 1fr) minmax(130px, 1fr) 80px minmax(110px, 1fr) minmax(180px, 1fr) minmax(180px, 1fr)";
});

// Modal State
const isModalOpen = ref(false);
const form = reactive({
  kelas_id: "",
  judul: "",
  jumlah: "",
  tanggal: "",
  kategori: "",
  bukti_foto: "",
  deskripsi: "",
});

const triggerAnimations = () => {
  anime({
    targets: ".pengeluaran-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".pengeluaran-chart-card",
    translateY: [20, 0],
    opacity: [0, 1],
    delay: 100,
    duration: 600,
    easing: "easeOutQuad",
  });

  anime({
    targets: ".pengeluaran-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchPengeluaran = async () => {
  loading.value = true;
  try {
    const response = await PengeluaranService.getAll();
    pengeluaranList.value = response.data.data || [];

    loading.value = false;
    await nextTick();
    triggerAnimations();
    renderChart();
  } catch (error) {
    toast.error("Gagal memuat data pengeluaran");
    loading.value = false;
  }
};

const fetchKelas = async () => {
  try {
    const response = await KelasService.getAll();
    kelasList.value = response.data.data || [];
  } catch (error) {
    console.error("Gagal memuat kelas", error);
  }
};

const formatRupiah = (angka) =>
  new Intl.NumberFormat("id-ID", { maximumFractionDigits: 0 }).format(
    angka || 0,
  );
const formatDate = (date) => (date ? dayjs(date).format("DD MMM YYYY") : "-");
const onImgError = (e) => {
  e.target.style.display = "none"; // Sembunyiin kalau link gambar rusak
};

const getStatusClass = (status) => {
  if (status === "approved") return "bg-emerald-50 text-emerald-600";
  if (status === "pending") return "bg-yellow-50 text-yellow-600";
  if (status === "rejected") return "bg-red-50 text-red-600";
  return "bg-zinc-50 text-zinc-600";
};

// Computed buat Chart Data
const statusData = computed(() => {
  let approved = 0,
    pending = 0,
    rejected = 0;
  pengeluaranList.value.forEach((p) => {
    if (p.status === "approved") approved++;
    else if (p.status === "pending") pending++;
    else if (p.status === "rejected") rejected++;
  });
  return { approved, pending, rejected };
});

const filteredPengeluaran = computed(() => {
  let list = pengeluaranList.value;

  // FIX: Kalau yang login Guru, filter cuma pengeluaran di kelasnya
  if (authStore.role === "guru" && authStore.user?.kelas_id) {
    list = list.filter((p) => p.kelas_id === authStore.user.kelas_id);
  }

  if (searchName.value) {
    const search = searchName.value.toLowerCase();
    list = list.filter(
      (p) =>
        p.judul?.toLowerCase().includes(search) ||
        p.kategori?.toLowerCase().includes(search) ||
        p.deskripsi?.toLowerCase().includes(search),
    );
  }
  if (filterStatus.value !== "Semua") {
    list = list.filter((p) => p.status === filterStatus.value);
  }
  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredPengeluaran.value.length / pageSize)),
);
const pagedPengeluaran = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredPengeluaran.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredPengeluaran.value.length === 0
    ? 0
    : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredPengeluaran.value.length),
);

// --- Render Chart ---
const renderChart = () => {
  if (chartInstance) chartInstance.destroy();

  if (statusChart.value) {
    chartInstance = new Chart(statusChart.value, {
      type: "doughnut",
      data: {
        labels: ["Disetujui", "Pending", "Ditolak"],
        datasets: [
          {
            data: [
              statusData.value.approved,
              statusData.value.pending,
              statusData.value.rejected,
            ],
            backgroundColor: ["#10b981", "#f59e0b", "#ef4444"],
            borderWidth: 0,
            hoverOffset: 4,
          },
        ],
      },
      options: {
        cutout: "70%",
        plugins: { legend: { display: false }, tooltip: { enabled: true } },
      },
    });
  }
};

// --- CRUD Functions ---
const openCreateModal = () => {
  Object.assign(form, {
    kelas_id: "",
    judul: "",
    jumlah: "",
    tanggal: dayjs().format("YYYY-MM-DD"),
    kategori: "",
    bukti_foto: "",
    deskripsi: "",
  });
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const submitForm = async () => {
  submitting.value = true;
  try {
    await PengeluaranService.create(form);
    toast.success("Pengeluaran berhasil diajukan!");
    closeModal();
    fetchPengeluaran();
  } catch (error) {
    const msg = error.response?.data?.message || "Terjadi kesalahan";
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};

const confirmAction = (p, newStatus) => {
  const statusText =
    newStatus === "approved" ? "Approve (Setujui)" : "Reject (Tolak)";
  Swal.fire({
    title: `${statusText} Pengeluaran?`,
    text: `Kamu yakin mau ${statusText} pengajuan dana "${p.judul}"?`,
    icon: "question",
    showCancelButton: true,
    confirmButtonColor: newStatus === "approved" ? "#10b981" : "#ef4444",
    cancelButtonColor: "#64748b",
    confirmButtonText: `Ya, ${statusText}`,
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await PengeluaranService.setujui(p.id, { status: newStatus });
        toast.success(`Pengeluaran berhasil di-${statusText}`);
        fetchPengeluaran();
      } catch (error) {
        const msg =
          error.response?.data?.message || `Gagal ${statusText} pengeluaran`;
        toast.error(msg);
      }
    }
  });
};

onMounted(() => {
  fetchPengeluaran();
  fetchKelas();
});
</script>