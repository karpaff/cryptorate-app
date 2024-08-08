<?php
include '../actions/database.php';

// Проверяем, если пользователь уже авторизован, перенаправляем его на главную страницу
if (isset($_COOKIE['username'])) {
    header("Location: ../index.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Проверяем, является ли запрос авторизацией
    if (isset($_POST['login'])) {
        $username = $_POST['username'];
        $password = $_POST['password'];

        try {
            // Подготавливаем SQL запрос
            $stmt = $conn->prepare("SELECT * FROM User WHERE username = :username");
            // Привязываем параметры
            $stmt->bindParam(':username', $username);
            // Выполняем запрос
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Устанавливаем куки
                setcookie("username", $username, time() + (86400 * 30), "/");

                // Получаем имя роли пользователя из базы данных
                $stmt = $conn->prepare("SELECT role_name FROM Role WHERE role_id = (SELECT role_id FROM User WHERE user_id = :user_id)");
                $stmt->bindParam(':user_id', $user['user_id']);
                $stmt->execute();
                $role = $stmt->fetch(PDO::FETCH_ASSOC);
                $role = $role['role_name'];

                // Устанавливаем куки
                setcookie("username", $username, time() + (86400 * 30), "/");
                setcookie("role", $role, time() + (86400 * 30), "/");

                // Перенаправляем пользователя на главную страницу
                 header("Location: ../index.php");
                exit;
            } else {
                $error = "Неверное имя пользователя или пароль";
            }
        } catch (PDOException $e) {
            echo $e;
            $error = "Ошибка входа";
        }
    }
}
?>

<?php include '../layouts/header.php'; ?>
    <div class="content card">
        <h2>Авторизация</h2>
        <?php
        // Если есть сообщение об ошибке, выводим его
        if (isset($error)) {
            echo "<p class='error'>$error</p>";
        }
        ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="username">Имя:</label><br>
            <input type="text" id="username" name="username" required><br>
            <label for="password">Пароль:</label><br>
            <input type="password" id="password" name="password" required><br><br>
            <input type="submit" name="login" value="Авторизация">
        </form>
    </div>
    <?php include '../layouts/footer.php'; ?>
