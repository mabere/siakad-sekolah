<?php

namespace Database\Seeders;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\CurriculumTarget;
use App\Models\Exam;
use App\Models\ExamSubmission;
use App\Models\Grade;
use App\Models\LearningDraft;
use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\Schedule;
use App\Models\School;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SubjectAttendance;
use App\Models\Teacher;
use App\Models\TeachingJournal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class FullSemesterLearningSeeder extends Seeder
{
    /**
     * Run the database seeds for a full 1-semester learning experience.
     */
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
        ]);

        DB::transaction(function () {
            // 1. Setup Sekolah
            $school = School::firstOrCreate(
                ['name' => 'SMA Merdeka Nusantara'],
                [
                    'level' => 'SMA',
                    'status' => 'SWASTA',
                    'npsn' => '20199887',
                    'address' => 'Jl. Pendidikan Karakter No. 45, Kota Edukasi',
                    'phone' => '021-77889900',
                    'email' => 'info@smamerdekanusantara.sch.id',
                    'is_active' => true,
                    'is_setup_completed' => true,
                ]
            );

            $school->update([
                'is_setup_completed' => true,
                'is_active' => true,
            ]);

            // 2. Setup Tahun Ajaran Aktif (Kurikulum Merdeka)
            $academicYear = AcademicYear::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'name' => '2026/2027',
                    'semester' => 'Ganjil',
                ],
                [
                    'curriculum_type' => 'MERDEKA',
                    'is_active' => true,
                ]
            );

            // Deactivate other years if any, ensure this one is active
            AcademicYear::where('school_id', $school->id)->where('id', '!=', $academicYear->id)->update(['is_active' => false]);
            $academicYear->update(['is_active' => true, 'curriculum_type' => 'MERDEKA']);

            // 3. Setup User & Profile Kepala Sekolah
            $kepsekUser = User::firstOrCreate(
                ['email' => 'kepsek@siakad.test'],
                [
                    'name' => 'Dr. H. Bambang Sudirman, M.Pd.',
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                    'is_active' => true,
                ]
            );
            if (! $kepsekUser->hasRole('Kepala Sekolah')) {
                $kepsekUser->assignRole('Kepala Sekolah');
            }

            Teacher::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'user_id' => $kepsekUser->id,
                ],
                [
                    'nip' => '197508122000031001',
                    'name' => 'Dr. H. Bambang Sudirman, M.Pd.',
                    'gender' => 'L',
                    'phone' => '081234567890',
                    'is_active' => true,
                ]
            );

            // 4. Setup User & Profile Guru Pengampu / Wali Kelas
            $guruUser = User::firstOrCreate(
                ['email' => 'guru.biologi@siakad.test'],
                [
                    'name' => 'Dewi Lestari, S.Pd., M.Si.',
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                    'is_active' => true,
                ]
            );
            $guruUser->syncRoles(['Guru', 'Wali Kelas']);

            $guruTeacher = Teacher::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'user_id' => $guruUser->id,
                ],
                [
                    'nip' => '199205152018012003',
                    'name' => 'Dewi Lestari, S.Pd., M.Si.',
                    'gender' => 'P',
                    'phone' => '081298765432',
                    'is_active' => true,
                ]
            );

            // Setup User Wakasek Kurikulum untuk monitoring
            $wakasekUser = User::firstOrCreate(
                ['email' => 'wakasek.kurikulum@siakad.test'],
                [
                    'name' => 'Ahmad Fauzi, M.Pd.',
                    'password' => Hash::make('password'),
                    'school_id' => $school->id,
                    'is_active' => true,
                ]
            );
            if (! $wakasekUser->hasRole('Wakasek Kurikulum')) {
                $wakasekUser->assignRole('Wakasek Kurikulum');
            }

            // 5. Setup Rombongan Belajar (Classroom X-1 Fase E)
            $classroom = Classroom::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'name' => 'X-1',
                ],
                [
                    'grade_level' => '10',
                    'teacher_id' => $guruTeacher->id,
                ]
            );

            // 6. Setup Mata Pelajaran Biologi Fase E
            $subject = Subject::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'code' => 'BIO-10',
                ],
                [
                    'name' => 'Biologi (Fase E)',
                    'type' => 'Wajib',
                ]
            );

            // 7. Setup Jadwal Mengajar 1 Semester (Selasa 07:30 - 09:00)
            $schedule = Schedule::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'classroom_id' => $classroom->id,
                    'subject_id' => $subject->id,
                    'teacher_id' => $guruTeacher->id,
                    'day_of_week' => 'Selasa',
                ],
                [
                    'start_time' => '07:30:00',
                    'end_time' => '09:00:00',
                ]
            );

            // 8. Buat 25 Siswa Kelas 10 Lengkap
            $studentNames = [
                ['Aditya Pratama', 'L'],
                ['Aisyah Nurul Hidayah', 'P'],
                ['Bagas Arya Wicaksono', 'L'],
                ['Bunga Cantika Putri', 'P'],
                ['Dimas Rizky Ramadhan', 'L'],
                ['Dinda Kirana Maheswari', 'P'],
                ['Fajar Sidik Permana', 'L'],
                ['Farah Nabila Zahra', 'P'],
                ['Galih Rakha Pratama', 'L'],
                ['Hana Salma Azzahra', 'P'],
                ['Ilham Tegar Prasetya', 'L'],
                ['Indah Permata Sari', 'P'],
                ['Kevin Jonathan', 'L'],
                ['Lestari Wulandari', 'P'],
                ['Muhammad Farhan Al-Ghifari', 'L'],
                ['Nabila Putri Salsabila', 'P'],
                ['Rafi Ahmad Maulana', 'L'],
                ['Rania Putri Utami', 'P'],
                ['Rian Wahyu Saputra', 'L'],
                ['Salma Fitriani', 'P'],
                ['Satria Bagus Nugroho', 'L'],
                ['Siti Rahmawati', 'P'],
                ['Taufik Hidayat', 'L'],
                ['Zahra Annisa Maharani', 'P'],
                ['Zulfa Nur Kamila', 'P'],
            ];

            $students = [];
            foreach ($studentNames as $idx => $st) {
                $num = $idx + 1;
                $nis = '10'.str_pad((string) $num, 2, '0', STR_PAD_LEFT);
                $nisn = '001000'.str_pad((string) $num, 4, '0', STR_PAD_LEFT);

                $student = Student::firstOrCreate(
                    [
                        'school_id' => $school->id,
                        'nisn' => $nisn,
                    ],
                    [
                        'classroom_id' => $classroom->id,
                        'nis' => $nis,
                        'name' => $st[0],
                        'gender' => $st[1],
                        'status' => 'Aktif',
                    ]
                );
                $student->update(['classroom_id' => $classroom->id]);
                $students[] = $student;
            }

            // 9. Isi 16 Pertemuan Jurnal Mengajar & Presensi Mapel (1 Semester Penuh)
            $topics = [
                1 => ['Keanekaragaman Hayati Indonesia', 'Mengidentifikasi tingkat keanekaragaman gen, jenis, dan ekosistem di Indonesia.', 'Discovery Learning & Observasi'],
                2 => ['Ancaman dan Pelestarian Hayati', 'Analisis ancaman deforestasi dan strategi pelestarian in-situ & ex-situ.', 'Studi Kasus & Diskusi'],
                3 => ['Karakteristik & Peranan Virus', 'Menganalisis ciri umum virus, struktur materi genetik, dan bentuk.', 'Visual Scaffolding & Model 3D'],
                4 => ['Replikasi Virus (Litik & Lisogenik)', 'Membedakan tahapan siklus litik dan siklus lisogenik pada bakteriofag.', 'Diagram Alur & Presentasi'],
                5 => ['Pencegahan & Vaksinasi', 'Prinsip kerja vaksin dan penanggulangan pandemi penyakit menular.', 'Problem Based Learning'],
                6 => ['Bioteknologi Konvensional', 'Eksperimen fermentasi sederhana makanan tradisional (tempe, tapai, yoghurt).', 'Praktikum Sederhana Mandiri'],
                7 => ['Bioteknologi Modern', 'Konsep DNA rekombinan, kultur jaringan, dan organisme transgenik (GMO).', 'Flipped Classroom & Debat'],
                8 => ['Asesmen Sumatif Tengah Semester (STS)', 'Pelaksanaan evaluasi tengah semester modul keanekaragaman hayati dan bioteknologi.', 'CBT Online Assessment'],
                9 => ['Komponen Ekosistem & Aliran Energi', 'Rantai makanan, jaring-jaring makanan, dan piramida biomassa.', 'Analisis Diagram Ekologi'],
                10 => ['Daur Biogeokimia', 'Siklus air, karbon, nitrogen, dan fosfor dalam keseimbangan biosfer.', 'Mind Mapping Interaktif'],
                11 => ['Interaksi Antar Komponen Ekosistem', 'Simbiosis (mutualisme, komensalisme, parasitisme), predasi, dan kompetisi.', 'Observasi Lapangan Sekolah'],
                12 => ['Pemanasan Global & Pencemaran', 'Dampak emisi karbon terhadap perubahan iklim dan kenaikan suhu bumi.', 'Projek Investigasi Lingkungan'],
                13 => ['Pengolahan Limbah Ramah Lingkungan', 'Prinsip 3R (Reduce, Reuse, Recycle) dan pengolahan limbah organik sekolah.', 'Design Thinking Mini Project'],
                14 => ['Praktikum Kompos Organik', 'Aksi nyata pembuatan pupuk kompos dari sampah dedaunan pekarangan sekolah.', 'Praktikum Aksi Nyata'],
                15 => ['Refleksi & Review Akhir Semester', 'Klarifikasi miskonsepsi ekologi dan penguatan materi esensial.', 'Refleksi Diferensiasi 3-Tier'],
                16 => ['Asesmen Sumatif Akhir Semester (SAS)', 'Evaluasi komprehensif pencapaian Capaian Pembelajaran Biologi Fase E.', 'CBT Online Assessment SAS'],
            ];

            $startDate = Carbon::create(2026, 7, 21); // Selasa pertama tahun ajaran baru

            foreach ($topics as $meetingNo => $topicInfo) {
                $meetingDate = $startDate->copy()->addWeeks($meetingNo - 1);

                // Teaching Journal
                TeachingJournal::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYear->id,
                        'schedule_id' => $schedule->id,
                        'meeting_number' => $meetingNo,
                    ],
                    [
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                        'teacher_id' => $guruTeacher->id,
                        'date' => $meetingDate->format('Y-m-d'),
                        'learning_method' => $topicInfo[2],
                        'topic_summary' => $topicInfo[0],
                        'activities' => $topicInfo[1],
                        'student_notes' => 'Siswa berpartisipasi aktif. Kelompok pengayaan mengeksplorasi literatur lebih lanjut.',
                        'obstacles_and_solutions' => 'Beberapa siswa perlu pengulangan konsep siklus biokimia, diberikan materi visual tambahan.',
                        'status' => 'completed',
                    ]
                );

                // Subject Attendance untuk setiap siswa pada pertemuan ini
                foreach ($students as $sIdx => $student) {
                    $status = 'Hadir';
                    $note = null;

                    // Siswa 1-2 sesekali izin/sakit/alpa untuk variasi data empiris presensi
                    if ($sIdx === 0 && $meetingNo === 4) {
                        $status = 'Sakit';
                        $note = 'Demam';
                    } elseif ($sIdx === 0 && $meetingNo === 11) {
                        $status = 'Alpa';
                        $note = 'Tanpa keterangan';
                    } elseif ($sIdx === 1 && $meetingNo === 7) {
                        $status = 'Izin';
                        $note = 'Acara keluarga';
                    } elseif ($sIdx === 2 && $meetingNo === 13) {
                        $status = 'Sakit';
                        $note = 'Flu';
                    }

                    SubjectAttendance::updateOrCreate(
                        [
                            'school_id' => $school->id,
                            'academic_year_id' => $academicYear->id,
                            'schedule_id' => $schedule->id,
                            'student_id' => $student->id,
                            'meeting_number' => $meetingNo,
                        ],
                        [
                            'classroom_id' => $classroom->id,
                            'subject_id' => $subject->id,
                            'teacher_id' => $guruTeacher->id,
                            'date' => $meetingDate->format('Y-m-d'),
                            'status' => $status,
                            'notes' => $note,
                        ]
                    );
                }
            }

            // 10. Rekap Presensi Kelas (Tabel `attendances`)
            foreach ($students as $sIdx => $student) {
                $sick = 0;
                $permission = 0;
                $absent = 0;

                if ($sIdx === 0) {
                    $sick = 1;
                    $absent = 1;
                } elseif ($sIdx === 1) {
                    $permission = 1;
                } elseif ($sIdx === 2) {
                    $sick = 1;
                }

                Attendance::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYear->id,
                        'classroom_id' => $classroom->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'sick' => $sick,
                        'permission' => $permission,
                        'absent' => $absent,
                        'notes' => 'Presensi semester ganjil terintegrasi',
                    ]
                );
            }

            // 11. Rekap Nilai Akademik Lengkap 1 Semester (Tabel `grades`)
            // Distribusi: 5 Siswa Remedial (<75), 14 Siswa Reguler (75-84), 6 Siswa Pengayaan (>=85)
            $gradeProfiles = [
                // 5 Siswa Remedial
                0 => ['tugas' => 62, 'uts' => 58, 'uas' => 64], // Final: 61.3
                1 => ['tugas' => 65, 'uts' => 60, 'uas' => 68], // Final: 64.3
                2 => ['tugas' => 68, 'uts' => 62, 'uas' => 66], // Final: 65.3
                3 => ['tugas' => 70, 'uts' => 65, 'uas' => 70], // Final: 68.3
                4 => ['tugas' => 72, 'uts' => 68, 'uas' => 71], // Final: 70.3

                // 14 Siswa Reguler
                5 => ['tugas' => 78, 'uts' => 76, 'uas' => 78], // 77.3
                6 => ['tugas' => 80, 'uts' => 75, 'uas' => 80], // 78.3
                7 => ['tugas' => 82, 'uts' => 78, 'uas' => 79], // 79.7
                8 => ['tugas' => 80, 'uts' => 80, 'uas' => 82], // 80.7
                9 => ['tugas' => 84, 'uts' => 78, 'uas' => 82], // 81.3
                10 => ['tugas' => 82, 'uts' => 82, 'uas' => 80], // 81.3
                11 => ['tugas' => 85, 'uts' => 80, 'uas' => 82], // 82.3
                12 => ['tugas' => 80, 'uts' => 84, 'uas' => 83], // 82.3
                13 => ['tugas' => 83, 'uts' => 82, 'uas' => 84], // 83.0
                14 => ['tugas' => 85, 'uts' => 82, 'uas' => 83], // 83.3
                15 => ['tugas' => 84, 'uts' => 84, 'uas' => 84], // 84.0
                16 => ['tugas' => 85, 'uts' => 83, 'uas' => 84], // 84.0
                17 => ['tugas' => 86, 'uts' => 82, 'uas' => 84], // 84.0
                18 => ['tugas' => 85, 'uts' => 84, 'uas' => 84], // 84.3

                // 6 Siswa Pengayaan (HOTS)
                19 => ['tugas' => 88, 'uts' => 86, 'uas' => 90], // 88.0
                20 => ['tugas' => 90, 'uts' => 88, 'uas' => 92], // 90.0
                21 => ['tugas' => 92, 'uts' => 90, 'uas' => 94], // 92.0
                22 => ['tugas' => 94, 'uts' => 92, 'uas' => 95], // 93.7
                23 => ['tugas' => 95, 'uts' => 94, 'uas' => 96], // 95.0
                24 => ['tugas' => 98, 'uts' => 96, 'uas' => 98], // 97.3
            ];

            foreach ($students as $sIdx => $student) {
                $scores = $gradeProfiles[$sIdx];
                $finalScore = round(($scores['tugas'] + $scores['uts'] + $scores['uas']) / 3, 2);

                Grade::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYear->id,
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subject->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'tugas' => $scores['tugas'],
                        'uts' => $scores['uts'],
                        'uas' => $scores['uas'],
                        'final_score' => $finalScore,
                    ]
                );
            }

            // 12. Setup Bank Soal CBT & Hasil Ujian Realistis (QuestionBank, Questions, Exam, ExamSubmissions)
            $questionBank = QuestionBank::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'teacher_id' => $guruTeacher->id,
                    'subject_id' => $subject->id,
                    'code' => 'BIO-X-STS',
                ],
                [
                    'title' => 'Bank Soal Asesmen Sumatif Biologi Fase E',
                    'grade_level' => '10',
                    'description' => 'Soal asesmen kurikulum merdeka materi Keanekaragaman Hayati dan Bioteknologi',
                ]
            );

            $sampleQuestions = [
                [
                    'text' => 'Tingkat keanekaragaman hayati yang ditunjukkan oleh perbedaan variasi warna mahkota bunga mawar merah, putih, dan kuning adalah contoh dari...',
                    'type' => 'pg',
                    'options' => [
                        'A. Keanekaragaman tingkat gen',
                        'B. Keanekaragaman tingkat jenis (spesies)',
                        'C. Keanekaragaman tingkat ekosistem',
                        'D. Keanekaragaman tingkat filogenetik',
                    ],
                    'correct' => 'A',
                    'weight' => 20,
                ],
                [
                    'text' => 'Pelestarian Badak Bercula Satu di Taman Nasional Ujung Kulon merupakan salah satu contoh bentuk pelestarian...',
                    'type' => 'pg',
                    'options' => [
                        'A. Ex-situ di habitat buatan',
                        'B. In-situ di habitat aslinya',
                        'C. Budidaya komersial',
                        'D. Introduksi spesies asing',
                    ],
                    'correct' => 'B',
                    'weight' => 20,
                ],
                [
                    'text' => 'Pada siklus replikasi virus, peristiwa penggabungan asam nukleat virus dengan materi genetik sel inang sehingga membentuk profag terjadi pada siklus...',
                    'type' => 'pg',
                    'options' => [
                        'A. Litik',
                        'B. Lisogenik',
                        'C. Sintesis lisis',
                        'D. Replikasi adsorpsi',
                    ],
                    'correct' => 'B',
                    'weight' => 20,
                ],
                [
                    'text' => 'Pemanfaatan mikroorganisme Rhizopus oryzae dalam pengolahan kedelai menjadi tempe merupakan aplikasi dari...',
                    'type' => 'pg',
                    'options' => [
                        'A. Bioteknologi modern berbasis kloning gen',
                        'B. Bioteknologi konvensional berbasis fermentasi',
                        'C. Rekayasa genetika molekuler',
                        'D. Kultur jaringan eksplan',
                    ],
                    'correct' => 'B',
                    'weight' => 20,
                ],
                [
                    'text' => 'Manakah di bawah ini yang merupakan dampak negatif dari pelepasan tanaman transgenik (GMO) tanpa pengawasan ketat terhadap lingkungan?',
                    'type' => 'pg',
                    'options' => [
                        'A. Meningkatkan kebutuhan pestisida kimia',
                        'B. Mengancam keanekaragaman plasma nutfah lokal karena terjadi perpindahan gen',
                        'C. Menurunkan produktivitas pertanian secara drastis',
                        'D. Mempercepat kepunahan mikroba tanah obligat',
                    ],
                    'correct' => 'B',
                    'weight' => 20,
                ],
            ];

            foreach ($sampleQuestions as $q) {
                Question::firstOrCreate(
                    [
                        'question_bank_id' => $questionBank->id,
                        'question_text' => $q['text'],
                    ],
                    [
                        'type' => $q['type'],
                        'options' => $q['options'],
                        'correct_answer' => $q['correct'],
                        'score_weight' => $q['weight'],
                    ]
                );
            }

            // Buat Jadwal Ujian CBT STS
            $exam = Exam::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'teacher_id' => $guruTeacher->id,
                    'subject_id' => $subject->id,
                    'classroom_id' => $classroom->id,
                    'title' => 'Asesmen Sumatif Tengah Semester (STS) Biologi X-1',
                ],
                [
                    'question_bank_id' => $questionBank->id,
                    'duration_minutes' => 60,
                    'start_time' => Carbon::create(2026, 9, 29, 8, 0, 0),
                    'end_time' => Carbon::create(2026, 9, 29, 10, 0, 0),
                    'randomize_questions' => false,
                    'status' => 'Aktif',
                ]
            );

            // Buat Exam Submissions untuk 25 siswa
            foreach ($students as $sIdx => $student) {
                $scores = $gradeProfiles[$sIdx];
                $cbtScore = $scores['uts']; // nilai CBT selaras dengan nilai UTS

                ExamSubmission::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'academic_year_id' => $academicYear->id,
                        'exam_id' => $exam->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'score' => (float) $cbtScore,
                        'total_correct' => (int) round(($cbtScore / 100) * 5),
                        'total_questions' => 5,
                        'answers' => [
                            1 => 'A',
                            2 => 'B',
                            3 => $cbtScore >= 75 ? 'B' : 'A',
                            4 => 'B',
                            5 => $cbtScore >= 85 ? 'B' : 'A',
                        ],
                        'started_at' => Carbon::create(2026, 9, 29, 8, 5, 0),
                        'submitted_at' => Carbon::create(2026, 9, 29, 8, 50, 0),
                        'status' => 'Selesai',
                    ]
                );
            }

            // 13. Setup Target Kurikulum / Bank CP & TP (CurriculumTarget)
            $chapters = [
                1 => [
                    'title' => 'Bab 1: Keanekaragaman Hayati dan Konservasi',
                    'topic' => 'Tingkat Keanekaragaman Hayati & Ancaman Kepunahan',
                    'element' => 'Pemahaman Sains Biologi',
                    'objectives' => "1. Mengidentifikasi keanekaragaman gen, jenis, dan ekosistem di Indonesia.\n2. Menganalisis ancaman deforestasi dan merumuskan upaya konservasi in-situ serta ex-situ.",
                    'model' => 'Discovery Learning & Observasi Lingkungan',
                    'p5' => ['Beriman, Bertakwa kepada Tuhan YME dan Berakhlak Mulia', 'Bernalar Kritis', 'Gotong Royong'],
                    'understanding' => 'Keanekaragaman hayati merupakan modalitas ekologis dan kekayaan hayati yang menopang stabilitas ekosistem Nusantara.',
                    'questions' => ['Mengapa mangga manalagi, arumanis, dan golek berbeda padahal satu spesies?', 'Apa dampak hilangnya predator puncak bagi ekosistem?'],
                    'duration' => '4 JP',
                ],
                2 => [
                    'title' => 'Bab 2: Virus dan Peranannya dalam Kehidupan',
                    'topic' => 'Struktur, Replikasi Litik-Lisogenik & Vaksinasi',
                    'element' => 'Pemahaman Sains Biologi',
                    'objectives' => "1. Menganalisis ciri dan struktur tubuh virus.\n2. Membedakan replikasi litik dan lisogenik.\n3. Mengevaluasi peranan vaksinasi dalam pencegahan penyakit.",
                    'model' => 'Problem-Based Learning & Visual Scaffolding',
                    'p5' => ['Bernalar Kritis', 'Mandiri'],
                    'understanding' => 'Virus memiliki struktur unik yang berada di perbatasan makhluk hidup dan benda mati serta dapat dimanfaatkan dalam bioteknologi medis.',
                    'questions' => ['Bagaimana vaksin melatih sistem imun kita?', 'Mengapa antibiotik tidak efektif melawan infeksi virus?'],
                    'duration' => '4 JP',
                ],
                3 => [
                    'title' => 'Bab 3: Inovasi Bioteknologi Konvensional dan Modern',
                    'topic' => 'Fermentasi Tradisional & Rekayasa Genetika',
                    'element' => 'Keterampilan Proses',
                    'objectives' => "1. Membedakan prinsip bioteknologi konvensional dan modern.\n2. Melakukan praktikum fermentasi sederhana secara higienis dan kolaboratif.\n3. Mengevaluasi isu bioetika GMO.",
                    'model' => 'Project-Based Learning',
                    'p5' => ['Kreatif', 'Gotong Royong', 'Bernalar Kritis'],
                    'understanding' => 'Bioteknologi memadukan sains dan teknologi untuk meningkatkan kesejahteraan pangan dan kesehatan manusia.',
                    'questions' => ['Apa peran mikroorganisme dalam fermentasi tempe dan yoghurt?', 'Bagaimana tanaman transgenik dibuat?'],
                    'duration' => '4 JP',
                ],
                4 => [
                    'title' => 'Bab 4: Ekosistem, Interaksi, dan Perubahan Lingkungan',
                    'topic' => 'Aliran Energi, Daur Biogeokimia & Mitigasi Iklim',
                    'element' => 'Pemahaman Sains Biologi & Aksi Nyata',
                    'objectives' => "1. Menganalisis rantai makanan dan daur biogeokimia.\n2. Merumuskan aksi nyata pengolahan limbah dan pembuatan kompos organik sekolah.",
                    'model' => 'Design Thinking & Aksi Nyata Lingkungan',
                    'p5' => ['Berakhlak Mulia', 'Kreatif', 'Bernalar Kritis'],
                    'understanding' => 'Keseimbangan ekosistem bergantung pada keharmonisan interaksi abiotik-biotik dan pengelolaan limbah berkelanjutan.',
                    'questions' => ['Bagaimana aktivitas manusia memicu pemanasan global?', 'Bagaimana kompos organik menyuburkan tanah?'],
                    'duration' => '4 JP',
                ],
            ];

            foreach ($chapters as $chNum => $ch) {
                CurriculumTarget::updateOrCreate(
                    [
                        'school_id' => $school->id,
                        'subject_name' => 'Biologi (Fase E)',
                        'phase' => 'Fase E',
                        'grade_level' => 10,
                        'chapter_number' => $chNum,
                    ],
                    [
                        'subject_id' => $subject->id,
                        'semester' => '1',
                        'chapter_title' => $ch['title'],
                        'element' => $ch['element'],
                        'topic' => $ch['topic'],
                        'learning_objectives' => $ch['objectives'],
                        'learning_model' => $ch['model'],
                        'p5_dimensions' => $ch['p5'],
                        'meaningful_understanding' => $ch['understanding'],
                        'inquiry_questions' => $ch['questions'],
                        'suggested_duration_jp' => $ch['duration'],
                        'reference_source' => 'Kepka BSKAP No. 032/H/KR/2024',
                        'is_active' => true,
                        'created_by' => $guruUser->id,
                    ]
                );
            }

            // 14. Setup Contoh Learning Draft (Modul Ajar Tersimpan & Terverifikasi)
            $sampleModulContent = [
                'metadata' => [
                    'mata_pelajaran' => 'Biologi (Fase E)',
                    'fase_kelas' => 'Fase E (Kelas 10)',
                    'alokasi_waktu' => '2 x 45 Menit (Pertemuan 1)',
                    'penyusun' => 'Dewi Lestari, S.Pd., M.Si.',
                    'institusi' => 'SMA Merdeka Nusantara',
                    'tahun_ajaran' => '2026/2027 Ganjil',
                ],
                'komponen_inti' => [
                    'tujuan_pembelajaran' => [
                        'Peserta didik mampu mengidentifikasi 3 tingkatan keanekaragaman hayati (gen, jenis, ekosistem) di Indonesia melalui observasi lingkungan.',
                        'Peserta didik mampu menganalisis faktor penyebab hilangnya keanekaragaman hayati dan merumuskan upaya konservasinya.',
                    ],
                    'pemahaman_bermakna' => 'Keanekaragaman hayati adalah modalitas ekologis dan kekayaan hayati Nusantara yang menopang ketahanan pangan, obat-obatan, dan stabilitas biosfer.',
                    'pertanyaan_pemantik' => [
                        'Mengapa mangga manalagi, arumanis, dan golek memiliki rasa dan aroma yang berbeda padahal satu spesies?',
                        'Apa dampak hilangnya satu predator puncak dalam keseimbangan jaring-jaring makanan di hutan tropis kita?',
                    ],
                    'profil_pelajar_pancasila' => [
                        'Beriman, Bertakwa kepada Tuhan YME dan Berakhlak Mulia (Menjaga alam)',
                        'Bernalar Kritis (Menganalisis data kepunahan flora-fauna)',
                        'Gotong Royong (Investigasi kelompok kolaboratif)',
                    ],
                ],
                'kegiatan_pembelajaran' => [
                    'pendahuluan' => [
                        'Guru menyapa, berdoa bersama, dan mengecek kesiapan belajar.',
                        'Apersepsi: Menampilkan video 1 menit kekayaan flora-fauna endemik Indonesia (Komodo, Rafflesia, Orangutan).',
                        'Penyampaian tujuan pembelajaran dan motivasi belajar.',
                    ],
                    'inti_diferensiasi' => [
                        'diferensiasi_konten' => 'Kelompok visual mengamati infografis sebaran bioregion Wallacea dan Weber; Kelompok kinestetik/auditori mengamati spesimen herbarium dan tayangan dokumenter.',
                        'diferensiasi_proses' => 'Siswa kelompok scaffolding didampingi guru membaca kunci determinasi bertahap; Siswa kelompok mahir melakukan investigasi mandiri mengenai laju deforestasi.',
                        'diferensiasi_produk' => 'Siswa bebas memilih unjuk kerja: infografis digital, laporan teks ilmiah ringkas, atau rekaman podcast 3 menit.',
                    ],
                    'penutup' => [
                        'Siswa menyimpulkan materi bersama guru.',
                        'Refleksi pembelajaran menggunakan exit ticket 1 menit.',
                        'Doa penutup dan pengingat materi pertemuan berikutnya.',
                    ],
                ],
                'asesmen' => [
                    'diagnostik' => 'Kuis awal 3 butir pertanyaan pemantik melalui tanya jawab interaktif.',
                    'formatif' => 'Lembar Kerja Peserta Didik (LKPD) 3 tingkat dan observasi keaktifan diskusi kelompok.',
                    'sumatif' => 'Tes tertulis pilihan ganda dan rubrik unjuk kerja infografis/laporan.',
                ],
            ];

            LearningDraft::updateOrCreate(
                [
                    'school_id' => $school->id,
                    'academic_year_id' => $academicYear->id,
                    'teacher_id' => $guruTeacher->id,
                    'schedule_id' => $schedule->id,
                    'document_type' => 'modul_ajar',
                ],
                [
                    'user_id' => $guruUser->id,
                    'input_context' => [
                        'topic' => 'Keanekaragaman Hayati Indonesia & Konservasi',
                        'target_tp' => 'TP 10.1: Mengidentifikasi keanekaragaman hayati Indonesia dan ancaman kepunahan.',
                        'learning_model' => 'Discovery Learning & Observasi Lingkungan',
                        'p5_dimensions' => ['Beriman, Bertakwa kepada Tuhan YME dan Berakhlak Mulia', 'Bernalar Kritis', 'Gotong Royong'],
                    ],
                    'output' => $sampleModulContent,
                    'source' => 'ai_assistant',
                    'version' => 1,
                    'status' => 'approved',
                    'approved_by' => $kepsekUser->id,
                    'approved_at' => Carbon::create(2026, 7, 20, 10, 0, 0),
                ]
            );
        });
    }
}
