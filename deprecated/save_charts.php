<?php
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "bebrabebra";
$dbname = "crypto_dash";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Устанавливаем режим ошибок PDO в исключения
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Удаляем все существующие записи о графиках из базы данных
    $stmt = $conn->query("DELETE FROM Chart");
    $stmt->execute();

    // Добавляем новые записи о графиках в базу данных
    if(isset($_POST['charts'])) {
        $charts = json_decode($_POST['charts'], true);
        foreach($charts as $chart) {
            $stmt = $conn->prepare("INSERT INTO Chart (user_id, x_position, y_position, width, height, graph_color)
                                    VALUES (:user_id, :x_position, :y_position, :width, :height, :graph_color)");
            $stmt->bindParam(':user_id', $chart['user_id']);
            $stmt->bindParam(':x_position', $chart['x_position']);
            $stmt->bindParam(':y_position', $chart['y_position']);
            $stmt->bindParam(':width', $chart['width']);
            $stmt->bindParam(':height', $chart['height']);
            $stmt->bindParam(':graph_color', $chart['graph_color']);
            $stmt->execute();
        }
    }

    // Отправляем успешный ответ
    echo json_encode(array('status' => 'success'));
} catch(PDOException $e) {
    // В случае ошибки отправляем сообщение об ошибке
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}

// Закрываем соединение с базой данных
$conn = null;
?>
