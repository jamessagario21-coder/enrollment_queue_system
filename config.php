<?php
$conn = new mysqli("127.0.0.1", "root", "", "enrollment_queue_db", 3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>