<?php

require_once 'vendor/autoload.php';

use WSSC\WebSocketClient;
use WSSC\Components\ClientConfig;

function establishConnection($serverUrl, $config)
{
    // Create a WebSocket client instance with ClientConfig
    $client = new WebSocketClient($serverUrl, $config);

    return $client;
}

// WebSocket server URL
$serverUrl = 'wss://ws.coinapi.io/v1/';

// Create a ClientConfig instance
$config = new ClientConfig();

// Prepare the "hello" message
$helloMessage = json_encode([
    "type" => "hello",
    "apikey" => "5ABB5D9B-EF4C-4654-9DDC-9188E398E71D",
    "subscribe_data_type" => ["trade"],
    "subscribe_filter_symbol_id" => ["BITSTAMP_SPOT_BTC_USD$", "BITFINEX_SPOT_BTC_LTC$"]
]);

// Establish connection
$client = establishConnection($serverUrl, $config);

// Send the "hello" message to the server
$client->connect($config);
$client->send($helloMessage);

// Receive messages from the server
while (true) {
    $message = $client->receive(); // This will block until a message is received
    if ($message === null) {
        break; // Break the loop if the connection is closed
    }
    echo "Received message: $message\n";
}

// Close the WebSocket connection
$client->close();
