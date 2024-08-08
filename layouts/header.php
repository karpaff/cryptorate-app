<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Валютный Трекер</title>
    <link rel="stylesheet" href="../css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="sticky">
    <a href="../index.php"><header>
        <h2>Валютный Трекер</h2>
    </header>
    </a>
    <nav>
        <div>
            <a href="../pages/dashboard.php">Дашборд</a>
            <a href="../pages/new_chart.php">Новый график</a>
            <!-- <a href="edit.php">Редактировать</a> -->
            <a href="../pages/export.php">Выгрузка</a>
            <?php if(isset($_COOKIE['role']) && ($_COOKIE['role'] == 'admin'))
                echo '<a href="../pages/admin.php">Админка</a>';
            ?>
        </div>
        <?php
        // Проверяем, установлены ли куки для имени пользователя
        if(isset($_COOKIE['username'])) {
            $username = $_COOKIE['username'];
            echo '<div>';
            echo '<a href="../actions/logout.php" class="logout">Выйти (' . $username . ')</a>';
            echo '</div>';
        } else {
            // Если куки не установлены, показываем кнопку авторизации
            echo '<a href="../pages/login.php" class="auth-button">Авторизация</a>';
            echo '<a href="../pages/register.php" class="auth-button">Регистрация</a>';
        }
        ?>
    </nav>
    </div>
