<?php
$servername = "localhost";
$username = "root";
$password = "bebrabebra";
$dbname = "crypto_dash";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Устанавливаем режим ошибок PDO в исключения
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Подготавливаем SQL-запрос для вставки нового чарта
    $stmt = $conn->prepare("INSERT INTO Graph (user_id, x_position, crypto_id, currency_id_to, y_position, width, height, graph_color)
                            VALUES (:user_id, :x_position, :crypto_id, :currency_id_to, :y_position, :width, :height, :graph_color)");

    // Данные для вставки
    $user_id = 1; // Замените на актуальный ID пользователя
    $x_position = 0; // Начальная позиция по оси X
    $crypto_id = 1; // Замените на актуальный ID криптовалюты
    $currency_id_to = 1; // Замените на актуальный ID валюты
    $y_position = 0; // Начальная позиция по оси Y
    $width = 400; // Ширина графика
    $height = 300; // Высота графика
    $graph_color = '#000000'; // Цвет графика

    // Привязываем значения к параметрам
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':x_position', $x_position);
    $stmt->bindParam(':crypto_id', $crypto_id);
    $stmt->bindParam(':currency_id_to', $currency_id_to);
    $stmt->bindParam(':y_position', $y_position);
    $stmt->bindParam(':width', $width);
    $stmt->bindParam(':height', $height);
    $stmt->bindParam(':graph_color', $graph_color);

    // Выполняем запрос
    $stmt->execute();

    echo "Новый чарт успешно добавлен в базу данных";
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Закрываем соединение с базой данных
$conn = null;
?>
