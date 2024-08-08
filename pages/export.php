<?php

if (!isset($_COOKIE['username'])) {
    header("Location: ../index.php");
    exit();
}

include '../actions/database.php';
include '../actions/current_user.php';

// Получение email пользователя из базы данных
$stmt = $conn->prepare("SELECT GetUserEmailByIdFunction(?) AS email");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
$user_email = $user['email'] ?? '';

// Получение доступных криптовалют из базы данных
$stmt = $conn->query("SELECT crypto_id, crypto_name, asset_id FROM Cryptocurrency");
$cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение доступных валют из базы данных
$stmt = $conn->query("SELECT currency_id, currency_name FROM Currency");
$currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../layouts/header.php' ?>
<div class="content card">
    <h2>Export Prices</h2>
    <form method="post" action="../actions/export_handler.php">
        <div>
            <label for="email">Email:</label>
            <input type="email" name="email" id="email" value="<?php echo $user_email; ?>" required>
        </div>
        <div>
            <label for="cryptocurrencies">Cryptocurrencies:</label>
</br>
            <?php if(count($cryptos) > 0):?>
                <?php foreach($cryptos as $crypto):?>
                    <input type="checkbox" id="crypto_<?php echo $crypto['crypto_id']; ?>" name="cryptos[]" value="<?php echo $crypto['crypto_id']; ?>">
                    <label for="crypto_<?php echo $crypto['crypto_id']; ?>"><?php echo $crypto['crypto_name'] . ' (' . $crypto['asset_id'] . ')'; ?></label><br>
                <?php endforeach; ?>
                <br>
            <?php else: ?>
                <p>Нет доступных криптовалют для выгрузки.</p>
            <?php endif; ?>
        </div>
        <div>
            <label>Валюты:</label>
            <?php foreach ($currencies as $key => $currency): ?>
                <label>
                    <input type="radio" name="currency_id" value="<?php echo $currency['currency_id']; ?>" <?php if ($key === 0) echo 'checked'; ?> required>
                    <?php echo $currency['currency_name']; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <input type="submit" name="" value="Отправить">
    </form>
    </div>
<?php include '../layouts/footer.php' ?>
