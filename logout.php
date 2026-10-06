<?php
declare(strict_types=1);
require __DIR__ . '/auth.php';
mulai_sesi();

// Keluar hanya lewat POST + token CSRF (tombol/form), bukan lewat tautan biasa
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_valid((string)($_POST['csrf'] ?? ''))) {
    keluar();
}
alihkan(HALAMAN_LOGIN);
