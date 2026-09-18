<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Guarded;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Guarded(['id'])]
class Keterlambatan extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'hari_telat' => 'integer',
            'denda' => 'decimal:2',
        ];
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function iuran(): BelongsTo
    {
        return $this->belongsTo(Iuran::class);
    }
}
