<?php
class User {
    private $id;
    private $username;
    private $nama;
    protected $role;

    public function __construct($username, $nama, $role) {
        $this->username = $username;
        $this->nama     = $nama;
        $this->role     = $role;
    }

    public function getId() { 
        return $this->id; 
    }

    public function getUsername() { 
        return $this->username; 
    }

    public function getNama() { 
        return $this->nama; 
    }

    public function getRole() { 
        return $this->role; 
    }

    public function setId($id) { 
        $this->id = $id; 
    }

    public function setNama($nama) { 
        $this->nama = $nama; 
    }

    public function login($username, $password, $koneksi) {
        $username = mysqli_real_escape_string($koneksi, $username);
        $password = mysqli_real_escape_string($koneksi, $password);

        $query = "SELECT * FROM users WHERE username='$username' AND password='$password'";
        $hasil = mysqli_query($koneksi, $query);

        if (mysqli_num_rows($hasil) > 0) {
            $data = mysqli_fetch_assoc($hasil);

            $_SESSION['user_id']       = $data['id'];
            $_SESSION['user_username'] = $data['username'];
            $_SESSION['user_nama']     = $data['nama'];
            $_SESSION['user_role']     = $data['role'];

            return true;
        }

        return false;
    }

    public function logout() {
        session_destroy();
    }
}
