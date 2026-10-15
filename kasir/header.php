<?php
// header.php - dipanggil di setiap halaman
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Aplikasi Kasir</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
  body { margin:0; font-family: 'Segoe UI', Arial, sans-serif; background:#f4f6f9; }
  .navbar-top {
    background:#1c1c2b; color:#fff; padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between;
  }
  .navbar-top .brand { font-size:20px; font-weight:600; }
  .layout { display:flex; min-height:calc(100vh - 56px); }
  .sidebar {
    width:230px; background:#1c1c2b; color:#cfcfe0; padding:20px 0; flex-shrink:0;
  }
  .sidebar .menu-label { padding:0 20px; font-size:12px; letter-spacing:1px; color:#8888a8; margin-bottom:10px; }
  .sidebar a {
    display:flex; align-items:center; gap:10px; padding:12px 20px; color:#cfcfe0;
    text-decoration:none; transition:0.15s;
  }
  .sidebar a:hover, .sidebar a.active { background:#2d2d44; color:#fff; }
  .content { flex:1; padding:24px; }
  .page-title { font-size:28px; font-weight:700; margin-bottom:4px; }
  .alert-banner { background:#e9ecef; color:#6c757d; padding:10px 16px; border-radius:6px; margin-bottom:16px; }
</style>
</head>
<body>

<div class="navbar-top">
  <div class="brand">Aplikasi Kasir</div>
  <div>&#9776;</div>
</div>

<div class="layout">
  <div class="sidebar">
    <div class="menu-label">MENU</div>
    <a href="order.php" class="<?= ($active ?? '') === 'order' ? 'active' : '' ?>">🛒 Order</a>
    <a href="stok.php" class="<?= ($active ?? '') === 'stok' ? 'active' : '' ?>">📦 Stock Barang</a>
    <a href="barang_masuk.php" class="<?= ($active ?? '') === 'barang_masuk' ? 'active' : '' ?>">📥 Barang Masuk</a>
    <a href="pelanggan.php" class="<?= ($active ?? '') === 'pelanggan' ? 'active' : '' ?>">👤 Kelola Pelanggan</a>
    <a href="logout.php">🚪 Logout</a>
  </div>

  <div class="content">
