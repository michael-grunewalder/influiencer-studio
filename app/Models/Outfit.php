<?php

namespace App\Models;

use Database\Factories\OutfitFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Outfit extends Model
{
    /** @use HasFactory<OutfitFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'id',
        'influencer_id',
        'name',
        'top',
        'bottom',
        'hairstyle',
        'full_look_description',
        'image_path',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
        ];
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }
}
