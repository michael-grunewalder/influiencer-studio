<?php

namespace App\Models;

use App\Data\InfluencerProperties;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Influencer extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'id',
        'team_id',
        'name',
        'stagename',
        'avatar',
        'bio',
        'properties',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'properties' => InfluencerProperties::class,
        ];
    }

    public function outfits(): HasMany
    {
        return $this->hasMany(Outfit::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    protected static function booted(): void
    {
        static::creating(function (Influencer $influencer) {
            if (! $influencer->team_id) {
                if (session('active_team_id')) {
                    $influencer->team_id = session('active_team_id');
                } elseif (auth()->check()) {
                    $user = auth()->user();
                    $team = $user->teams()->first();
                    if (! $team) {
                        $team = Team::create(['name' => $user->last_name ? $user->last_name."'s Team" : 'Personal Team']);
                        $user->teams()->attach($team);
                    }
                    $influencer->team_id = $team->id;
                } else {
                    $team = Team::first() ?: Team::create(['name' => 'Default Team']);
                    $influencer->team_id = $team->id;
                }
            }
        });
    }
}
