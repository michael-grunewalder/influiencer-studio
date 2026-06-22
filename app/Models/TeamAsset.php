<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamAsset extends Model
{
    use HasUlids;

    protected $fillable = [
        'id',
        'team_id',
        'local_url',
        'remote_url',
        'mime_type',
        'purpose',
    ];

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
