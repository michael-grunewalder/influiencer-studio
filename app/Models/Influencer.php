<?php

namespace App\Models;

use App\Data\InfluencerProperties;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Influencer extends Model
{
    use HasUlids;

    protected $fillable = [
        'id',
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
}
