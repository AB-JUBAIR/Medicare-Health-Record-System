<?php
// save_profile.php - Register using NID (no email verification)

$servername = "localhost";
$username = "root";
$password = "root"; // MAMP default (Windows এ ফাঁকা হতে পারে "")
$dbname = "profile_db";

// Connect
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Invalid request method.");
}

// Sanitize & receive
$fullName   = $conn->real_escape_string(trim($_POST['fullName'] ?? ''));
$fatherName = $conn->real_escape_string(trim($_POST['fatherName'] ?? ''));
$motherName = $conn->real_escape_string(trim($_POST['motherName'] ?? ''));
$dob        = $_POST['dob'] ?? null;
$nid        = $conn->real_escape_string(trim($_POST['nid'] ?? ''));
$address    = $conn->real_escape_string(trim($_POST['address'] ?? ''));
$phone      = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
$bloodGroup = $conn->real_escape_string(trim($_POST['bloodGroup'] ?? ''));
$gender     = $conn->real_escape_string(trim($_POST['gender'] ?? ''));
$email      = $conn->real_escape_string(trim($_POST['email'] ?? ''));
$password   = $_POST['password'] ?? '';

// Basic validation
if (empty($fullName) || empty($nid) || empty($password)) {
    die("<script>alert('Full name, NID and password are required.'); window.location.href='Register.html';</script>");
}

// Check NID uniqueness
$chk = $conn->prepare("SELECT id FROM profiles WHERE nid = ?");
$chk->bind_param("s", $nid);
$chk->execute();
$chk->store_result();
if ($chk->num_rows > 0) {
    $chk->close();
    die("<script>alert('এই NID/বাচসন নম্বর দিয়ে আগে থেকেই অ্যাকাউন্ট আছে।'); window.location.href='Register.html';</script>");
}
$chk->close();

// Hash password
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

// Profile picture (optional) — store as blob (current approach)
$profilePic = null;
if (!empty($_FILES['profilePic']['tmp_name']) && is_uploaded_file($_FILES['profilePic']['tmp_name'])) {
    $profilePic = file_get_contents($_FILES['profilePic']['tmp_name']);
}

// Insert into DB (verified set to 1 because we are not verifying by email)
$stmt = $conn->prepare("INSERT INTO profiles (fullName, fatherName, motherName, dob, nid, address, phone, bloodGroup, gender, email, password, profilePic, verified) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("ssssssssssss", $fullName, $fatherName, $motherName, $dob, $nid, $address, $phone, $bloodGroup, $gender, $email, $passwordHash, $profilePic);

if ($stmt->execute()) {
    // successful
    echo "<script>alert('প্রোফাইল সফলভাবে তৈরি হয়েছে। এখন আপনি লগইন করতে পারেন।'); window.location.href='login.html';</script>";
} else {
    // handle duplicate nid error or other errors
    if ($conn->errno == 1062) {
        echo "<script>alert('Duplicate entry: এই NID দিয়ে আগে থেকেই অ্যাকাউন্ট আছে।'); window.location.href='Register.html';</script>";
    } else {
        echo "Error: " . htmlspecialchars($stmt->error);
    }
}

$stmt->close();
$conn->close();
?>
