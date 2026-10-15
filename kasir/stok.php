<?php
require __DIR__ . '/config.php';
$barang = $pdo->query("SELECT * FROM barang ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$active = 'stok';
include __DIR__ . '/header.php';
?>

<div class="page-title">Stock Barang</div>
<div class="alert-banner">Daftar stok barang saat ini</div>

<div class="card">
  <div class="card-body">
    <table id="tabelBarang" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr><th>No</th><th>Nama Barang</th><th>Harga</th><th>Stok</th></tr>
      </thead>
      <tbody>
        <?php foreach ($barang as $i => $b): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($b['nama_barang']) ?></td>
            <td>Rp <?= number_format($b['harga'],0,',','.') ?></td>
            <td><?= $b['stok'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>$(document).ready(function(){ $('#tabelBarang').DataTable(); });</script>

<?php include __DIR__ . '/footer.php'; ?>
