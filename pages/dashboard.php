<?php

if (!isset($_COOKIE['username'])) {
    header("Location: ../index.php");
    exit();
}

include '../actions/database.php';
include '../actions/current_user.php';
// Функция для получения данных о графиках из базы данных
function getGraphsFromDatabase($conn, $user_id) {
    $stmt = $conn->prepare("SELECT * FROM Chart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $graphs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $graphs;
}

$graphs = getGraphsFromDatabase($conn, $user_id);

// Получение данных цен из CryptocurrencyHistory
$cryptoHistory = array();
foreach ($graphs as $graph) {
    $crypto_id = $graph['crypto_id'];
    $sql = "SELECT crypto_id, timestamp, price_usd FROM CryptoPriceHistoryView WHERE crypto_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$crypto_id]);
    $timestamps = array();
    $prices = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $timestamps[] = $row['timestamp'];
        $prices[] = $row['price_usd'];
    }
    $cryptoHistory[$graph['chart_id']] = array('timestamps' => $timestamps, 'prices' => $prices);
}

function getAssetId($conn, $crypto_id, $crypto) {
    if ($crypto) {
        $sql = "SELECT DISTINCT asset_id FROM Cryptocurrency WHERE crypto_id = ?";
    }
    else {
        $sql = "SELECT DISTINCT asset_id FROM Currency WHERE currency_id = ?";
    }
    $stmt = $conn->prepare($sql);
    $stmt->execute([$crypto_id]);
    $asset_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
    return $asset_ids[0];
}
?>

<?php include '../layouts/header.php' ?>;


    <div class="content" style="position: relative;">
        <button id="realtime-button">RealTime</button>
        <button id="update-history-button">Update</button>
        <button id="add-button">Add chart</button>
        <?php foreach ($graphs as $graph): ?>
            <div class="chart-container" style="width:<?php echo $graph['width']; ?>px; height:<?php echo $graph['height']; ?>px; top:<?php echo $graph['y_position']; ?>px; left:<?php echo $graph['x_position']; ?>px;">
                <canvas id="chart-<?php echo $graph['chart_id']; ?>"></canvas>
            </div>
        <?php endforeach; ?>
    </div>

    <script>
        let charts = [];
        let socket = null;
        let isRealTimeActive = false;

        <?php foreach ($graphs as $graph): ?>

            (function() {
                const ctx = document.getElementById('chart-<?php echo $graph['chart_id']; ?>').getContext('2d');
                const timestamps = <?php echo json_encode($cryptoHistory[$graph['chart_id']]['timestamps']); ?>;
                const prices = <?php echo json_encode($cryptoHistory[$graph['chart_id']]['prices']); ?>;
                const asset_id = "<?php echo getAssetId($conn, $graph["crypto_id"], true)?>";
                const asset_id_to = "<?php echo getAssetId($conn, $graph["currency_id_to"], false)?>";
                const chart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: timestamps,
                        datasets: [{
                            data: prices,
                            label: asset_id + '/' + asset_id_to,
                            borderColor: '<?php echo $graph["graph_color"]; ?>',
                            fill: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            xAxes: [{
                                display: true
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

                charts.push({ chart: chart, asset_id: asset_id, container: document.querySelector('.chart-container') });
            })();
        <?php endforeach; ?>



        $(document).ready(function() {
            $('#update-history-button').click(function() {
                $.ajax({
                    url: '../actions/history.php',
                    method: 'GET',
                    success: function(response) {
                        $('#history-content').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', status, error);
                    }
                });
            });
        });


        document.getElementById('add-button').addEventListener('click', function() {
            window.location.href = 'new_chart.php';
        }

        )

        document.getElementById('realtime-button').addEventListener('click', function() {
            if (isRealTimeActive) {
                if (socket) {
                    socket.close();
                    socket = null;
                }
                isRealTimeActive = false;
                this.textContent = "RealTime";
            } else {
            // Создаем массив для asset_id
            const assetIds = [];

            // Запрашиваем asset_id для каждого crypto_id
            <?php foreach ($graphs as $graph): ?>
                <?php
                $crypto_id = $graph['crypto_id'];
                $sql = "SELECT DISTINCT asset_id FROM Cryptocurrency WHERE crypto_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->execute([$crypto_id]);
                $asset_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
                ?>

                assetIds.push(<?php echo json_encode($asset_ids); ?>);
            <?php endforeach; ?>

            // Объединяем массивы в один и удаляем дубликаты
            const uniqueAssetIds = [...new Set(assetIds.flat())];
            console.log(uniqueAssetIds);
                socket = new WebSocket('wss://ws.coinapi.io/v1/');
                socket.onopen = function(event) {
                    socket.send(JSON.stringify({
                        "type": "hello",
                        "apikey": "5ABB5D9B-EF4C-4654-9DDC-9188E398E71D",
                        "subscribe_data_type": ["trade"],
                        "subscribe_filter_asset_id": uniqueAssetIds
                    }));
                };

                socket.onmessage = function(event) {
                    const data = JSON.parse(event.data);

                    // Проходимся по каждому графику
                    for (const chartData of charts) {
                        console.log(data);
                        const price = data.price;
                        const sell = data.taker_side == 'SELL';
                        if (price > 1 && sell) {
                            const chart = chartData.chart;
                            const chartContainer = chartData.container;
                            const assetId = chartData.asset_id; // Получаем asset_id для данного графика
                            const timeExchange = new Date(data.time_exchange);
                            const formattedTime = `${("0" + timeExchange.getHours()).slice(-2)}:${("0" + timeExchange.getMinutes()).slice(-2)}:${("0" + timeExchange.getSeconds()).slice(-2)}`;

                            // console.log(price);

                            // Проверяем, содержит ли строка symbol_id значение asset_id
                            if (data.symbol_id.includes(assetId)) {
                                chart.data.labels.push(formattedTime);
                                chart.data.datasets[0].data.push(price);

                                // Ограничение количества точек на графике
                                const maxPoints = Math.floor(chartContainer.clientWidth / 10);
                                if (chart.data.labels.length > maxPoints) {
                                    const numPointsToRemove = chart.data.labels.length - maxPoints;
                                    chart.data.labels.splice(0, numPointsToRemove);
                                    chart.data.datasets[0].data.splice(0, numPointsToRemove);
                                }

                                chart.update({ duration: 0, lazy: false, easing: 'linear' }, false);
                            }
                        }
                    }
                };





                socket.onerror = function(error) {
                    console.log(`WebSocket error: ${error}`);
                };

                isRealTimeActive = true;
                this.textContent = "Stop RealTime";
            }
        });


    </script>

<?php include '../layouts/footer.php' ?>;</body>
