<?php
session_start();

require_once 'config/db.php';
require_once 'classes/Admin.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$admin = new Admin($_SESSION['user_username'], $_SESSION['user_nama']);
$event = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM event LIMIT 1"));

$pesan = '';
$tipe  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_event'])) {
    $nama      = trim($_POST['nama_event']);
    $tema      = trim($_POST['tema']);
    $deadline  = trim($_POST['deadline']);
    $deskripsi = trim($_POST['deskripsi']);

    if (empty($nama) || empty($tema) || empty($deadline)) {
        $pesan = 'Nama event, tema, dan deadline wajib diisi!';
        $tipe  = 'gagal';
    } else {
        $ok    = $admin->editEvent($koneksi, $event['id'], $nama, $tema, $deadline, $deskripsi);
        $pesan = $ok ? 'Event berhasil diperbarui!' : 'Gagal memperbarui event.';
        $tipe  = $ok ? 'sukses' : 'gagal';
        $event = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM event LIMIT 1"));
    }
}

if (isset($_GET['status'])) {
    $admin->ubahStatusEvent($koneksi, $event['id'], $_GET['status']);
    header("Location: admin.php");
    exit;
}

if (isset($_GET['hapus_game'])) {
    $ok    = $admin->deleteGame($koneksi, $_GET['hapus_game']);
    $pesan = $ok ? 'Game berhasil dihapus.' : 'Gagal menghapus game.';
    $tipe  = $ok ? 'sukses' : 'gagal';
}

$stat          = $admin->getStatistik($koneksi, $event['id']);
$semua_peserta = $admin->getAllPeserta($koneksi);
$semua_games   = $admin->getAllGames($koneksi);

$sudah_ids     = [];
$hasil_ids     = mysqli_query($koneksi, "SELECT DISTINCT peserta_id FROM games WHERE event_id={$event['id']}");

while ($baris = mysqli_fetch_assoc($hasil_ids)) {
    $sudah_ids[] = $baris['peserta_id'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin — GameJam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="admin.php" class="brand">🕹️ Game<span>Jam</span></a>
    <div class="nav-links">
        <a href="admin.php" class="nav-link active">Dashboard Admin</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">

    <?php if ($pesan) { ?>
        <div class="alert alert-<?= $tipe ?>"><?= $pesan ?></div>
    <?php } ?>

    <div class="stat-grid">
        <div class="stat-box">
            <div class="angka"><?= $stat['total_peserta'] ?></div>
            <div class="label">Total Peserta</div>
        </div>
        <div class="stat-box">
            <div class="angka hijau"><?= $stat['sudah_submit'] ?></div>
            <div class="label">Sudah Submit</div>
        </div>
        <div class="stat-box">
            <div class="angka abu"><?= $stat['belum_submit'] ?></div>
            <div class="label">Belum Submit</div>
        </div>
    </div>

    <?php if ($event) { ?>
    <div class="card">
        <h2>✏️ Kelola Event</h2>

        <div class="event-box" style="margin-bottom:20px;">
            <div>
                <h3>🏆 <?= htmlspecialchars($event['nama_event']) ?></h3>
                <div class="meta">Tema: <?= htmlspecialchars($event['tema']) ?></div>
                <div class="meta">Deadline: <?= date('d/m/Y', strtotime($event['deadline'])) ?></div>
                <div style="margin-top:8px;">
                    <?php if ($event['status'] === 'buka') { ?>
                        <span class="badge badge-green">● Pengumpulan Dibuka</span>
                    <?php } else { ?>
                        <span class="badge badge-red">● Pengumpulan Ditutup</span>
                    <?php } ?>
                </div>
            </div>
            <div class="flex gap-8" style="flex-wrap:wrap;">
                <?php if ($event['status'] === 'buka') { ?>
                    <a href="?status=tutup" class="btn btn-danger btn-sm" onclick="return confirm('Yakin tutup pengumpulan sekarang?')">✕ Tutup</a>
                <?php } else { ?>
                    <a href="?status=buka" class="btn btn-success btn-sm">✓ Buka</a>
                <?php } ?>
            </div>
        </div>

        <form method="POST">
            <div class="grid-2">
                <div class="form-group">
                    <label>Nama Event</label>
                    <input type="text" name="nama_event" value="<?= htmlspecialchars($event['nama_event']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Tema</label>
                    <input type="text" name="tema" value="<?= htmlspecialchars($event['tema']) ?>" required>
                </div>
                <div class="form-group">
                    <label>Deadline</label>
                    <input type="date" name="deadline" value="<?= $event['deadline'] ?>" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="deskripsi" value="<?= htmlspecialchars($event['deskripsi']) ?>">
                </div>
            </div>
            <button type="submit" name="edit_event" class="btn btn-primary">💾 Simpan Perubahan Event</button>
        </form>
    </div>
    <?php } ?>

    <div class="card">
        <h2>👥 Rekap Status Peserta</h2>
        <?php if (mysqli_num_rows($semua_peserta) > 0) { ?>
        <table>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Username</th>
                <th>Status Submission</th>
            </tr>
            <?php
            $no = 1;
            while ($p = mysqli_fetch_assoc($semua_peserta)) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($p['nama']) ?></td>
                <td><?= htmlspecialchars($p['username']) ?></td>
                <td>
                    <?php if (in_array($p['id'], $sudah_ids)) { ?>
                        <span class="badge badge-green">✅ Sudah Submit</span>
                    <?php } else { ?>
                        <span class="badge badge-yellow">⏳ Belum Submit</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <div class="alert alert-info">Belum ada peserta terdaftar.</div>
        <?php } ?>
    </div>

    <div class="card">
        <h2>🎮 Semua Game Submission</h2>
        <?php if (mysqli_num_rows($semua_games) > 0) { ?>
        <table>
            <tr>
                <th>No</th>
                <th>Judul Game</th>
                <th>Peserta</th>
                <th>Genre</th>
                <th>Tanggal Submit</th>
                <th>Link</th>
                <th>Aksi</th>
            </tr>
            <?php
            $no = 1;
            while ($g = mysqli_fetch_assoc($semua_games)) {
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><strong><?= htmlspecialchars($g['judul']) ?></strong></td>
                <td><?= htmlspecialchars($g['nama_peserta']) ?></td>
                <td><span class="badge badge-gray"><?= htmlspecialchars($g['genre']) ?></span></td>
                <td style="font-size:12px; color:#b5b5c3;">
                    <?= date('d/m/Y', strtotime($g['published_at'])) ?>
                </td>
                <td>
                    <a href="<?= htmlspecialchars($g['link_game']) ?>" target="_blank" style="color:#fe7a7c; font-size:13px;">↗ Buka</a>
                </td>
                <td>
                    <a href="?hapus_game=<?= $g['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus game <?= htmlspecialchars($g['judul']) ?>?')">
                       🗑 Hapus
                    </a>
                </td>
            </tr>
            <?php } ?>
        </table>
        <?php } else { ?>
            <div class="alert alert-info">Belum ada game yang disubmit.</div>
        <?php } ?>
    </div>

</div>
</body>
</html>