<?php
include 'database.php';

// Функция для выполнения запроса к API
function getExchangeRate($baseAsset, $filterAsset, $startTime, $endTime) {
    $apiKey = '5ABB5D9B-EF4C-4654-9DDC-9188E398E71D';
    $period = '1MIN';
    $limit = 5;
    $url = 'https://rest.coinapi.io/v1/exchangerate/' . $baseAsset . '/' . $filterAsset . '/history' . '?period_id=' . $period . '&time_start=' . $startTime . '&time_end=' . $endTime . '&limit=' . $limit;
    echo $url . '</br>' ;
    $headers = [
        'X-CoinAPI-Key: ' . $apiKey
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}



try {
    // Получаем список криптовалют из базы данных
    $stmt = $conn->query("SELECT * FROM Cryptocurrency");
    $cryptos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Определяем время начала и конца периода (5 минут назад от текущего времени)
    $minutes_back = 5;
    $minutes_back_str = '-' . $minutes_back . 'minutes';
    $startTime = gmdate('Y-m-d\TH:i:s\Z', strtotime($minutes_back_str));
    $endTime = gmdate('Y-m-d\TH:i:s\Z');
    echo $startTime . '</br>';
    echo $endTime . '</br>';

    // Перебираем каждую криптовалюту
    foreach ($cryptos as $crypto) {

        // Выполняем запрос к API для получения курса обмена
        $exchangeRateData = getExchangeRate($crypto['asset_id'], 'USD', $startTime, $endTime);
        // Получаем курс обмена
        $exchangeRate = $exchangeRateData[0]['rate_close']; // Предполагаем, что используется курс на момент закрытия

        foreach ($exchangeRateData as $data) {
            // Получаем курс обмена
            $exchangeRate = $data['rate_close']; // Предполагаем, что используется курс на момент закрытия

            // Записываем данные в таблицу Cryptocurrency
            $timestamp = gmdate('Y-m-d H:i:s', strtotime($data['time_close'])); // Дата и время в UTC из ответа API
            $stmt = $conn->prepare("CALL InsertCryptoHistoryProcedure(:crypto_id, :timestamp, :price_usd)");
            $stmt->bindParam(':crypto_id', $crypto['crypto_id']);
            $stmt->bindParam(':timestamp', $timestamp);
            $stmt->bindParam(':price_usd', $exchangeRate);
            $stmt->execute();
        }
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

header('Location: dashboard.php');
?>
