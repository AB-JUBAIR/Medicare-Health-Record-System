<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Data View</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background-color: #f4f4f4; }
        h2 { color: #00796b; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #00796b; color: white; }
        tr:hover { background-color: #f1f1f1; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Hospital Registration Data from Localhost</h2>
    
<?php
// ডেটাবেস সংযোগ তথ্য (Database Connection Details for MAMP)
$servername = "localhost";
$username = "root";  // MAMP Default
$password = "root";  // MAMP Default
$dbname = "profile_db"; 

// MySQL-এ সংযোগ স্থাপন
$conn = new mysqli($servername, $username, $password, $dbname);

// সংযোগের ত্রুটি পরীক্ষা করা
if ($conn->connect_error) {
    die("<p class='error'>❌ ডেটাবেস সংযোগ ব্যর্থ: " . $conn->connect_error . "</p>");
}

// SQL Query: hospitals টেবিলের সমস্ত ডেটা নির্বাচন করা
$sql = "SELECT id, hospital_name, email, phone_number, address, registration_date FROM hospitals";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // ডেটা থাকলে একটি HTML টেবিল তৈরি করা
    echo "<table>";
    echo "<thead><tr><th>ID</th><th>Hospital Name</th><th>Email</th><th>Phone</th><th>Address</th><th>Registration Date</th></tr></thead>";
    echo "<tbody>";

    // প্রতিটি সারি ধরে ডেটা প্রদর্শন করা
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row["id"] . "</td>";
        echo "<td>" . $row["hospital_name"] . "</td>";
        echo "<td>" . $row["email"] . "</td>";
        echo "<td>" . $row["phone_number"] . "</td>";
        echo "<td>" . $row["address"] . "</td>";
        echo "<td>" . $row["registration_date"] . "</td>";
        echo "</tr>";
    }
    
    echo "</tbody></table>";
} else {
    echo "<p>ডেটাবেসে কোনো হাসপাতাল রেজিস্ট্রেশন ডেটা পাওয়া যায়নি।</p>";
}

// সংযোগ বন্ধ করা
$conn->close();
?>
    
</body>
</html>