<?php

namespace App\Services\Curriculum;

use App\Models\CurriculumTarget;
use App\Models\Subject;

final class CurriculumBank
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function getTopicsForSubjectAndFase(string $subjectName, string $fase, ?int $schoolId = null): array
    {
        $normalizedSubject = strtolower(trim($subjectName));
        $normalizedFase = strtoupper(trim($fase));

        // 1. Try to read from school database first if schoolId is provided
        if ($schoolId) {
            $dbTargets = CurriculumTarget::query()
                ->where('school_id', $schoolId)
                ->where('is_active', true)
                ->forSubject($subjectName)
                ->orderBy('chapter_number')
                ->get();

            if ($dbTargets->isNotEmpty()) {
                $topics = [];
                foreach ($dbTargets as $target) {
                    $key = 'db-target-'.$target->id;
                    $topics[$key] = [
                        'id' => $key,
                        'db_id' => $target->id,
                        'chapter_number' => (int) $target->chapter_number,
                        'chapter_title' => (string) $target->chapter_title,
                        'elements' => is_string($target->element) ? array_map('trim', explode(',', $target->element)) : [],
                        'topic' => (string) $target->topic,
                        'learning_objectives' => (string) $target->learning_objectives,
                        'learning_model' => (string) ($target->learning_model ?? 'Problem-Based Learning (PBL)'),
                        'p5_dimensions' => (array) ($target->p5_dimensions ?? []),
                        'meaningful_understanding' => (string) ($target->meaningful_understanding ?? ''),
                        'inquiry_questions' => (array) ($target->inquiry_questions ?? []),
                        'suggested_duration_jp' => (string) ($target->suggested_duration_jp ?? '6 JP'),
                        'reference' => (string) ($target->reference_source ?? 'Kurikulum Satuan Pendidikan (KSP) Sekolah'),
                    ];
                }

                return $topics;
            }
        }

        // 2. Built-in National Presets Fallback (BSKAP No. 032/H/KR/2024)
        if (str_contains($normalizedFase, 'E') || str_contains($normalizedFase, '10')) {
            if (str_contains($normalizedSubject, 'indonesia')) {
                return self::bahasaIndonesiaFaseE();
            }

            if (str_contains($normalizedSubject, 'matematika') || str_contains($normalizedSubject, 'mtk')) {
                return self::matematikaFaseE();
            }

            if (str_contains($normalizedSubject, 'pancasila') || str_contains($normalizedSubject, 'pkn') || str_contains($normalizedSubject, 'ppkn')) {
                return self::pendidikanPancasilaFaseE();
            }

            if (str_contains($normalizedSubject, 'informatika') || str_contains($normalizedSubject, 'komputer')) {
                return self::informatikaFaseE();
            }

            if (str_contains($normalizedSubject, 'inggris') || str_contains($normalizedSubject, 'english')) {
                return self::bahasaInggrisFaseE();
            }
        }

        return self::genericSubjectTopics($subjectName, $fase);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function bahasaIndonesiaFaseE(): array
    {
        return [
            'bindo-e-bab1' => [
                'id' => 'bindo-e-bab1',
                'chapter_number' => 1,
                'chapter_title' => 'Bab 1: Mengungkap Fakta Alam Secara Objektif (Teks Laporan Hasil Observasi)',
                'elements' => ['Membaca dan Memirsa', 'Menulis'],
                'topic' => 'Teks Laporan Hasil Observasi (LHO)',
                'learning_objectives' => "1. Peserta didik mampu mengevaluasi informasi akurat dan fakta dalam teks LHO.\n2. Peserta didik mampu mengidentifikasi struktur teks (pernyataan umum, deskripsi bagian, deskripsi manfaat) dan kaidah kebahasaan (kata kerja material, verba relasional, kalimat definisi, kalimat deskripsi).\n3. Peserta didik mampu menyusun draf teks LHO berdasarkan pengamatan lingkungan sekolah atau lingkungan sekitar secara objektif.",
                'learning_model' => 'Problem-Based Learning (PBL) & Pedagogi Genre',
                'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong', 'Mandiri'],
                'meaningful_understanding' => 'Observasi berbasis data dan penyusunan fakta yang objektif melatih nalar kritis dan kejujuran intelektual dalam memahami realitas alam dan sosial.',
                'inquiry_questions' => [
                    'Bagaimana cara membedakan fakta ilmiah objektif dengan opini atau kabar burung?',
                    'Mengapa sistematika klasifikasi dan deskripsi bagian penting dalam laporan ilmiah?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
            'bindo-e-bab2' => [
                'id' => 'bindo-e-bab2',
                'chapter_number' => 2,
                'chapter_title' => 'Bab 2: Mengungkapkan Kritik Lewat Senyuman (Teks Anekdot & Lawakan Tunggal)',
                'elements' => ['Menyimak', 'Berbicara dan Mempresentasikan', 'Menulis'],
                'topic' => 'Teks Anekdot dan Lawakan Tunggal (Stand-Up Comedy)',
                'learning_objectives' => "1. Peserta didik mampu mengevaluasi gagasan, pesan tersirat, dan struktur anekdot (abstraksi, orientasi, krisis, reaksi, koda).\n2. Peserta didik mampu mengolah dan menyajikan kritik sosial melalui pertunjukan lawakan tunggal (stand-up comedy) secara santun dan etis.\n3. Peserta didik mampu menulis teks anekdot bertema fenomena sosial atau lingkungan dengan kaidah kebahasaan yang tepat.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Kreatif', 'Bernalar Kritis', 'Berkebinekaan Global'],
                'meaningful_understanding' => 'Kritik konstruktif yang disampaikan secara kreatif dan santun melalui humor dapat membangun kepedulian sosial tanpa menyinggung martabat orang lain.',
                'inquiry_questions' => [
                    'Bagaimana menyampaikan kritik terhadap isu publik secara persuasif dan menghibur tanpa ujaran kebencian?',
                    'Apa perbedaan mendasar antara teks anekdot dengan lelucon biasa?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
            'bindo-e-bab3' => [
                'id' => 'bindo-e-bab3',
                'chapter_number' => 3,
                'chapter_title' => 'Bab 3: Menyusuri Nilai Cerita Lintas Zaman (Teks Hikayat & Cerpen)',
                'elements' => ['Membaca dan Memirsa', 'Menulis'],
                'topic' => 'Kajian Nilai Kehidupan dalam Hikayat dan Cerpen',
                'learning_objectives' => "1. Peserta didik mampu mengidentifikasi karakteristik hikayat (kemustahilan, kesaktian, arkais, istanasentris) dan membandingkannya dengan cerpen modern.\n2. Peserta didik mampu menganalisis nilai-nilai kehidupan (moral, religius, sosial, budaya, edukasi) dalam hikayat dan relevansinya masa kini.\n3. Peserta didik mampu mengalihwahanakan teks hikayat menjadi cerpen modern berlatar kearifan lokal.",
                'learning_model' => 'Discovery Learning & Pedagogi Genre',
                'p5_dimensions' => ['Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia', 'Berkebinekaan Global', 'Kreatif'],
                'meaningful_understanding' => 'Nilai-nilai luhur dan kearifan masa lalu yang termuat dalam sastra klasik tetap relevan sebagai kompas moral dan identitas generasi modern.',
                'inquiry_questions' => [
                    'Mengapa nilai moral dalam cerita masa lampau (hikayat) masih relevan dengan kehidupan remaja saat ini?',
                    'Bagaimana cara mentransformasikan cerita klasik agar menarik bagi pembaca masa kini?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
            'bindo-e-bab4' => [
                'id' => 'bindo-e-bab4',
                'chapter_number' => 4,
                'chapter_title' => 'Bab 4: Belajar Menjadi Negosiator Ulung (Teks Negosiasi & Surat Penawaran)',
                'elements' => ['Menyimak', 'Berbicara dan Mempresentasikan', 'Menulis'],
                'topic' => 'Teks Negosiasi dan Komunikasi Bisnis/Sosial',
                'learning_objectives' => "1. Peserta didik mampu menganalisis faktor penentu keberhasilan negosiasi serta struktur orientasi, pengajuan, penawaran, dan persetujuan.\n2. Peserta didik mampu mempraktikkan simulasi negosiasi lisan (win-win solution) dengan tuturan persuasif dan santun.\n3. Peserta didik mampu menyusun surat penawaran barang/jasa resmi yang memenuhi kaidah korespondensi bisnis.",
                'learning_model' => 'Problem-Based Learning (PBL) & Role Playing',
                'p5_dimensions' => ['Gotong Royong', 'Bernalar Kritis', 'Mandiri'],
                'meaningful_understanding' => 'Keterampilan bernegosiasi secara etis dan kolaboratif memungkinkan penyelesaian perbedaan kepentingan dengan saling menguntungkan (win-win solution).',
                'inquiry_questions' => [
                    'Apa strategi terbaik untuk mencapai kesepakatan ketika kedua pihak memiliki kepentingan yang bertolak belakang?',
                    'Bagaimana memilih kata persuasif yang efektif namun tetap menjunjung rasa hormat?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
            'bindo-e-bab5' => [
                'id' => 'bindo-e-bab5',
                'chapter_number' => 5,
                'chapter_title' => 'Bab 5: Memetik Keteladanan Tokoh Bangsa (Teks Biografi)',
                'elements' => ['Membaca dan Memirsa', 'Menulis'],
                'topic' => 'Teks Biografi Tokoh Inspiratif & Keteladanan Hidup',
                'learning_objectives' => "1. Peserta didik mampu mengidentifikasi peristiwa penting, motivasi hidup, dan karakter unggul tokoh dalam teks biografi.\n2. Peserta didik mampu menganalisis gaya penceritaan naratif dan penggunaan kata ganti pronomina serta konjungsi temporal.\n3. Peserta didik mampu menulis biografi singkat seorang tokoh lokal atau sosok teladan di lingkungannya secara padu dan runtut.",
                'learning_model' => 'Inquiry Learning & Pedagogi Genre',
                'p5_dimensions' => ['Mandiri', 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia', 'Bernalar Kritis'],
                'meaningful_understanding' => 'Mempelajari perjuangan hidup dan keteladanan tokoh menumbuhkan daya juang, integritas, dan inspirasi dalam merancang masa depan pribadi.',
                'inquiry_questions' => [
                    'Nilai karakter apa yang membedakan seorang tokoh besar dari orang biasa dalam menghadapi tantangan hidup?',
                    'Bagaimana cara merekonstruksi kisah hidup seseorang agar mampu menginspirasi pembaca?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
            'bindo-e-bab6' => [
                'id' => 'bindo-e-bab6',
                'chapter_number' => 6,
                'chapter_title' => 'Bab 6: Berkarya Melalui Puisi (Apresiasi, Penulisan & Musikalisasi Puisi)',
                'elements' => ['Membaca dan Memirsa', 'Berbicara dan Mempresentasikan', 'Menulis'],
                'topic' => 'Teks Puisi, Gaya Bahasa (Majas), dan Penampilan Ekspresif',
                'learning_objectives' => "1. Peserta didik mampu menelaah tema, suasana, nada, imaji, diksi, dan majas dalam puisi modern.\n2. Peserta didik mampu mentransformasikan teks cerita/artikel nonfiksi menjadi karya puisi yang bermakna dan berestetika.\n3. Peserta didik mampu membacakan dan memusikalisasikan puisi karya sendiri atau karya penyair ternama dengan intonasi, penghayatan, dan ekspresi yang tepat.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Kreatif', 'Mandiri', 'Berkebinekaan Global'],
                'meaningful_understanding' => 'Puisi adalah sarana ekspresi rasa, empati kemanusiaan, dan kecerdasan berbahasa yang memperkaya kepekaan batin dan daya cipta.',
                'inquiry_questions' => [
                    'Bagaimana cara memadukan diksi dan gaya bahasa agar pesan perasaan dalam puisi tersampaikan secara mendalam?',
                    'Apa yang membuat sebuah pembacaan atau musikalisasi puisi mampu menyentuh hati pendengar?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Teks Utama B. Indonesia Kelas X',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function matematikaFaseE(): array
    {
        return [
            'mat-e-bab1' => [
                'id' => 'mat-e-bab1',
                'chapter_number' => 1,
                'chapter_title' => 'Bab 1: Eksponen dan Logaritma',
                'elements' => ['Bilangan', 'Aljabar dan Fungsi'],
                'topic' => 'Eksponen dan Logaritma dalam Konteks Pertumbuhan & Peluruhan',
                'learning_objectives' => "1. Peserta didik mampu menggeneralisasi sifat-sifat bilangan berpangkat (eksponen) dan bentuk akar.\n2. Peserta didik mampu menerapkan konsep eksponen untuk memodelkan fenomena pertumbuhan populasi atau peluruhan zat radioaktif.\n3. Peserta didik mampu menyelesaikan masalah kontekstual menggunakan sifat-sifat operasi logaritma.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
                'meaningful_understanding' => 'Pola eksponensial dan logaritmik membantu memodelkan perubahan alam dan finansial secara presisi.',
                'inquiry_questions' => [
                    'Bagaimana menghitung waktu pelipatgandaan suatu investasi atau penyebaran virus?',
                    'Mengapa skala gempa bumi (Richter) menggunakan konsep logaritma?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Matematika Kelas X',
            ],
            'mat-e-bab2' => [
                'id' => 'mat-e-bab2',
                'chapter_number' => 2,
                'chapter_title' => 'Bab 2: Barisan dan Deret (Aritmetika & Geometri)',
                'elements' => ['Aljabar dan Fungsi'],
                'topic' => 'Barisan, Deret Aritmetika, dan Deret Geometri Tak Hingga',
                'learning_objectives' => "1. Peserta didik mampu menentukan suku ke-n dan jumlah n suku pertama barisan/deret aritmetika.\n2. Peserta didik mampu menganalisis pola deret geometri serta deret geometri tak hingga konvergen.\n3. Peserta didik mampu memecahkan masalah kontekstual terkait bunga majemuk, anuitas, dan pertumbuhan berulang.",
                'learning_model' => 'Discovery Learning',
                'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong'],
                'meaningful_understanding' => 'Pola keteraturan deret angka menjadi dasar perhitungan finansial dan optimasi proses berulang.',
                'inquiry_questions' => [
                    'Bagaimana menentukan total tabungan dengan skema bunga periodik?',
                    'Kapan deret geometri tak terhingga menghasilkan nilai terhingga?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Matematika Kelas X',
            ],
            'mat-e-bab3' => [
                'id' => 'mat-e-bab3',
                'chapter_number' => 3,
                'chapter_title' => 'Bab 3: Vektor dan Trigonometri Dasar',
                'elements' => ['Geometri dan Pengukuran'],
                'topic' => 'Operasi Vektor di R2 dan Perbandingan Trigonometri Segitiga Siku-Siku',
                'learning_objectives' => "1. Peserta didik mampu menyatakan besaran skalar dan vektor serta melakukan operasi penjumlahan dan pengurangan vektor.\n2. Peserta didik mampu menentukan perbandingan trigonometri (sin, cos, tan) pada segitiga siku-siku.\n3. Peserta didik mampu menyelesaikan masalah tinggi objek atau jarak tidak terjangkau menggunakan sudut elevasi/depresi.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Kreatif'],
                'meaningful_understanding' => 'Trigonometri dan vektor memungkinkan pengukuran jarak dan gaya secara akurat tanpa harus menyentuh objek secara fisik.',
                'inquiry_questions' => [
                    'Bagaimana navigator kapal atau pesawat menentukan arah haluan dengan pengaruh angin?',
                    'Bagaimana mengukur tinggi gedung tinggi tanpa memanjatnya?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Matematika Kelas X',
            ],
            'mat-e-bab4' => [
                'id' => 'mat-e-bab4',
                'chapter_number' => 4,
                'chapter_title' => 'Bab 4: Sistem Persamaan dan Pertidaksamaan Linear',
                'elements' => ['Aljabar dan Fungsi'],
                'topic' => 'SPLDV, SPLTV, dan Sistem Pertidaksamaan Linear Dua Variabel (SPtLDV)',
                'learning_objectives' => "1. Peserta didik mampu memodelkan masalah sehari-hari ke dalam SPLTV.\n2. Peserta didik mampu menentukan daerah penyelesaian sistem pertidaksamaan linear dua variabel secara grafik.\n3. Peserta didik mampu mengoptimasi solusi biaya/keuntungan dasar menggunakan titik pojok daerah penyelesaian.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong', 'Mandiri'],
                'meaningful_understanding' => 'Sistem persamaan linear membantu pengambilan keputusan multi-faktor dalam alokasi sumber daya terbatas.',
                'inquiry_questions' => [
                    'Bagaimana menentukan kombinasi produksi terbaik agar keuntungan maksimal?',
                    'Kapan sistem persamaan memiliki banyak solusi atau tidak memiliki solusi sama sekali?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Matematika Kelas X',
            ],
            'mat-e-bab5' => [
                'id' => 'mat-e-bab5',
                'chapter_number' => 5,
                'chapter_title' => 'Bab 5: Analisis Data dan Peluang',
                'elements' => ['Analisis Data dan Peluang'],
                'topic' => 'Ukuran Pemusatan, Ukuran Penempatan, Ukuran Penyebaran, dan Peluang Kejadian Majemuk',
                'learning_objectives' => "1. Peserta didik mampu menyajikan dan menginterpretasikan data menggunakan diagram pencar, boxplot, dan histogram.\n2. Peserta didik mampu menghitung dan membandingkan mean, median, modus, kuartil, dan standar deviasi data tunggal/kelompok.\n3. Peserta didik mampu menentukan peluang kejadian saling lepas dan saling bebas dalam pengambilan keputusan berbasis risiko.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
                'meaningful_understanding' => 'Literasi data dan probabilitas membimbing kita berpikir objektif dan tidak mudah tertipu bias statistik.',
                'inquiry_questions' => [
                    'Bagaimana menyimpulkan tren dari sekelompok data yang bervariasi tinggi?',
                    'Bagaimana peluang membantu mitigasi risiko asuransi dan perkiraan cuaca?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Matematika Kelas X',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function pendidikanPancasilaFaseE(): array
    {
        return [
            'ppkn-e-bab1' => [
                'id' => 'ppkn-e-bab1',
                'chapter_number' => 1,
                'chapter_title' => 'Bab 1: Pancasila sebagai Pemersatu Bangsa',
                'elements' => ['Pancasila'],
                'topic' => 'Kedudukan Pancasila sebagai Ideologi Terbuka dan Panduan Hidup Berbangsa',
                'learning_objectives' => "1. Peserta didik mampu menganalisis kedudukan dan fungsi Pancasila sebagai dasar negara, ideologi negara, dan pandangan hidup bangsa.\n2. Peserta didik mampu mengidentifikasi peluang dan tantangan penerapan nilai-nilai Pancasila di era digital.\n3. Peserta didik mampu menginisiasi aksi nyata bergotong royong dalam menyelesaikan masalah di lingkungan sekolah.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia', 'Gotong Royong', 'Bernalar Kritis'],
                'meaningful_understanding' => 'Pancasila bukan sekadar teks sejarah, melainkan etika hidup bersama yang dinamis dalam menjaga keutuhan Indonesia.',
                'inquiry_questions' => [
                    'Bagaimana mengamalkan nilai Pancasila di ruang publik digital dan media sosial?',
                    'Mengapa ideologi Pancasila mampu bertahan di tengah arus globalisasi?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Pendidikan Pancasila Kelas X',
            ],
            'ppkn-e-bab2' => [
                'id' => 'ppkn-e-bab2',
                'chapter_number' => 2,
                'chapter_title' => 'Bab 2: Membangun Budaya Taat Hukum dan Konstitusi',
                'elements' => ['Undang-Undang Dasar Negara Republik Indonesia Tahun 1945'],
                'topic' => 'Hierarki Peraturan Perundang-undangan dan Norma Sosial',
                'learning_objectives' => "1. Peserta didik mampu menelaah hierarki perundang-undangan di Indonesia sesuai UU No. 12 Tahun 2011 jo UU No. 13 Tahun 2022.\n2. Peserta didik mampu menunjukkan sikap patuh hukum dalam kehidupan sehari-hari (tertib berlalu lintas, anti-korupsi, anti-perundungan).\n3. Peserta didik mampu mengevaluasi keselarasan peraturan di lingkungan sekolah dengan konstitusi.",
                'learning_model' => 'Inquiry Learning',
                'p5_dimensions' => ['Mandiri', 'Bernalar Kritis'],
                'meaningful_understanding' => 'Supremasi hukum dan kesadaran konstitusional adalah fondasi terciptanya masyarakat yang adil dan beradab.',
                'inquiry_questions' => [
                    'Apa konsekuensi sosial jika masyarakat mengabaikan kepatuhan terhadap hukum?',
                    'Bagaimana menyuarakan aspirasi perubahan hukum secara konstitusional?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Pendidikan Pancasila Kelas X',
            ],
            'ppkn-e-bab3' => [
                'id' => 'ppkn-e-bab3',
                'chapter_number' => 3,
                'chapter_title' => 'Bab 3: Mengelola Keragaman Budaya dalam Bingkai Kebinekaan',
                'elements' => ['Bhinneka Tunggal Ika'],
                'topic' => 'Identitas Budaya, Keragaman Suku/Agama, dan Dialog Antarbudaya',
                'learning_objectives' => "1. Peserta didik mampu mengidentifikasi identitas diri, kelompok, dan keragaman budaya masyarakat Indonesia.\n2. Peserta didik mampu mempromosikan toleransi dan sikap inklusif dalam menyikapi perbedaan.\n3. Peserta didik mampu merancang kampanye dialog lintas budaya untuk mencegah potensi konflik sosial.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Berkebinekaan Global', 'Gotong Royong'],
                'meaningful_understanding' => 'Keragaman adalah kekayaan strategis bangsa yang membutuhkan sikap saling menghormati dan kolaborasi.',
                'inquiry_questions' => [
                    'Bagaimana memelihara identitas lokal tanpa menjadi etnosentris yang sempit?',
                    'Bagaimana memupuk persaudaraan di tengah perbedaan keyakinan dan latar belakang?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Pendidikan Pancasila Kelas X',
            ],
            'ppkn-e-bab4' => [
                'id' => 'ppkn-e-bab4',
                'chapter_number' => 4,
                'chapter_title' => 'Bab 4: Kedaulatan Negara dan Keutuhan NKRI',
                'elements' => ['Negara Kesatuan Republik Indonesia'],
                'topic' => 'Batas Wilayah Negara, Hak & Kewajiban Bela Negara, dan Ketahanan Nasional',
                'learning_objectives' => "1. Peserta didik mampu menganalisis konsep kedaulatan negara, batas wilayah darat/laut/udara Indonesia.\n2. Peserta didik mampu menguraikan bentuk-bentuk ancaman terhadap keutuhan NKRI di bidang militer dan non-militer (siber, ekonomi, ideologi).\n3. Peserta didik mampu menunjukkan komitmen bela negara melalui prestasi dan kontribusi sosial nyata.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri', 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia'],
                'meaningful_understanding' => 'Menjaga kedaulatan dan keutuhan NKRI merupakan tanggung jawab bersama setiap warga negara.',
                'inquiry_questions' => [
                    'Apa wujud konkret bela negara yang dapat dilakukan oleh pelajar SMA/SMK saat ini?',
                    'Bagaimana menghadapi ancaman siber terhadap kedaulatan informasi nasional?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Pendidikan Pancasila Kelas X',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function informatikaFaseE(): array
    {
        return [
            'inf-e-bab1' => [
                'id' => 'inf-e-bab1',
                'chapter_number' => 1,
                'chapter_title' => 'Bab 1: Berpikir Komputasional (Computational Thinking)',
                'elements' => ['Berpikir Komputasional'],
                'topic' => 'Dekomposisi, Pengenalan Pola, Abstraksi, Algoritma, dan Struktur Data (Stack, Queue)',
                'learning_objectives' => "1. Peserta didik mampu menerapkan 4 pilar computational thinking untuk memecahkan persoalan logika kompleks.\n2. Peserta didik mampu mengoperasikan struktur data Antrean (Queue) dan Tumpukan (Stack) dalam pemodelan sistem nyata.\n3. Peserta didik mampu mengevaluasi efisiensi langkah algoritma pencarian dan pengurutan data.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
                'meaningful_understanding' => 'Berpikir komputasional adalah kecakapan bernalar tingkat tinggi untuk memecahkan masalah besar secara terstruktur dan efisien.',
                'inquiry_questions' => [
                    'Bagaimana sistem antrean bank atau tumpukan kartu diprogram secara matematis?',
                    'Mengapa abstraksi penting saat menghadapi persoalan yang sangat rumit?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Informatika Kelas X',
            ],
            'inf-e-bab2' => [
                'id' => 'inf-e-bab2',
                'chapter_number' => 2,
                'chapter_title' => 'Bab 2: Teknologi Informasi, Komunikasi & Integrasi Aplikasi Perkantoran',
                'elements' => ['Teknologi Informasi dan Komunikasi'],
                'topic' => 'Integrasi Aplikasi Office (Mail Merge, OLE), Pencarian Informasi Lanjut, dan Penyimpanan Cloud',
                'learning_objectives' => "1. Peserta didik mampu mengintegrasikan pengolah kata, lembar sebar, dan presentasi menggunakan fitur OLE dan Mail Merge.\n2. Peserta didik mampu memanfaatkan mesin pencari dengan operator boolean untuk menyaring informasi ilmiah valid.\n3. Peserta didik mampu mengelola data kolaboratif berbasis komputasi awan (cloud storage) dengan memperhatikan hak akses dan keamanan data.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Kreatif', 'Gotong Royong', 'Mandiri'],
                'meaningful_understanding' => 'Penguasaan ekosistem digital produktif meningkatkan efisiensi komunikasi dan manajemen data profesional.',
                'inquiry_questions' => [
                    'Bagaimana cara menghasilkan ratusan surat undangan resmi otomatis dalam hitungan detik?',
                    'Bagaimana memastikan file di cloud tetap aman dari akses pihak tak berwenang?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Informatika Kelas X',
            ],
            'inf-e-bab3' => [
                'id' => 'inf-e-bab3',
                'chapter_number' => 3,
                'chapter_title' => 'Bab 3: Algoritma dan Pemrograman (Coding Bahasa Python / C)',
                'elements' => ['Algoritma dan Pemrograman'],
                'topic' => 'Tipe Data, Variabel, Percabangan (If-Else), Perulangan (Looping), dan Fungsi',
                'learning_objectives' => "1. Peserta didik mampu membaca dan menulis program prosedural sederhana dengan sintaks yang benar.\n2. Peserta didik mampu mengimplementasikan struktur kontrol kondisi dan iterasi untuk menyelesaikan masalah perhitungan otomatis.\n3. Peserta didik mampu melakukan pengujian (testing) dan penelusuran kesalahan (debugging) kode program.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Kreatif', 'Mandiri'],
                'meaningful_understanding' => 'Pemrograman melatih ketelitian logika dan memberdayakan manusia menciptakan otomasi solutif.',
                'inquiry_questions' => [
                    'Bagaimana algoritma komputer mengambil keputusan logis berdasarkan input pengguna?',
                    'Langkah apa yang dilakukan programmer ketika kode menghasilkan error (bug)?',
                ],
                'suggested_duration_jp' => '8 JP (4 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Informatika Kelas X',
            ],
            'inf-e-bab4' => [
                'id' => 'inf-e-bab4',
                'chapter_number' => 4,
                'chapter_title' => 'Bab 4: Jaringan Komputer, Internet & Keamanan Siber',
                'elements' => ['Jaringan Komputer dan Internet'],
                'topic' => 'Topologi Jaringan, Model OSI, IP Address, Enkripsi, dan Keamanan Siber Dasar',
                'learning_objectives' => "1. Peserta didik mampu menjelaskan cara kerja transmisi data pada jaringan lokal (LAN) dan internet.\n2. Peserta didik mampu memahami konsep pengalamatan IP, DNS, dan protokol komunikasi data.\n3. Peserta didik mampu mempraktikkan langkah-langkah proteksi data pribadi, mengenali phishing, dan menerapkan enkripsi data.",
                'learning_model' => 'Discovery Learning',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri'],
                'meaningful_understanding' => 'Memahami infrastruktur jaringan dan etika keamanan digital adalah benteng proteksi diri di era interkoneksi global.',
                'inquiry_questions' => [
                    'Bagaimana paket pesan chat kita bisa sampai ke belahan bumi lain dalam milidetik?',
                    'Mengapa kita tidak boleh sembarangan mengklik tautan mencurigakan di internet?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Informatika Kelas X',
            ],
            'inf-e-bab5' => [
                'id' => 'inf-e-bab5',
                'chapter_number' => 5,
                'chapter_title' => 'Bab 5: Analisis Data dan Dampak Sosial Informatika',
                'elements' => ['Analisis Data', 'Dampak Sosial Informatika'],
                'topic' => 'Koleksi Data, Visualisasi Data, Hukum UU ITE, dan Etika Kecerdasan Buatan (AI)',
                'learning_objectives' => "1. Peserta didik mampu mengumpulkan, membersihkan, dan memvisualisasikan data mentah menjadi grafik informatif.\n2. Peserta didik mampu menganalisis dampak sosial, hukum (UU ITE), dan etika pemanfaatan teknologi digital serta AI.\n3. Peserta didik mampu menyusun karya advokasi literasi digital sehat dan anti-hoaks.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Berkebinekaan Global', 'Gotong Royong'],
                'meaningful_understanding' => 'Data adalah sumber informasi berharga yang harus diolah secara etis dan bertanggung jawab untuk kemaslahatan publik.',
                'inquiry_questions' => [
                    'Bagaimana grafik dan visualisasi data dapat memengaruhi opini publik?',
                    'Bagaimana memanfaatkan kecerdasan buatan (AI) secara etis tanpa melanggar hak cipta atau integritas akademik?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & Buku Informatika Kelas X',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public static function bahasaInggrisFaseE(): array
    {
        return [
            'bing-e-bab1' => [
                'id' => 'bing-e-bab1',
                'chapter_number' => 1,
                'chapter_title' => 'Chapter 1: Great Athletes (Descriptive Text on Sports Personalities)',
                'elements' => ['Menyimak - Berbicara', 'Membaca - Memirsa', 'Menulis - Mempresentasikan'],
                'topic' => 'Descriptive Text (Physical Appearance, Personality Traits, Achievements)',
                'learning_objectives' => "1. Students are able to identify main ideas and detailed information about great athletes from spoken and written descriptive texts.\n2. Students are able to describe their favorite sports figures using appropriate adjectives, action verbs, and simple present tense.\n3. Students are able to produce a multimodal descriptive poster about inspiring Indonesian athletes.",
                'learning_model' => 'Genre-Based Approach (Pedagogi Genre) & PBL',
                'p5_dimensions' => ['Bernalar Kritis', 'Mandiri', 'Kreatif'],
                'meaningful_understanding' => 'Describing characters and inspiring figures fosters sportsmanship, resilience, and appreciation of human achievements.',
                'inquiry_questions' => [
                    'What makes a person truly inspiring beyond their physical talents?',
                    'How can we use descriptive adjectives to create vivid portraits in the reader’s mind?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & English for Change Class X',
            ],
            'bing-e-bab2' => [
                'id' => 'bing-e-bab2',
                'chapter_number' => 2,
                'chapter_title' => 'Chapter 2: Traditional Stories & Folklore (Narrative Text)',
                'elements' => ['Membaca - Memirsa', 'Menulis - Mempresentasikan'],
                'topic' => 'Narrative Text (Folklore, Legends, Fables, Moral Values, Past Tense)',
                'learning_objectives' => "1. Students are able to analyze story structure (orientation, complication, evaluation, resolution, coda) and moral lessons in local folklore.\n2. Students are able to distinguish between direct and indirect speech in narrative contexts.\n3. Students are able to retell and write an adapted traditional story using simple past tense and sequence connectors.",
                'learning_model' => 'Discovery Learning & Role Play',
                'p5_dimensions' => ['Berkebinekaan Global', 'Kreatif', 'Beriman, Bertakwa kepada Tuhan YME, dan Berakhlak Mulia'],
                'meaningful_understanding' => 'Folklore and narrative tales preserve cultural heritage and timeless moral virtues across generations.',
                'inquiry_questions' => [
                    'Why do traditional legends often contain moral lessons for younger generations?',
                    'How do narrative complications and climaxes create emotional engagement for readers?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & English for Change Class X',
            ],
            'bing-e-bab3' => [
                'id' => 'bing-e-bab3',
                'chapter_number' => 3,
                'chapter_title' => 'Chapter 3: Healthy Food & Lifestyle (Procedure & Expository Text)',
                'elements' => ['Menyimak - Berbicara', 'Menulis - Mempresentasikan'],
                'topic' => 'Procedure Text & Tips (Imperative Sentences, Sequence Words, Health Adverbs)',
                'learning_objectives' => "1. Students are able to comprehend spoken and written procedural tips regarding balanced nutrition and healthy habits.\n2. Students are able to give instructions and health advice using modal verbs and imperative sentences.\n3. Students are able to design a digital infoguide for healthy school lifestyle.",
                'learning_model' => 'Project-Based Learning (PjBL)',
                'p5_dimensions' => ['Mandiri', 'Bernalar Kritis', 'Gotong Royong'],
                'meaningful_understanding' => 'Clear communication in giving health instructions supports well-being and active physical wellness.',
                'inquiry_questions' => [
                    'How does good nutrition affect our daily learning performance?',
                    'What makes a procedure or tip easy to follow for various readers?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & English for Change Class X',
            ],
            'bing-e-bab4' => [
                'id' => 'bing-e-bab4',
                'chapter_number' => 4,
                'chapter_title' => 'Chapter 4: Digital Literacy & Social Media Etiquette (Analytical Exposition)',
                'elements' => ['Membaca - Memirsa', 'Menulis - Mempresentasikan'],
                'topic' => 'Analytical Exposition (Thesis, Arguments, Reiteration, Connectives of Cause-Effect)',
                'learning_objectives' => "1. Students are able to evaluate persuasive arguments and rhetorical devices in analytical exposition texts on digital literacy.\n2. Students are able to construct evidence-based arguments regarding ethical social media usage.\n3. Students are able to deliver a short persuasive speech expressing their viewpoint on modern technological issues.",
                'learning_model' => 'Problem-Based Learning (PBL) & Debate',
                'p5_dimensions' => ['Bernalar Kritis', 'Berkebinekaan Global'],
                'meaningful_understanding' => 'Constructing well-reasoned analytical arguments empowers young citizens to advocate constructive ideas in public discourse.',
                'inquiry_questions' => [
                    'How can we persuade others effectively using factual evidence and logical connectors?',
                    'What responsibility do we bear when sharing opinions in digital spaces?',
                ],
                'suggested_duration_jp' => '6 JP (3 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024 & English for Change Class X',
            ],
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private static function genericSubjectTopics(string $subjectName, string $fase): array
    {
        return [
            'generic-topik1' => [
                'id' => 'generic-topik1',
                'chapter_number' => 1,
                'chapter_title' => "Modul Utama: {$subjectName} ({$fase})",
                'elements' => ['Pemahaman Konsep', 'Keterampilan Proses'],
                'topic' => "Konsep Fundamental {$subjectName}",
                'learning_objectives' => "1. Peserta didik mampu memahami prinsip dan konsep esensial pada mata pelajaran {$subjectName}.\n2. Peserta didik mampu menerapkan metode eksplorasi terstruktur untuk memecahkan persoalan kontekstual.\n3. Peserta didik mampu mengomunikasikan hasil analisis dan simpulan pembelajaran secara kolaboratif.",
                'learning_model' => 'Problem-Based Learning (PBL)',
                'p5_dimensions' => ['Bernalar Kritis', 'Gotong Royong', 'Mandiri'],
                'meaningful_understanding' => "Penguasaan konsep {$subjectName} membekali peserta didik dengan kecakapan berpikir kritis dan solutif di kehidupan nyata.",
                'inquiry_questions' => [
                    "Bagaimana konsep {$subjectName} diterapkan dalam memecahkan permasalahan sehari-hari?",
                    "Apa dampak pemahaman materi ini terhadap pengembangan keterampilan masa depan?",
                ],
                'suggested_duration_jp' => '4 JP (2 Pertemuan)',
                'reference' => 'Kemendikdasmen BSKAP No. 032/H/KR/2024',
            ],
        ];
    }

    /**
     * Seed national preset topics for a specific school into database.
     *
     * @return int Number of topics created
     */
    public static function seedPresetsToSchool(int $schoolId, ?string $subjectName = null, ?string $fase = 'Fase E', ?int $userId = null): int
    {
        $subjectsToSeed = [];

        if ($subjectName) {
            $subjectsToSeed[] = $subjectName;
        } else {
            $subjectsToSeed = ['Bahasa Indonesia', 'Matematika', 'Pendidikan Pancasila', 'Informatika', 'Bahasa Inggris'];
        }

        $count = 0;
        $faseVal = $fase ?: 'Fase E';

        foreach ($subjectsToSeed as $subj) {
            $topics = self::getTopicsForSubjectAndFase($subj, $faseVal, null);

            // Find matching subject record in school if exists
            $subjectRecord = Subject::query()
                ->where('school_id', $schoolId)
                ->where('name', 'like', "%{$subj}%")
                ->first();

            foreach ($topics as $t) {
                // Determine grade level
                $gradeLevel = 10;
                if (str_contains($faseVal, 'A')) $gradeLevel = 1;
                elseif (str_contains($faseVal, 'B')) $gradeLevel = 3;
                elseif (str_contains($faseVal, 'C')) $gradeLevel = 5;
                elseif (str_contains($faseVal, 'D')) $gradeLevel = 7;
                elseif (str_contains($faseVal, 'E')) $gradeLevel = 10;
                elseif (str_contains($faseVal, 'F')) $gradeLevel = 11;

                $elementStr = is_array($t['elements'] ?? null) ? implode(', ', $t['elements']) : ($t['elements'] ?? '');

                CurriculumTarget::updateOrCreate(
                    [
                        'school_id' => $schoolId,
                        'subject_name' => $subj,
                        'phase' => $faseVal,
                        'grade_level' => $gradeLevel,
                        'chapter_number' => (int) ($t['chapter_number'] ?? 1),
                    ],
                    [
                        'subject_id' => $subjectRecord?->id,
                        'chapter_title' => (string) ($t['chapter_title'] ?? $t['topic']),
                        'element' => $elementStr,
                        'topic' => (string) ($t['topic'] ?? ''),
                        'learning_objectives' => (string) ($t['learning_objectives'] ?? ''),
                        'learning_model' => (string) ($t['learning_model'] ?? 'Problem-Based Learning (PBL)'),
                        'p5_dimensions' => (array) ($t['p5_dimensions'] ?? []),
                        'meaningful_understanding' => (string) ($t['meaningful_understanding'] ?? ''),
                        'inquiry_questions' => (array) ($t['inquiry_questions'] ?? []),
                        'suggested_duration_jp' => (string) ($t['suggested_duration_jp'] ?? '6 JP'),
                        'reference_source' => (string) ($t['reference'] ?? 'Kemendikdasmen BSKAP No. 032/H/KR/2024'),
                        'is_active' => true,
                        'created_by' => $userId,
                    ]
                );

                $count++;
            }
        }

        return $count;
    }
}
