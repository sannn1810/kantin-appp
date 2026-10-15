<?php
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $barang_id = (int)$_POST['barang_id'];
    $jumlah = (int)$_POST['jumlah'];
    $tanggal = $_POST['tanggal'];

    if ($barang_id && $jumlah > 0 && $tanggal) {
        $pdo->prepare("INSERT INTO barang_masuk (barang_id, jumlah, tanggal) VALUES (?, ?, ?)")
            ->execute([$barang_id, $jumlah, $tanggal]);

        $pdo->prepare("UPDATE barang SET stok = stok + ? WHERE id = ?")
            ->execute([$jumlah, $barang_id]);
    }
    header("Location: barang_masuk.php");
    exit;
}

$barangList = $pdo->query("SELECT * FROM barang ORDER BY nama_barang")->fetchAll(PDO::FETCH_ASSOC);

$riwayat = $pdo->query("
    SELECT bm.*, b.nama_barang
    FROM barang_masuk bm
    JOIN barang b ON b.id = bm.barang_id
    ORDER BY bm.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

$active = 'barang_masuk';
include __DIR__ . '/header.php';
?>

<div class="page-title">Barang Masuk</div>
<div class="alert-banner">Catat penambahan stok barang</div>

<div class="card mb-3">
  <div class="card-body">
    <form method="post" class="row g-3">
      <div class="col-md-4">
        <label class="form-label">Barang</label>
        <select name="barang_id" class="form-select" required>
          <option value="">-- Pilih Barang --</option>
          <?php foreach ($barangList as $b): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['nama_barang']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label">Jumlah</label>
        <input type="number" name="jumlah" class="form-control" min="1" required>
      </div>
      <div class="col-md-3">
        <label class="form-label">Tanggal</label>
        <input type="date" name="tanggal" class="form-control" required value="<?= date('Y-m-d') ?>">
      </div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="submit" class="btn btn-success w-100">Simpan</button>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <table id="tabelMasuk" class="table table-striped table-bordered" style="width:100%">
      <thead><tr><th>No</th><th>Nama Barang</th><th>Jumlah</th><th>Tanggal</th></tr></thead>
      <tbody>
        <?php foreach ($riwayat as $i => $r): ?>
          <tr>
            <td><?= $i+1 ?></td>
            <td><?= htmlspecialchars($r['nama_barang']) ?></td>
            <td><?= $r['jumlah'] ?></td>
            <td><?= $r['tanggal'] ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>$(document).ready(function(){ $('#tabelMasuk').DataTable(); });</script>

<?php include __DIR__ . '/footer.php'; ?>
