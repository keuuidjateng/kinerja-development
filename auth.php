<?php
declare(strict_types=1);
/*
 * auth.php - fungsi autentikasi & sesi (butuh PHP 8.0+)
 * Dipakai oleh login.php, logout.php, dan setiap halaman yang harus login.
 */

const NAMA_SESI      = 'ANEV_SESI';
const HALAMAN_LOGIN  = 'login.php';
const HALAMAN_UTAMA  = 'agustus-2026/index.php';   // tujuan setelah login (relatif terhadap folder auth.php)
const MAKS_GAGAL     = 5;                          // percobaan gagal sebelum dikunci
const WAKTU_KUNCI    = 300;                        // detik (5 menit)
const MASA_INGAT     = 60 * 60 * 24 * 30;          // "Ingat saya": 30 hari
const BATAS_DIAM     = 60 * 60 * 2;                // tanpa "Ingat saya": keluar otomatis setelah 2 jam tidak aktif

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function https_aktif(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? '') === '443');
}

/* Awalan URL folder aplikasi, mis. "" atau "/anev" */
function url_dasar(): string
{
    $doc = rtrim(str_replace('\\', '/', (string)realpath($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
    $dir = str_replace('\\', '/', __DIR__);
    return ($doc !== '' && str_starts_with($dir, $doc)) ? substr($dir, strlen($doc)) : '';
}

function alihkan(string $tujuan): void
{
    header('Location: ' . url_dasar() . '/' . ltrim($tujuan, '/'));
    exit;
}

function mulai_sesi(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.gc_maxlifetime', (string)MASA_INGAT);
    session_name(NAMA_SESI);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => https_aktif(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/* ---------- Tabel pengguna ---------- */
function cari_pengguna(string $username): ?array
{
    static $tabel = null;
    if ($tabel === null) {
        $tabel = require __DIR__ . '/data/pengguna.php';
    }
    return $tabel[$username] ?? null;
}

/* ---------- CSRF ---------- */
function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid(string $token): bool
{
    return !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ---------- Batas percobaan gagal (per sesi) ---------- */
function sisa_kunci(): int
{
    $sampai = (int)($_SESSION['kunci_sampai'] ?? 0);
    return max(0, $sampai - time());
}

function catat_gagal(): void
{
    $_SESSION['gagal'] = (int)($_SESSION['gagal'] ?? 0) + 1;
    if ($_SESSION['gagal'] >= MAKS_GAGAL) {
        $_SESSION['kunci_sampai'] = time() + WAKTU_KUNCI;
        $_SESSION['gagal'] = 0;
    }
}

/* ---------- Login / logout ---------- */
function login_berhasil(string $username, array $p, bool $ingat): void
{
    session_regenerate_id(true);            // cegah session fixation
    $_SESSION = [
        'pengguna' => [
            'username' => $username,
            'nama'     => $p['nama'],
            'peran'    => $p['peran'],
        ],
        'ingat'     => $ingat,
        'login_at'  => time(),
        'aktivitas' => time(),
    ];
    if ($ingat) {                           // perpanjang masa cookie sesi
        setcookie(session_name(), session_id(), [
            'expires'  => time() + MASA_INGAT,
            'path'     => '/',
            'secure'   => https_aktif(),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
    csrf_token();
}

function keluar(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $c = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $c['path'],
            'domain'   => $c['domain'],
            'secure'   => $c['secure'],
            'httponly' => $c['httponly'],
            'samesite' => $c['samesite'] ?: 'Lax',
        ]);
    }
    session_destroy();
}

function sudah_login(): bool
{
    if (empty($_SESSION['pengguna'])) {
        return false;
    }
    if (empty($_SESSION['ingat']) && time() - (int)($_SESSION['aktivitas'] ?? 0) > BATAS_DIAM) {
        keluar();
        mulai_sesi();                       // sesi baru yang bersih
        return false;
    }
    $_SESSION['aktivitas'] = time();
    return true;
}

function pengguna_aktif(): ?array
{
    return $_SESSION['pengguna'] ?? null;
}

/*
 * Panggil di baris paling atas setiap halaman yang harus login:
 *   require __DIR__ . '/../auth.php'; wajib_login();   (di dalam blok php)
 * Batasi per peran:  wajib_login(['Admin', 'Editor']);
 */
function wajib_login(array $peranBoleh = []): void
{
    mulai_sesi();
    header('Cache-Control: no-store');
    if (!sudah_login()) {
        alihkan(HALAMAN_LOGIN);
    }
    if ($peranBoleh && !in_array($_SESSION['pengguna']['peran'], $peranBoleh, true)) {
        http_response_code(403);
        exit('Anda tidak memiliki akses ke halaman ini.');
    }
}
