<?php

namespace App\Models;

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
    ];

    protected function casts(): array
    {
        return [
            'id' => 'string',
        ];
    }
}
