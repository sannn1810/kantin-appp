<?php
require __DIR__ . '/config.php';

// === Handle Tambah / Edit ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $nama    = trim($_POST['nama'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $alamat  = trim($_POST['alamat'] ?? '');

    if ($_POST['action'] === 'add' && $nama && $telepon && $alamat) {
        $stmt = $pdo->prepare("INSERT INTO pelanggan (nama, telepon, alamat) VALUES (?, ?, ?)");
        $stmt->execute([$nama, $telepon, $alamat]);
    }

    if ($_POST['action'] === 'edit' && $nama && $telepon && $alamat) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("UPDATE pelanggan SET nama=?, telepon=?, alamat=? WHERE id=?");
        $stmt->execute([$nama, $telepon, $alamat, $id]);
    }

    header("Location: pelanggan.php");
    exit;
}

// === Handle Hapus ===
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM pelanggan WHERE id=?");
    $stmt->execute([$id]);
    header("Location: pelanggan.php");
    exit;
}

// === Ambil Data ===
$pelanggan = $pdo->query("SELECT * FROM pelanggan ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
$jumlah = count($pelanggan);

$active = 'pelanggan';
include __DIR__ . '/header.php';
?>

<div class="page-title">Data Pelanggan</div>
<div class="alert-banner">Selamat Datang</div>

<div class="card text-white mb-3" style="background:#3a5fcd; max-width:300px;">
  <div class="card-body fw-bold">
    Jumlah Pelanggan: <?= $jumlah ?>
  </div>
</div>

<button class="btn btn-teal mb-3" style="background:#16a3a3; color:#fff;" data-bs-toggle="modal" data-bs-target="#modalTambah">
  Tambah Pelanggan
</button>

<div class="card">
  <div class="card-header fw-bold">
    <i class="bi bi-table"></i> Data Pelanggan
  </div>
  <div class="card-body">
    <table id="tabelPelanggan" class="table table-striped table-bordered" style="width:100%">
      <thead>
        <tr>
          <th>No</th>
          <th>Nama Pelanggan</th>
          <th>No Telepon</th>
          <th>Alamat</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($pelanggan as $i => $p): ?>
          <tr>
            <td><?= $i + 1 ?></td>
            <td><?= htmlspecialchars($p['nama']) ?></td>
            <td><?= htmlspecialchars($p['telepon']) ?></td>
            <td><?= htmlspecialchars($p['alamat']) ?></td>
            <td>
              <button class="btn btn-warning btn-sm" data-bs-toggle="modal"
                data-bs-target="#modalEdit"
                data-id="<?= $p['id'] ?>"
                data-nama="<?= htmlspecialchars($p['nama']) ?>"
                data-telepon="<?= htmlspecialchars($p['telepon']) ?>"
                data-alamat="<?= htmlspecialchars($p['alamat']) ?>">
                Edit
              </button>
              <a href="pelanggan.php?delete=<?= $p['id'] ?>"
                 class="btn btn-danger btn-sm"
                 onclick="return confirm('Hapus data pelanggan ini?')">
                Delete
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Tambah Data Pelanggan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="mb-3">
            <input type="text" name="nama" class="form-control" placeholder="Nama Pelanggan" required>
          </div>
          <div class="mb-3">
            <input type="text" name="telepon" class="form-control" placeholder="No Telepon" required>
          </div>
          <div class="mb-3">
            <input type="text" name="alamat" class="form-control" placeholder="Alamat" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Submit</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title">Edit Data Pelanggan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="edit">
          <input type="hidden" name="id" id="edit_id">
          <div class="mb-3">
            <input type="text" name="nama" id="edit_nama" class="form-control" placeholder="Nama Pelanggan" required>
          </div>
          <div class="mb-3">
            <input type="text" name="telepon" id="edit_telepon" class="form-control" placeholder="No Telepon" required>
          </div>
          <div class="mb-3">
            <input type="text" name="alamat" id="edit_alamat" class="form-control" placeholder="Alamat" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Update</button>
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  $('#tabelPelanggan').DataTable();

  $('#modalEdit').on('show.bs.modal', function (event) {
    const btn = $(event.relatedTarget);
    $('#edit_id').val(btn.data('id'));
    $('#edit_nama').val(btn.data('nama'));
    $('#edit_telepon').val(btn.data('telepon'));
    $('#edit_alamat').val(btn.data('alamat'));
  });
});
</script>

<?php include __DIR__ . '/footer.php'; ?>
