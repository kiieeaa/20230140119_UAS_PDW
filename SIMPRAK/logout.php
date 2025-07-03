<?php
session_start();
$_SESSION = array();
session_destroy();

// Redirect ke halaman login. Path ../ disesuaikan jika logout dari folder /asisten atau /mahasiswa
header("Location: login.php");
exit;