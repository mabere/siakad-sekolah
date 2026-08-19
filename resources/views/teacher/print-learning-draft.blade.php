<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ data_get($output, 'title', 'Perangkat Pembelajaran') }} - {{ $schedule?->subject?->name ?? 'Mapel' }}</title>
    @if(!isset($isWordExport) || !$isWordExport)
    <script src="https://cdn.tailwindcss.com"></script>
    @endif
    <style>
        @media print {
            body { font-size: 11pt; color: #000; background: #fff !important; }
            .no-print { display: none !important; }
            .print-container { max-width: 100% !important; margin: 0 !important; padding: 0 !important; box-shadow: none !important; }
            @page { size: A4 portrait; margin: 2cm 2cm 2cm 2cm; }
            .page-break { page-break-before: always; }
        }
        body { font-family: 'Times New Roman', Times, serif; line-height: 1.5; color: #111; background: #f8fafc; margin: 0; padding: 0; }
        .print-container { max-width: 21cm; margin: 2rem auto; background: white; padding: 2cm; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); }
        .kop-surat { border-bottom: 3px double #000; padding-bottom: 12px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; }
        .kop-surat img { width: 80px; height: 80px; object-fit: contain; }
        .kop-teks { flex: 1; text-align: center; padding: 0 15px; }
        .judul-dokumen { text-align: center; font-weight: bold; text-transform: uppercase; font-size: 13.5pt; margin-bottom: 15px; text-decoration: underline; }
        .tabel-identitas { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10pt; }
        .tabel-identitas td { padding: 4px 8px; vertical-align: top; }
        .section-title { font-weight: bold; font-size: 11pt; text-transform: uppercase; margin-top: 18px; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 3px; }
        .tabel-kbm { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9.5pt; }
        .tabel-kbm th, .tabel-kbm td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        .tabel-kbm th { background-color: #f1f5f9; font-weight: bold; text-align: center; }
        .tanda-tangan { width: 100%; margin-top: 35px; border-collapse: collapse; font-size: 10.5pt; }
        .tanda-tangan td { text-align: center; vertical-align: top; width: 50%; padding: 0 20px; }
    </style>
</head>
<body>
    @if(!isset($isWordExport) || !$isWordExport)
    <div class="no-print p-4 bg-slate-900 text-white flex flex-wrap justify-between items-center sticky top-0 left-0 z-50 shadow-md">
        <div class="flex items-center gap-3">
            <a href="{{ route('guru.learning-assistant') }}" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg border border-slate-700">
                ← Kembali ke Asisten Pembelajaran
            </a>
            <span class="text-sm font-bold text-teal-400">Preview Cetak Resmi: {{ data_get($output, 'title', 'Perangkat Pembelajaran') }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.learning-assistant.export-word', $draft->id) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3.5 py-2 rounded-lg font-bold shadow flex items-center gap-1.5 transition">
                📥 Unduh Word (.doc)
            </a>
            <button onclick="window.print()" class="bg-teal-600 hover:bg-teal-700 text-white text-xs px-4 py-2 rounded-lg font-bold shadow flex items-center gap-1.5 transition">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>
    @endif

    <div class="print-container">
        <!-- Kop Surat Resmi -->
        <div class="kop-surat">
            @if($school->logo_url && (!isset($isWordExport) || !$isWordExport))
                <img src="{{ $school->logo_url }}" alt="Logo">
            @else
                <div style="width: 80px; height: 80px; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 9pt; color: #777;">LOGO</div>
            @endif
            <div class="kop-teks">
                <h2 style="font-size: 14pt; font-weight: bold; margin: 0; text-transform: uppercase;">PEMERINTAH DAERAH PROVINSI / KABUPATEN</h2>
                <h1 style="font-size: 16pt; font-weight: bold; margin: 2px 0; text-transform: uppercase;">{{ $school->name }}</h1>
                <p style="font-size: 9pt; margin: 2px 0;">{{ $school->address ?? 'Alamat Sekolah Belum Diisi' }}</p>
                <p style="font-size: 9pt; margin: 2px 0;">Telepon: {{ $school->phone ?? '-' }} | Email: {{ $school->email ?? '-' }} | Website: {{ $school->website ?? '-' }}</p>
            </div>
            <div style="width: 80px;"></div>
        </div>

        @php
            $docType = $draft->document_type;
            $docTitle = match($docType) {
                'atp' => 'ALUR TUJUAN PEMBELAJARAN (ATP)',
                'prota_prosem' => 'PROGRAM TAHUNAN DAN PROGRAM SEMESTER (PROTA & PROSEM)',
                'bahan_ajar', 'materi_ajar' => 'BAHAN AJAR & RINGKASAN KONSEP MATERI',
                'lkpd_bertingkat', 'lkpd' => 'LEMBAR KERJA PESERTA DIDIK (LKPD) BERDIFERENSIASI',
                'modul_p5' => 'MODUL PROJEK PENGUATAN PROFIL PELAJAR PANCASILA (P5)',
                'asesmen_kktp', 'asesmen' => 'KISI-KISI, INSTRUMEN ASESMEN & RUBRIK KKTP',
                default => 'MODUL AJAR / RENCANA PELAKSANAAN PEMBELAJARAN (RPP+)',
            };
        @endphp

        <!-- Judul Dokumen -->
        <div class="judul-dokumen">
            {{ $docTitle }}
        </div>

        <!-- Tabel Identitas Modul -->
        <table class="tabel-identitas">
            <tr>
                <td style="width: 22%; font-weight: bold;">Satuan Pendidikan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 36%;">{{ $school->name }}</td>
                <td style="width: 18%; font-weight: bold;">Tahun Ajaran</td>
                <td style="width: 2%;">:</td>
                <td style="width: 20%;">{{ $academicYear?->name ?? '-' }} ({{ $academicYear?->semester ?? '-' }})</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Mata Pelajaran</td>
                <td>:</td>
                <td><strong>{{ $schedule?->subject?->name ?? '-' }}</strong></td>
                <td style="font-weight: bold;">Fase / Kelas</td>
                <td>:</td>
                <td>
                    <strong>
                        @php
                            $grade = (int) ($schedule?->classroom?->grade_level ?? 10);
                            $faseStr = match(true) {
                                $grade >= 11 => 'Fase F (Kelas 11–12)',
                                $grade === 10 => 'Fase E (Kelas 10)',
                                $grade >= 7 => 'Fase D (Kelas 7–9)',
                                $grade >= 5 => 'Fase C (Kelas 5–6)',
                                $grade >= 3 => 'Fase B (Kelas 3–4)',
                                default => 'Fase A (Kelas 1–2)',
                            };
                        @endphp
                        {{ $faseStr }} / {{ $schedule?->classroom?->name ?? '-' }}
                    </strong>
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Guru Pengampu</td>
                <td>:</td>
                <td>{{ $teacher?->user?->name ?? auth()->user()->name }}</td>
                <td style="font-weight: bold;">Alokasi Waktu</td>
                <td>:</td>
                <td>{{ $schedule ? substr($schedule->start_time, 0, 5).' - '.substr($schedule->end_time, 0, 5) : '-' }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Model Pembelajaran</td>
                <td>:</td>
                <td>{{ data_get($output, 'learning_model', 'Problem-Based Learning / Pedagogi Genre') }}</td>
                <td style="font-weight: bold;">Profil Pancasila</td>
                <td>:</td>
                <td>
                    @php $p5Dims = data_get($output, 'p5_dimensions', []); @endphp
                    {{ is_array($p5Dims) && count($p5Dims) > 0 ? implode(', ', $p5Dims) : 'Bernalar Kritis, Gotong Royong, Kreatif' }}
                </td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Topik / Pokok Materi</td>
                <td>:</td>
                <td colspan="4"><strong>{{ data_get($output, 'title', '-') }}</strong></td>
            </tr>
        </table>

        <!-- Ringkasan Pembelajaran -->
        @if(data_get($output, 'summary'))
        <div style="margin-bottom: 15px; font-style: italic; background-color: #f8fafc; border-left: 3px solid #0d9488; padding: 8px 12px; font-size: 10pt;">
            <strong>Gambaran Umum:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($output, 'summary')) !!}
        </div>
        @endif

        {{-- 1. DOKUMEN MODUL AJAR (RPP+) --}}
        @if($docType === 'modul_ajar' || $docType === null)
            @php
                $meaningful = data_get($output, 'meaningful_understanding');
                $inquiries = data_get($output, 'inquiry_questions', []);
            @endphp
            @if($meaningful || (is_array($inquiries) && count($inquiries) > 0))
            <div class="section-title">I. PEMAHAMAN BERMAKNA & PERTANYAAN PEMANTIK</div>
            @if($meaningful)
                <p style="margin: 4px 0 8px 0; font-size: 10pt; text-align: justify;"><strong>A. Pemahaman Bermakna:</strong> {!! \App\Support\SafeHtml::formatHumanText($meaningful) !!}</p>
            @endif
            @if(is_array($inquiries) && count($inquiries) > 0)
                <p style="margin: 4px 0 2px 0; font-size: 10pt;"><strong>B. Pertanyaan Pemantik:</strong></p>
                <ul style="margin-top: 2px; margin-bottom: 12px; padding-left: 20px; font-size: 9.5pt;">
                    @foreach($inquiries as $inq)
                        <li>{!! \App\Support\SafeHtml::formatHumanText($inq) !!}</li>
                    @endforeach
                </ul>
            @endif
            @endif

            <div class="section-title">II. TUJUAN PEMBELAJARAN (TP)</div>
            <ol style="margin-top: 5px; margin-bottom: 15px; padding-left: 20px; font-size: 10pt;">
                @foreach(data_get($output, 'learning_objectives', []) as $obj)
                    <li style="margin-bottom: 4px;">{!! \App\Support\SafeHtml::formatHumanText($obj) !!}</li>
                @endforeach
            </ol>

            <div class="section-title">III. RANGKAIAN KEGIATAN PEMBELAJARAN (KBM)</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 15%;">Tahapan</th>
                        <th style="width: 12%;">Waktu</th>
                        <th>Aktivitas Pembelajaran</th>
                        <th style="width: 25%;">Peran Guru & Murid</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($output, 'activities', []) as $act)
                    <tr>
                        <td style="font-weight: bold; text-align: center;">{{ data_get($act, 'stage', '-') }}</td>
                        <td style="text-align: center;">{{ data_get($act, 'duration_minutes', 0) }} Menit</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($act, 'activity', '-')) !!}</td>
                        <td>
                            <p style="margin: 0 0 3px 0;"><strong>Guru:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($act, 'teacher_role', '-')) !!}</p>
                            <p style="margin: 0;"><strong>Murid:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($act, 'student_role', '-')) !!}</p>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            @php $worksheet = data_get($output, 'student_worksheet'); @endphp
            @if(!empty($worksheet))
            <div class="section-title">IV. LEMBAR KERJA PESERTA DIDIK (LKPD)</div>
            <div style="border: 1px solid #94a3b8; border-radius: 4px; padding: 10px 14px; margin-bottom: 15px; background: #f8fafc;">
                <p style="font-weight: bold; font-size: 10.5pt; margin: 0 0 4px 0;">{{ data_get($worksheet, 'title', 'Lembar Kerja Peserta Didik') }}</p>
                <p style="font-style: italic; font-size: 9pt; margin: 0 0 8px 0; color: #475569;">Petunjuk: {!! \App\Support\SafeHtml::formatHumanText(data_get($worksheet, 'instructions', '-')) !!}</p>
                <ol style="margin: 0; padding-left: 20px; font-size: 9.5pt;">
                    @foreach(data_get($worksheet, 'tasks', []) as $task)
                        <li style="margin-bottom: 4px;">{!! \App\Support\SafeHtml::formatHumanText($task) !!}</li>
                    @endforeach
                </ol>
            </div>
            @endif

            <div class="section-title">V. ASESMEN PEMBELAJARAN & RUBRIK PENILAIAN</div>
            @php $assessment = data_get($output, 'assessment', []); @endphp
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 12px; font-size: 9.5pt;">
                <tr><td style="width: 25%; font-weight: bold; padding: 3px 0;">1. Asesmen Diagnostik</td><td>: {!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'diagnostic', '-')) !!}</td></tr>
                <tr><td style="font-weight: bold; padding: 3px 0;">2. Asesmen Formatif</td><td>: {!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'formative', '-')) !!}</td></tr>
                <tr><td style="font-weight: bold; padding: 3px 0;">3. Asesmen Sumatif</td><td>: {!! \App\Support\SafeHtml::formatHumanText(data_get($assessment, 'summative', '-')) !!}</td></tr>
            </table>

            @php $rubrics = data_get($output, 'assessment_rubric', []); @endphp
            @if(is_array($rubrics) && count($rubrics) > 0)
            <table class="tabel-kbm" style="font-size: 9pt; margin-bottom: 15px;">
                <thead>
                    <tr>
                        <th style="width: 25%;">Kriteria Penilaian</th>
                        <th style="width: 40%;">Indikator Pencapaian</th>
                        <th>Pedoman Skor / Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rubrics as $rub)
                    <tr>
                        <td style="font-weight: bold;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'criteria', '-')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'indicator', '-')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'scoring_guide', '-')) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif

        {{-- 2. DOKUMEN ALUR TUJUAN PEMBELAJARAN (ATP) --}}
        @elseif($docType === 'atp')
            <div class="section-title">I. CAPAIAN PEMBELAJARAN (CP)</div>
            <p style="font-size: 10pt; text-align: justify; margin: 4px 0 10px 0;"><strong>CP Umum:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($output, 'cp_general')) !!}</p>
            
            @foreach(data_get($output, 'cp_elements', []) as $elem)
                <p style="font-size: 9.5pt; margin: 2px 0;">• <strong>Elemen {{ data_get($elem, 'element_name') }}:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($elem, 'cp_statement')) !!}</p>
            @endforeach

            <div class="section-title">II. MATRIKS ALUR TUJUAN PEMBELAJARAN (ATP FLOW)</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 8%;">Alur</th>
                        <th style="width: 22%;">Bab & Topik</th>
                        <th>Tujuan Pembelajaran (TP)</th>
                        <th style="width: 25%;">Indikator Ketercapaian</th>
                        <th style="width: 8%;">JP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($output, 'atp_flow', []) as $flow)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ data_get($flow, 'sequence_number') }}</td>
                        <td><strong>{{ data_get($flow, 'chapter') }}</strong><br><small>{{ data_get($flow, 'topic') }}</small></td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($flow, 'learning_objectives')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($flow, 'indicators')) !!}</td>
                        <td style="text-align: center; font-weight: bold;">{{ data_get($flow, 'suggested_duration_jp') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        {{-- 3. DOKUMEN PROGRAM TAHUNAN & SEMESTER --}}
        @elseif($docType === 'prota_prosem')
            <div class="section-title">I. DISTRIBUSI PROGRAM TAHUNAN (PROTA)</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 10%;">Bab</th>
                        <th>Pokok Bahasan / Tujuan Pembelajaran</th>
                        <th style="width: 15%;">Semester</th>
                        <th style="width: 12%;">Alokasi JP</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($output, 'prota_distribution', []) as $prota)
                    <tr>
                        <td style="text-align: center; font-weight: bold;">{{ data_get($prota, 'chapter_number') }}</td>
                        <td><strong>{{ data_get($prota, 'chapter_title') }}</strong><br><small>{!! \App\Support\SafeHtml::formatHumanText(data_get($prota, 'learning_objectives')) !!}</small></td>
                        <td style="text-align: center;">{{ data_get($prota, 'semester') }}</td>
                        <td style="text-align: center; font-weight: bold;">{{ data_get($prota, 'allocated_jp') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        {{-- 4. DOKUMEN BAHAN AJAR --}}
        @elseif($docType === 'bahan_ajar' || $docType === 'materi_ajar')
            <div class="section-title">I. RINGKASAN KONSEP ESENSIAL</div>
            <p style="font-size: 10pt; text-align: justify;">{!! \App\Support\SafeHtml::formatHumanText(data_get($output, 'concept_summary')) !!}</p>

            <div class="section-title">II. POKOK MATERI PEMBELAJARAN</div>
            @foreach(data_get($output, 'key_sections', []) as $sec)
                <div style="margin-bottom: 12px;">
                    <p style="font-weight: bold; font-size: 10pt; margin: 4px 0;">• {{ data_get($sec, 'subtitle') }}</p>
                    <p style="font-size: 9.5pt; margin: 2px 0 4px 0; text-align: justify;">{!! \App\Support\SafeHtml::formatHumanText(data_get($sec, 'content')) !!}</p>
                    <p style="font-size: 9pt; color: #334155; margin: 0;"><em>Poin Kunci: {!! \App\Support\SafeHtml::formatHumanText(data_get($sec, 'key_takeaway')) !!}</em></p>
                </div>
            @endforeach

        {{-- 5. DOKUMEN LKPD 3 LEVEL --}}
        @elseif($docType === 'lkpd_bertingkat' || $docType === 'lkpd')
            <div class="section-title">I. PETUNJUK UMUM LKPD</div>
            <p style="font-size: 10pt;">{!! \App\Support\SafeHtml::formatHumanText(data_get($output, 'general_instructions')) !!}</p>

            <div class="section-title">II. TUGAS BERJENJANG (TIERED TASKS)</div>
            <div style="border: 1px solid #10b981; padding: 8px 12px; margin-bottom: 10px; background: #f0fdf4;">
                <p style="font-weight: bold; font-size: 10pt; color: #065f46; margin: 0 0 4px 0;">LEVEL 1: PERLU BIMBINGAN (SCAFFOLDING)</p>
                <ol style="margin: 0; padding-left: 20px; font-size: 9.5pt;">
                    @foreach(data_get($output, 'level_1_scaffolding.tasks', []) as $t)
                        <li>{!! \App\Support\SafeHtml::formatHumanText($t) !!}</li>
                    @endforeach
                </ol>
            </div>
            <div style="border: 1px solid #3b82f6; padding: 8px 12px; margin-bottom: 10px; background: #eff6ff;">
                <p style="font-weight: bold; font-size: 10pt; color: #1e40af; margin: 0 0 4px 0;">LEVEL 2: REGULER (CAKAP)</p>
                <ol style="margin: 0; padding-left: 20px; font-size: 9.5pt;">
                    @foreach(data_get($output, 'level_2_regular.core_tasks', []) as $t)
                        <li>{!! \App\Support\SafeHtml::formatHumanText($t) !!}</li>
                    @endforeach
                </ol>
            </div>
            <div style="border: 1px solid #8b5cf6; padding: 8px 12px; margin-bottom: 10px; background: #f5f3ff;">
                <p style="font-weight: bold; font-size: 10pt; color: #5b21b6; margin: 0 0 4px 0;">LEVEL 3: PENGAYAAN / HOTS (MAHIR)</p>
                <ol style="margin: 0; padding-left: 20px; font-size: 9.5pt;">
                    @foreach(data_get($output, 'level_3_advanced.hots_tasks', []) as $t)
                        <li>{!! \App\Support\SafeHtml::formatHumanText($t) !!}</li>
                    @endforeach
                </ol>
            </div>

        {{-- 6. DOKUMEN MODUL P5 --}}
        @elseif($docType === 'modul_p5')
            <div class="section-title">I. PROFIL DAN LATAR BELAKANG PROJEK P5</div>
            <p style="font-size: 10pt; margin: 4px 0;"><strong>Tema P5:</strong> {{ data_get($output, 'p5_theme') }}</p>
            <p style="font-size: 10pt; margin: 4px 0;"><strong>Topik Projek:</strong> {{ data_get($output, 'project_topic') }}</p>
            <p style="font-size: 9.5pt; text-align: justify; margin: 4px 0 10px 0;"><strong>Latar Belakang:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($output, 'project_background')) !!}</p>

            <div class="section-title">II. 4 TAHAP ALUR AKTIVITAS PROJEK</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 25%;">Tahapan Projek</th>
                        <th style="width: 12%;">Alokasi JP</th>
                        <th>Aktivitas Pembelajaran</th>
                        <th style="width: 25%;">Output / Artefak</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($output, 'project_stages', []) as $stg)
                    <tr>
                        <td style="font-weight: bold;">{{ data_get($stg, 'stage_name') }}</td>
                        <td style="text-align: center;">{{ data_get($stg, 'duration_jp') }}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($stg, 'activities')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($stg, 'output_artifact')) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

        {{-- 7. DOKUMEN ASESMEN & RUBRIK KKTP --}}
        @elseif($docType === 'asesmen_kktp' || $docType === 'asesmen')
            <div class="section-title">I. KISI-KISI & NASKAH SOAL SUMATIF (AKM / HOTS)</div>
            @foreach(data_get($output, 'summative_assessment.questions', []) as $q)
                <div style="margin-bottom: 12px; font-size: 9.5pt;">
                    <p style="font-weight: bold; margin: 2px 0;">Soal No. {{ data_get($q, 'number') }} ({{ data_get($q, 'question_type') }} - {{ data_get($q, 'scoring_points') }} Poin)</p>
                    @if(data_get($q, 'stimulus_text'))
                        <p style="font-style: italic; background: #f8fafc; padding: 4px 8px; margin: 2px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'stimulus_text')) !!}</p>
                    @endif
                    <p style="margin: 2px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'question_text')) !!}</p>
                    @if(is_array(data_get($q, 'options')) && count(data_get($q, 'options')) > 0)
                        <ul style="margin: 2px 0 4px 20px; padding: 0;">
                            @foreach(data_get($q, 'options') as $opt)
                                <li>{!! \App\Support\SafeHtml::formatHumanText($opt) !!}</li>
                            @endforeach
                        </ul>
                    @endif
                    <p style="font-size: 9pt; color: #047857; margin: 2px 0;"><strong>Kunci/Pedoman:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'correct_answer')) !!} ({!! \App\Support\SafeHtml::formatHumanText(data_get($q, 'explanation')) !!})</p>
                </div>
            @endforeach

            <div class="section-title">II. RUBRIK INTERVAL KKTP</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 25%;">Aspek Kriteria</th>
                        <th>Perlu Bimbingan</th>
                        <th>Cukup</th>
                        <th>Baik</th>
                        <th>Sangat Baik</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(data_get($output, 'kktp_rubric', []) as $kktp)
                    <tr>
                        <td style="font-weight: bold;">{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'aspect')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'perlu_bimbingan')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'cukup')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'baik')) !!}</td>
                        <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($kktp, 'sangat_baik')) !!}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- VI. DAFTAR PUSTAKA -->
        @php $references = data_get($output, 'references', []); @endphp
        @if(is_array($references) && count($references) > 0)
        <div class="section-title">REFERENSI & SUMBER BELAJAR</div>
        <ul style="margin-top: 5px; margin-bottom: 15px; padding-left: 20px; font-size: 9pt;">
            @foreach($references as $ref)
                <li>{!! \App\Support\SafeHtml::formatHumanText($ref) !!}</li>
            @endforeach
        </ul>
        @endif

        <!-- Lembar Pengesahan / Tanda Tangan Resmi -->
        <table class="tanda-tangan">
            <tr>
                <td>
                    Mengetahui,<br>
                    <strong>Kepala Sekolah</strong><br><br><br><br><br>
                    <u><strong>{{ $headmasterUser?->name ?? '.........................................' }}</strong></u><br>
                    NIP. {{ $headmasterTeacher?->nip ?? '-' }}
                </td>
                <td>
                    {{ $school->city ?? 'Kota' }}, {{ $printDate }}<br>
                    <strong>Guru Mata Pelajaran</strong><br><br><br><br><br>
                    <u><strong>{{ $teacher?->user?->name ?? auth()->user()->name }}</strong></u><br>
                    NIP. {{ $teacher?->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
