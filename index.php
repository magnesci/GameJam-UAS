<?php
session_start();

require_once 'config/db.php';
require_once 'classes/User.php';

$event = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM event LIMIT 1"));

if ($event) {
    $games = mysqli_query($koneksi,
        "SELECT g.*, u.nama AS nama_peserta
         FROM games g
         JOIN users u ON g.peserta_id = u.id
         WHERE g.event_id = {$event['id']}
         ORDER BY g.published_at DESC"
    );
    $total_game = mysqli_num_rows($games);
} else {
    $games      = null;
    $total_game = 0;
}

$sudah_login = isset($_SESSION['user_id']);
$role        = $_SESSION['user_role'] ?? '';

$deadline_ts  = $event ? strtotime($event['deadline'] . ' 23:59:59') : 0;
$sudah_lewat  = $deadline_ts && $deadline_ts < time();
$event_buka   = $event && $event['status'] === 'buka';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GameJam — Portal Submission</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<nav class="navbar">
    <a href="index.php" class="brand">🕹️ Game<span>Jam</span></a>
    <div class="nav-links">
        <a href="index.php" class="nav-link active">Gallery</a>
        <?php if ($sudah_login) { ?>
            <?php if ($role === 'admin') { ?>
                <a href="admin.php" class="nav-link">Dashboard Admin</a>
            <?php } else { ?>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
            <?php } ?>
            <a href="logout.php" class="btn-logout">Logout</a>
        <?php } else { ?>
            <a href="login.php" class="nav-link">Login</a>
        <?php } ?>
    </div>
</nav>

<div class="hero">
    <?php if ($event) { ?>
        <h1>🏆 <span><?= htmlspecialchars($event['nama_event']) ?></span></h1>
        <p>
            Tema: <strong><?= htmlspecialchars($event['tema']) ?></strong><br>
            <?= $total_game ?> game terkumpul
        </p>
        <p style="font-size:13px; color:#b5b5c3; max-width:480px; margin:-10px auto 20px;">
            <?= htmlspecialchars($event['deskripsi']) ?>
        </p>
    <?php } else { ?>
        <h1>🎮 <span>GameJam</span> Portal</h1>
        <p>Platform pengumpulan project game. Tunjukkan kreativitasmu!</p>
    <?php } ?>

    <?php if ($sudah_login && $role === 'peserta') { ?>
        <a href="dashboard.php" class="btn btn-primary">+ Submit Game</a>
    <?php } else { ?>
        <a href="login.php" class="btn btn-primary">+ Submit Game</a>
    <?php } ?>
</div>

<div class="container">
    <?php if ($event && $event_buka && !$sudah_lewat) { ?>
    <div class="countdown-box">
        <div class="cd-label">⏳ Sisa Waktu Pengumpulan</div>
        <div class="countdown-timer">
            <div class="countdown-unit">
                <div class="cd-num" id="cd-hari">00</div>
                <div class="cd-sat">Hari</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="cd-num" id="cd-jam">00</div>
                <div class="cd-sat">Jam</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="cd-num" id="cd-menit">00</div>
                <div class="cd-sat">Menit</div>
            </div>
            <div class="countdown-sep">:</div>
            <div class="countdown-unit">
                <div class="cd-num" id="cd-detik">00</div>
                <div class="cd-sat">Detik</div>
            </div>
        </div>
    </div>
    <?php } elseif ($event && $sudah_lewat) { ?>
    <div class="countdown-box tutup">
        <div class="cd-label">⏰ Deadline Telah Berakhir</div>
        <div style="color:#d63a3a; font-weight:bold;">Waktu pengumpulan sudah habis.</div>
    </div>
    <?php } elseif ($event && !$event_buka) { ?>
    <div class="countdown-box tutup">
        <div class="cd-label">🔒 Pengumpulan Ditutup oleh Admin</div>
        <div style="color:#d63a3a;">Admin telah menutup sesi pengumpulan project.</div>
    </div>
    <?php } ?>

    <div style="display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:8px; margin-bottom:8px;">
        <div>
            <div class="section-title">Game Submissions</div>
            <div class="section-sub"><?= $total_game ?> game telah dikumpulkan</div>
        </div>
        <?php if ($event) { ?>
            <span class="badge <?= $event_buka ? 'badge-green' : 'badge-red' ?>">
                ● <?= $event_buka ? 'Pengumpulan Dibuka' : 'Pengumpulan Ditutup' ?>
            </span>
        <?php } ?>
    </div>

    <?php if ($games && mysqli_num_rows($games) > 0) { ?>
    <div class="game-grid">
        <?php while ($g = mysqli_fetch_assoc($games)) { ?>
        <div class="game-card">
            <div class="cover">
                <?php
                $cover_path = 'uploads/covers/' . $g['cover_image'];
                if ($g['cover_image'] !== 'default.png' && file_exists($cover_path)) {
                ?>
                    <img src="<?= $cover_path ?>" alt="Cover <?= htmlspecialchars($g['judul']) ?>">
                <?php } else { ?>
                    🎮
                <?php } ?>
            </div>
            <div class="info">
                <div class="judul"><?= htmlspecialchars($g['judul']) ?></div>
                <div class="by">
                    oleh <?= htmlspecialchars($g['nama_peserta']) ?>
                    &nbsp;&nbsp;
                    <span class="badge badge-gray" style="font-size:11px;"><?= htmlspecialchars($g['genre']) ?></span>
                </div>
                <div class="desc"><?= htmlspecialchars($g['deskripsi']) ?></div>
                <div class="footer">
                    <span style="font-size:11px; color:#b5b5c3;"><?= date('d/m/Y', strtotime($g['published_at'])) ?></span>
                    <a href="<?= htmlspecialchars($g['link_game']) ?>" target="_blank" class="btn btn-primary btn-sm">Main ↗</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
    <?php } else { ?>
    <div style="text-align:center; padding:60px 0; color:#b5b5c3;">
        <div style="font-size:48px; margin-bottom:12px;">🎮</div>
        <div style="font-size:16px; font-weight:bold; color:#0d1013; margin-bottom:6px;">Belum ada game yang disubmit</div>
        <div>Jadilah yang pertama submit game kamu!</div>
        <a href="login.php" class="btn btn-primary" style="margin-top:20px; display:inline-flex;">+ Submit Game</a>
    </div>
    <?php } ?>
</div>

<?php if ($event && $event_buka && !$sudah_lewat) { ?>
<script>
var deadline = new Date("<?= $event['deadline'] ?>T23:59:59").getTime();

function tick() {
    var sekarang = new Date().getTime();
    var sisa     = deadline - sekarang;

    if (sisa <= 0) {
        clearInterval(timer);
        return;
    }

    var hari   = Math.floor(sisa / 86400000);
    var jam    = Math.floor((sisa % 86400000) / 3600000);
    var menit  = Math.floor((sisa % 3600000) / 60000);
    var detik  = Math.floor((sisa % 60000) / 1000);

    document.getElementById('cd-hari').textContent  = String(hari).padStart(2, '0');
    document.getElementById('cd-jam').textContent   = String(jam).padStart(2, '0');
    document.getElementById('cd-menit').textContent = String(menit).padStart(2, '0');
    document.getElementById('cd-detik').textContent = String(detik).padStart(2, '0');
}

tick();
var timer = setInterval(tick, 1000);
</script>
<?php } ?>

</body>
</html>