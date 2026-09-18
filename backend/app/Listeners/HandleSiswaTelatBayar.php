<?php

namespace App\Listeners;

use App\Events\SiswaTelatBayar;
use App\Models\Notifikasi;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class HandleSiswaTelatBayar
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SiswaTelatBayar $event): void
    {
        // 1. Notifikasi ke siswa
        $this->kirimNotifikasiKeSiswa($event);

        // 2. Notifikasi ke guru
        $this->kirimNotifikasiKeGuru($event);

        // 3. Notifikasi ke bendahara
        $this->kirimNotifikasiKeBendahara($event);
    }

    /**
     * Kirim notifikasi ke siswa
     */
    private function kirimNotifikasiKeSiswa(SiswaTelatBayar $event): void
    {
        $user = $event->siswa->user;

        if ($user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => '⚠️ Peringatan Telat Bayar',
                'pesan' => "Anda belum membayar iuran bulan {$this->getNamaBulan($event->iuran->bulan)} {$event->iuran->tahun}. Telat {$event->hariTelat} hari. Segera lunasi!",
                'tipe' => 'danger',
                'is_read' => false,
                'link' => '/transaksi/saya',
            ]);
        }
    }

    /**
     * Kirim notifikasi ke guru (wali kelas)
     */
    private function kirimNotifikasiKeGuru(SiswaTelatBayar $event): void
    {
        $kelas = $event->siswa->kelas;
        
        // Cari wali kelas
        if ($kelas && $kelas->wali_kelas_id) {
            $guru = User::find($kelas->wali_kelas_id);

            if ($guru) {
                Notifikasi::create([
                    'user_id' => $guru->id,
                    'judul' => '🔴 Siswa Telat Bayar',
                    'pesan' => "Siswa {$event->siswa->user->name} ({$event->siswa->nis}) dari kelas {$kelas->nama} belum membayar iuran bulan {$this->getNamaBulan($event->iuran->bulan)} {$event->iuran->tahun}. Telat {$event->hariTelat} hari.",
                    'tipe' => 'danger',
                    'is_read' => false,
                    'link' => "/siswa/{$event->siswa->id}",
                ]);
            }
        }
    }

    /**
     * Kirim notifikasi ke bendahara
     */
    private function kirimNotifikasiKeBendahara(SiswaTelatBayar $event): void
    {
        // Ambil semua bendahara
        $bendahara = User::where('role_id', 2)->get();

        foreach ($bendahara as $user) {
            Notifikasi::create([
                'user_id' => $user->id,
                'judul' => '🔴 Siswa Telat Bayar',
                'pesan' => "Siswa {$event->siswa->user->name} ({$event->siswa->nis}) dari kelas {$event->siswa->kelas->nama} belum membayar iuran bulan {$this->getNamaBulan($event->iuran->bulan)} {$event->iuran->tahun}. Telat {$event->hariTelat} hari.",
                'tipe' => 'danger',
                'is_read' => false,
                'link' => "/transaksi/pending",
            ]);
        }
    }

    /**
     * Helper: get nama bulan
     */
    private function getNamaBulan(int $bulan): string
    {
        $namaBulan = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $namaBulan[$bulan] ?? $bulan;
    }
}