<?php

//prod
$apiBaseUrl = 'https://api.merchants.zalando.com';
//dev
//$apiBaseUrl = 'https://api-sandbox.merchants.zalando.com';
$salesChannelID = '01924c48-49bb-40c2-9c32-ab582e6db6f4';
$merchantID = '5c7d2f22-394e-4abd-8466-b38f1bf2306a';
$clientID = 'f77018d2174f77e2e9b1fe94a0349abd';
$clientKey = 'bc31074a-89f8-4089-a09b-e057198eafc9';
$accessToken = '';

$tokenUrl = $apiBaseUrl . '/auth/token?v=' . time();
$ch = curl_init($tokenUrl);
curl_setopt($ch, CURLOPT_USERPWD, "$clientID:$clientKey");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    exit("Error obtaining token: HTTP $httpCode — $response\n");
}

$data = json_decode($response, true);
if ($data['access_token']) {
    $accessToken = $data['access_token'];
}

if (!$accessToken) {
    exit('Token Error');
}

//****************
$csvFile = __DIR__ . '/export.csv';
$logFile = __DIR__ . '/export_log.txt';

$stockPayload = [];
$pricePayload = [];

$counter = 0;
$handleLog = fopen($logFile, "w+");

$continue = false;

if (($handle = fopen($csvFile, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
        $num = count($data);
        if ($num < 3)
            continue;

        $ean = $data[0];
        $qty = $data[1];
        $price = (float) str_replace(',', '.', $data[2]);
        $list_price = (float) str_replace(',', '.', $data[3]);

        /*
          if( $ean == '4050768921006')
          $continue = true;

          if( !$continue )
          continue;
         */

        fwrite($handleLog, "Started export EAN: " . $ean . "\r\n");

        $putEanUrl = $apiBaseUrl . '/merchants/' . $merchantID . '/products/identifiers/' . $ean;
        $data = [
            "merchant_product_simple_id" => $ean
        ];
        $payload = json_encode($data);

        $ch = curl_init($putEanUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 204 && !$response) {
            fwrite($handleLog, "Export finished EAN: " . $ean . "\r\n");
        } else {
            fwrite($handleLog, "Export ERROR EAN: " . $ean . "\r\n");
            fwrite($handleLog, json_encode($response));
            fwrite($handleLog, "******-----------------------------------------\r\n");
            continue;
        }

        //*************************
        if ($price) {
            $postPriceUrl = $apiBaseUrl . '/merchants/' . $merchantID . '/prices';

            if ($list_price) {
                $data = [
                    "product_prices" => [[
                    "ean" => $ean,
                    "sales_channel_id" => $salesChannelID,
                    "regular_price" => [
                        "amount" => $list_price,
                        "currency" => "EUR"
                    ],
                    "promotional_price" => [
                        "amount" => $price,
                        "currency" => "EUR"
                    ]
                        ]]
                ];
            } else {
                $data = [
                    "product_prices" => [[
                    "ean" => $ean,
                    "sales_channel_id" => $salesChannelID,
                    "regular_price" => [
                        "amount" => $price,
                        "currency" => "EUR"
                    ]
                        ]]
                ];
            }
            $payload = json_encode($data);

            $ch = curl_init($postPriceUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer {$accessToken}",
                'Content-Type: application/json',
                'Accept: application/json'
            ]);

            $response = curl_exec($ch);

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode == 207 && $response) {
                fwrite($handleLog, "Price saved EAN: " . $ean . " price=$price" . "\r\n");
            } else {
                fwrite($handleLog, "Price ERROR EAN: " . $ean . "\r\n");
                fwrite($handleLog, json_encode($response));
            }
        }

        //*******************************

        $postStockUrl = $apiBaseUrl . '/merchants/' . $merchantID . '/stocks';
        $data = [
            "items" => [[
            "sales_channel_id" => $salesChannelID,
            "ean" => $ean,
            "quantity" => $qty
                ]]
        ];
        $payload = json_encode($data);

        $ch = curl_init($postStockUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$accessToken}",
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($httpCode == 207 && $response) {
            fwrite($handleLog, "Stock saved EAN: " . $ean . " stock=$qty" . "\r\n");
        } else {
            fwrite($handleLog, "Stock ERROR EAN: " . $ean . "\r\n");
            fwrite($handleLog, json_encode($response));
        }

        fwrite($handleLog, "-----------------------------------------------\r\n");
        //var_dump(++$counter);
    }

    fclose($handle);
    fclose($handleLog);
}

exit('....................');

