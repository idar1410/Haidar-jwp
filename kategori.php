<?php
if (!isset($_SESSION['username'])) { exit; }

$action = isset($_GET['action']) ? $_GET['action'] : 'list';

if ($action == 'insert') {
    $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    if(!empty($nama_kategori)){
        $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE nama_kategori = '$nama_kategori'");
        if(mysqli_num_rows($cek) > 0) header("Location: index.php?page=kategori&msg=err_dup");
        else {
            mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama_kategori')");
            header("Location: index.php?page=kategori&msg=add_ok");
        }
    } else header("Location: index.php?page=kategori&msg=add_err");
    exit;
}

if ($action == 'update') {
    $id_kategori = (int)$_POST['id_kategori'];
    $nama_kategori = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
    $cek = mysqli_query($conn, "SELECT * FROM kategori WHERE nama_kategori = '$nama_kategori' AND id_kategori != $id_kategori");
    if(mysqli_num_rows($cek) > 0) header("Location: index.php?page=kategori&msg=err_dup");
    else {
        mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama_kategori' WHERE id_kategori=$id_kategori");
        header("Location: index.php?page=kategori&msg=edit_ok");
    }
    exit;
}

if ($action == 'delete') {
    $id_kategori = (int)$_POST['id_kategori'];
    mysqli_query($conn, "DELETE FROM kategori WHERE id_kategori=$id_kategori");
    header("Location: index.php?page=kategori&msg=del_ok");
    exit;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-dark">Menu Kategori Barang</h2>
        <p class="text-muted small">Kelola klasifikasi produk makanan ringan.</p>
    </div>
    <button type="button" class="btn btn-primary fw-bold btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
        ➕ Tambah Kategori
    </button>
</div>

<?php if(isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show <?php echo ($_GET['msg'] == 'err_dup' || $_GET['msg'] == 'add_err') ? 'alert-danger' : 'alert-info'; ?>" role="alert">
        <?php
        if($_GET['msg']=='add_ok') echo "✅ Kategori baru berhasil disimpan!";
        if($_GET['msg']=='edit_ok') echo "📝 Pembaruan data kategori berhasil dilakukan!";
        if($_GET['msg']=='del_ok') echo "🗑️ Kategori berhasil dihapus!";
        if($_GET['msg']=='add_err') echo "❌ Nama kategori tidak boleh kosong.";
        if($_GET['msg']=='err_dup') echo "❌ Gagal! Kategori tersebut sudah terdaftar di sistem.";
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
                    <th>Nama Kategori</th>
                    <th width="180" class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $q = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");
                $no = 1;
                if (mysqli_num_rows($q) == 0) echo "<tr><td colspan='3' class='text-center text-muted py-3'>Tidak ada data.</td></tr>";
                while($row = mysqli_fetch_assoc($q)):
                ?>
                <tr>
                    <td class="text-center"><?= $no++; ?></td>
                    <td class="fw-medium"><?= htmlspecialchars($row['nama_kategori']); ?></td>
                    <td class="text-center">
                        <button class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $row['id_kategori']; ?>">Edit</button>
                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalHapus<?= $row['id_kategori']; ?>">Hapus</button>
                    </td>
                </tr>

                <div class="modal fade" id="modalEdit<?= $row['id_kategori']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-warning">
                        <h5 class="modal-title fw-bold">Edit Kategori</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="index.php?page=kategori&action=update" method="POST">
                          <div class="modal-body">
                              <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                              <div class="mb-3">
                                  <label class="form-label">Nama Kategori</label>
                                  <input type="text" name="nama_kategori" value="<?= htmlspecialchars($row['nama_kategori']); ?>" class="form-control" required>
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

                <div class="modal fade" id="modalHapus<?= $row['id_kategori']; ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title fw-bold">Konfirmasi Hapus</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <form action="index.php?page=kategori&action=delete" method="POST">
                          <div class="modal-body text-center">
                              <input type="hidden" name="id_kategori" value="<?= $row['id_kategori']; ?>">
                              <p>Apakah Anda yakin ingin menghapus kategori <strong><?= htmlspecialchars($row['nama_kategori']); ?></strong>?</p>
                              <small class="text-danger">Peringatan: Semua barang di kategori ini juga akan ikut terhapus!</small>
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
        <h5 class="modal-title fw-bold">Tambah Kategori Baru</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form action="index.php?page=kategori&action=insert" method="POST">
          <div class="modal-body">
              <div class="mb-3">
                  <label class="form-label fw-bold">Nama Kategori</label>
                  <input type="text" name="nama_kategori" class="form-control" required autocomplete="off">
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