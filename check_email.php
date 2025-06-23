<?php
include 'koneksi.php';

if(isset($_POST['email'])) {
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result->num_rows > 0) {
        echo "taken";
    } else {
        echo "available";
    }
}
?> 