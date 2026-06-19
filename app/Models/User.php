<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPasswordNotification;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\LaravelPasskeys\Models\Concerns\HasPasskeys;
use Spatie\LaravelPasskeys\Models\Concerns\InteractsWithPasskeys;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements HasPasskeys
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, HasUlids, InteractsWithPasskeys, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'email',
        'password',
        'avatar',
        'credits',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'credits' => 'decimal:2',
        ];
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    /**
     * Get the user's full name.
     */
    public function getNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class)->withPivot('role');
    }

    public function hasTeamRole(Team|string $team, string|array $roles): bool
    {
        if ($this->hasPermissionTo('team.view-all')) {
            return true;
        }

        $teamId = $team instanceof Team ? $team->id : $team;
        if (! $teamId) {
            return false;
        }

        $member = $this->teams()->where('team_id', $teamId)->first();
        if (! $member) {
            return false;
        }

        $userRole = $member->pivot->role;

        if (is_array($roles)) {
            return in_array($userRole, $roles);
        }

        if ($roles === 'admin') {
            return $userRole === 'admin';
        }

        if ($roles === 'manage') {
            return in_array($userRole, ['admin', 'manage']);
        }

        if ($roles === 'view') {
            return in_array($userRole, ['admin', 'manage', 'view']);
        }

        return false;
    }

    /**
     * Transfer credits from the user's personal balance to a team's balance.
     */
    public function transferCreditsToTeam(Team $team, float $amount): void
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Transfer amount must be greater than zero.');
        }

        if ((float) $this->credits < $amount) {
            throw new \Exception('Insufficient personal credits.');
        }

        $this->decrement('credits', $amount);
        $team->increment('credits', $amount);

        Transaction::create([
            'user_id' => $this->id,
            'team_id' => $team->id,
            'type' => 'transfer',
            'amount' => $amount,
            'description' => 'Transfer vom persönlichen Wallet zum Team: '.$team->name,
        ]);
    }
}
