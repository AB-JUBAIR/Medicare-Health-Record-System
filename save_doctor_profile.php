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

// 1. ডেটা রিসিভ ও স্যানিটাইজ করা (Receiving and Sanitizing Data)
$doctorName = $conn->real_escape_string($_POST['doctorName']);
$regNo = $conn->real_escape_string($_POST['regNo']);
$specialty = $conn->real_escape_string($_POST['specialty']);
$mobile = $conn->real_escape_string($_POST['mobile']);
$passwordPlain = $conn->real_escape_string($_POST['password']);
$degrees = $conn->real_escape_string($_POST['degrees']);
$experience = (int)$_POST['experience'];
$about = $conn->real_escape_string($_POST['about']);
$videoFee = isset($_POST['video_fee']) ? (float)$_POST['video_fee'] : null;

// চেম্বার ডেটা আলাদাভাবে তৈরি করা (Filtering Chamber Data)
$chamberData = [];
$chamberCount = 1;

while (isset($_POST["chamber_name_{$chamberCount}"])) {
    $chamber = [
        'name' => $conn->real_escape_string($_POST["chamber_name_{$chamberCount}"]),
        'address' => $conn->real_escape_string($_POST["chamber_address_{$chamberCount}"]),
        'time' => $conn->real_escape_string($_POST["chamber_time_{$chamberCount}"]),
        'fee' => (float)$_POST["chamber_fee_{$chamberCount}"],
    ];
    $chamberData[] = $chamber;
    $chamberCount++;
}


// 2. ট্রানজেকশন শুরু করা (Start Transaction)
// যদি একটি ধাপও ব্যর্থ হয়, তবে সব ডেটা বাতিল হবে
$conn->begin_transaction();
$success = true;

try {
    // A. doctors টেবিলে ডেটা ইনসার্ট করা
    $sql_doctor = "INSERT INTO doctors (doctor_name, bmdc_reg_no, specialty, mobile_number, password, degrees, experience, about_doctor, video_fee)
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_doctor = $conn->prepare($sql_doctor);
    
    // Bind parameters (s=string, i=integer, d=double)
    $stmt_doctor->bind_param("ssssssisi", $doctorName, $regNo, $specialty, $mobile, $passwordPlain, $degrees, $experience, $about, $videoFee);

    if (!$stmt_doctor->execute()) {
        throw new Exception("ডাক্তার ডেটা ইনসার্ট করতে ব্যর্থ: " . $stmt_doctor->error);
    }

    // সদ্য ইনসার্ট হওয়া ডাক্তারের ID সংগ্রহ করা
    $doctor_id = $conn->insert_id;
    $stmt_doctor->close();


    // B. chambers টেবিলে ডেটা ইনসার্ট করা
    if (!empty($chamberData)) {
        $sql_chamber = "INSERT INTO chambers (doctor_id, chamber_name, chamber_address, consultation_time, first_visit_fee)
                        VALUES (?, ?, ?, ?, ?)";
        
        $stmt_chamber = $conn->prepare($sql_chamber);

        foreach ($chamberData as $chamber) {
            $stmt_chamber->bind_param("isssd", 
                $doctor_id, 
                $chamber['name'], 
                $chamber['address'], 
                $chamber['time'], 
                $chamber['fee']
            );

            if (!$stmt_chamber->execute()) {
                throw new Exception("চেম্বার ডেটা ইনসার্ট করতে ব্যর্থ: " . $stmt_chamber->error);
            }
        }
        $stmt_chamber->close();
    }
    
    // 3. সব সফল হলে ট্রানজেকশন কমিট করা
    $conn->commit();
    echo "<h1>✅ অভিনন্দন! ডাক্তার প্রোফাইল সফলভাবে তৈরি ও সংরক্ষণ করা হয়েছে।</h1>";

} catch (Exception $e) {
    // 4. কোনো ত্রুটি হলে ট্রানজেকশন রোলব্যাক করা
    $conn->rollback();
    echo "<h1>❌ প্রোফাইল সংরক্ষণে ত্রুটি:</h1>";
    echo "<p>{$e->getMessage()}</p>";
    $success = false;

}

// 5. সংযোগ বন্ধ করা
$conn->close();

?>