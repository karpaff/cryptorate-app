<?php
include '../actions/database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['cryptos'])) {
    // Получение данных из формы
    $email = $_POST['email'];
    $cryptos = $_POST['cryptos'];
    $currency_id = $_POST['currency_id'];

    // Формирование текста письма
    $subject = "Текущие цены криптовалют";
    $message = "<h3>Текущие цены криптовалют, на которые вы подписаны:</h3>";
    $no_data = true;

    foreach ($cryptos as $crypto_id) {
        // Получение последней записи цены криптовалюты из базы данных
        $stmt = $conn->prepare("SELECT crypto_name FROM Cryptocurrency WHERE crypto_id = ?");
        $stmt->execute([$crypto_id]);
        $crypto_name = $stmt->fetch(PDO::FETCH_ASSOC)['crypto_name'];

        $stmt = $conn->prepare("SELECT price_usd, timestamp FROM CryptocurrencyHistory WHERE crypto_id = ? ORDER BY timestamp DESC LIMIT 1");
        $stmt->execute([$crypto_id]);
        $price_data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($price_data) {
            $price = number_format($price_data['price_usd'], 2, '.', '');
            $message .= "<b>$crypto_name</b>: {$price}$ ({$price_data['timestamp']}UTC)" . '</br>';
            $no_data = true;
        }
    }

    // Отправка письма
    $headers = "From: your_email@example.com"; // Замените на ваш email

    if(!$no_data) { mail($email, $subject, $message, $headers); echo 'done'; }

    // Перенаправление на страницу с подтверждением
    header("Location: ../pages/export_confirmation.php");
    exit();
}
else {
    header("Location: ../pages/export.php");
}
?>
