<?php
session_start();
if (!isset($_SESSION['doctor_id'])) {
  header("Location: doctor_login.html");
  exit;
}

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "profile_db";

$conn = new mysqli($servername, $username, $password, $dbname);

$patient_id = $_POST['patient_id'];
$doctor_id = $_SESSION['doctor_id'];
$report_name = $_POST['report_name'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Unknown Doctor';

if (isset($_FILES['report']) && $_FILES['report']['error'] == 0) {
    $fileName = uniqid() . "_" . basename($_FILES['report']['name']);
    $targetPath = "uploads/reports/" . $fileName;

    if (!is_dir("uploads/reports")) {
        mkdir("uploads/reports", 0777, true);
    }

    if (move_uploaded_file($_FILES['report']['tmp_name'], $targetPath)) {
        $sql = "INSERT INTO reports (doctor_id, patient_id, report_name, doctor_name, file_path, uploaded_at)
                VALUES (?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $doctor_id, $patient_id, $report_name, $doctor_name, $targetPath);
        $stmt->execute();
        echo "✅ Report uploaded successfully!";
    } else {
        echo "❌ Failed to upload file.";
    }
} else {
    echo "❌ No file selected or upload error.";
}
?>
