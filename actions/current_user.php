<?php
$user_id = 1;
if (isset($_COOKIE['username'])) {
$user_name = $_COOKIE['username'];

$sql = "SELECT user_id FROM User WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$user_name]);
$user_id = $stmt->fetch(PDO::FETCH_ASSOC)['user_id'];
}
?>
