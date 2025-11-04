<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Counterparty extends Model
{
    protected $fillable = [
        'user_id',
        'inn',
        'name',
        'ogrn',
        'address',
        'raw_response',
    ];

    protected $casts = [
        'raw_response' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
