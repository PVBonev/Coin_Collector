<?php
function getMetalPrices() {
    $apiKey = "goldapi-d1e919mljwi818-io";
    $cacheFile = __DIR__ . '/../assets/json/metal_prices.json';
    $cacheTime = 86400; // 24 hours in sesonds (60*60*24)

    
    if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
        // if cache is still valid, return cached data
        return json_decode(file_get_contents($cacheFile), true);
    }

    $metalsToFetch = ['XAU', 'XAG']; 
    $newData = [];
    $currency = 'EUR'; 

    $myHeaders = array(
        'x-access-token: ' . $apiKey,
        'Content-Type: application/json'
    );

    foreach ($metalsToFetch as $symbol) {
        $curl = curl_init();
        $url = "https://www.goldapi.io/api/{$symbol}/{$currency}";
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $myHeaders
        ));

        $response = curl_exec($curl);
        $error = curl_error($curl);
        curl_close($curl);

        if (!$error) {
            $jsonObj = json_decode($response, true);
            // GoldAPI returns price per ounce
            // if there is none, we can calculate it ourselves (1 ounce = 31.1035 grams)
            if(isset($jsonObj['price'])) {
                $pricePerOunce = $jsonObj['price'];
                $pricePerGram = $pricePerOunce / 31.1035;
                
                $newData[$symbol] = [
                    'price_gram' => $pricePerGram,
                    'price_ounce' => $pricePerOunce,
                    'updated_at' => time()
                ];
            }
        } else {
            // in case of error, log it but don't stop the whole process
            error_log("GoldAPI Error for $symbol: $error");
        }
        
        // little delay to avoid hitting rate limits
        sleep(1); 
    }

    // if we got new data, save it to cache(file)
    if (!empty($newData)) {
        // make the folder if it doesn't exist
        $dir = dirname($cacheFile);
        if (!is_dir($dir)) mkdir($dir, 0777, true);
        
        file_put_contents($cacheFile, json_encode($newData));
        return $newData;
    }
    //if all else fails, return empty array or old data if exists
    return file_exists($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
}
?>