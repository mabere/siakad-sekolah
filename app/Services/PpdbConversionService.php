<?php

namespace App\Services;

use App\Mail\PpdbPortalCredentials;
use App\Models\ParentStudentRelation;
use App\Models\PpdbApplication;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PpdbConversionService
{
    public function convert(PpdbApplication $application): Student
    {
        return $this->convertWithCredentials($application)['student'];
    }

    /**
     * @return array{student: Student, credentials: list<array{role: string, username: string, password: string, delivery: string}>}
     */
    public function convertWithCredentials(PpdbApplication $application): array
    {
        /** @var array{student: Student, credentials: list<array{role: string, username: string, password: string, delivery: string}>, mails: list<array{email: string, name: string, role: string, username: string, password: string}>} $result */
        $result = DB::transaction(function () use ($application): array {
            $lockedApplication = PpdbApplication::query()
                ->where('school_id', $application->school_id)
                ->with(['candidate', 'guardians', 'period', 'reRegistration'])
                ->lockForUpdate()
                ->findOrFail($application->id);

            if ($lockedApplication->conversion_status === PpdbApplication::CONVERSION_CONVERTED && $lockedApplication->converted_student_id) {
                return [
                    'student' => Student::query()->where('school_id', $lockedApplication->school_id)->findOrFail($lockedApplication->converted_student_id),
                    'credentials' => [],
                    'mails' => [],
                ];
            }

            if ($lockedApplication->period?->selection_finalized_at === null) {
                throw new \DomainException('Hasil seleksi belum difinalisasi. Konversi siswa belum dapat dilakukan.');
            }

            if ($lockedApplication->selection_status !== PpdbApplication::SELECTION_ACCEPTED) {
                throw new \DomainException('Calon siswa belum ditetapkan diterima.');
            }
            if ($lockedApplication->reregistration_status !== 'verified') {
                throw new \DomainException('Daftar ulang belum diverifikasi.');
            }

            $candidate = $lockedApplication->candidate;
            if ($candidate === null) {
                throw new \DomainException('Data calon siswa belum lengkap untuk konversi.');
            }

            $normalizedNisn = self::normalize($candidate->nisn);
            if ($normalizedNisn !== null && Student::query()
                ->where('school_id', $lockedApplication->school_id)
                ->where(function ($query) use ($normalizedNisn, $candidate): void {
                    $query->where('nisn_normalized', $normalizedNisn)
                        ->orWhere('nisn', $candidate->nisn);
                })
                ->exists()) {
                throw new \DomainException('NISN calon siswa sudah digunakan oleh siswa lain di sekolah ini.');
            }

            if (Student::query()->where('school_id', $lockedApplication->school_id)->where('nis', $lockedApplication->application_number)->exists()) {
                throw new \DomainException('Nomor pendaftaran ini sudah terhubung dengan data siswa lain.');
            }

            $credentials = [];
            $mails = [];
            $studentLogin = 'siswa-'.Str::slug(strtolower($lockedApplication->application_number), '-').'@'.config('ppdb.student_login_domain');
            if (User::query()->where('email', $studentLogin)->exists()) {
                throw new \DomainException('Username siswa sudah ada. Hubungi administrator sebelum mengulang konversi.');
            }
            $studentPassword = Str::random(32);
            $studentUser = User::create([
                'name' => $candidate->name,
                'email' => $studentLogin,
                'password' => Hash::make($studentPassword),
                'school_id' => $lockedApplication->school_id,
                'is_active' => true,
            ]);
            $studentUser->assignRole('Siswa');
            $credentials[] = [
                'role' => 'Siswa',
                'username' => $studentLogin,
                'password' => '(buat melalui aktivasi akun)',
                'delivery' => 'aktivasi dari halaman cek status PPDB',
            ];

            $student = Student::create([
                'school_id' => $lockedApplication->school_id,
                'user_id' => $studentUser->id,
                'nisn' => $candidate->nisn,
                'nisn_normalized' => $normalizedNisn,
                'nis' => $lockedApplication->application_number,
                'name' => $candidate->name,
                'gender' => $candidate->gender,
                'birth_place' => $candidate->birth_place,
                'birth_date' => $candidate->birth_date,
                'address' => $candidate->address,
                'parent_phone' => $lockedApplication->guardians->first()?->phone,
                'status' => 'Aktif',
            ]);

            $guardian = $lockedApplication->guardians->firstWhere('is_primary', true) ?? $lockedApplication->guardians->first();
            if ($guardian?->email) {
                $email = strtolower(trim($guardian->email));
                $parent = User::query()->where('email', $email)->first();

                if ($parent && $parent->school_id !== null && (int) $parent->school_id !== (int) $lockedApplication->school_id) {
                    throw new \DomainException('Email orang tua sudah terhubung ke sekolah lain dan tidak dapat dipakai pada sekolah ini.');
                }

                if (! $parent) {
                    $parentPassword = Str::random(16);
                    $parent = User::create([
                        'name' => $guardian->name,
                        'email' => $email,
                        'password' => Hash::make($parentPassword),
                        'school_id' => $lockedApplication->school_id,
                        'is_active' => true,
                    ]);
                    $parent->assignRole('Orang Tua');
                    $credentials[] = [
                        'role' => 'Orang Tua',
                        'username' => $email,
                        'password' => $parentPassword,
                        'delivery' => 'email dan tampilkan sekali kepada operator',
                    ];
                    $mails[] = [
                        'email' => $email,
                        'name' => $guardian->name,
                        'role' => 'Orang Tua',
                        'username' => $email,
                        'password' => $parentPassword,
                    ];
                } elseif (! $parent->hasRole('Orang Tua')) {
                    $parent->assignRole('Orang Tua');
                }

                if (! $parent->school_id) {
                    $parent->update(['school_id' => $lockedApplication->school_id]);
                }
                ParentStudentRelation::firstOrCreate([
                    'parent_user_id' => $parent->id,
                    'student_id' => $student->id,
                ], [
                    'relationship_type' => $guardian->relationship,
                ]);
            }

            $lockedApplication->update([
                'conversion_status' => PpdbApplication::CONVERSION_CONVERTED,
                'converted_student_id' => $student->id,
                'converted_at' => now(),
            ]);
            app(PpdbApplicationService::class)->audit($lockedApplication, 'application_converted', PpdbApplication::CONVERSION_NOT_READY, PpdbApplication::CONVERSION_CONVERTED, [
                'student_id' => $student->id,
                'student_account_created' => true,
                'parent_account_created' => $mails !== [],
            ]);

            return compact('student', 'credentials', 'mails');
        });

        foreach ($result['mails'] as $mail) {
            try {
                Mail::to($mail['email'])->send(new PpdbPortalCredentials(
                    $mail['name'],
                    $mail['role'],
                    $mail['username'],
                    $mail['password'],
                ));
            } catch (\Throwable $exception) {
                Log::error('PPDB portal credentials email could not be sent.', [
                    'email' => $mail['email'],
                    'exception' => $exception::class,
                ]);
            }
        }

        return [
            'student' => $result['student'],
            'credentials' => $result['credentials'],
        ];
    }

    private static function normalize(?string $value): ?string
    {
        $normalized = preg_replace('/[^0-9a-z]/i', '', trim((string) $value));

        return $normalized === '' ? null : strtolower((string) $normalized);
    }
}
