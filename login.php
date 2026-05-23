<?php
session_start();

require_once 'config/db.php';
require_once 'classes/User.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

$pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (empty($username) || empty($password)) {
        $pesan = 'Username dan password wajib diisi!';
    } else {
        $user = new User($username, '', '');
        $berhasil = $user->login($username, $password, $koneksi);

        if ($berhasil) {
            if ($_SESSION['user_role'] === 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit;
        } else {
            $pesan = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login — GameJam</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<div class="login-wrapper">
    <div class="login-box">

        <a href="index.php" class="btn-back">← Kembali</a>

        <h1>Login</h1>
        <p class="subtitle">Masuk ke portal GameJam</p>

        <?php if ($pesan) { ?>
            <div class="alert alert-gagal"><?= $pesan ?></div>
        <?php } ?>

        <form method="POST">
            <div class="form-group">
                <label>Username</label>
                <div class="input-wrapper">
                    <span class="input-icon">👤</span>
                    <input type="text" name="username" placeholder="Masukkan username" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                </div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔒</span>
                    <input type="password" id="pass" name="password" placeholder="Masukkan password" required>
                    <button type="button" class="toggle-pass" onclick="togglePass()">👁</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-full">→ Log In</button>
        </form>
    </div>
</div>

<script>
function togglePass() {
    var input  = document.getElementById('pass');
    var tombol = document.querySelector('.toggle-pass');

    if (input.type === 'password') {
        input.type      = 'text';
        tombol.textContent = '🙈';
    } else {
        input.type      = 'password';
        tombol.textContent = '👁';
    }
}
</script>
</body>
</html>