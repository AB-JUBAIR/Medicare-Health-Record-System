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
$doctor_id = $_SESSION['doctor_id'];

$sql = "SELECT * FROM doctors WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctor_id);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Doctor Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #eef6ff;
      padding: 20px;
    }
    .dashboard {
      max-width: 950px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    }
    .profile-pic {
      width: 120px;
      height: 120px;
      border-radius: 50%;
      border: 3px solid #007bff;
      object-fit: cover;
    }
    h2 {
      color: #0056b3;
    }
    .logout-btn {
      background-color: #dc3545;
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      float: right;
    }
    input, button {
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 6px;
      margin-top: 8px;
    }
    button {
      background-color: #007bff;
      color: white;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    .result-box {
      background: #f9f9f9;
      border: 1px solid #ccc;
      padding: 20px;
      margin-top: 20px;
      border-radius: 8px;
    }
    .file-upload {
      margin-top: 10px;
    }
  </style>
</head>
<body>

<div class="dashboard">
  <form action="logout.php" method="POST">
    <button class="logout-btn" type="submit">Logout</button>
  </form>

  <h2>Welcome, Dr. <?php echo htmlspecialchars($doctor['doctor_name']); ?></h2>
  <img src="<?php echo htmlspecialchars($doctor['profile_photo_url'] ?? 'https://via.placeholder.com/120'); ?>" class="profile-pic" alt="Profile Photo">
  <p><strong>Specialty:</strong> <?php echo $doctor['specialty']; ?></p>
  <p><strong>Mobile:</strong> <?php echo $doctor['mobile_number']; ?></p>
  <p><strong>Experience:</strong> <?php echo $doctor['experience']; ?> years</p>
  <p><strong>Video Fee:</strong> ৳<?php echo $doctor['video_fee']; ?></p>
  <p><strong>About:</strong> <?php echo $doctor['about_doctor']; ?></p>

  <hr>
  <h3>🔍 Search Patient by NID</h3>
  <form action="" method="GET">
    <input type="text" name="nid" placeholder="Enter NID Number" required>
    <button type="submit">Search</button>
  </form>

  <?php
  if (isset($_GET['nid'])) {
    $nid = $_GET['nid'];

    $sql_patient = "SELECT * FROM profiles WHERE nid = ?";
    $stmt2 = $conn->prepare($sql_patient);
    $stmt2->bind_param("s", $nid);
    $stmt2->execute();
    $res = $stmt2->get_result();

    if ($res->num_rows > 0) {
      $patient = $res->fetch_assoc();
      echo "<div class='result-box'>";
      echo "<h4>👤 Patient Found:</h4>";
      // **রোগীর প্রোফাইল পিকচার দেখানোর জন্য যোগ করা হয়েছে**
     
     
     
     
     
      // **শেষ**
      echo "<p><strong>Name:</strong> " . htmlspecialchars($patient['fullName']) . "</p>";
      echo "<p><strong>NID:</strong> " . htmlspecialchars($patient['nid']) . "</p>";
      echo "<p><strong>Mobile:</strong> " . htmlspecialchars($patient['phone']) . "</p>";
      echo "<p><strong>Date Of Birth:</strong> " . htmlspecialchars($patient['dob']) . "</p>";
      echo "<p><strong>Gender:</strong> " . htmlspecialchars($patient['gender']) . "</p>";
      echo "<p><strong>Blood Group:</strong> " . htmlspecialchars($patient['bloodGroup']) . "</p>";

      // Upload forms
      echo '<div class="file-upload">';
      echo '<h4>📄 Upload Prescription</h4>';
      echo '<form action="upload_prescription.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="patient_id" value="' . $patient['id'] . '">
              <input type="text" name="prescriptions_name" placeholder ="Prescription Name?" required>
              <input type="file" name="prescription" required>
              <button type="submit">Upload Prescription</button>
            </form>';

      echo '<h4 style="margin-top:15px;">🧪 Upload Test Report</h4>';
      echo '<form action="upload_report.php" method="POST" enctype="multipart/form-data">
              <input type="hidden" name="patient_id" value="' . $patient['id'] . '">
              <input type="text" name="report_name" placeholder ="Report Name?" required>
              <input type="file" name="report" required>
              <button type="submit">Upload Report</button>
            </form>';
      echo '</div>';
      echo "</div>";
      // --- প্রেসক্রিপশন প্রদর্শন ---
      echo '<h3 style="margin-top: 25px;">📄 Prescriptions</h3>';
      $sql_pres = "SELECT id, prescriptions_name, file_path, uploaded_at FROM prescriptions WHERE patient_id = ? ORDER BY uploaded_at DESC";
      $stmt_pres = $conn->prepare($sql_pres);
      $stmt_pres->bind_param("i", $patient['id']);
      $stmt_pres->execute();
      $res_pres = $stmt_pres->get_result();

      if ($res_pres->num_rows > 0) {
        while ($pres = $res_pres->fetch_assoc()) {
          echo "<div style='border: 1px dashed #007bff; padding: 10px; margin-bottom: 10px; border-radius: 6px;'>";
          echo "<p><strong>Name:</strong> " . htmlspecialchars($pres['prescriptions_name']) . "</p>";
          echo "<p><strong>Uploaded:</strong> " . date('Y-m-d', strtotime($pres['uploaded_at'])) . "</p>";
          // ফাইলের লিঙ্কের জন্য সঠিক পাথ ব্যবহার করুন
          echo '<p><a href="uploads/prescriptions/' . htmlspecialchars(basename($pres['file_path'])) . '" target="_blank">View File</a></p>';
          
          // **ডিলিট ফর্ম** (Delete.php ফাইল তৈরি করতে হবে)
          echo '<form action="delete_file.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="file_id" value="' . $pres['id'] . '">
                  <input type="hidden" name="file_type" value="prescription">
                  <input type="hidden" name="file_path" value="' . htmlspecialchars($pres['file_path']) . '">
                  <button type="submit" onclick="return confirm(\'Are you sure you want to delete this prescription?\')" style="background-color:#dc3545; padding: 5px 10px;">Delete</button>
                </form>';
          echo "</div>";
        }
      } else {
        echo "<p>No prescriptions found for this patient.</p>";
      }
      $stmt_pres->close();
      
      // --- রিপোর্ট প্রদর্শন ---
      echo '<h3 style="margin-top: 25px;">🧪 Test Reports</h3>';
      $sql_report = "SELECT id, report_name, file_path, uploaded_at FROM reports WHERE patient_id = ? ORDER BY uploaded_at DESC";
      $stmt_report = $conn->prepare($sql_report);
      $stmt_report->bind_param("i", $patient['id']);
      $stmt_report->execute();
      $res_report = $stmt_report->get_result();

      if ($res_report->num_rows > 0) {
        while ($report = $res_report->fetch_assoc()) {
          echo "<div style='border: 1px dashed #28a745; padding: 10px; margin-bottom: 10px; border-radius: 6px;'>";
          echo "<p><strong>Name:</strong> " . htmlspecialchars($report['report_name']) . "</p>";
          echo "<p><strong>Uploaded:</strong> " . date('Y-m-d', strtotime($report['uploaded_at'])) . "</p>";
          // ফাইলের লিঙ্কের জন্য সঠিক পাথ ব্যবহার করুন
          echo '<p><a href="uploads/reports/' . htmlspecialchars(basename($report['file_path'])) . '" target="_blank">View File</a></p>';

          // **ডিলিট ফর্ম** (Delete.php ফাইল তৈরি করতে হবে)
          echo '<form action="delete_file.php" method="POST" style="display:inline-block;">
                  <input type="hidden" name="file_id" value="' . $report['id'] . '">
                  <input type="hidden" name="file_type" value="report">
                  <input type="hidden" name="file_path" value="' . htmlspecialchars($report['file_path']) . '">
                  <button type="submit" onclick="return confirm(\'Are you sure you want to delete this report?\')" style="background-color:#dc3545; padding: 5px 10px;">Delete</button>
                </form>';
          echo "</div>";
        }
      } else {
        echo "<p>No test reports found for this patient.</p>";
      }
      $stmt_report->close();
      // --- সেকশন শেষ ---
    } else {
      echo "<p style='color:red;'>❌ No patient found with that NID.</p>";
    }
  }
  ?>
</div>

</body>
</html>
