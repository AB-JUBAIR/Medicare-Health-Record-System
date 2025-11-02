<?php
session_start();
$conn = new mysqli("localhost", "root", "root", "profile_db");

if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'] ?? 1; // test purpose

// Patient info
$sql = "SELECT * FROM profiles WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Reports
$reports_sql = "SELECT * FROM reports WHERE patient_id = ? ORDER BY uploaded_at DESC";
$rep_stmt = $conn->prepare($reports_sql);
$rep_stmt->bind_param("i", $user_id);
$rep_stmt->execute();
$reports = $rep_stmt->get_result();

// Prescriptions
$pres_sql = "SELECT * FROM prescriptions WHERE patient_id = ? ORDER BY uploaded_at DESC";
$pres_stmt = $conn->prepare($pres_sql);
$pres_stmt->bind_param("i", $user_id);
$pres_stmt->execute();
$prescriptions = $pres_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Patient Dashboard</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(to bottom, #d9f8e5, #00e2d8);
      min-height: 100vh;
      padding: 30px;
    }

    .container {
      max-width: 900px;
      margin: auto;
      background: #ffffffc2;
      padding: 25px;
      border-radius: 12px;
      box-shadow: 0px 5px 15px rgba(0,0,0,0.1);
    }

    .profile {
      display: flex;
      align-items: center;
      margin-bottom: 20px;
    }

    .profile img {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 20px;
      border: 4px solid #00a89d;
    }

    .buttons {
      margin: 20px 0;
      display: flex;
      justify-content: center;
      flex-wrap: wrap;
      gap: 15px;
    }

    .buttons button {
      background: #047857;
      color: #fff;
      border: none;
      padding: 10px 18px;
      border-radius: 8px;
      cursor: pointer;
      font-size: 16px;
      transition: 0.3s;
    }

    .buttons button:hover {
      background: #065f46;
    }

    section {
      display: none;
    }

    section.active {
      display: block;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background: #ffffffb3;
      margin-top: 20px;
    }

    th, td {
      padding: 10px;
      border-bottom: 1px solid #ccc;
      text-align: left;
    }

    th {
      background-color: #00a89d;
      color: white;
    }

    a.download-btn {
      background-color: #28a745;
      color: white;
      padding: 5px 10px;
      text-decoration: none;
      border-radius: 5px;
    }

    a.download-btn:hover {
      background-color: #1e7e34;
    }

    .info-table td {
      padding: 8px;
    }
  </style>
</head>

<body>
  <div class="container">
    <div class="profile">
      <?php
        if (!empty($user['profilePic'])) {
          $img = base64_encode($user['profilePic']);
          echo "<img src='data:image/jpeg;base64,$img' alt='Profile'>";
        } else {
          echo "<img src='Images/default.png' alt='Default'>";
        }
      ?>
      <div>
        <h2><?= htmlspecialchars($user['fullName']) ?></h2>
        <p><strong>ID:</strong> <?= htmlspecialchars($user['nid']) ?></p>
      </div>
    </div>

    <div class="buttons">
      <button onclick="showSection('about')">About</button>
      <button onclick="showSection('reports')">Reports</button>
      <button onclick="showSection('prescriptions')">Prescriptions</button>
    </div>

    <!-- About section -->
    <section id="section-about" class="active">
      <h2>Patient Information</h2>
      <table class="info-table">
        <tr><td><strong>Full Name:</strong></td><td><?= htmlspecialchars($user['fullName']) ?></td></tr>
        <tr><td><strong>Date of Birth:</strong></td><td><?= htmlspecialchars($user['dob']) ?></td></tr>
        <tr><td><strong>Email:</strong></td><td><?= htmlspecialchars($user['email']) ?></td></tr>
        <tr><td><strong>Phone:</strong></td><td><?= htmlspecialchars($user['phone']) ?></td></tr>
        <tr><td><strong>Address:</strong></td><td><?= htmlspecialchars($user['address']) ?></td></tr>
      </table>
    </section>

    <!-- Reports section -->
    <section id="section-reports">
      <h2>Reports</h2>
      <?php if ($reports->num_rows > 0): ?>
      <table>
        <tr><th>Report Name</th><th>Doctor</th> <th>Date</th><th>Download</th></tr>
        <?php while ($row = $reports->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['report_name']) ?></td>
          <td><?= htmlspecialchars($row['doctor_name']) ?></td>
          <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
          <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="download-btn">View / Download</a></td>
        </tr>
        <?php endwhile; ?>
      </table>
      <?php else: ?>
        <p>No reports uploaded yet.</p>
      <?php endif; ?>
    </section>

    <!-- Prescriptions section -->
    <section id="section-prescriptions">
      <h2>Prescriptions</h2>
      <?php if ($prescriptions->num_rows > 0): ?>
      <table>
        <tr><th>Doctor</th><th>Prescription</th><th>Date</th><th>Download</th></tr>
        <?php while ($row = $prescriptions->fetch_assoc()): ?>
        <tr>
          <td><?= htmlspecialchars($row['doctor_name']) ?></td>
          <td><?= htmlspecialchars($row['prescriptions_name']) ?></td>
          <td><?= htmlspecialchars($row['uploaded_at']) ?></td>
          <td><a href="<?= htmlspecialchars($row['file_path']) ?>" target="_blank" class="download-btn">View / Download</a></td>
        </tr>
        <?php endwhile; ?>
      </table>
      <?php else: ?>
        <p>No prescriptions available yet.</p>
      <?php endif; ?>
    </section>
  </div>

  <script>
    function showSection(id) {
      document.querySelectorAll('section').forEach(s => s.classList.remove('active'));
      document.getElementById('section-' + id).classList.add('active');
    }
  </script>
</body>
</html>
