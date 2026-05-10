<?php
// © Atia Hegazy — atiaeno.com

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
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
        ];
    }

    public function links(): HasMany
    {
        return $this->hasMany(Link::class);
    }

    public function affiliate(): HasOne
    {
        return $this->hasOne(Affiliate::class);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function reviewedReports(): HasMany
    {
        return $this->hasMany(Report::class, 'reviewed_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}
