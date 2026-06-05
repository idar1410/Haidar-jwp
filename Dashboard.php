<?php
// --------------------------------------------------------
// File: pages/dashboard.php
// Deskripsi: Dashboard ringkasan data inventaris snack
// --------------------------------------------------------
if (!isset($_SESSION['username'])) { exit; }

// Perhitungan data statistika ringkas
$q_total = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang");
$total_snack = mysqli_fetch_assoc($q_total)['total'];

$q_rendah = mysqli_query($conn, "SELECT COUNT(*) as total FROM barang WHERE stok < 10");
$stok_rendah = mysqli_fetch_assoc($q_rendah)['total'];

$q_in = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM riwayat_stok WHERE jenis_transaksi='Masuk'");
$total_masuk = mysqli_fetch_assoc($q_in)['total'] ?? 0;

$q_out = mysqli_query($conn, "SELECT SUM(jumlah) as total FROM riwayat_stok WHERE jenis_transaksi='Keluar'");
$total_keluar = mysqli_fetch_assoc($q_out)['total'] ?? 0;

// QUERY BARU: Ambil 5 Stok Tertinggi
$q_top5_tinggi = mysqli_query($conn, "SELECT nama_barang, stok FROM barang ORDER BY stok DESC LIMIT 5");

// QUERY BARU: Ambil 5 Stok Terendah
$q_top5_rendah = mysqli_query($conn, "SELECT nama_barang, stok FROM barang ORDER BY stok ASC LIMIT 5");
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark">Dashboard Persediaan</h2>
    <p class="text-muted">Ikhtisar data persediaan logistik makanan ringan toko Anda.</p>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold">Total Snack</h6>
                <h3 class="mb-0 fw-bold"><?= $total_snack; ?> Item</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold">Stok < 10</h6>
                <h3 class="mb-0 fw-bold"><?= $stok_rendah; ?> Item</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold">Total Masuk</h6>
                <h3 class="mb-0 fw-bold"><?= $total_masuk; ?> Pcs</h3>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white shadow-sm border-0 h-100">
            <div class="card-body">
                <h6 class="fw-bold">Total Keluar</h6>
                <h3 class="mb-0 fw-bold"><?= $total_keluar; ?> Pcs</h3>
            </div>
        </div>
    </div>
</div>

<div class="row">
    
    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-info text-white fw-bold">
                📈 Top 5 Stok Tertinggi
            </div>
            <ul class="list-group list-group-flush">
                <?php
                if (mysqli_num_rows($q_top5_tinggi) > 0) {
                    while($row = mysqli_fetch_assoc($q_top5_tinggi)) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo htmlspecialchars($row['nama_barang']);
                        echo '<span class="badge bg-primary rounded-pill">'.$row['stok'].' Pcs</span>';
                        echo '</li>';
                    }
                } else {
                    echo '<li class="list-group-item text-muted text-center py-3">Belum ada data barang.</li>';
                }
                ?>
            </ul>
        </div>
    </div>

    <div class="col-md-6 mb-4">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-warning text-dark fw-bold">
                📉 Top 5 Stok Terendah
            </div>
            <ul class="list-group list-group-flush">
                <?php
                if (mysqli_num_rows($q_top5_rendah) > 0) {
                    while($row = mysqli_fetch_assoc($q_top5_rendah)) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">';
                        echo htmlspecialchars($row['nama_barang']);
                        echo '<span class="badge bg-danger rounded-pill">'.$row['stok'].' Pcs</span>';
                        echo '</li>';
                    }
                } else {
                    echo '<li class="list-group-item text-muted text-center py-3">Belum ada data barang.</li>';
                }
                ?>
            </ul>
        </div>
    </div>

</div>
