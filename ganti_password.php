<?php
// --------------------------------------------------------
// File: pages/ganti_password.php
// Deskripsi: Halaman ganti password untuk user yang sedang login
// --------------------------------------------------------
if (!isset($_SESSION['username'])) { exit; }

// PROSES UPDATE PASSWORD
if (isset($_GET['action']) && $_GET['action'] == 'update') {
    $id_user = $_SESSION['id_user'];
    
    // Ambil inputan form dan enkripsi password lama pakai MD5
    $pass_lama  = md5($_POST['pass_lama']);
    $pass_baru  = $_POST['pass_baru'];
    $konfirmasi = $_POST['konfirmasi'];
    
    // 1. Cek apakah password lama yang dimasukkan benar
    $cek_db = mysqli_query($conn, "SELECT password FROM users WHERE id_user=$id_user");
    $data_db = mysqli_fetch_assoc($cek_db);
    
    if ($data_db['password'] !== $pass_lama) {
        header("Location: index.php?page=ganti_password&msg=err_lama");
        exit;
    }
    
    // 2. Cek apakah password baru dan konfirmasi cocok
    if ($pass_baru !== $konfirmasi) {
        header("Location: index.php?page=ganti_password&msg=err_konfirm");
        exit;
    }
    
    // 3. Jika aman, update password di database
    $pass_baru_md5 = md5($pass_baru);
    mysqli_query($conn, "UPDATE users SET password='$pass_baru_md5' WHERE id_user=$id_user");
    
    header("Location: index.php?page=ganti_password&msg=ok");
    exit;
}
?>

<div class="mb-4">
    <h2 class="fw-bold text-dark">Ganti Password</h2>
    <p class="text-muted small">Ubah kata sandi akun <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong> secara berkala demi keamanan.</p>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show <?php echo ($_GET['msg'] == 'ok') ? 'alert-success' : 'alert-danger'; ?>" role="alert">
        <?php
        if($_GET['msg']=='ok') echo "✅ Sukses! Password akun Anda berhasil diperbarui.";
        if($_GET['msg']=='err_lama') echo "❌ Gagal! Password Lama yang Anda masukkan salah.";
        if($_GET['msg']=='err_konfirm') echo "❌ Gagal! Password Baru dan Konfirmasi Password tidak cocok.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card bg-white p-4 shadow-sm border-0" style="max-width: 500px;">
    <form action="index.php?page=ganti_password&action=update" method="POST">
        <div class="mb-3">
            <label class="form-label fw-bold">Password Lama</label>
            <input type="password" name="pass_lama" class="form-control" placeholder="Masukkan password saat ini" required>
        </div>
        <hr class="text-muted">
        <div class="mb-3">
            <label class="form-label fw-bold">Password Baru</label>
            <input type="password" name="pass_baru" class="form-control" placeholder="Masukkan password baru" required>
        </div>
        <div class="mb-4">
            <label class="form-label fw-bold">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi" class="form-control" placeholder="Ulangi password baru" required>
        </div>
        <button type="submit" class="btn btn-primary fw-bold w-100">Simpan Password Baru</button>
    </form>
</div>