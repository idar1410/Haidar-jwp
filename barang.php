<?php
if (!isset($_SESSION['username'])) { exit; }
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'insert') {
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $cek = mysqli_query($conn, "SELECT * FROM barang WHERE nama_barang = '$nama_barang'");
    if(mysqli_num_rows($cek) > 0) header("Location: index.php?page=barang&msg=err_dup");
    else {
        mysqli_query($conn, "INSERT INTO barang (id_kategori, nama_barang, stok, status) VALUES ($id_kategori, '$nama_barang', 0, 'Tidak Tersedia')");
        header("Location: index.php?page=barang&msg=add_ok");
    }
    exit;
}

if ($action == 'update') {
    $id_barang = (int)$_POST['id_barang'];
    $nama_barang = mysqli_real_escape_string($conn, $_POST['nama_barang']);
    $id_kategori = (int)$_POST['id_kategori'];
    $cek = mysqli_query($conn, "SELECT * FROM barang WHERE nama_barang = '$nama_barang' AND id_barang != $id_barang");
    if(mysqli_num_rows($cek) > 0) header("Location: index.php?page=barang&msg=err_dup");
    else {
        mysqli_query($conn, "UPDATE barang SET nama_barang='$nama_barang', id_kategori=$id_kategori WHERE id_barang=$id_barang");
        header("Location: index.php?page=barang&msg=edit_ok");
    }
    exit;
}

if ($action == 'delete') {
    $id_barang = (int)$_POST['id_barang'];
    mysqli_query($conn, "DELETE FROM barang WHERE id_barang=$id_barang");
    header("Location: index.php?page=barang&msg=del_ok");
    exit;
}

// Ambil data kategori sekali untuk dropdown form
$kategori_arr = [];
$qk = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
while($rk = mysqli_fetch_assoc($qk)) { $kategori_arr[] = $rk; }

// Ambil data barang dan simpan di array untuk memisahkan tabel dan modal
$data_barang = [];
$sql = "SELECT b.*, k.nama_kategori FROM barang b JOIN kategori k ON b.id_kategori = k.id_kategori ORDER BY b.id_barang DESC";
$q = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($q)) { $data_barang[] = $row; }
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark">Menu Daftar Barang (Snack)</h2>
        <p class="text-muted small">Kelola data makanan ringan, monitoring stok, dan status ketersediaan.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        ➕ Tambah Snack Baru
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show <?php echo ($_GET['msg'] == 'err_dup') ? 'alert-danger' : 'alert-info'; ?>" role="alert">
        <?php
        if($_GET['msg']=='add_ok') echo "✅ Snack berhasil didaftarkan (Stok awal diset 0).";
        if($_GET['msg']=='edit_ok') echo "📝 Data snack berhasil diperbarui.";
        if($_GET['msg']=='del_ok') echo "🗑️ Data snack berhasil dihapus.";
        if($_GET['msg']=='err_dup') echo "❌ Gagal! Nama barang (snack) tersebut sudah terdaftar.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card bg-white p-4 shadow-sm border-0 mb-4">
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60" class="text-center">No</th>
                    <th>Nama Makanan Ringan</th>
                    <th>Kategori</th>
                    <th>Stok</th>
                    <th class="text-center">Status</th>
                    <th width="200" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $no = 1;
                if(empty($data_barang)) echo "<tr><td colspan='6' class='text-center py-3 text-muted'>Belum ada item snack.</td></tr>";
                foreach($data_barang as $row):
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="fw-bold text-secondary"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($row['nama_kategori']); ?></span></td>
                    <td class="fw-bold"><?= $row['stok']; ?> Pcs</td>
                    <td class="text-center">
                        <?= ($row['status'] == 'Tersedia') ? '<span class="badge bg-success px-2">Tersedia</span>' : '<span class="badge bg-danger px-2">Tidak Tersedia</span>'; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-info btn-sm text-white" data-bs-toggle="modal" data-bs-target="#modalDetail<?= $row['id_barang']; ?>">Detail</button>
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_barang']; ?>">Edit</button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $row['id_barang']; ?>">Hapus</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Tambah Snack Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=barang&action=insert" method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label fw-bold">Nama Snack</label>
                  <input type="text" name="nama_barang" class="form-control" required autocomplete="off">
              </div>
              <div class="mb-3">
                  <label class="form-label fw-bold">Kategori</label>
                  <select name="id_kategori" class="form-select" required>
                      <option value="">-- Pilih Kategori --</option>
                      <?php foreach($kategori_arr as $rk) { echo "<option value='".$rk['id_kategori']."'>".htmlspecialchars($rk['nama_kategori'])."</option>"; } ?>
                  </select>
              </div>
              <div class="alert alert-light border text-muted small p-2 mb-0">ℹ️ Stok awal otomatis di-set 0.</div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>

<?php 
// Looping ulang khusus untuk merender Modal Detail, Edit, dan Hapus
foreach($data_barang as $row): 
?>
<div class="modal fade" id="modalDetail<?= $row['id_barang']; ?>" tabindex="-1">
    <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header bg-info text-white">
        <h5 class="modal-title fw-bold">Detail Produk: <?= htmlspecialchars($row['nama_barang']); ?></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <table class="table table-bordered table-striped mt-2">
                <tr><th width="200">ID Barang</th><td>#SNACK-<?= $row['id_barang']; ?></td></tr>
                <tr><th>Nama Makanan Ringan</th><td class="fw-bold text-primary"><?= htmlspecialchars($row['nama_barang']); ?></td></tr>
                <tr><th>Kategori</th><td><?= htmlspecialchars($row['nama_kategori']); ?></td></tr>
                <tr><th>Stok Terakhir</th><td><strong><?= $row['stok']; ?> Pcs</strong></td></tr>
                <tr><th>Status Ketersediaan</th><td><?= ($row['status']=='Tersedia') ? '<span class="badge bg-success">Tersedia</span>':'<span class="badge bg-danger">Tidak Tersedia</span>'; ?></td></tr>
            </table>
            <h6 class="fw-bold mt-4 mb-2 text-secondary">Riwayat Keluar Masuk:</h6>
            <div style="max-height: 250px; overflow-y: auto;">
                <table class="table table-sm table-bordered text-center small">
                    <thead class="table-secondary sticky-top">
                        <tr><th>No</th><th>Tanggal</th><th>Transaksi</th><th>Jumlah</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $id_b = $row['id_barang'];
                        $qr = mysqli_query($conn, "SELECT * FROM riwayat_stok WHERE id_barang=$id_b ORDER BY id_riwayat DESC");
                        $n = 1;
                        if(mysqli_num_rows($qr) == 0) echo "<tr><td colspan='4' class='text-muted'>Belum ada transaksi.</td></tr>";
                        while($rl = mysqli_fetch_assoc($qr)):
                        ?>
                        <tr>
                            <td><?= $n++; ?></td>
                            <td><?= date('d-m-Y', strtotime($rl['tanggal'])); ?></td>
                            <td><?= ($rl['jenis_transaksi']=='Masuk') ? '<span class="badge bg-success">Masuk</span>':'<span class="badge bg-danger">Keluar</span>'; ?></td>
                            <td class="fw-bold"><?= $rl['jumlah']; ?> Pcs</td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        </div>
    </div>
    </div>
</div>

<div class="modal fade" id="modalEdit<?= $row['id_barang']; ?>" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header bg-warning">
        <h5 class="modal-title fw-bold">Edit Data Snack</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <form action="index.php?page=barang&action=update" method="POST">
            <div class="modal-body">
                <input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Snack</label>
                    <input type="text" name="nama_barang" value="<?= htmlspecialchars($row['nama_barang']); ?>" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kategori</label>
                    <select name="id_kategori" class="form-select" required>
                        <?php
                        foreach($kategori_arr as $rk){
                            $s = ($rk['id_kategori'] == $row['id_kategori']) ? 'selected' : '';
                            echo "<option value='".$rk['id_kategori']."' $s>".htmlspecialchars($rk['nama_kategori'])."</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-success fw-bold text-white">Simpan</button>
            </div>
        </form>
    </div>
    </div>
</div>

<div class="modal fade" id="modalHapus<?= $row['id_barang']; ?>" tabindex="-1">
    <div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header bg-danger text-white">
        <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <form action="index.php?page=barang&action=delete" method="POST">
            <div class="modal-body text-center">
                <input type="hidden" name="id_barang" value="<?= $row['id_barang']; ?>">
                <p>Apakah Anda yakin ingin menghapus barang <strong><?= htmlspecialchars($row['nama_barang']); ?></strong>?</p>
                <small class="text-danger">Semua riwayat transaksi untuk barang ini juga akan hilang!</small>
            </div>
            <div class="modal-footer justify-content-center">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger fw-bold">Ya, Hapus!</button>
            </div>
        </form>
    </div>
    </div>
</div>
<?php endforeach; ?>