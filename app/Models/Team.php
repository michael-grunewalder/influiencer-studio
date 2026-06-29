<?php

namespace App\Models;

use Database\Factories\TeamFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    /** @use HasFactory<TeamFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'id',
        'name',
        'description',
        'fal_api_key',
        'claude_api_key',
        'credits',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
            'credits' => 'decimal:2',
        ];
    }

    public function hasFalApiKey(): bool
    {
        return ! empty($this->fal_api_key);
    }

    public function hasClaudeApiKey(): bool
    {
        return ! empty($this->claude_api_key);
    }

    public function hasCreditsFor(int $imageCount): bool
    {
        $required = $imageCount * 0.35;

        return (float) $this->credits >= $required;
    }

    public function chargeForImages(int $imageCount): void
    {
        $cost = $imageCount * 0.35;
        $this->decrement('credits', $cost);

        Transaction::create([
            'user_id' => auth()->id(),
            'team_id' => $this->id,
            'type' => 'spending',
            'amount' => -$cost,
            'description' => 'Medien-Generierung ('.$imageCount.' Bild'.($imageCount > 1 ? 'er' : '').')',
        ]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role');
    }

    public function influencers(): HasMany
    {
        return $this->hasMany(Influencer::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(TeamAsset::class);
    }
}
