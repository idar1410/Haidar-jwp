<?php
// --------------------------------------------------------
// File: pages/laporan.php
// Deskripsi: Menampilkan sirkulasi mutasi dengan Filter Tanggal lengkap
// --------------------------------------------------------
if (!isset($_SESSION['username'])) { exit; }
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark">Halaman Laporan</h2>
    <p class="text-muted">Informasi keluar masuk barang yang terjadi dilengkapi dengan filter tanggal.</p>
</div>

<div class="card bg-white p-4 mb-4 shadow-sm border-0">
    <h6 class="fw-bold text-secondary mb-3">🔍 Saring Laporan Berdasarkan Rentang Tanggal</h6>
    <form method="GET" action="index.php" class="row g-3 align-items-end">
        <input type="hidden" name="page" value="laporan">
        
        <div class="col-md-4">
            <label class="form-label small text-muted">Dari Tanggal</label>
            <input type="date" name="mulai" class="form-control" value="<?= isset($_GET['mulai']) ? htmlspecialchars($_GET['mulai']) : ''; ?>" required>
        </div>
        <div class="col-md-4">
            <label class="form-label small text-muted">Sampai Tanggal</label>
            <input type="date" name="selesai" class="form-control" value="<?= isset($_GET['selesai']) ? htmlspecialchars($_GET['selesai']) : ''; ?>" required>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary fw-bold px-3">Filter</button>
            <a href="index.php?page=laporan" class="btn btn-secondary px-3">Reset</a>
        </div>
    </form>
</div>

<div class="card bg-white p-4 shadow-sm border-0">
<div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark m-0">Riwayat Mutasi Persediaan Logistik</h5>
        <div>
            <?php if (isset($_GET['mulai']) && isset($_GET['selesai'])): ?>
                <span class="badge bg-info p-2 me-2">Periode: <?= date('d/m/Y', strtotime($_GET['mulai'])); ?> s/d <?= date('d/m/Y', strtotime($_GET['selesai'])); ?></span>
                
                <a href="cetak.php?mulai=<?= $_GET['mulai']; ?>&selesai=<?= $_GET['selesai']; ?>" target="_blank" class="btn btn-success btn-sm fw-bold">🖨️ Cetak PDF / Print</a>
            <?php else: ?>
                <a href="cetak.php" target="_blank" class="btn btn-success btn-sm fw-bold">🖨️ Cetak Semua Data</a>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="60" class="text-center">No</th>
                    <th class="text-center" width="140">Tanggal</th>
                    <th>Nama Makanan Ringan (Snack)</th>
                    <th class="text-center" width="180">Keterangan Transaksi</th>
                    <th class="text-center" width="120">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $where = "";
                if (isset($_GET['mulai']) && isset($_GET['selesai'])) {
                    $mulai = mysqli_real_escape_string($conn, $_GET['mulai']);
                    $selesai = mysqli_real_escape_string($conn, $_GET['selesai']);
                    $where = "WHERE r.tanggal BETWEEN '$mulai' AND '$selesai'";
                }
                
                $sql_report = "SELECT r.*, b.nama_barang FROM riwayat_stok r 
                              JOIN barang b ON r.id_barang = b.id_barang 
                              $where 
                              ORDER BY r.id_riwayat DESC";
                              
                $res_report = mysqli_query($conn, $sql_report);
                $no = 1;
                
                if (mysqli_num_rows($res_report) == 0) {
                    echo "<tr><td colspan='5' class='text-center py-4 text-muted small'>Tidak ada data logistik ditemukan pada rentang tanggal tersebut.</td></tr>";
                }
                
                while($row = mysqli_fetch_assoc($res_report)):
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="text-center"><?= date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                    <td class="fw-bold text-secondary"><?= htmlspecialchars($row['nama_barang']); ?></td>
                    <td class="text-center">
                        <?php if($row['jenis_transaksi'] == 'Masuk'): ?>
                            <span class="badge bg-success px-3 py-1">Barang Masuk</span>
                        <?php else: ?>
                            <span class="badge bg-danger px-3 py-1">Barang Keluar</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold"><?= $row['jumlah']; ?> Pcs</td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>