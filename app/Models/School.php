<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class School extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_setup_completed' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function getLogoUrlAttribute(): ?string
    {
        $value = $this->logo;

        if ($value && Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        if ($value && (str_starts_with($value, 'http://') || str_starts_with($value, 'https://'))) {
            return $value;
        }

        return null;
    }

    /** @return HasMany<AcademicYear, $this> */
    public function academicYears(): HasMany
    {
        return $this->hasMany(AcademicYear::class);
    }

    /** @return HasMany<Major, $this> */
    public function majors(): HasMany
    {
        return $this->hasMany(Major::class);
    }

    /** @return HasMany<Teacher, $this> */
    public function teachers(): HasMany
    {
        return $this->hasMany(Teacher::class);
    }

    /** @return HasMany<Student, $this> */
    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    /** @return HasMany<Subject, $this> */
    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    /** @return HasMany<Classroom, $this> */
    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }
}
