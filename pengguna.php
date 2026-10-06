<?php
/*
 * TABEL PENGGUNA (contoh)
 * Kunci array = nama pengguna (huruf kecil). Kata sandi disimpan sebagai hash bcrypt
 * (hasil password_hash), bukan teks asli.
 *
 * Akun contoh (hapus/ganti sebelum dipakai sungguhan):
 *   admin.uidjateng     / Jateng@2026
 *   kinkeu.uidjateng    / Kinkeu@2026
 *   manajemen.uidjateng / Eksekutif@2026
 *
 * Membuat hash baru:  php -r "echo password_hash('KataSandiBaru', PASSWORD_DEFAULT), PHP_EOL;"
 *
 * Untuk produksi, pindahkan folder data/ ke luar document root atau ganti dengan tabel database.
 */
return [
    'admin.uidjateng' => [
        'hash'  => '$2y$12$Ol7ZAkkAIj/2deEmkTXX3e657RvTVSzPijcCINBIS6YWCq5AX1RXm',
        'nama'  => 'Administrator UID Jateng',
        'peran' => 'Admin',
    ],
    'kinkeu.uidjateng' => [
        'hash'  => '$2y$12$N2RQgyrjMhqNk81U6KmC8OwgddRp/WLl1mE1dQj/G9g8WtTWMYG4G',
        'nama'  => 'Tim Kinerja Keuangan',
        'peran' => 'Editor',
    ],
    'manajemen.uidjateng' => [
        'hash'  => '$2y$12$WrLs9ceNAAuONtVLBjE22.CexpRyJ5GfWdk2/zk2Gh9mzAa4NA.UK',
        'nama'  => 'Manajemen UID Jateng',
        'peran' => 'Viewer',
    ],
];
