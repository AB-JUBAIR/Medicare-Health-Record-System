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
    die("❌ ডেটাবেস সংযোগ ব্যর্থ: " . $conn->connect_error);
}

// নিশ্চিত করুন যে অনুরোধটি POST মেথডে এসেছে
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. ডেটা রিসিভ করা ও স্যানিটাইজ করা
    $hospitalName = $conn->real_escape_string($_POST['hospitalName']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $address = $conn->real_escape_string($_POST['address']);
    $password = $_POST['password']; // পাসওয়ার্ড হ্যাশ করার জন্য আগে সেভ করলাম

    // পাসওয়ার্ড হ্যাশ করা (Password Hashing for Security)
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
    
    $licensePath = null; // লাইসেন্স ফাইলের সেভ করা পাথ
    
    // 2. লাইসেন্স ফাইল আপলোড হ্যান্ডলিং (License File Upload Handling)
    if (isset($_FILES['license']) && $_FILES['license']['error'] == 0) {
        $targetDir = "uploads/licenses/";
        
        // নিশ্চিত করুন যে আপলোড ডিরেক্টরি বিদ্যমান
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["license"]["name"]);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = uniqid('license_') . '.' . $fileExt;
        $targetFilePath = $targetDir . $newFileName;
        
        // ফাইলটি অস্থায়ী স্থান থেকে গন্তব্যে সরিয়ে নেওয়া
        if (move_uploaded_file($_FILES["license"]["tmp_name"], $targetFilePath)) {
            $licensePath = $conn->real_escape_string($targetFilePath);
        } else {
            echo "❌ ফাইল আপলোড ব্যর্থ হয়েছে।";
            $conn->close();
            exit;
        }
    }


    // 3. hospitals টেবিলে ডেটা ইনসার্ট করা
    $sql = "INSERT INTO hospitals (hospital_name, email, phone_number, address, license_path, password_hash)
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    
    // Bind parameters (s=string)
    $stmt->bind_param("ssssss", $hospitalName, $email, $phone, $address, $licensePath, $passwordHash);

    if ($stmt->execute()) {
        echo "<h1>✅ সফল! হাসপাতাল '{$hospitalName}' সফলভাবে রেজিস্টার্ড হয়েছে।</h1>";
        echo "<p>আপনার লাইসেন্স ফাইল এই পাথে সেভ হয়েছে: {$licensePath}</p>";
    } else {
        // যদি ইমেইল আগে থেকেই বিদ্যমান থাকে (UNIQUE constraint), তবে ত্রুটি দেখাবে
        if ($conn->errno == 1062) {
             echo "<h1>❌ রেজিস্ট্রেশন ব্যর্থ:</h1>";
             echo "<p>এই ইমেইলটি (<strong>{$email}</strong>) দিয়ে আগেই রেজিস্ট্রেশন করা হয়েছে।</p>";
        } else {
            echo "<h1>❌ রেজিস্ট্রেশন ব্যর্থ:</h1>";
            echo "<p>সার্ভার ত্রুটি: " . $stmt->error . "</p>";
        }
    }

    $stmt->close();

} else {
    echo "❌ ডেটাবেসে সরাসরি অ্যাক্সেস অনুমোদিত নয়।";
}

// 4. সংযোগ বন্ধ করা
$conn->close();

?>