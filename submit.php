<?php
session_start();

require_once 'config/db.php';
require_once 'classes/Peserta.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'peserta') {
    header("Location: login.php");
    exit;
}

$id_saya = (int) $_SESSION['user_id'];
$peserta = new Peserta($_SESSION['user_username'], $_SESSION['user_nama']);

$event = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM event LIMIT 1"));

if (!$event || $event['status'] !== 'buka') {
    header("Location: dashboard.php");
    exit;
}

if ($peserta->sudahSubmit($koneksi, $id_saya, $event['id'])) {
    header("Location: dashboard.php");
    exit;
}

$pesan  = '';
$tipe   = '';
$genres = ['Action', 'Adventure', 'Puzzle', 'Platformer', 'Survival', 'RPG', 'Strategy', 'Horror', 'Other'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul     = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi']);
    $genre     = trim($_POST['genre']);
    $link      = trim($_POST['link_game']);
    $cover     = 'default.png';

    if (empty($judul) || empty($deskripsi) || empty($genre) || empty($link)) {
        $pesan = 'Semua field wajib diisi!';
        $tipe  = 'gagal';
    } else {
        if (!empty($_FILES['cover']['name'])) {
            $nama_file = $_FILES['cover']['name'];
            $ukuran    = $_FILES['cover']['size'];
            $tmp_path  = $_FILES['cover']['tmp_name'];
            $ext       = strtolower(pathinfo($nama_file, PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

            if (!in_array($ext, $allowed)) {
                $pesan = 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.';
                $tipe  = 'gagal';
            } elseif ($ukuran > 2097152) {
                $pesan = 'Ukuran file terlalu besar. Maksimal 2MB.';
                $tipe  = 'gagal';
            } else {
                $cover      = uniqid('cover_') . '.' . $ext;
                $tujuan     = 'uploads/covers/' . $cover;
                $berhasil   = move_uploaded_file($tmp_path, $tujuan);

                if (!$berhasil) {
                    $pesan = 'Gagal mengupload cover. Pastikan folder uploads/covers/ bisa ditulis.';
                    $tipe  = 'gagal';
                    $cover = 'default.png';
                }
            }
        }

        if (empty($pesan)) {
            $ok = $peserta->submitGame($koneksi, $id_saya, $event['id'], $judul, $deskripsi, $genre, $link, $cover);

            if ($ok) {
                header("Location: dashboard.php");
                exit;
            } else {
                $pesan = 'Gagal menyimpan game. Mungkin kamu sudah submit game sebelumnya.';
                $tipe  = 'gagal';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Submit Game — GameJam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">🕹️ Game<span>Jam</span></a>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link">Dashboard</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container" style="max-width:640px;">
    <div class="card">
        <h2>🚀 Submit Game</h2>

        <?php if ($pesan) { ?>
            <div class="alert alert-<?= $tipe ?>"><?= $pesan ?></div>
        <?php } ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Judul Game <span style="color:#fe7a7c;">*</span></label>
                <input type="text" name="judul"
                       placeholder="Nama game kamu"
                       value="<?= htmlspecialchars($_POST['judul'] ?? '') ?>"
                       required>
            </div>

            <div class="form-group">
                <label>Genre <span style="color:#fe7a7c;">*</span></label>
                <select name="genre" required>
                    <option value="">-- Pilih Genre --</option>
                    <?php foreach ($genres as $g) { ?>
                        <option value="<?= $g ?>"
                            <?= (isset($_POST['genre']) && $_POST['genre'] === $g) ? 'selected' : '' ?>>
                            <?= $g ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="form-group">
                <label>Deskripsi Game <span style="color:#fe7a7c;">*</span></label>
                <textarea name="deskripsi" rows="4"
                          placeholder="Ceritakan tentang game kamu: cerita, mekanik, cara main..."
                          required><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
            </div>

            <div class="form-group">
                <label>Link Game <span style="color:#fe7a7c;">*</span></label>
                <input type="url" name="link_game"
                       placeholder="https://namauser.itch.io/nama-game"
                       value="<?= htmlspecialchars($_POST['link_game'] ?? '') ?>"
                       required>
                <small class="text-muted mt-8" style="display:block;">
                    Bisa pakai itch.io, Steam, Google Play, atau platform lainnya.
                </small>
            </div>

            <div class="form-group">
                <label>Cover Image <span class="text-muted">(opsional, maks. 2MB)</span></label>
                <input type="file" name="cover" accept="image/*"
                       style="padding:8px; background:#f7f7f9; border:1.5px solid #e0e0e8; border-radius:8px; cursor:pointer; width:100%;">
                <small class="text-muted mt-8" style="display:block;">
                    Format yang didukung: JPG, PNG, GIF, WebP.
                </small>
            </div>

            <div class="flex gap-8" style="margin-top:8px;">
                <button type="submit" class="btn btn-primary">🚀 Publikasikan Game</button>
                <a href="dashboard.php" class="btn btn-outline">✕ Batal</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>