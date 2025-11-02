<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "profile_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request");
}

// Accept either NID or email? Here we assume login by NID
$nidOrEmail = trim($_POST['email'] ?? ''); // your form field name is 'email' but contains NID
$inputPassword = $_POST['password'] ?? '';

if (empty($nidOrEmail) || empty($inputPassword)) {
    die("<script>alert('NID/Email ও Password প্রয়োজন।'); window.location.href='login.html';</script>");
}

// Try to find by nid first, else by email
$sql = "SELECT * FROM profiles WHERE nid = ? OR email = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $nidOrEmail, $nidOrEmail);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    echo "<script>alert('Account পাওয়া যায়নি। দয়া করে সঠিক NID/Email ব্যবহার করুন।'); window.location.href='login.html';</script>";
    exit;
}

$user = $res->fetch_assoc();
$stmt->close();

// Verify password
if (!password_verify($inputPassword, $user['password'])) {
    echo "<script>alert('পাসওয়ার্ড ভুল।'); window.location.href='login.html';</script>";
    exit;
}

// Optional: check verified (we set verified=1 on creation)
if (isset($user['verified']) && $user['verified'] != 1) {
    echo "<script>alert('অ্যাকাউন্ট সক্রিয় নয়।'); window.location.href='login.html';</script>";
    exit;
}

// Login success
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['fullName'];
// redirect to dashboard
header("Location: patient-dashboard.php");
exit;
?>
