<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\PostCategory;
use App\Models\School;
use App\Models\Slider;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $school = School::first();
        if (! $school) {
            return;
        }

        $admin = User::whereHas('roles', function ($q) {
            $q->where('name', 'Admin Sekolah');
        })->first();

        $authorId = $admin ? $admin->id : User::first()->id;

        // Categories
        $categories = [
            'Pengumuman',
            'Berita Sekolah',
            'Prestasi',
            'Artikel Edukasi',
        ];

        $createdCategories = [];
        foreach ($categories as $cat) {
            $createdCategories[] = PostCategory::firstOrCreate(
                ['school_id' => $school->id, 'slug' => Str::slug($cat)],
                ['name' => $cat]
            );
        }

        // Dummy Posts
        $posts = [
            [
                'title' => 'Penerimaan Peserta Didik Baru (PPDB) Tahun Ajaran Depan Segera Dibuka',
                'category_id' => $createdCategories[0]->id,
                'content' => '<p>Diberitahukan kepada seluruh orang tua / wali murid dan calon siswa, pendaftaran PPDB akan segera dibuka secara online melalui website ini. Silakan persiapkan dokumen-dokumen persyaratan yang dibutuhkan.</p><p>Timeline pendaftaran akan diumumkan lebih lanjut minggu depan.</p>',
            ],
            [
                'title' => 'Siswa Kami Meraih Juara 1 Olimpiade Sains Nasional',
                'category_id' => $createdCategories[2]->id,
                'content' => '<p>Kabar membanggakan datang dari tim ekstrakurikuler sains kita. Perwakilan siswa berhasil memenangkan juara 1 Olimpiade Sains Nasional tingkat Provinsi.</p><p>Terima kasih atas bimbingan para guru dan dukungan penuh dari orang tua murid.</p>',
            ],
            [
                'title' => 'Pentingnya Pendidikan Karakter Anak Sejak Dini',
                'category_id' => $createdCategories[3]->id,
                'content' => '<p>Pendidikan karakter tidak hanya didapatkan di sekolah, melainkan juga di lingkungan keluarga. Pembentukan akhlak dan kemandirian sangat krusial pada usia pertumbuhan.</p><ul><li>Menanamkan nilai-nilai pancasila</li><li>Mengajarkan sopan santun</li><li>Melatih kepemimpinan dalam kegiatan sehari-hari</li></ul>',
            ],
            [
                'title' => 'Peresmian Fasilitas Perpustakaan Digital Sekolah',
                'category_id' => $createdCategories[1]->id,
                'content' => '<p>Bapak Kepala Sekolah meresmikan layanan perpustakaan digital E-Library yang kini dapat diakses oleh seluruh siswa melalui portal SIAKAD. Tersedia ribuan e-book literasi yang bisa dibaca kapan saja dan di mana saja.</p>',
            ],
            [
                'title' => 'Panduan Penggunaan Portal Orang Tua di SIAKAD',
                'category_id' => $createdCategories[0]->id,
                'content' => '<p>Bagi orang tua / wali murid, kini Anda dapat memantau presensi dan nilai rapor siswa langsung dari HP. Silakan masuk dengan akun yang telah dibagikan oleh Wali Kelas masing-masing pada menu Login Portal.</p>',
            ],
        ];

        foreach ($posts as $postData) {
            Post::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'slug' => Str::slug($postData['title']),
                ],
                [
                    'title' => $postData['title'],
                    'content' => $postData['content'],
                    'post_category_id' => $postData['category_id'],
                    'author_id' => $authorId,
                    'status' => 'Published',
                    'published_at' => now()->subDays(rand(1, 10)),
                ]
            );
        }

        // Dummy Sliders
        $sliders = [
            [
                'title' => 'Transformasi Pendidikan Modern',
                'description' => 'Platform SIAKAD terintegrasi untuk mengelola akademik, memantau presensi, hingga pelaporan nilai dengan cepat, transparan, dan akurat.',
                'image_path' => 'sliders/default-1.jpg',
                'button_text' => 'Masuk ke Portal',
                'button_url' => '/login',
                'order' => 1,
            ],
            [
                'title' => 'Lingkungan Belajar Interaktif',
                'description' => 'Fasilitas modern dengan pendekatan Kurikulum Merdeka yang memaksimalkan potensi setiap peserta didik dalam meraih prestasi unggul.',
                'image_path' => 'sliders/default-2.jpg',
                'button_text' => 'Lihat Fitur',
                'button_url' => '/#fitur',
                'order' => 2,
            ],
        ];

        foreach ($sliders as $sliderData) {
            Slider::firstOrCreate(
                [
                    'school_id' => $school->id,
                    'title' => $sliderData['title'],
                ],
                [
                    'description' => $sliderData['description'],
                    'image_path' => $sliderData['image_path'],
                    'button_text' => $sliderData['button_text'],
                    'button_url' => $sliderData['button_url'],
                    'order' => $sliderData['order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
