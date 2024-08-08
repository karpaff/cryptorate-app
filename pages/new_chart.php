<?php

if (!isset($_COOKIE['username'])) {
    header("Location: ../index.php");
    exit();
}

include '../actions/database.php';
include '../actions/current_user.php';

// Получение списка криптовалют из базы данных
$stmt = $conn->query("SELECT crypto_id, crypto_name FROM Cryptocurrency");
$cryptocurrencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение списка валют из базы данных
$stmt = $conn->query("SELECT currency_id, currency_name FROM Currency");
$currencies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Получение максимального значения y_position из базы данных
$stmt = $conn->prepare("SELECT COALESCE(MAX(y_position), 0) AS max_y FROM Chart WHERE user_id = ?");
$stmt->execute([$user_id]);
$max_y = $stmt->fetch(PDO::FETCH_ASSOC)['max_y'];

$stmt = $conn->prepare("SELECT COUNT(*) AS charts_num FROM Chart WHERE user_id = ?");
$stmt->execute([$user_id]);
$charts_count = $stmt->fetch(PDO::FETCH_ASSOC)['charts_num'];

// Обработка отправки формы
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['add_chart_form'])) {
    // Получаем данные из формы
    $crypto_id = $_POST['crypto_id'];
    $currency_id_to = $_POST['currency_id_to'];
    $width = $_POST['width'];
    $height = $_POST['height'];
    $graph_color = $_POST['graph_color'];
    $y_position = $max_y + 10;

    if ($charts_count > 0) {
        $y_position += $height + 10;
    }

    // Вставляем данные в базу данных
    $stmt = $conn->prepare("CALL InsertOrUpdateChartProcedure(?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $crypto_id, $currency_id_to, $y_position, $width, $height, $graph_color]);

    // Перенаправляем на dashboard.php
        header("Location: dashboard.php");
    exit();
}
?>

<?php include '../layouts/header.php';?>

<div class="content card">
    <h2>Add New Chart</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <input type="hidden" name="add_chart_form" value="1">
        <div>
            <label for="crypto_id">Cryptocurrency:</label>
            <select name="crypto_id" id="crypto_id">
                <?php foreach ($cryptocurrencies as $crypto): ?>
                    <option value="<?php echo $crypto['crypto_id']; ?>"><?php echo $crypto['crypto_name']; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="currency_id_to">Currency:</label>
            <?php foreach ($currencies as $key => $currency): ?>
                <label>
                    <input type="radio" name="currency_id_to" value="<?php echo $currency['currency_id']; ?>" <?php if ($key === 0) echo 'checked'; ?> required>
                    <?php echo $currency['currency_name']; ?>
                </label>
            <?php endforeach; ?>
        </div>
        <div>
            <label for="width">Width:</label>
            <input type="number" name="width" id="width" value="800" required>
        </div>
        <div>
            <label for="height">Height:</label>
            <input type="number" name="height" id="height" value="400"required>
        </div>
        <div>
            <label for="graph_color">Graph Color:</label>
            <input type="color" name="graph_color" id="graph_color" required>
        </div>
        <button type="submit">Add Chart</button>
    </form>
</div>

<?php
include '../layouts/footer.php';
?>
