<?php
$servername = "localhost";
$username = "root";
$password = "root"; // MAMP Default
$dbname = "profile_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
  die("Database Connection Failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $user_id = $_POST['user_id'];

  if (!empty($_FILES['profilePic']['tmp_name'])) {
    $profilePic = file_get_contents($_FILES['profilePic']['tmp_name']);

    $stmt = $conn->prepare("UPDATE profiles SET profilePic = ? WHERE id = ?");
    $stmt->bind_param("bi", $null, $user_id);

    // ⚠️ MySQLi-এর blob update একটু আলাদা — send_long_data ব্যবহার করবো:
    $stmt->send_long_data(0, $profilePic);

    if ($stmt->execute()) {
      echo "<script>alert('✅ Profile photo updated successfully!'); window.location.href='patient-dashboard.html';</script>";
    } else {
      echo "❌ Update failed: " . $stmt->error;
    }

    $stmt->close();
  } else {
    echo "❌ No photo uploaded!";
  }
}

$conn->close();
?>
