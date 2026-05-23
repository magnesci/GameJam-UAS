<?php
require_once 'User.php';

class Peserta extends User {
    public function __construct($username, $nama) {
        parent::__construct($username, $nama, 'peserta');
    }

    public function sudahSubmit($koneksi, $peserta_id, $event_id) {
        $peserta_id = (int) $peserta_id;
        $event_id   = (int) $event_id;
        $hasil = mysqli_query($koneksi, "SELECT id FROM games WHERE peserta_id=$peserta_id AND event_id=$event_id");
        
        return mysqli_num_rows($hasil) > 0;
    }

    public function submitGame($koneksi, $peserta_id, $event_id, $judul, $deskripsi, $genre, $link, $cover) {
        $peserta_id = (int) $peserta_id;
        $event_id   = (int) $event_id;
        $judul      = mysqli_real_escape_string($koneksi, $judul);
        $deskripsi  = mysqli_real_escape_string($koneksi, $deskripsi);
        $genre      = mysqli_real_escape_string($koneksi, $genre);
        $link       = mysqli_real_escape_string($koneksi, $link);
        $cover      = mysqli_real_escape_string($koneksi, $cover);

        return mysqli_query($koneksi, "INSERT INTO games (peserta_id, event_id, judul, deskripsi, genre, link_game, cover_image) VALUES ($peserta_id, $event_id, '$judul', '$deskripsi', '$genre', '$link', '$cover')");
    }

    public function editOwnGame($koneksi, $game_id, $peserta_id, $judul, $deskripsi, $genre, $link, $cover) {
        $game_id    = (int) $game_id;
        $peserta_id = (int) $peserta_id;
        $judul      = mysqli_real_escape_string($koneksi, $judul);
        $deskripsi  = mysqli_real_escape_string($koneksi, $deskripsi);
        $genre      = mysqli_real_escape_string($koneksi, $genre);
        $link       = mysqli_real_escape_string($koneksi, $link);
        $cover      = mysqli_real_escape_string($koneksi, $cover);

        $cek = mysqli_query($koneksi, "SELECT id FROM games WHERE id=$game_id AND peserta_id=$peserta_id");
        
        if (mysqli_num_rows($cek) === 0) {
            return false;
        }

        $setCover = '';
        if ($cover) {
            $setCover = ", cover_image='$cover'";
        }

        return mysqli_query($koneksi, "UPDATE games SET judul='$judul', deskripsi='$deskripsi', genre='$genre', link_game='$link'$setCover WHERE id=$game_id AND peserta_id=$peserta_id");
    }

    public function getMyGame($koneksi, $peserta_id, $event_id) {
        $peserta_id = (int) $peserta_id;
        $event_id   = (int) $event_id;
        $hasil = mysqli_query($koneksi, "SELECT g.*, e.nama_event, e.status AS status_event FROM games g JOIN event e ON g.event_id = e.id WHERE g.peserta_id=$peserta_id AND g.event_id=$event_id");
        
        return mysqli_fetch_assoc($hasil);
    }
}