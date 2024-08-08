<?php
include 'database.php';

if (isset($_GET['crypto_id'])) {
    $crypto_id = $_GET['crypto_id'];
    $sql = "SELECT timestamp, price_usd FROM CryptocurrencyHistory WHERE crypto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$crypto_id]);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($data);
} else {
    echo json_encode([]);
}
?>
