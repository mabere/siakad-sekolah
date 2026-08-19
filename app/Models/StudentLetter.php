<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentLetter extends Model
{
    protected $fillable = [
        'school_id',
        'student_id',
        'letter_number',
        'letter_type',
        'purpose',
        'status',
        'issued_by',
        'issued_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
    ];

    /** @return BelongsTo<School, $this> */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** @return BelongsTo<Student, $this> */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /** @return BelongsTo<User, $this> */
    public function issuer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }

    public function getTypeNameAttribute(): string
    {
        return match ($this->getAttribute('letter_type')) {
            'surat_keterangan_aktif' => 'Surat Keterangan Siswa Aktif',
            'surat_berkelakuan_baik' => 'Surat Keterangan Berkelakuan Baik',
            'surat_pindah_sekolah' => 'Surat Keterangan Pindah Sekolah',
            default => 'Surat Keterangan',
        };
    }
}
