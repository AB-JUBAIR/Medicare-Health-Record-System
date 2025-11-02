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
if ($conn->connect_error) {
    die("❌ Database connection failed: " . $conn->connect_error);
}

$patient_id = $_POST['patient_id'] ?? null;
$doctor_id = $_SESSION['doctor_id'];
$doctor_name = $_SESSION['doctor_name'] ?? 'Unknown Doctor';
$prescriptions_name= $_POST['prescriptions_name'] ?? 'Prescription';

if (!$patient_id) {
    die("❌ Patient ID is missing.");
}

if (isset($_FILES['prescription']) && $_FILES['prescription']['error'] === 0) {
    $uploadDir = "uploads/prescriptions/";
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = uniqid() . "_" . basename($_FILES['prescription']['name']);
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['prescription']['tmp_name'], $targetPath)) {
        $sql = "INSERT INTO prescriptions (doctor_id, patient_id, prescriptions_name, file_path, uploaded_at, doctor_name)
                VALUES (?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iisss", $doctor_id, $patient_id, $prescriptions_name, $targetPath, $doctor_name);

        if ($stmt->execute()) {
            echo "✅ Prescription uploaded successfully!";
        } else {
            echo "❌ Database Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "❌ Failed to upload file.";
    }
} else {
    echo "❌ No file selected or upload error.";
}

$conn->close();
?>
