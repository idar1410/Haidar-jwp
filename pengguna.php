<?php
if (!isset($_SESSION['username'])) { exit; }
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'insert') {
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    $password     = md5($_POST['password']);
    
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
    if (mysqli_num_rows($cek) > 0) header("Location: index.php?page=pengguna&msg=err_dup");
    else {
        mysqli_query($conn, "INSERT INTO users (nama_lengkap, username, password) VALUES ('$nama_lengkap', '$username', '$password')");
        header("Location: index.php?page=pengguna&msg=add_ok");
    }
    exit;
}

if ($action == 'update') {
    $id_user      = (int)$_POST['id_user'];
    $nama_lengkap = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $username     = mysqli_real_escape_string($conn, $_POST['username']);
    
    $cek = mysqli_query($conn, "SELECT * FROM users WHERE username='$username' AND id_user != $id_user");
    if (mysqli_num_rows($cek) > 0) header("Location: index.php?page=pengguna&msg=err_dup");
    else {
        if (!empty($_POST['password'])) {
            $password = md5($_POST['password']);
            mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama_lengkap', username='$username', password='$password' WHERE id_user=$id_user");
        } else {
            mysqli_query($conn, "UPDATE users SET nama_lengkap='$nama_lengkap', username='$username' WHERE id_user=$id_user");
        }
        header("Location: index.php?page=pengguna&msg=edit_ok");
    }
    exit;
}

if ($action == 'delete') {
    $id_user = (int)$_POST['id_user'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user=$id_user");
    header("Location: index.php?page=pengguna&msg=del_ok");
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark">Menu Manajemen Pengguna</h2>
        <p class="text-muted small">Kelola data otorisasi user admin.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        ➕ Tambah Admin
    </button>
</div>

<?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show <?php echo ($_GET['msg'] == 'err_dup') ? 'alert-danger' : 'alert-info'; ?>" role="alert">
        <?php
        if($_GET['msg']=='add_ok') echo "✅ Pengguna baru berhasil ditambahkan!";
        if($_GET['msg']=='edit_ok') echo "📝 Data pengguna berhasil diperbarui.";
        if($_GET['msg']=='del_ok') echo "🗑️ Pengguna berhasil dihapus.";
        if($_GET['msg']=='err_dup') echo "❌ Gagal! Username tersebut sudah digunakan orang lain.";
        ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<div class="card bg-white p-4 shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-striped table-bordered align-middle">
            <thead class="table-dark">
                <tr>
                    <th width="80" class="text-center">No</th>
                    <th>Nama Lengkap</th>
                    <th>Username</th>
                    <th width="180" class="text-center">Aksi Operasi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT * FROM users ORDER BY id_user DESC");
                $no = 1;
                while($row = mysqli_fetch_assoc($q)):
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="fw-medium text-dark"><?= htmlspecialchars($row['nama_lengkap']); ?></td>
                    <td><span class="badge bg-secondary">@<?= htmlspecialchars($row['username']); ?></span></td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_user']; ?>">Edit</button>
                        <?php if($row['username'] != $_SESSION['username']): ?>
                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $row['id_user']; ?>">Hapus</button>
                        <?php else: ?>
                            <button class="btn btn-light btn-sm text-muted" disabled>Sedang Aktif</button>
                        <?php endif; ?>
                    </td>
                </tr>

                <div class="modal fade" id="modalEdit<?= $row['id_user']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-warning">
                        <h5 class="modal-title fw-bold">Edit Pengguna</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="index.php?page=pengguna&action=update" method="POST">
                          <div class="modal-body">
                              <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
                              <div class="mb-3">
                                  <label class="form-label">Nama Lengkap</label>
                                  <input type="text" name="nama_lengkap" value="<?= htmlspecialchars($row['nama_lengkap']); ?>" class="form-control" required>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label">Username</label>
                                  <input type="text" name="username" value="<?= htmlspecialchars($row['username']); ?>" class="form-control" required>
                              </div>
                              <div class="mb-3">
                                  <label class="form-label text-danger">Ubah Password <small>(Kosongkan jika tidak diganti)</small></label>
                                  <input type="password" name="password" class="form-control" placeholder="Masukkan password baru">
                              </div>
                          </div>
                          <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-success fw-bold text-white">Update</button>
                          </div>
                      </form>
                    </div>
                  </div>
                </div>

                <div class="modal fade" id="modalHapus<?= $row['id_user']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="index.php?page=pengguna&action=delete" method="POST">
                          <div class="modal-body text-center">
                              <input type="hidden" name="id_user" value="<?= $row['id_user']; ?>">
                              <p>Apakah Anda yakin ingin mencabut akses admin <strong><?= htmlspecialchars($row['nama_lengkap']); ?></strong>?</p>
                          </div>
                          <div class="modal-footer justify-content-center">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                              <button type="submit" class="btn btn-danger fw-bold">Ya, Hapus!</button>
                          </div>
                      </form>
                    </div>
                  </div>
                </div>

                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold">Registrasi Admin Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=pengguna&action=insert" method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label">Nama Lengkap</label>
                  <input type="text" name="nama_lengkap" class="form-control" required autocomplete="off">
              </div>
              <div class="mb-3">
                  <label class="form-label">Username</label>
                  <input type="text" name="username" class="form-control" required autocomplete="off">
              </div>
              <div class="mb-3">
                  <label class="form-label">Password</label>
                  <input type="password" name="password" class="form-control" required>
              </div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
              <button type="submit" class="btn btn-primary fw-bold">Simpan</button>
          </div>
      </form>
    </div>
  </div>
</div>