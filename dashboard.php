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

$my_game = null;
if ($event) {
    $my_game = $peserta->getMyGame($koneksi, $id_saya, $event['id']);
}

$event_buka  = $event && $event['status'] === 'buka';
$deadline_ts = $event ? strtotime($event['deadline'] . ' 23:59:59') : 0;
$sudah_lewat = $deadline_ts && $deadline_ts < time();

$pesan = '';
$tipe  = '';

if (isset($_GET['hapus']) && $my_game) {
    $ok    = $peserta->editOwnGame($koneksi, $my_game['id'], $id_saya, '', '', '', '', '');
    $hapus = mysqli_query($koneksi, "DELETE FROM games WHERE id={$my_game['id']} AND peserta_id=$id_saya");
    if ($hapus) {
        $pesan   = 'Game berhasil dihapus.';
        $tipe    = 'sukses';
        $my_game = null;
    } else {
        $pesan = 'Gagal menghapus game.';
        $tipe  = 'gagal';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — GameJam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="dashboard.php" class="brand">🕹️ Game<span>Jam</span></a>
    <div class="nav-links">
        <a href="dashboard.php" class="nav-link active">Dashboard</a>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<div class="container">

    <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:8px;">
            <div>
                <div style="font-size:18px; font-weight:bold; margin-bottom:4px;">
                    👾 Halo, <?= htmlspecialchars($_SESSION['user_nama']) ?>!
                </div>
                <div class="text-muted">@<?= htmlspecialchars($_SESSION['user_username']) ?></div>
            </div>
            <?php if ($event_buka && !$sudah_lewat && !$my_game) { ?>
                <a href="submit.php" class="btn btn-primary">+ Submit Game</a>
            <?php } ?>
        </div>
    </div>

    <?php if ($pesan) { ?>
        <div class="alert alert-<?= $tipe ?>"><?= $pesan ?></div>
    <?php } ?>

    <?php if ($event) { ?>
    <div class="event-box">
        <div>
            <h3>🏆 <?= htmlspecialchars($event['nama_event']) ?></h3>
            <div class="meta">Tema: <?= htmlspecialchars($event['tema']) ?></div>
            <div class="meta">Deadline: <?= date('d/m/Y', strtotime($event['deadline'])) ?></div>
            <div style="margin-top:8px;">
                <?php if ($event_buka && !$sudah_lewat) { ?>
                    <span class="badge badge-green">● Pengumpulan Dibuka</span>
                <?php } else { ?>
                    <span class="badge badge-red">● Pengumpulan Ditutup</span>
                <?php } ?>
            </div>
        </div>
    </div>
    <?php } ?>

    <div class="card">
        <h2>🎮 Game Saya</h2>

        <?php if ($my_game) { ?>
        <div class="game-card" style="max-width:400px;">
            <div class="cover">
                <?php
                $cover_path = 'uploads/covers/' . $my_game['cover_image'];
                if ($my_game['cover_image'] !== 'default.png' && file_exists($cover_path)) {
                ?>
                    <img src="<?= $cover_path ?>" alt="Cover <?= htmlspecialchars($my_game['judul']) ?>">
                <?php } else { ?>
                    🎮
                <?php } ?>
            </div>
            <div class="info">
                <div class="judul"><?= htmlspecialchars($my_game['judul']) ?></div>
                <div class="by">
                    <span class="badge badge-gray"><?= htmlspecialchars($my_game['genre']) ?></span>
                    &nbsp; <?= date('d/m/Y', strtotime($my_game['published_at'])) ?>
                </div>
                <div class="desc"><?= htmlspecialchars($my_game['deskripsi']) ?></div>
                <div class="footer flex gap-8" style="flex-wrap:wrap;">
                    <a href="<?= htmlspecialchars($my_game['link_game']) ?>" target="_blank" class="btn btn-primary btn-sm">↗ Main</a>
                    <?php if ($event_buka && !$sudah_lewat) { ?>
                        <a href="edit.php?id=<?= $my_game['id'] ?>" class="btn btn-outline btn-sm">✏️ Edit</a>
                    <?php } ?>
                    <a href="?hapus=1" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus game ini?')">🗑 Hapus</a>
                </div>
            </div>
        </div>
        <?php } else { ?>
        <div style="text-align:center; padding:40px 0; color:#b5b5c3;">
            <div style="font-size:15px; font-weight:bold; color:#0d1013; margin-bottom:6px;">
                Kamu belum submit game
            </div>
            <?php if ($event_buka && !$sudah_lewat) { ?>
                <a href="submit.php" class="btn btn-primary" style="margin-top:14px; display:inline-flex;">
                    + Submit Game Sekarang
                </a>
            <?php } else { ?>
                <div class="text-muted">Pengumpulan sudah ditutup.</div>
            <?php } ?>
        </div>
        <?php } ?>
    </div>

</div>
</body>
</html>