<?php
include '../actions/database.php';

// Проверяем, если пользователь уже аутентифицирован, перенаправляем на главную страницу
if (isset($_COOKIE['username']) && isset($_COOKIE['role'])) {
    header("Location: ../index.php");
    exit;
}

$username = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $role_id = 1; // По умолчанию устанавливаем роль пользователя

    try {
        // Подготавливаем SQL запрос
        $stmt = $conn->prepare("CALL InsertUserProcedure(:username, :email, :password, :role_id)");
        // Привязываем параметры
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':password', $password);
        $stmt->bindParam(':role_id', $role_id);
        // Выполняем запрос
        $stmt->execute();

        // Записываем логин текущего пользователя в куки
        setcookie("username", $username, time() + (86400 * 30), "/");
        // Записываем имя его роли в куки
        $role_name = "user";
        setcookie("role", $role_name, time() + (86400 * 30), "/");

        // Перенаправляем на главную страницу
        header("Location: ../index.php");
        exit;
    } catch (PDOException $e) {
        echo $e;
        $error = "Ошибка регистрации";
    }
}
?>


    <?php include '../layouts/header.php'; ?>
    <div class="content card">
        <?php if (isset($_COOKIE['username']) && isset($_COOKIE['role'])): ?>
            <h2>Добро пожаловать, <?php echo $_COOKIE['username']; ?>!</h2>
            <a href="logout.php" class="auth-button">Выйти</a>
        <?php else: ?>
            <h2>Регистрация</h2>
            <?php
            // Если есть сообщение об ошибке, выводим его
            if (isset($error)) {
                echo "<p class='error'>$error</p>";
            }
            ?>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="username">Имя:</label><br>
                <input type="text" id="username" name="username" value="<?php echo $username; ?>" required><br>
                <label for="email">Почта:</label><br>
                <input type="email" id="email" name="email" required><br>
                <label for="password">Пароль:</label><br>
                <input type="password" id="password" name="password" required><br><br>
                <input type="submit" value="Зарегистрироваться">
            </form>
        <?php endif; ?>
    </div>
    <?php include '../layouts/footer.php'; ?>

</html>
