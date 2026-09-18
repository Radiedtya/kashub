<?php

namespace App\Events;

use App\Models\Siswa;
use App\Models\Iuran;
use App\Models\Keterlambatan;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SiswaTelatBayar
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $siswa;
    public $iuran;
    public $keterlambatan;
    public $hariTelat;

    /**
     * Create a new event instance.
     */
    public function __construct(Siswa $siswa, Iuran $iuran, Keterlambatan $keterlambatan, int $hariTelat)
    {
        $this->siswa = $siswa;
        $this->iuran = $iuran;
        $this->keterlambatan = $keterlambatan;
        $this->hariTelat = $hariTelat;
    }

    /**
     * Get the channels the event should broadcast on.
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('channel-name'),
        ];
    }
}