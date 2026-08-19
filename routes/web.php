<?php

use App\Http\Controllers\Admin\Cms\PostAttachmentController;
use App\Http\Controllers\Admin\Finance\StudentPaymentProofController;
use App\Http\Controllers\Admin\PpdbFileDownloadController;
use App\Http\Controllers\Public\PpdbReceiptDownloadController;
use App\Http\Controllers\Teacher\ExportLearningDraftWordController;
use App\Http\Controllers\Teacher\PrintLearningDraftController;
use App\Http\Controllers\Teacher\PrintSpController;
use App\Livewire\Admin\Academic\ReportCard\Show;
use App\Livewire\Admin\Cms\Post\Form;
use App\Livewire\Admin\Dashboard;
use App\Livewire\Admin\Finance\PaymentCategoryIndex;
use App\Livewire\Admin\Finance\StudentPaymentIndex;
use App\Livewire\Admin\Ppdb\Applications;
use App\Livewire\Admin\Settings\Index as SettingsIndex;
use App\Livewire\Admin\Users\Generator;
use App\Livewire\Admin\Users\Index;
use App\Livewire\Auth\Login;
use App\Livewire\Parent\PaymentsIndex;
use App\Livewire\Profile\Edit;
use App\Livewire\Public\Home;
use App\Livewire\Public\PageViewer;
use App\Livewire\Public\Ppdb\AccessRecovery;
use App\Livewire\Public\Ppdb\ActivateStudentAccount;
use App\Livewire\Public\Ppdb\Announcement;
use App\Livewire\Public\Ppdb\Guide;
use App\Livewire\Public\Ppdb\Register;
use App\Livewire\Public\Ppdb\ReRegistration;
use App\Livewire\Public\Ppdb\Status;
use App\Livewire\SetupWizard;
use App\Livewire\Teacher\Attendances;
use App\Livewire\Teacher\Counseling;
use App\Livewire\Teacher\Exams;
use App\Livewire\Teacher\Extracurriculars;
use App\Livewire\Teacher\Grades;
use App\Livewire\Teacher\Journals;
use App\Livewire\Teacher\LearningAssistant;
use App\Livewire\Teacher\P5;
use App\Livewire\Teacher\Schedules;
use App\Livewire\Tu\StudentLettersIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');
Route::get('/berita', App\Livewire\Public\Blog\Index::class)->name('public.blog.index');
Route::get('/berita/{slug}', App\Livewire\Public\Blog\Show::class)->name('public.blog.show');
Route::get('/p/{slug}', PageViewer::class)->name('public.pages.show');
Route::get('/ppdb', App\Livewire\Public\Ppdb\Index::class)->name('public.ppdb.index');
Route::get('/ppdb/panduan', Guide::class)->name('public.ppdb.guide');
Route::get('/ppdb/pengumuman/{period}', Announcement::class)->name('public.ppdb.announcement');
Route::get('/ppdb/daftar/{period}', Register::class)->name('public.ppdb.register');
Route::get('/ppdb/status', Status::class)->name('public.ppdb.status');
Route::get('/ppdb/aktivasi-akun/{token}', ActivateStudentAccount::class)->name('public.ppdb.student-activation');
Route::get('/ppdb/bukti-pendaftaran/{application}/{token}', PpdbReceiptDownloadController::class)
    ->middleware('signed')
    ->name('public.ppdb.receipt.download');
Route::get('/ppdb/lupa-pin', AccessRecovery::class)->name('public.ppdb.access-recovery');
Route::get('/ppdb/daftar-ulang', ReRegistration::class)->name('public.ppdb.reregistration');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect('/');
})->middleware('auth')->name('logout');

Route::middleware('auth')->get('/profile', Edit::class)->name('profile.edit');

$managementPrefixes = ['admin', 'kepsek', 'wakasek', 'tu'];

$managementRoutes = function () {
    // Dashboard
    Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kurikulum,Wakasek Kesiswaan,Wakasek Sarana,Wakasek Humas,Staf Tata Usaha,Panitia PPDB')
        ->get('/', Dashboard::class)->name('dashboard');

    // Pengaturan & Pengguna
    Route::middleware('active_role:Super Admin,Admin Sekolah')->group(function () {
        Route::get('/settings', SettingsIndex::class)->name('settings');

        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', Index::class)->name('index');
            Route::get('/generator', Generator::class)->name('generator');
        });
    });

    // CMS & Blog Publik
    Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Humas')->prefix('cms')->name('cms.')->group(function () {
        Route::get('/categories', App\Livewire\Admin\Cms\Category\Index::class)->name('categories');
        Route::get('/posts', App\Livewire\Admin\Cms\Post\Index::class)->name('posts');
        Route::get('/posts/create', Form::class)->name('posts.create');
        Route::post('/posts/attachments', PostAttachmentController::class)->name('posts.attachments.store');
        Route::get('/posts/{id}/edit', Form::class)->name('posts.edit');
        Route::get('/pages', App\Livewire\Admin\Cms\Pages\Index::class)->name('pages.index');
        Route::get('/menus', App\Livewire\Admin\Cms\Menus\Index::class)->name('menus.index');
        Route::get('/sliders', App\Livewire\Admin\Cms\Slider\Index::class)->name('sliders');
        Route::get('/sliders/create', App\Livewire\Admin\Cms\Slider\Form::class)->name('sliders.create');
        Route::get('/sliders/{id}/edit', App\Livewire\Admin\Cms\Slider\Form::class)->name('sliders.edit');
    });

    // Penerimaan Peserta Didik Baru
    Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Staf Tata Usaha,Panitia PPDB')
        ->prefix('ppdb')->name('ppdb.')
        ->group(function () {
            Route::get('/', App\Livewire\Admin\Ppdb\Index::class)->name('index');
            Route::get('/applications', Applications::class)->name('applications');
            Route::get('/documents/{id}/download', [PpdbFileDownloadController::class, 'document'])->name('documents.download');
            Route::get('/payments/{id}/proof', [PpdbFileDownloadController::class, 'paymentProof'])->name('payments.proof');
        });

    // Manajemen Master Data
    Route::prefix('master')->name('master.')->group(function () {
        // Master Data Umum
        Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kurikulum,Staf Tata Usaha')->group(function () {
            Route::get('/academic-years', App\Livewire\Admin\Master\AcademicYear\Index::class)->name('academic-years.index');
            Route::get('/majors', App\Livewire\Admin\Master\Major\Index::class)->name('majors.index');
            Route::get('/subjects', App\Livewire\Admin\Master\Subject\Index::class)->name('subjects.index');
            Route::get('/teachers', App\Livewire\Admin\Master\Teacher\Index::class)->name('teachers.index');
            Route::get('/classrooms', App\Livewire\Admin\Master\Classroom\Index::class)->name('classrooms.index');
            Route::get('/extracurriculars', App\Livewire\Admin\Master\Extracurriculars\Index::class)->name('extracurriculars.index');
        });

        // Master Data Kesiswaan
        Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kesiswaan')->group(function () {
            Route::get('/students', App\Livewire\Admin\Master\Student\Index::class)->name('students.index');
            Route::get('/violations', App\Livewire\Admin\Master\ViolationMaster\Index::class)->name('violations.index');
        });
    });

    // Modul Operasional Akademik
    Route::prefix('academic')->name('academic.')->group(function () {
        // Operasional Umum
        Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kurikulum,Staf Tata Usaha')->group(function () {
            Route::get('/rombel', App\Livewire\Admin\Academic\Rombel\Index::class)->name('rombel');
            Route::get('/schedules', App\Livewire\Admin\Academic\Schedule\Index::class)->name('schedules');
        });

        // Nilai & Promosi & Kurikulum
        Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kurikulum')->group(function () {
            Route::get('/curriculum-targets', App\Livewire\Admin\Academic\CurriculumTarget\Index::class)->name('curriculum-targets');
            Route::get('/panduan-kurikulum', App\Livewire\Admin\CurriculumGuide::class)->name('curriculum-guide');
            Route::get('/grades', App\Livewire\Admin\Academic\Grade\Index::class)->name('grades');
            Route::get('/ledger', App\Livewire\Admin\Academic\Ledger\Index::class)->name('ledger');
            Route::get('/promotion', App\Livewire\Admin\Academic\Promotion\Index::class)->name('promotion');
            Route::get('/report-cards', App\Livewire\Admin\Academic\ReportCard\Index::class)->name('report-cards');
            Route::get('/report-cards/{student}', Show::class)->name('report-cards.show');
        });

        // Presensi (Kurikulum & Kesiswaan)
        Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Wakasek Kurikulum,Wakasek Kesiswaan')->group(function () {
            Route::get('/attendances', App\Livewire\Admin\Academic\Attendance\Index::class)->name('attendances');
        });
    });

    // Modul Keuangan & SPP (Admin & TU)
    Route::middleware('active_role:Super Admin,Admin Sekolah,Kepala Sekolah,Staf Tata Usaha')->prefix('finance')->name('finance.')->group(function () {
        Route::get('/categories', PaymentCategoryIndex::class)->name('categories');
        Route::get('/payments', StudentPaymentIndex::class)->name('payments');
        Route::get('/payments/{payment}/proof', StudentPaymentProofController::class)->name('payments.proof');
    });
};

foreach ($managementPrefixes as $prefix) {
    Route::middleware(['auth'])->prefix($prefix)->name($prefix.'.')->group($managementRoutes);
}

// Route khusus Layanan TU
Route::middleware(['auth', 'active_role:Super Admin,Admin Sekolah,Staf Tata Usaha'])->prefix('tu-services')->group(function () {
    Route::get('/dashboard', App\Livewire\Tu\Dashboard::class)->name('tu.services.dashboard');
    Route::get('/letters', StudentLettersIndex::class)->name('tu.letters');
});

// Route Portal Orang Tua / Wali Siswa
Route::middleware(['auth', 'active_role:Orang Tua'])->prefix('ortu')->name('parent.')->group(function () {
    Route::get('/', App\Livewire\Parent\Dashboard::class)->name('dashboard');
    Route::get('/payments', PaymentsIndex::class)->name('payments');
});

// Setup Wizard (restricted to an authenticated active Super Admin)
Route::middleware(['auth', 'active_role:Super Admin'])
    ->get('/setup/wizard', SetupWizard::class)
    ->name('setup.wizard');

Route::middleware('auth')->prefix('guru')->name('guru.')->group(function () {
    Route::middleware('active_role:Guru,Wali Kelas,Guru BK,Pembina Ekstrakurikuler')->group(function () {
        Route::get('/', App\Livewire\Teacher\Dashboard::class)->name('dashboard');
        Route::get('/schedules', Schedules::class)->name('schedules');
        Route::get('/p5', P5::class)->name('p5');
        Route::get('/perangkat-pembelajaran', LearningAssistant::class)->name('learning-assistant');
        Route::get('/perangkat-pembelajaran/{draft}/print', PrintLearningDraftController::class)->name('learning-assistant.print');
        Route::get('/perangkat-pembelajaran/{draft}/export-word', ExportLearningDraftWordController::class)->name('learning-assistant.export-word');
        Route::get('/rekomendasi-diferensiasi', App\Livewire\Teacher\ClassroomDifferentiationIndex::class)->name('differentiation');
        Route::get('/remedial-pengayaan', App\Livewire\Teacher\RemedialEnrichmentIndex::class)->name('remedial-enrichment');
        Route::get('/remedial-pengayaan/print', App\Http\Controllers\Teacher\PrintRemedialEnrichmentController::class)->name('remedial-enrichment.print');
        Route::get('/remedial-pengayaan/export-word', App\Http\Controllers\Teacher\ExportRemedialEnrichmentWordController::class)->name('remedial-enrichment.export-word');
        Route::get('/panduan-perangkat-pembelajaran', App\Livewire\Teacher\LearningGuide::class)->name('learning-guide');
        Route::get('/extracurriculars', Extracurriculars::class)->name('extracurriculars');
        Route::get('/exams', Exams::class)->name('exams');
        Route::get('/counseling', Counseling::class)->name('counseling');
        Route::get('/counseling/sp/{student}', PrintSpController::class)->name('counseling.sp');
    });

    Route::middleware('active_role:Guru')->group(function () {
        Route::get('/journals', Journals::class)->name('journals');
        Route::get('/attendances', Attendances::class)->name('attendances');
        Route::get('/grades', Grades::class)->name('grades');
    });
});

Route::middleware(['auth', 'active_role:Siswa'])->prefix('siswa')->name('siswa.')->group(function () {
    Route::get('/', App\Livewire\Student\Dashboard::class)->name('dashboard');
    Route::get('/schedules', App\Livewire\Student\Schedules::class)->name('schedules');
    Route::get('/attendances', App\Livewire\Student\Attendances::class)->name('attendances');
    Route::get('/grades', App\Livewire\Student\Grades::class)->name('grades');
    Route::get('/exams', App\Livewire\Student\Exams::class)->name('exams');
    Route::get('/counseling', App\Livewire\Student\Counseling::class)->name('counseling');
});
