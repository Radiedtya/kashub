<template>
  <div class="space-y-6">
    <!-- Header -->
    <div
      class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
    >
      <div>
        <p class="text-zinc-400 mt-1 text-sm">
          <template v-if="authStore.role === 'siswa'">
            {{ filteredIuran.length }} tagihan iuran ditemukan
          </template>
          <template v-else>
            {{ filteredIuran.length }} iuran terdaftar
          </template>
        </p>
      </div>
      <button
        v-if="authStore.role === 'guru'"
        @click="openCreateModal"
        class="bg-zinc-900 text-white px-4 py-2.5 rounded-lg font-semibold text-sm hover:bg-zinc-800 transition flex items-center gap-2 w-full sm:w-auto justify-center"
      >
        <BanknotesIcon class="w-4 h-4" />
        Tambah Iuran
      </button>
    </div>

    <!-- Chart Card -->
    <div
      class="iuran-chart-card bg-white border border-zinc-200 rounded-xl p-6 flex flex-col sm:flex-row items-center gap-6"
    >
      <div class="relative w-40 h-40 shrink-0">
        <canvas ref="iuranChart"></canvas>
        <div
          class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
        >
          <span class="text-xl font-bold text-zinc-900">{{
            iuranList.length
          }}</span>
          <span class="text-zinc-400 text-[10px] uppercase tracking-wide"
            >Total Iuran</span
          >
        </div>
      </div>
      <div class="flex-1 w-full grid grid-cols-2 sm:grid-cols-3 gap-4">
        <div
          v-for="(label, index) in chartData.labels"
          :key="label"
          class="flex items-center gap-3 p-3 bg-zinc-50 rounded-lg"
        >
          <span
            class="w-3 h-3 rounded-full"
            :style="{ backgroundColor: chartData.colors[index] }"
          ></span>
          <div class="flex-1">
            <p class="text-xs text-zinc-500">{{ label }}</p>
            <p class="text-lg font-bold text-zinc-800">
              {{ chartData.data[index] }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Card Tabel -->
    <div
      class="iuran-card bg-white border border-zinc-200 rounded-xl overflow-hidden"
    >
      <!-- Filter Row -->
      <div
        class="flex flex-col md:flex-row items-stretch md:items-center gap-3 p-4 border-b border-zinc-100 bg-zinc-50/50"
      >
        <!-- Search (Flex-1) -->
        <div class="relative flex-1 w-full">
          <MagnifyingGlassIcon
            class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
          />
          <input
            v-model="searchBulan"
            type="text"
            placeholder="Cari bulan/tahun..."
            class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 outline-none transition"
          />
        </div>

        <!-- Filter untuk bendahara -->
        <select
          v-if="authStore.role === 'bendahara'"
          v-model="filterKelas"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Kelas</option>
          <option v-for="k in kelasList" :key="k.id" :value="k.id">
            {{ k.nama }}
          </option>
        </select>

        <select
          v-model="filterStatus"
          class="w-full md:w-auto px-3 py-2 border border-zinc-200 rounded-lg text-sm text-zinc-600 focus:ring-1 focus:ring-zinc-900 outline-none bg-white"
        >
          <option value="Semua">Semua Status</option>
          <option v-if="authStore.role === 'guru'" value="true">Aktif</option>
          <option v-if="authStore.role === 'guru'" value="false">
            Nonaktif
          </option>
          <option v-if="authStore.role === 'siswa'" value="belum_bayar">
            Belum Bayar
          </option>
          <option v-if="authStore.role === 'siswa'" value="pending">
            Pending
          </option>
          <option v-if="authStore.role === 'siswa'" value="confirmed">
            Lunas
          </option>
        </select>
      </div>

      <!-- Tabel -->
      <div class="overflow-x-auto">
        <div class="min-w-full">
          <!-- Header Row -->
          <div
            class="grid items-center px-6 py-3 text-zinc-500 text-xs font-semibold uppercase tracking-wider border-b border-zinc-100 bg-white"
            :style="{ gridTemplateColumns: gridTemplate }"
          >
            <div class="text-center">No</div>
            <div v-if="authStore.role === 'guru'">Kelas</div>
            <div>Periode</div>
            <div>Nominal</div>
            <div>Jatuh Tempo</div>
            <div v-if="authStore.role === 'guru'">Dibuat Oleh</div>
            <div v-if="authStore.role === 'siswa'">Status</div>
            <!-- Posisi Aksi ke tengah -->
            <div class="text-center">Aksi</div>
          </div>

          <!-- Skeleton Loading State -->
          <div v-if="loading" class="bg-white">
            <div
              v-for="i in 8"
              :key="i"
              class="grid items-center px-6 py-4 border-b border-zinc-50 animate-pulse"
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <div class="flex justify-center">
                <div class="w-4 h-4 bg-zinc-200 rounded"></div>
              </div>
              <div v-if="authStore.role === 'guru'" class="pr-4 min-w-30">
                <div class="w-20 h-4 bg-zinc-200 rounded"></div>
              </div>
              <div class="pr-4 min-w-30">
                <div class="w-28 h-4 bg-zinc-200 rounded"></div>
              </div>
              <div class="pr-4 min-w-30">
                <div class="w-24 h-4 bg-zinc-200 rounded"></div>
              </div>
              <div class="pr-4 min-w-30">
                <div class="w-16 h-3 bg-zinc-200 rounded"></div>
              </div>
              <div v-if="authStore.role === 'guru'" class="pr-4 min-w-50">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-zinc-200 shrink-0"></div>
                  <div class="flex-1 space-y-2">
                    <div class="w-3/4 h-3 bg-zinc-200 rounded"></div>
                    <div class="w-1/2 h-2 bg-zinc-100 rounded"></div>
                  </div>
                </div>
              </div>
              <div v-if="authStore.role === 'siswa'" class="pr-4 min-w-30">
                <div class="w-16 h-5 bg-zinc-200 rounded-full"></div>
              </div>
              <div class="flex justify-center gap-2">
                <div class="w-8 h-8 rounded-md bg-zinc-200"></div>
                <div class="w-8 h-8 rounded-md bg-zinc-200"></div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div
            v-else-if="filteredIuran.length === 0"
            class="px-6 py-16 text-center text-zinc-400 text-sm"
          >
            Data tidak ditemukan.
          </div>

          <div v-else>
            <div
              v-for="(iuran, index) in pagedIuran"
              :key="iuran.id"
              class="iuran-row grid items-center px-6 py-4 border-b border-zinc-50 last:border-0 hover:bg-zinc-50 transition-colors text-sm"
              :style="{ gridTemplateColumns: gridTemplate }"
            >
              <div class="text-center text-zinc-400 font-medium">
                {{ (currentPage - 1) * pageSize + index + 1 }}
              </div>

              <div v-if="authStore.role === 'guru'" class="pr-4 min-w-30">
                <span
                  class="px-2 py-0.5 bg-zinc-100 text-zinc-600 text-xs rounded font-medium"
                >
                  {{ iuran.kelas?.nama || "-" }}
                </span>
              </div>

              <div class="pr-4 min-w-30">
                <span class="font-semibold text-zinc-800 capitalize"
                  >{{ getMonthName(iuran.bulan) }} {{ iuran.tahun }}</span
                >
              </div>

              <div class="pr-4 min-w-30 font-semibold text-zinc-700">
                Rp {{ formatRupiah(iuran.nominal) }}
              </div>

              <div class="pr-4 text-zinc-500 text-xs min-w-30">
                {{ formatDate(iuran.jatuh_tempo) }}
              </div>

              <!-- Dibuat Oleh + Profile -->
              <div
                v-if="authStore.role === 'guru'"
                class="pr-4 min-w-50"
              >
                <div class="flex items-center gap-3">
                  <img
                    v-if="iuran.created_by?.foto"
                    :src="iuran.created_by.foto"
                    class="w-8 h-8 rounded-full object-cover shrink-0 border border-zinc-100"
                    alt="foto"
                  />
                  <div
                    v-else
                    class="w-8 h-8 rounded-full bg-zinc-100 text-zinc-500 flex items-center justify-center font-semibold text-xs shrink-0"
                  >
                    {{ iuran.created_by?.name?.charAt(0) || "?" }}
                  </div>
                  <div class="flex flex-col">
                    <span class="font-medium text-zinc-700 block">{{
                      iuran.created_by?.name || "-"
                    }}</span>
                    <span class="text-zinc-400 text-[10px]"
                      >Dibuat: {{ formatDate(iuran.created_at) }}</span
                    >
                    <span class="text-zinc-400 text-[10px]"
                      >Diperbarui: {{ formatDate(iuran.updated_at) }}</span
                    >
                  </div>
                </div>
              </div>

              <!-- Status Pembayaran Siswa -->
              <div v-if="authStore.role === 'siswa'" class="pr-4 min-w-30">
                <span
                  class="px-2 py-1 text-xs rounded font-medium capitalize"
                  :class="getPaymentStatus(iuran.id).class"
                >
                  {{ getPaymentStatus(iuran.id).text }}
                </span>
              </div>

              <!-- Aksi (Posisi Tengah) -->
              <div class="flex items-center justify-center gap-1 min-w-30">
                <template v-if="authStore.role === 'guru'">
                  <button
                    @click="openEditModal(iuran)"
                    class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 transition"
                  >
                    <PencilSquareIcon class="w-4 h-4" />
                  </button>
                  <button
                    @click="confirmDelete(iuran)"
                    class="w-8 h-8 flex items-center justify-center rounded-md text-red-500 hover:bg-red-50 transition"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </template>

                <template v-else-if="authStore.role === 'siswa'">
                  <button
                    v-if="
                      getPaymentStatus(iuran.id).status === 'belum_bayar' ||
                      getPaymentStatus(iuran.id).status === 'rejected'
                    "
                    @click="openPayModal(iuran)"
                    class="px-2.5 py-1 rounded-md border border-blue-600 text-blue-600 bg-white hover:bg-blue-50 text-xs font-semibold transition"
                  >
                    Bayar Sekarang
                  </button>

                  <span
                    v-else-if="getPaymentStatus(iuran.id).status === 'pending'"
                    class="text-xs text-zinc-400 italic"
                  >
                    Menunggu Konfirmasi
                  </span>

                  <span v-else class="text-xs text-emerald-600 font-medium">
                    Selesai
                  </span>
                </template>

                <span v-else class="text-xs text-zinc-300 italic"
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
          Showing {{ rangeStart }}–{{ rangeEnd }} of {{ filteredIuran.length }}
        </p>
        <div class="flex items-center gap-1">
          <button
            @click="currentPage = Math.max(1, currentPage - 1)"
            :disabled="currentPage === 1"
            class="w-8 h-8 flex items-center justify-center rounded-md text-zinc-500 hover:bg-zinc-100 disabled:opacity-30 transition"
          >
            <ChevronLeftIcon class="w-4 h-4" />
          </button>
          <button
            v-for="p in totalPages"
            :key="p"
            @click="currentPage = p"
            class="w-8 h-8 flex items-center justify-center rounded-md text-xs font-medium transition"
            :class="
              currentPage === p
                ? 'bg-zinc-900 text-white'
                : 'text-zinc-500 hover:bg-zinc-100'
            "
          >
            {{ p }}
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

    <!-- Modal Form Iuran (Guru) -->
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
                    {{ isEditMode ? "Edit Data Iuran" : "Tambah Iuran Baru" }}
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
                  class="p-6 grid grid-cols-2 gap-x-6 gap-y-4 max-h-[70vh] overflow-y-auto"
                >
                  <!-- Isi Form Guru -->
                  <div class="col-span-2">
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
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none disabled:bg-zinc-50 disabled:cursor-not-allowed disabled:text-zinc-500"
                        :disabled="authStore.role === 'guru'"
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
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Bulan</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <select
                        v-model="form.bulan"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-white appearance-none capitalize"
                      >
                        <option v-for="n in 12" :key="n" :value="n">
                          {{ getMonthName(n) }}
                        </option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Tahun</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.tahun"
                        type="number"
                        min="2000"
                        max="2100"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Nominal (Rp)</label
                    >
                    <div class="relative mt-1">
                      <CurrencyDollarIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.nominal"
                        type="number"
                        min="0"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Jatuh Tempo</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="form.jatuh_tempo"
                        type="date"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>
                  <div class="col-span-2 flex items-center gap-2 mt-2">
                    <input
                      v-model="form.is_active"
                      type="checkbox"
                      id="is_active"
                      class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900"
                    />
                    <label
                      for="is_active"
                      class="text-sm text-zinc-600 font-medium"
                      >Status Aktif (Bisa dibayar siswa)</label
                    >
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
                    {{ submitting ? "Menyimpan..." : "Simpan Data" }}
                  </button>
                </div>
              </DialogPanel>
            </TransitionChild>
          </div>
        </div>
      </Dialog>
    </TransitionRoot>

    <!-- Modal Pembayaran Iuran (Siswa) -->
    <TransitionRoot appear :show="isPayModalOpen" as="template">
      <Dialog as="div" @close="closePayModal" class="relative z-50">
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
                    Pembayaran Iuran {{ payForm.iuran_bulan }}
                  </DialogTitle>
                  <button
                    @click="closePayModal"
                    class="text-zinc-400 hover:text-zinc-600"
                  >
                    <XMarkIcon class="w-5 h-5" />
                  </button>
                </div>
                <form
                  @submit.prevent="submitPayment"
                  class="p-6 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-4 max-h-[70vh] overflow-y-auto"
                >
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Tanggal Bayar</label
                    >
                    <div class="relative mt-1">
                      <CalendarDaysIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="payForm.tanggal_bayar"
                        type="date"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                      />
                    </div>
                  </div>
                  <div>
                    <label class="text-xs text-zinc-600 font-medium"
                      >Jumlah Dibayar (Rp)</label
                    >
                    <div class="relative mt-1">
                      <CurrencyDollarIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="payForm.jumlah"
                        type="number"
                        min="0"
                        required
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none bg-zinc-50"
                        readonly
                      />
                    </div>
                  </div>

                  <!-- Metode Pembayaran (Disabled Input - Cash Only) -->
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Metode Pembayaran</label
                    >
                    <div class="relative mt-1">
                      <BanknotesIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2 z-10"
                      />
                      <input
                        type="text"
                        value="Tunai (Cash)"
                        disabled
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm bg-zinc-50 cursor-not-allowed capitalize"
                      />
                    </div>
                  </div>

                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Link Foto Bukti Bayar (Opsional)</label
                    >
                    <div class="relative mt-1">
                      <LinkIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-1/2 -translate-y-1/2"
                      />
                      <input
                        v-model="payForm.bukti_bayar"
                        type="text"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none"
                        placeholder="https://..."
                      />
                    </div>
                    <div
                      v-if="payForm.bukti_bayar"
                      class="mt-3 flex justify-center border border-zinc-100 rounded-lg p-2 bg-zinc-50"
                    >
                      <img
                        :src="payForm.bukti_bayar"
                        @error="onImgError"
                        class="max-h-40 rounded-md object-contain"
                        alt="preview bukti"
                      />
                    </div>
                  </div>
                  <div class="md:col-span-2">
                    <label class="text-xs text-zinc-600 font-medium"
                      >Keterangan</label
                    >
                    <div class="relative mt-1">
                      <ChatBubbleLeftIcon
                        class="w-4 h-4 text-zinc-400 absolute left-3 top-3"
                      />
                      <textarea
                        v-model="payForm.keterangan"
                        rows="2"
                        class="w-full pl-9 pr-3 py-2 border border-zinc-200 rounded-lg text-sm focus:ring-1 focus:ring-zinc-900 outline-none resize-none"
                        placeholder="Contoh: Bayar tunai di kelas"
                      ></textarea>
                    </div>
                  </div>
                </form>
                <div
                  class="px-6 py-4 bg-zinc-50 border-t border-zinc-100 flex justify-end gap-3"
                >
                  <button
                    type="button"
                    @click="closePayModal"
                    class="px-4 py-2 rounded-lg bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-100 text-sm font-medium transition"
                  >
                    Batal
                  </button>
                  <button
                    @click="submitPayment"
                    :disabled="paying"
                    class="px-4 py-2 rounded-lg bg-zinc-900 text-white hover:bg-zinc-800 text-sm font-medium transition disabled:opacity-50"
                  >
                    {{ paying ? "Mengirim..." : "Kirim Bukti Bayar" }}
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
import { ref, computed, onMounted, reactive, nextTick } from "vue";
import { toast } from "vue3-toastify";
import Swal from "sweetalert2";
import { useAuthStore } from "@/stores/auth";
import IuranService from "@/api/iuran";
import TransaksiService from "@/api/transaksi";
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
  BanknotesIcon,
  PencilSquareIcon,
  TrashIcon,
  ChevronLeftIcon,
  ChevronRightIcon,
  MagnifyingGlassIcon,
  XMarkIcon,
  AcademicCapIcon,
  CalendarDaysIcon,
  CurrencyDollarIcon,
  LinkIcon,
  ChatBubbleLeftIcon,
} from "@heroicons/vue/24/outline";
import dayjs from "dayjs";

Chart.register(...registerables);

const authStore = useAuthStore();
const iuranList = ref([]);
const kelasList = ref([]);
const myTransaksi = ref([]);
const loading = ref(false);
const submitting = ref(false);
const paying = ref(false);

const isModalOpen = ref(false);
const isPayModalOpen = ref(false);
const isEditMode = ref(false);
const editId = ref(null);
const form = reactive({
  kelas_id: "",
  bulan: "",
  tahun: new Date().getFullYear(),
  nominal: "",
  jatuh_tempo: "",
  is_active: true,
});

const payForm = reactive({
  iuran_id: "",
  iuran_bulan: "",
  tanggal_bayar: dayjs().format("YYYY-MM-DD"),
  jumlah: 0,
  metode: "tunai",
  bukti_bayar: "",
  keterangan: "",
});

const searchBulan = ref("");
const filterKelas = ref("Semua");
const filterStatus = ref("Semua");
const currentPage = ref(1);
const pageSize = 15;

// Chart State
const iuranChart = ref(null);
let chartInstance = null;

const gridTemplate = computed(() => {
  if (authStore.role === "guru") {
    return "60px minmax(100px,1fr) minmax(100px,1fr) minmax(100px,1fr) minmax(100px,1fr) minmax(200px,1.5fr) minmax(100px,120px)";
  }
  return "60px minmax(100px,1fr) minmax(100px,1fr) minmax(100px,1fr) minmax(100px,1fr) minmax(100px,120px)";
});

const triggerAnimations = () => {
  anime({
    targets: ".iuran-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    easing: "easeOutQuad",
  });
  anime({
    targets: ".iuran-chart-card",
    translateY: [20, 0],
    opacity: [0, 1],
    duration: 600,
    delay: 100,
    easing: "easeOutQuad",
  });
  anime({
    targets: ".iuran-row",
    translateY: [10, 0],
    opacity: [0, 1],
    delay: anime.stagger(50, { start: 200 }),
    duration: 500,
    easing: "easeOutQuad",
  });
};

const fetchIuran = async () => {
  loading.value = true;
  try {
    let params = {};
    const kelasId = authStore.user?.kelas_id || authStore.user?.siswa?.kelas_id;
    if (authStore.role === "siswa" && kelasId) {
      params.kelas_id = kelasId;
    }

    const response = await IuranService.getAll(params);
    iuranList.value = response.data.data || [];

    if (authStore.role === "siswa") {
      await fetchMyTransaksi();
    }

    loading.value = false;
    await nextTick();
    triggerAnimations();
    renderChart(); // Render chart setelah data ada
  } catch (error) {
    toast.error("Gagal memuat data iuran");
    loading.value = false;
  }
};

const fetchMyTransaksi = async () => {
  try {
    const res = await TransaksiService.getMyTransaksi();
    myTransaksi.value = res.data.data.transaksi || [];
  } catch (error) {
    console.error("Gagal fetch transaksi siswa", error);
  }
};

const fetchKelas = async () => {
  if (authStore.role !== "guru") return;
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
  e.target.style.display = "none";
};
const getMonthName = (monthNum) => {
  const months = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember",
  ];
  return months[monthNum - 1] || "-";
};

const getPaymentStatus = (iuranId) => {
  const trx = myTransaksi.value.find((t) => t.iuran_id === iuranId);
  if (!trx)
    return {
      status: "belum_bayar",
      text: "Belum Bayar",
      class: "bg-zinc-100 text-zinc-600",
    };
  if (trx.status === "confirmed")
    return {
      status: "confirmed",
      text: "Lunas",
      class: "bg-emerald-50 text-emerald-600",
    };
  if (trx.status === "pending")
    return {
      status: "pending",
      text: "Pending",
      class: "bg-yellow-50 text-yellow-600",
    };
  if (trx.status === "rejected")
    return {
      status: "rejected",
      text: "Ditolak",
      class: "bg-red-50 text-red-600",
    };
  return {
    status: "belum_bayar",
    text: "Belum Bayar",
    class: "bg-zinc-100 text-zinc-600",
  };
};

// Computed buat Chart Data
const chartData = computed(() => {
  if (authStore.role === "siswa") {
    let lunas = 0,
      pending = 0,
      belum_bayar = 0;
    iuranList.value.forEach((i) => {
      const status = getPaymentStatus(i.id).status;
      if (status === "confirmed") lunas++;
      else if (status === "pending") pending++;
      else belum_bayar++;
    });
    return {
      labels: ["Lunas", "Pending", "Belum Bayar"],
      data: [lunas, pending, belum_bayar],
      colors: ["#10b981", "#f59e0b", "#e4e4e7"],
    };
  } else {
    let active = 0,
      inactive = 0;
    iuranList.value.forEach((i) => {
      if (i.is_active) active++;
      else inactive++;
    });
    return {
      labels: ["Aktif", "Nonaktif"],
      data: [active, inactive],
      colors: ["#3b82f6", "#e4e4e7"],
    };
  }
});

const renderChart = () => {
  if (chartInstance) chartInstance.destroy();

  if (iuranChart.value) {
    chartInstance = new Chart(iuranChart.value, {
      type: "doughnut",
      data: {
        labels: chartData.value.labels,
        datasets: [
          {
            data: chartData.value.data,
            backgroundColor: chartData.value.colors,
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

const filteredIuran = computed(() => {
  let list = iuranList.value;

  // FIX: Kalau yang login Guru, filter cuma kelasnya dia aja
  if (authStore.role === "guru" && authStore.user?.kelas_id) {
    list = list.filter((i) => i.kelas_id === authStore.user.kelas_id);
  }

  if (searchBulan.value) {
    const search = searchBulan.value.toLowerCase();
    list = list.filter(
      (i) =>
        String(i.tahun).includes(search) ||
        getMonthName(i.bulan).toLowerCase().includes(search),
    );
  }

  if (authStore.role === "guru") {
    if (filterKelas.value !== "Semua")
      list = list.filter(
        (i) => String(i.kelas_id) === String(filterKelas.value),
      );
    if (filterStatus.value !== "Semua")
      list = list.filter((i) => String(i.is_active) === filterStatus.value);
  } else if (authStore.role === "siswa") {
    if (filterStatus.value !== "Semua") {
      list = list.filter(
        (i) => getPaymentStatus(i.id).status === filterStatus.value,
      );
    }
  }
  return list;
});

const totalPages = computed(() =>
  Math.max(1, Math.ceil(filteredIuran.value.length / pageSize)),
);
const pagedIuran = computed(() => {
  const start = (currentPage.value - 1) * pageSize;
  return filteredIuran.value.slice(start, start + pageSize);
});
const rangeStart = computed(() =>
  filteredIuran.value.length === 0 ? 0 : (currentPage.value - 1) * pageSize + 1,
);
const rangeEnd = computed(() =>
  Math.min(currentPage.value * pageSize, filteredIuran.value.length),
);

// --- Guru CRUD Functions ---
const openCreateModal = () => {
  isEditMode.value = false;
  Object.assign(form, {
    // FIX: Auto-fill kelas guru
    kelas_id: authStore.role === "guru" ? authStore.user.kelas_id : "",
    bulan: "",
    tahun: new Date().getFullYear(),
    nominal: "",
    jatuh_tempo: "",
    is_active: true,
  });
  isModalOpen.value = true;
};
const openEditModal = (iuran) => {
  isEditMode.value = true;
  editId.value = iuran.id;
  Object.assign(form, {
    kelas_id: iuran.kelas_id || "",
    bulan: iuran.bulan || "",
    tahun: iuran.tahun || "",
    nominal: iuran.nominal || "",
    jatuh_tempo: iuran.jatuh_tempo
      ? dayjs(iuran.jatuh_tempo).format("YYYY-MM-DD")
      : "",
    is_active: iuran.is_active,
  });
  isModalOpen.value = true;
};
const closeModal = () => (isModalOpen.value = false);
const submitForm = async () => {
  submitting.value = true;
  try {
    if (isEditMode.value) {
      await IuranService.update(editId.value, form);
      toast.success("Data iuran berhasil diperbarui!");
    } else {
      await IuranService.create(form);
      toast.success("Iuran baru berhasil ditambahkan!");
    }
    closeModal();
    fetchIuran();
  } catch (error) {
    const msg = error.response?.data?.message || "Terjadi kesalahan";
    toast.error(msg);
  } finally {
    submitting.value = false;
  }
};
const confirmDelete = (iuran) => {
  Swal.fire({
    title: "Hapus Iuran?",
    text: `Kamu yakin mau hapus iuran bulan ${getMonthName(iuran.bulan)} ${iuran.tahun}?`,
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#64748b",
    confirmButtonText: "Ya, Hapus!",
  }).then(async (result) => {
    if (result.isConfirmed) {
      try {
        await IuranService.delete(iuran.id);
        toast.success("Iuran berhasil dihapus");
        fetchIuran();
      } catch (error) {
        const msg = error.response?.data?.message || "Gagal menghapus iuran";
        toast.error(msg);
      }
    }
  });
};

// --- Siswa Payment Functions ---
const openPayModal = (iuran) => {
  Object.assign(payForm, {
    iuran_id: iuran.id,
    iuran_bulan: `${getMonthName(iuran.bulan)} ${iuran.tahun}`,
    tanggal_bayar: dayjs().format("YYYY-MM-DD"),
    jumlah: iuran.nominal,
    metode: "tunai",
    bukti_bayar: "",
    keterangan: "",
  });
  isPayModalOpen.value = true;
};
const closePayModal = () => {
  isPayModalOpen.value = false;
};
const submitPayment = async () => {
  paying.value = true;
  try {
    await TransaksiService.create(payForm);
    toast.success("Bukti pembayaran berhasil dikirim! Menunggu konfirmasi.");
    closePayModal();
    fetchIuran();
  } catch (error) {
    const msg = error.response?.data?.message || "Gagal mengirim pembayaran";
    toast.error(msg);
  } finally {
    paying.value = false;
  }
};

onMounted(() => {
  fetchIuran();
  fetchKelas();
});
</script>