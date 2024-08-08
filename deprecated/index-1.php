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

    // Получаем данные о графиках из базы данных
    $stmt = $conn->query("SELECT * FROM chart");
    $charts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    echo "Ошибка: " . $e->getMessage();
}

// Закрываем соединение с базой данных
$conn = null;
?>

<!DOCTYPE html>
<html>
<head>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            border: 1px solid black;
        }
        .chart-container canvas {
            width: 100% !important;
            height: auto !important;
        }
    </style>
    <title>Visualizing Data with Chart.js</title>
</head>
<body>
    <script>
        var charts = []; // Объявляем массив для хранения созданных графиков

        // Функция для создания и отрисовки графиков
        function createcharts() {
            // Перебираем данные о графиках из PHP
            <?php foreach ($charts as $chart): ?>
                var chartContainer = document.createElement('div');
                chartContainer.classList.add('chart-container');
                chartContainer.style.position = 'absolute';
                chartContainer.style.left = '<?php echo $chart["x_position"]; ?>px';
                chartContainer.style.top = '<?php echo $chart["y_position"]; ?>px';
                chartContainer.style.width = '<?php echo $chart["width"]; ?>px';
                chartContainer.style.height = '<?php echo $chart["height"]; ?>px';
                document.body.appendChild(chartContainer);

                var ctx = document.createElement('canvas').getContext('2d');
                chartContainer.appendChild(ctx.canvas);

                var newChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: [], // Данные для графика
                        datasets: [{
                            data: [], // Данные для графика
                            label: 'BTC/USD',
                            borderColor: '<?php echo $chart["graph_color"]; ?>', // Цвет графика
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                display: false
                            }],
                            yAxes: [{
                                scaleLabel: {
                                    display: true,
                                    labelString: 'Price'
                                }
                            }]
                        }
                    }
                });

                // Добавляем графики в массив для дальнейшего управления
                charts.push({ chart: newChart, container: chartContainer });
            <?php endforeach; ?>
        }

        // Вызываем функцию создания графиков после загрузки страницы
        window.onload = function() {
            createcharts();
        };
    </script>
</body>
</html>
