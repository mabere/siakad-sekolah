<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'avatar_url',
        'password',
        'school_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'two_factor_secret',
        'two_factor_recovery_codes',
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
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function getAvatarUrlAttribute(?string $value): string
    {
        if ($value && Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        if ($value && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
            return $value;
        }

        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=0D9488&color=ffffff&bold=true';
    }

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return HasOne<Teacher, $this> */
    public function teacher(): HasOne
    {
        return $this->hasOne(Teacher::class);
    }

    /** @return HasOne<Student, $this> */
    public function student(): HasOne
    {
        return $this->hasOne(Student::class);
    }
}
