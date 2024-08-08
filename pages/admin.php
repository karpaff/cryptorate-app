<?php
include '../layouts/header.php';
include '../actions/database.php';

// Проверяем, если пользователь не администратор, перенаправляем его на главную страницу
if(!isset($_COOKIE['role']) || (isset($_COOKIE['role']) && $_COOKIE['role'] !== "admin")) {
    header("Location: ../index.php");
    exit;
}

// Обработка добавления криптовалюты
if(isset($_POST['add_crypto'])) {
    $crypto_name = $_POST['crypto_name'];
    $asset_id = $_POST['asset_id'];

    try {
        // Подготавливаем SQL запрос для добавления криптовалюты
        $stmt = $conn->prepare("CALL InsertCryptocurrencyProcedure(:crypto_name, :asset_id)");
        // Привязываем параметры
        $stmt->bindParam(':crypto_name', $crypto_name);
        $stmt->bindParam(':asset_id', $asset_id);
        echo $crypto_name;
        echo $asset_id;
        // Выполняем запрос
        $stmt->execute();
        header("Location: admin.php");
        exit;
    } catch(PDOException $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Обработка удаления криптовалют
if(isset($_POST['delete_crypto'])) {
    // Проверяем, были ли выбраны криптовалюты для удаления
    if(isset($_POST['cryptos'])) {
        $cryptos = $_POST['cryptos'];
        try {
            // Подготавливаем SQL запрос для удаления криптовалют
            $stmt = $conn->prepare("CALL DeleteCryptocurrencyProcedure(:crypto_id)");
            foreach($cryptos as $crypto_id) {
                // Привязываем параметры
                $stmt->bindParam(':crypto_id', $crypto_id);
                // Выполняем запрос для каждой выбранной криптовалюты
                $stmt->execute();
            }
            header("Location: admin.php");
            exit;
        } catch(PDOException $e) {
            $error =  "Error: " . $e->getMessage();
        }
    }
}

// Получаем текущие названия и символы криптовалют из базы данных
try {
    $stmt = $conn->query("SELECT * FROM Cryptocurrency");
    $cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $error =  "Error: " . $e->getMessage();
}
?>

<?php include '../layouts/header.php'; ?>

    <div class="content card">
        <h2>Админка</h2>
        <?php
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
        <!-- Форма для добавления криптовалюты -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="crypto_name">Название криптовалюты:</label><br>
            <input type="text" id="crypto_name" name="crypto_name" required><br>
            <label for="asset_id">Символ криптовалюты:</label><br>
            <input type="text" id="asset_id" name="asset_id" required maxlength="10"><br><br>
            <input type="submit" name="add_crypto" value="Добавить криптовалюту">
        </form>

        <!-- Форма для удаления криптовалюты -->
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <h3>Удалить криптовалюту:</h3>
            <?php if(count($cryptos) > 0):?>
                <?php foreach($cryptos as $crypto):?>
                    <input type="checkbox" id="crypto_<?php echo $crypto['crypto_id']; ?>" name="cryptos[]" value="<?php echo $crypto['crypto_id']; ?>">
                    <label for="crypto_<?php echo $crypto['crypto_id']; ?>"><?php echo $crypto['crypto_name'] . ' (' . $crypto['asset_id'] . ')'; ?></label><br>
                <?php endforeach; ?>
                <br>
                <input type="submit" name="delete_crypto" value="Удалить выбранные криптовалюты">
            <?php else: ?>
                <p>Нет доступных криптовалют для удаления.</p>
            <?php endif; ?>
        </form>
    </div>

<?php include '../layouts/footer.php'; ?>
