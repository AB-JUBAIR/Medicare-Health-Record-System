<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "profile_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

// Initialize error message
$error_msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regNo = $_POST['regNo'];
    $pass = $_POST['password'];

    $sql = "SELECT * FROM doctors WHERE bmdc_reg_no = ? AND password = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $regNo, $pass);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $doctor = $result->fetch_assoc();
        $_SESSION['doctor_id'] = $doctor['id'];
        $_SESSION['doctor_name'] = $doctor['doctor_name'];

        header("Location: doctor-dashboard.php");
        exit;
    } else {
        $error_msg = "Invalid Registration No or Password!";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Doctor Login</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: linear-gradient(to bottom, #f0f9ff, #c3e2ff);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .login-box {
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 8px 16px rgba(0,0,0,0.1);
      width: 350px;
      text-align: center;
    }
    h2 {
      margin-bottom: 20px;
      color: #0056b3;
    }
    label {
      font-weight: bold;
      margin-top: 10px;
      display: block;
      text-align: left;
    }
    input {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      border: 1px solid #ccc;
      border-radius: 6px;
    }
    button {
      background-color: #007bff;
      color: white;
      width: 100%;
      border: none;
      padding: 12px;
      margin-top: 15px;
      border-radius: 6px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    .error {
      color: red;
      margin-top: 10px;
    }
    .register-btn {
      background-color: #28a745;
      margin-top: 10px;
    }
    .register-btn:hover {
      background-color: #1e7e34;
    }
  </style>
</head>
<body>

  <div class="login-box">
    <h2>Doctor Login</h2>

    <?php if($error_msg !== ""): ?>
      <p class="error"><?= $error_msg ?></p>
    <?php endif; ?>

    <form action="" method="POST">
      <label for="regNo">BMDC Registration No</label>
      <input type="text" id="regNo" name="regNo" required>
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required>
      <button type="submit">Login</button>
    </form>
    <button type="button" class="register-btn" onclick="window.location.href='Doctorregistration.html'">
      Registration
    </button>
  </div>

</body>
</html>
