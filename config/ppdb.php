<?php

return [
    'uploads' => [
        'max_file_size_kb' => (int) env('PPDB_MAX_FILE_SIZE_KB', 10240),
        'allowed_extensions' => ['pdf', 'jpg', 'jpeg', 'png'],
        'clamav' => [
            'enabled' => (bool) env('PPDB_CLAMAV_ENABLED', false),
            'binary' => env('PPDB_CLAMAV_BINARY', 'clamdscan'),
        ],
    ],
    'orphan_retention_days' => (int) env('PPDB_ORPHAN_RETENTION_DAYS', 7),
    'student_login_domain' => env('PPDB_STUDENT_LOGIN_DOMAIN', 'akun.siakad.local'),
];
