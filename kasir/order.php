<?php
require __DIR__ . '/config.php';
session_start();

if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_to_cart') {
        $id = (int)$_POST['barang_id'];
        $_SESSION['cart'][$id] = ($_SESSION['cart'][$id] ?? 0) + 1;
    }

    if ($action === 'change_qty') {
        $id = (int)$_POST['barang_id'];
        $_SESSION['cart'][$id] += (int)$_POST['delta'];
        if ($_SESSION['cart'][$id] <= 0) unset($_SESSION['cart'][$id]);
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][(int)$_POST['barang_id']]);
    }

    if ($action === 'clear') {
        $_SESSION['cart'] = [];
    }

    if ($action === 'checkout') {
        $paid = (int)$_POST['paid'];
        $pelanggan_id = (int)($_POST['pelanggan_id'] ?? 0);

        $items = [];
        $total = 0;
        if (!empty($_SESSION['cart'])) {
            $ids = array_keys($_SESSION['cart']);
            $in = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $pdo->prepare("SELECT * FROM barang WHERE id IN ($in)");
            $stmt->execute($ids);
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
                $qty = $_SESSION['cart'][$b['id']];
                $subtotal = $b['harga'] * $qty;
                $total += $subtotal;
                $items[] = ['id'=>$b['id'], 'nama'=>$b['nama_barang'], 'harga'=>$b['harga'], 'qty'=>$qty, 'subtotal'=>$subtotal];
            }
        }

        if (empty($items)) {
            $message = "Keranjang masih kosong.";
        } elseif ($paid < $total) {
            $message = "Uang bayar kurang.";
        } else {
            $change = $paid - $total;
            $stmt = $pdo->prepare("INSERT INTO transaksi (pelanggan_id, total, bayar, kembalian) VALUES (?, ?, ?, ?)");
            $stmt->execute([$pelanggan_id ?: null, $total, $paid, $change]);
            $trxId = $pdo->lastInsertId();

            $stmtItem = $pdo->prepare("INSERT INTO transaksi_item (transaksi_id, barang_id, nama_barang, harga, qty) VALUES (?, ?, ?, ?, ?)");
            $stmtStok = $pdo->prepare("UPDATE barang SET stok = stok - ? WHERE id = ?");
            foreach ($items as $it) {
                $stmtItem->execute([$trxId, $it['id'], $it['nama'], $it['harga'], $it['qty']]);
                $stmtStok->execute([$it['qty'], $it['id']]);
            }

            $_SESSION['last_receipt'] = ['id'=>$trxId, 'date'=>date('d-m-Y H:i:s'), 'items'=>$items, 'total'=>$total, 'paid'=>$paid, 'change'=>$change];
            $_SESSION['cart'] = [];
        }
    }
}

$barangList = $pdo->query("SELECT * FROM barang ORDER BY nama_barang")->fetchAll(PDO::FETCH_ASSOC);
$pelangganList = $pdo->query("SELECT * FROM pelanggan ORDER BY nama")->fetchAll(PDO::FETCH_ASSOC);

$cartItems = [];
$total = 0;
if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmt = $pdo->prepare("SELECT * FROM barang WHERE id IN ($in)");
    $stmt->execute($ids);
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $b) {
        $qty = $_SESSION['cart'][$b['id']];
        $subtotal = $b['harga'] * $qty;
        $total += $subtotal;
        $cartItems[] = ['id'=>$b['id'], 'nama'=>$b['nama_barang'], 'harga'=>$b['harga'], 'qty'=>$qty, 'subtotal'=>$subtotal];
    }
}

$receipt = $_SESSION['last_receipt'] ?? null;
unset($_SESSION['last_receipt']);

$active = 'order';
include __DIR__ . '/header.php';
?>

<div class="page-title">Order</div>
<div class="alert-banner">Transaksi Penjualan</div>

<?php if ($message): ?><div class="alert alert-warning"><?= htmlspecialchars($message) ?></div><?php endif; ?>

<div class="row">
  <div class="col-md-7">
    <div class="card">
      <div class="card-header fw-bold">Daftar Barang</div>
      <div class="card-body">
        <div class="row g-2">
          <?php foreach ($barangList as $b): ?>
            <div class="col-md-4">
              <form method="post">
                <input type="hidden" name="action" value="add_to_cart">
                <input type="hidden" name="barang_id" value="<?= $b['id'] ?>">
                <button type="submit" class="btn btn-outline-primary w-100 text-start">
                  <strong><?= htmlspecialchars($b['nama_barang']) ?></strong><br>
                  Rp <?= number_format($b['harga'],0,',','.') ?> | Stok: <?= $b['stok'] ?>
                </button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="col-md-5">
    <div class="card">
      <div class="card-header fw-bold">Keranjang</div>
      <div class="card-body">
        <table class="table table-sm">
          <thead><tr><th>Barang</th><th>Qty</th><th>Subtotal</th><th></th></tr></thead>
          <tbody>
            <?php foreach ($cartItems as $item): ?>
              <tr>
                <td><?= htmlspecialchars($item['nama']) ?></td>
                <td>
                  <form class="d-inline" method="post">
                    <input type="hidden" name="action" value="change_qty">
                    <input type="hidden" name="barang_id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="delta" value="-1">
                    <button class="btn btn-sm btn-light border">-</button>
                  </form>
                  <?= $item['qty'] ?>
                  <form class="d-inline" method="post">
                    <input type="hidden" name="action" value="change_qty">
                    <input type="hidden" name="barang_id" value="<?= $item['id'] ?>">
                    <input type="hidden" name="delta" value="1">
                    <button class="btn btn-sm btn-light border">+</button>
                  </form>
                </td>
                <td>Rp <?= number_format($item['subtotal'],0,',','.') ?></td>
                <td>
                  <form method="post">
                    <input type="hidden" name="action" value="remove">
                    <input type="hidden" name="barang_id" value="<?= $item['id'] ?>">
                    <button class="btn btn-sm btn-danger">✕</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($cartItems)): ?>
              <tr><td colspan="4">Keranjang kosong</td></tr>
            <?php endif; ?>
          </tbody>
        </table>

        <h5 class="text-end">Total: Rp <?= number_format($total,0,',','.') ?></h5>

        <form method="post">
          <input type="hidden" name="action" value="checkout">
          <div class="mb-2">
            <label class="form-label">Pelanggan (opsional)</label>
            <select name="pelanggan_id" class="form-select">
              <option value="">-- Umum --</option>
              <?php foreach ($pelangganList as $p): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Uang Bayar</label>
            <input type="number" name="paid" class="form-control" required>
          </div>
          <button class="btn btn-success w-100">Bayar & Cetak Struk</button>
        </form>

        <form method="post" class="mt-2">
          <input type="hidden" name="action" value="clear">
          <button class="btn btn-danger w-100">Kosongkan Keranjang</button>
        </form>

        <?php if ($receipt): ?>
        <pre class="mt-3 border bg-light p-2">
===== STRUK #<?= $receipt['id'] ?> =====
<?= $receipt['date'] ?>
-------------------------
<?php foreach ($receipt['items'] as $it): ?>
<?= $it['nama'] ?> x<?= $it['qty'] ?>
   Rp <?= number_format($it['harga'],0,',','.') ?> = Rp <?= number_format($it['subtotal'],0,',','.') ?>
<?php endforeach; ?>
-------------------------
Total     : Rp <?= number_format($receipt['total'],0,',','.') ?>
Bayar     : Rp <?= number_format($receipt['paid'],0,',','.') ?>
Kembalian : Rp <?= number_format($receipt['change'],0,',','.') ?>
=========================
        </pre>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/footer.php'; ?>
