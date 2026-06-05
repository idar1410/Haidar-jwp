<?php
// --------------------------------------------------------
// File: pages/persediaan.php
// Deskripsi: Form Transaksi Stok In/Out & Auto Update Status Barang
// --------------------------------------------------------
if (!isset($_SESSION['username'])) { exit; }

// PROSES SAVE TRANSAKSI PERSEDIAAN
if (isset($_GET['action']) && $_GET['action'] == 'save_transaction') {
    $id_barang       = (int)$_POST['id_barang'];
    $jenis_transaksi = $_POST['jenis_transaksi']; // 'Masuk' atau 'Keluar'
    $jumlah          = (int)$_POST['jumlah'];
    $tanggal         = $_POST['tanggal'];
    
    if ($jumlah <= 0) {
        header("Location: index.php?page=persediaan&msg=err_qty");
        exit;
    }
    
    // VALIDASI: Jika barang keluar, pastikan stok fisik di gudang mencukupi
    if ($jenis_transaksi == 'Keluar') {
        $q_cek = mysqli_query($conn, "SELECT stok FROM barang WHERE id_barang=$id_barang");
        $r_cek = mysqli_fetch_assoc($q_cek);
        if ($r_cek['stok'] < $jumlah) {
            header("Location: index.php?page=persediaan&msg=err_insufficient");
            exit;
        }
    }
    
    // 1. Catat ke riwayat log transaksi
    mysqli_query($conn, "INSERT INTO riwayat_stok (id_barang, jenis_transaksi, jumlah, tanggal) VALUES ($id_barang, '$jenis_transaksi', $jumlah, '$tanggal')");
    
    // 2. Perbarui jumlah stok di master barang
    if ($jenis_transaksi == 'Masuk') {
        mysqli_query($conn, "UPDATE barang SET stok = stok + $jumlah WHERE id_barang=$id_barang");
    } else {
        mysqli_query($conn, "UPDATE barang SET stok = stok - $jumlah WHERE id_barang=$id_barang");
    }
    
    // 3. Update otomatis Status Ketersediaan aktual
    mysqli_query($conn, "UPDATE barang SET status = IF(stok > 0, 'Tersedia', 'Tidak Tersedia') WHERE id_barang=$id_barang");
    
    header("Location: index.php?page=persediaan&msg=ok");
    exit;
}
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark">Halaman Persediaan Barang</h2>
    <p class="text-muted">Kelola pencatatan keluar masuk stok makanan ringan secara berkala.</p>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show <?php echo ($_GET['msg']=='ok') ? 'alert-success':'alert-danger'; ?>" role="alert">
        <?php
        if($_GET['msg']=='ok') echo "✅ Sukses! Log transaksi sirkulasi stok berhasil disimpan.";
        if($_GET['msg']=='err_qty') echo "❌ Gagal! Jumlah transaksi minimal harus 1 unit.";
        if($_GET['msg']=='err_insufficient') echo "❌ Ditolak! Sisa stok barang di toko tidak mencukupi untuk melakukan transaksi keluar.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-5 mb-4">
        <div class="card bg-white p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-3 text-primary">Input Barang Masuk / Keluar</h5>
            <hr>
            <form action="index.php?page=persediaan&action=save_transaction" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Pilih Snack</label>
                    <select name="id_barang" class="form-select" required>
                        <option value="">-- Pilih Item --</option>
                        <?php
                        $q_item = mysqli_query($conn, "SELECT id_barang, nama_barang, stok FROM barang ORDER BY nama_barang ASC");
                        while($ri = mysqli_fetch_assoc($q_item)){
                            echo "<option value='".$ri['id_barang']."'>".htmlspecialchars($ri['nama_barang'])." (Stok Saat Ini: ".$ri['stok']." Pcs)</option>";
                        }
                        ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary d-block">Jenis Logistik</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_transaksi" id="radioMasuk" value="Masuk" checked>
                        <label class="form-check-label text-success fw-bold" for="radioMasuk">📦 Barang Masuk (+)</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="jenis_transaksi" id="radioKeluar" value="Keluar">
                        <label class="form-check-label text-danger fw-bold" for="radioKeluar">🚚 Barang Keluar (-)</label>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Jumlah (Pcs)</label>
                    <input type="number" name="jumlah" class="form-control" min="1" placeholder="Kuantitas angka" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d'); ?>" required>
                </div>
                
                <button type="submit" class="btn btn-primary w-100 fw-bold mt-2">Simpan Riwayat Persediaan</button>
            </form>
        </div>
    </div>
    
    <div class="col-md-7 mb-4">
        <div class="card bg-white p-4 shadow-sm border-0">
            <h5 class="fw-bold mb-3 text-secondary">Status Stok Aktual Saat Ini</h5>
            <hr>
            <div style="max-height: 400px; overflow-y: auto;">
                <table class="table table-sm table-bordered align-middle">
                    <thead class="table-dark text-center sticky-top">
                        <tr>
                            <th>Nama Snack</th>
                            <th width="120">Stok Akhir</th>
                            <th width="130">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $q_live = mysqli_query($conn, "SELECT nama_barang, stok, status FROM barang ORDER BY nama_barang ASC");
                        while($rl = mysqli_fetch_assoc($q_live)):
                        ?>
                        <tr>
                            <td class="fw-medium"><?= htmlspecialchars($rl['nama_barang']); ?></td>
                            <td class="text-center fw-bold text-primary"><?= $rl['stok']; ?> Pcs</td>
                            <td class="text-center">
                                <?= ($rl['status']=='Tersedia') ? '<span class="badge bg-success">Tersedia</span>':'<span class="badge bg-danger">Tidak Tersedia</span>'; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>