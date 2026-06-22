<?php

namespace App\Models;

use Database\Factories\OutfitFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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

    protected function imagePath(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (! $value) {
                    return null;
                }

                if (str_starts_with($value, '/storage/teams/')) {
                    $path = substr($value, strlen('/storage/'));
                    try {
                        return Storage::disk('local')->temporaryUrl($path, now()->addDay());
                    } catch (\Throwable $e) {
                        return $value;
                    }
                }

                return $value;
            },
            set: function ($value) {
                if (! $value) {
                    return null;
                }

                $pos = strpos($value, '/storage/teams/');
                if ($pos !== false) {
                    $clean = substr($value, $pos);

                    return explode('?', $clean)[0];
                }

                return $value;
            }
        );
    }

    public function influencer(): BelongsTo
    {
        return $this->belongsTo(Influencer::class);
    }
}
