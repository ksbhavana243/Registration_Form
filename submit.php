<?php
// submit.php
// -----------------
// EDIT THESE if needed:
$db_host = '127.0.0.1';
$db_user = 'root';    // change if you use another user
$db_pass = '';        // put MySQL root password here if you set one
$db_name = 'registrations';
// -----------------

// show errors for development (remove in production)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// helper redirect
function redirect_with($params = []) {
    $qs = http_build_query($params);
    header("Location: index.php?$qs");
    exit;
}

// require POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with(['error' => 'Invalid request method']);
}

// collect + basic sanitize
$fullname = trim($_POST['fullname'] ?? '');
$email    = trim($_POST['email'] ?? '');
$phone    = trim($_POST['phone'] ?? '');
$dob      = trim($_POST['dob'] ?? '');
$gender   = trim($_POST['gender'] ?? '');
$country  = trim($_POST['country'] ?? '');
$city     = trim($_POST['city'] ?? '');
$about    = trim($_POST['about'] ?? '');

// server-side basic validation
if ($fullname === '' || $email === '' || $phone === '' || $dob === '' || $gender === '') {
    redirect_with(['error' => 'Please fill required fields']);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirect_with(['error' => 'Invalid email address']);
}
// validate date format (YYYY-MM-DD)
$d = DateTime::createFromFormat('Y-m-d', $dob);
if (!$d || $d->format('Y-m-d') !== $dob) {
    redirect_with(['error' => 'Invalid date of birth']);
}

// connect to MySQL server (no DB selected yet)
$mysqli = new mysqli($db_host, $db_user, $db_pass);

if ($mysqli->connect_errno) {
    redirect_with(['error' => 'DB connection failed: ' . $mysqli->connect_error]);
}

// create database if not exists
$createDbSql = "CREATE DATABASE IF NOT EXISTS `{$db_name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (!$mysqli->query($createDbSql)) {
    $err = $mysqli->error ?: 'Could not create database';
    $mysqli->close();
    redirect_with(['error' => $err]);
}

// select the database
if (!$mysqli->select_db($db_name)) {
    $err = $mysqli->error ?: 'Could not select database';
    $mysqli->close();
    redirect_with(['error' => $err]);
}

// create users table if not exists
$createTableSql = "
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `fullname` VARCHAR(200) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(40),
  `dob` DATE,
  `gender` ENUM('male','female','other') DEFAULT 'other',
  `country` VARCHAR(100),
  `city` VARCHAR(100),
  `about` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
if (!$mysqli->query($createTableSql)) {
    $err = $mysqli->error ?: 'Could not create table';
    $mysqli->close();
    redirect_with(['error' => $err]);
}

// prepared insert
$stmt = $mysqli->prepare("INSERT INTO users (fullname,email,phone,dob,gender,country,city,about) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    $err = $mysqli->error ?: 'Prepare failed';
    $mysqli->close();
    redirect_with(['error' => $err]);
}
$stmt->bind_param('ssssssss', $fullname, $email, $phone, $dob, $gender, $country, $city, $about);

if ($stmt->execute()) {
    $stmt->close();
    $mysqli->close();
    redirect_with(['success' => 1]);
} else {
    $err = $stmt->error ?: 'Insert failed';
    $stmt->close();
    $mysqli->close();
    redirect_with(['error' => $err]);
}
