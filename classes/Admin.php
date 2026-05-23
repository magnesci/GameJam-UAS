<?php
require_once 'User.php';

class Admin extends User {
    private $level;

    public function __construct($username, $nama) {
        parent::__construct($username, $nama, 'admin');
        $this->level = 'superadmin';
    }

    public function getLevel() {
        return $this->level;
    }

    public function editEvent($koneksi, $id, $nama, $tema, $deadline, $deskripsi) {
        $id        = (int) $id;
        $nama      = mysqli_real_escape_string($koneksi, $nama);
        $tema      = mysqli_real_escape_string($koneksi, $tema);
        $deadline  = mysqli_real_escape_string($koneksi, $deadline);
        $deskripsi = mysqli_real_escape_string($koneksi, $deskripsi);

        return mysqli_query($koneksi, "UPDATE event SET nama_event='$nama', tema='$tema', deadline='$deadline', deskripsi='$deskripsi' WHERE id=$id");
    }

    public function ubahStatusEvent($koneksi, $id, $status) {
        $id     = (int) $id;
        $status = mysqli_real_escape_string($koneksi, $status);
        
        return mysqli_query($koneksi, "UPDATE event SET status='$status' WHERE id=$id");
    }

    public function getAllPeserta($koneksi) {
        return mysqli_query($koneksi, "SELECT * FROM users WHERE role='peserta' ORDER BY nama ASC");
    }

    public function getAllGames($koneksi) {
        return mysqli_query($koneksi, "SELECT g.*, u.nama AS nama_peserta FROM games g JOIN users u ON g.peserta_id = u.id ORDER BY g.published_at DESC");
    }

    public function deleteGame($koneksi, $game_id) {
        $game_id = (int) $game_id;
        
        return mysqli_query($koneksi, "DELETE FROM games WHERE id=$game_id");
    }

    public function getStatistik($koneksi, $event_id) {
        $event_id = (int) $event_id;

        $total = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) AS n FROM users WHERE role='peserta'"))['n'];
        $sudah = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(DISTINCT peserta_id) AS n FROM games WHERE event_id=$event_id"))['n'];

        return [
            'total_peserta' => $total,
            'sudah_submit'  => $sudah,
            'belum_submit'  => $total - $sudah,
        ];
    }
}