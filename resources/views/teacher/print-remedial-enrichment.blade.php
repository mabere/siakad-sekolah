<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $type === 'enrichment' ? 'Lembar Kerja Pengayaan' : 'Lembar Kerja Remedial' }} - {{ $subject }} ({{ $classroom }})</title>
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
            <a href="{{ route('guru.remedial-enrichment') }}" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg border border-slate-700">
                ← Kembali ke Generator Remedial & Pengayaan
            </a>
            <span class="text-sm font-bold text-purple-400">
                Preview Cetak Resmi: {{ $type === 'enrichment' ? 'Lembar Kerja Pengayaan' : 'Lembar Kerja Remedial' }}
            </span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('guru.remedial-enrichment.export-word', ['type' => $type]) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3.5 py-2 rounded-lg font-bold shadow flex items-center gap-1.5 transition">
                📥 Unduh Word (.doc)
            </a>
            <button onclick="window.print()" class="bg-purple-600 hover:bg-purple-700 text-white text-xs px-4 py-2 rounded-lg font-bold shadow flex items-center gap-1.5 transition">
                🖨️ Cetak / Simpan PDF
            </button>
        </div>
    </div>
    @endif

    <div class="print-container">
        <!-- KOP SURAT RESMI -->
        <div class="kop-surat">
            @if(!empty($school->logo))
                <img src="{{ asset('storage/' . $school->logo) }}" alt="Logo Sekolah" style="width: 75px; height: 75px; object-fit: contain;">
            @else
                <div style="width: 75px; height: 75px; border: 1px dashed #999; display: flex; align-items: center; justify-content: center; font-size: 8pt; text-align: center;">LOGO SEKOLAH</div>
            @endif
            <div class="kop-teks">
                <div style="font-size: 13pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">{{ $school->name ?? 'SIAKAD SEKOLAH' }}</div>
                <div style="font-size: 9.5pt; margin-top: 2px;">{{ $school->address ?? 'Alamat Lengkap Sekolah' }}</div>
                <div style="font-size: 9pt; color: #444;">Telp: {{ $school->phone ?? '-' }} | Email: {{ $school->email ?? '-' }} | NPSN: {{ $school->npsn ?? '-' }}</div>
            </div>
            <div style="width: 75px;"></div>
        </div>

        <!-- JUDUL DOKUMEN -->
        <div class="judul-dokumen">
            {{ $type === 'enrichment' ? 'LEMBAR KERJA PENGAYAAN (HOTS) KURIKULUM MERDEKA' : 'LEMBAR KERJA REMEDIAL (SCAFFOLDING) KURIKULUM MERDEKA' }}
        </div>

        <!-- TABEL IDENTITAS -->
        <table class="tabel-identitas">
            <tr>
                <td style="width: 25%; font-weight: bold;">Satuan Pendidikan</td>
                <td style="width: 2%;">:</td>
                <td style="width: 38%;">{{ $school->name }}</td>
                <td style="width: 15%; font-weight: bold;">Kelas / Rombel</td>
                <td style="width: 2%;">:</td>
                <td style="width: 18%;">{{ $classroom }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Mata Pelajaran</td>
                <td>:</td>
                <td>{{ $subject }}</td>
                <td style="font-weight: bold;">Tahun Ajaran</td>
                <td>:</td>
                <td>2026/2027</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Topik Pokok</td>
                <td>:</td>
                <td colspan="4">{{ $topic }}</td>
            </tr>
            <tr>
                <td style="font-weight: bold;">Guru Pengampu</td>
                <td>:</td>
                <td colspan="4">{{ $teacher->name ?? '-' }} (NIP: {{ $teacher->nip ?? '-' }})</td>
            </tr>
        </table>

        {{-- FORMAT REMEDIAL --}}
        @if ($type === 'remedial')
            @php $rem = data_get($package, 'remedial_package', []); @endphp

            <div class="section-title">A. Capaian / Indikator Sasaran Remedial</div>
            <p style="margin: 4px 0 12px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rem, 'target_competency')) !!}</p>

            @if (!empty($remedialStudents))
                <div class="section-title">B. Daftar Peserta Remedial</div>
                <table class="tabel-kbm" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 8%;">No</th>
                            <th style="width: 25%;">NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 20%;">Nilai Asesmen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($remedialStudents as $idx => $st)
                            <tr>
                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                <td>{{ $st['nis'] }}</td>
                                <td><strong>{{ $st['name'] }}</strong></td>
                                <td style="text-align: center; font-weight: bold; color: #dc2626;">{{ $st['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="section-title">C. Rangkuman Konsep Esensial (Re-Teaching)</div>
            <div style="background-color: #f8fafc; border: 1px solid #cbd5e1; padding: 10px; margin: 8px 0 14px 0; border-radius: 4px;">
                <p style="margin: 0; text-align: justify;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rem, 'concept_recap')) !!}</p>
            </div>

            <div class="section-title">D. Contoh Soal Terbimbing (Worked Example)</div>
            <div style="border: 1px solid #e2e8f0; padding: 10px; margin: 8px 0 14px 0; background: #fffbeb;">
                <div style="font-weight: bold; margin-bottom: 6px;">Soal Model:</div>
                <p style="margin: 0 0 8px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rem, 'worked_example.problem_statement')) !!}</p>
                <div style="font-weight: bold; margin-bottom: 4px;">Langkah Penyelesaian:</div>
                <ol style="margin: 0; padding-left: 20px;">
                    @foreach (data_get($rem, 'worked_example.step_by_step_solution', []) as $step)
                        <li style="margin-bottom: 3px;">{!! \App\Support\SafeHtml::formatHumanText($step) !!}</li>
                    @endforeach
                </ol>
                @if(data_get($rem, 'worked_example.key_takeaway'))
                    <p style="margin-top: 6px; font-weight: bold; color: #b45309;">📌 Kunci Ingat: {!! \App\Support\SafeHtml::formatHumanText(data_get($rem, 'worked_example.key_takeaway')) !!}</p>
                @endif
            </div>

            <div class="section-title">E. Butir Latihan Remedial Berpemandu</div>
            <div style="margin-top: 8px;">
                @foreach (data_get($rem, 'practice_items', []) as $item)
                    <div style="margin-bottom: 12px; padding-bottom: 8px; border-bottom: 1px dashed #ccc;">
                        <div style="font-weight: bold;">Soal #{!! data_get($item, 'item_number') !!}:</div>
                        <p style="margin: 3px 0 6px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($item, 'question_text')) !!}</p>
                        @if(is_array(data_get($item, 'options')) && count(data_get($item, 'options')) > 0)
                            <div style="margin-left: 15px;">
                                @foreach(data_get($item, 'options') as $opt)
                                    <div>{!! \App\Support\SafeHtml::formatHumanText($opt) !!}</div>
                                @endforeach
                            </div>
                        @endif
                        <div style="margin-top: 4px; font-size: 9pt; color: #047857;">
                            <em>💡 Petunjuk Bantu: {!! \App\Support\SafeHtml::formatHumanText(data_get($item, 'hint')) !!}</em>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="page-break"></div>
            <div class="section-title">F. Kunci Jawaban & Panduan Penilaian Guru (Dokumen Pendidik)</div>
            <table class="tabel-kbm">
                <thead>
                    <tr>
                        <th style="width: 10%;">No</th>
                        <th style="width: 25%;">Kunci Jawaban</th>
                        <th>Penjelasan / Pembahasan Konsep</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (data_get($rem, 'practice_items', []) as $item)
                        <tr>
                            <td style="text-align: center; font-weight: bold;">#{!! data_get($item, 'item_number') !!}</td>
                            <td style="font-weight: bold; color: #047857;">{!! \App\Support\SafeHtml::formatHumanText(data_get($item, 'answer_key')) !!}</td>
                            <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($item, 'explanation')) !!}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if(data_get($rem, 'teacher_scaffolding_guide'))
                <div style="margin-top: 15px; padding: 10px; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 4px; font-size: 9.5pt;">
                    <strong>Panduan Khusus Guru Pengampu:</strong><br>
                    {!! \App\Support\SafeHtml::formatHumanText(data_get($rem, 'teacher_scaffolding_guide')) !!}
                </div>
            @endif

        {{-- FORMAT PENGAYAAN --}}
        @else
            @php $enr = data_get($package, 'enrichment_package', []); @endphp

            <div class="section-title">A. Capaian Pengayaan (HOTS)</div>
            <p style="margin: 4px 0 12px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'target_competency')) !!}</p>

            @if (!empty($enrichmentStudents))
                <div class="section-title">B. Daftar Siswa Peserta Pengayaan</div>
                <table class="tabel-kbm" style="margin-bottom: 15px;">
                    <thead>
                        <tr>
                            <th style="width: 8%;">No</th>
                            <th style="width: 25%;">NIS</th>
                            <th>Nama Siswa</th>
                            <th style="width: 20%;">Nilai Asesmen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($enrichmentStudents as $idx => $st)
                            <tr>
                                <td style="text-align: center;">{{ $idx + 1 }}</td>
                                <td>{{ $st['nis'] }}</td>
                                <td><strong>{{ $st['name'] }}</strong></td>
                                <td style="text-align: center; font-weight: bold; color: #047857;">{{ $st['score'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            <div class="section-title">C. Wacana Stimulus & Studi Kasus Nyata</div>
            <div style="background-color: #faf5ff; border: 1px solid #e9d5ff; padding: 12px; margin: 8px 0 14px 0; border-radius: 4px; text-align: justify;">
                <p style="margin: 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'real_world_case')) !!}</p>
            </div>

            <div class="section-title">D. Butir Tantangan Berpikir Tingkat Tinggi (HOTS C4–C6)</div>
            <div style="margin-top: 8px;">
                @foreach (data_get($enr, 'hots_items', []) as $hItem)
                    <div style="margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1px dashed #ccc;">
                        <div style="font-weight: bold;">
                            Tantangan #{!! data_get($hItem, 'item_number') !!} 
                            <span style="font-size: 8.5pt; background: #ede9fe; color: #5b21b6; padding: 2px 6px; border-radius: 3px; margin-left: 6px;">
                                [{!! data_get($hItem, 'cognitive_level') !!}]
                            </span>
                        </div>
                        <p style="margin: 4px 0 6px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($hItem, 'question_text')) !!}</p>
                        <div style="font-size: 9pt; color: #4b5563;">
                            <em>Ekspektasi Hasil / Panduan Jawaban: {!! \App\Support\SafeHtml::formatHumanText(data_get($hItem, 'expected_response_guide')) !!}</em>
                        </div>
                    </div>
                @endforeach
            </div>

            @if(data_get($enr, 'mini_project_prompt'))
                <div class="section-title">E. Ide Mini-Projek / Investigasi Mandiri</div>
                <div style="border: 1px solid #c7d2fe; background: #eef2ff; padding: 10px; margin: 8px 0 14px 0; border-radius: 4px;">
                    <div style="font-weight: bold; color: #3730a3;">{!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'mini_project_prompt.project_title')) !!}</div>
                    <p style="margin: 4px 0 6px 0;">{!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'mini_project_prompt.instructions')) !!}</p>
                    <div style="font-size: 9pt; color: #4338ca;">
                        <strong>Estimasi Waktu:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'mini_project_prompt.estimated_duration', '1-2 Minggu')) !!} | 
                        <strong>Hasil Akhir:</strong> {!! \App\Support\SafeHtml::formatHumanText(data_get($enr, 'mini_project_prompt.deliverable_product')) !!}
                    </div>
                </div>
            @endif

            @if(count(data_get($enr, 'scoring_rubric', [])) > 0)
                <div class="section-title">F. Rubrik Penilaian Pengayaan</div>
                <table class="tabel-kbm">
                    <thead>
                        <tr>
                            <th style="width: 25%;">Kriteria</th>
                            <th>Indikator Capaian</th>
                            <th style="width: 20%;">Rentang Skor</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(data_get($enr, 'scoring_rubric', []) as $rub)
                            <tr>
                                <td style="font-weight: bold;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'criteria')) !!}</td>
                                <td>{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'indicator')) !!}</td>
                                <td style="text-align: center; font-weight: bold; color: #047857;">{!! \App\Support\SafeHtml::formatHumanText(data_get($rub, 'score_range')) !!}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endif

        <!-- TANDA TANGAN -->
        <table class="tanda-tangan">
            <tr>
                <td>
                    Mengetahui,<br>
                    Kepala Sekolah
                    <br><br><br><br>
                    <strong>{{ $headmasterTeacher->name ?? ($headmasterUser->name ?? '..................................') }}</strong><br>
                    NIP. {{ $headmasterTeacher->nip ?? '-' }}
                </td>
                <td>
                    {{ $school->city ?? 'Jakarta' }}, {{ $currentDate }}<br>
                    Guru Mata Pelajaran
                    <br><br><br><br>
                    <strong>{{ $teacher->name ?? '..................................' }}</strong><br>
                    NIP. {{ $teacher->nip ?? '-' }}
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
