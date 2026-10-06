<?php
require __DIR__ . '/auth.php';
wajib_login();                      // wajib login; contoh pembatasan peran: wajib_login(['Admin','Editor']);
$u = pengguna_aktif();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Contoh Halaman Terproteksi</title>
<style>body{font-family:"Segoe UI",system-ui,sans-serif;max-width:560px;margin:60px auto;padding:0 16px;color:#0d2f3f}
button{padding:8px 16px;border:0;border-radius:8px;background:#0a7ea4;color:#fff;font:inherit;cursor:pointer}</style>
</head>
<body>
<h1>Selamat datang, <?= e($u['nama']) ?></h1>
<p>Nama pengguna: <strong><?= e($u['username']) ?></strong> &middot; Peran: <strong><?= e($u['peran']) ?></strong></p>
<form method="post" action="<?= e(url_dasar()) ?>/logout.php">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <button type="submit">Keluar</button>
</form>
</body>
</html>
