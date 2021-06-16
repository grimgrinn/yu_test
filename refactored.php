<?php
error_reporting(0);
const EXHACNGERATESAPIURI = 'http://api.exchangeratesapi.io/v1/latest?access_key=08914569ac738cfaad9d780d06135caf';
const BINLISTURI = 'https://lookup.binlist.net/';



$transactionRows = file_get_contents($argv[1]);

foreach (explode("\n", $transactionRows) as $row) {
    if (empty($row)) break;

    [$bin, $amount, $currency] = array_values(json_decode($row, true));

    $ratio = getBinRatio(BINLISTURI,$bin);
    $rate = getFixedAmount(EXHACNGERATESAPIURI, $amount, $currency);

    echo $rate * $ratio;
    print "\n";
}

function getBinRatio($uri, $bin) {
    $europa =  ['AT','BE','BG','CY','CZ','DE','DK','EE','ES','FI','FR','GR','HR','HU','IE','IT','LT','LU','LV','MT','NL','PO','PT','RO','SE','SI','SK'];

    try {
        $binResults = file_get_contents($uri . $bin);
    } catch (Exception $error) {
        throw new Error($error,'Error occured while tried to get bin ratio');
    }

    $country = json_decode($binResults, true)['country']['alpha2'];
    $isEu = in_array($country, $europa);
    $ratio = $isEu ? 0.01 : 0.02;
    return $ratio;
}

function getFixedAmount($apiUri, $amount, $currency) {
    try {
        $rate = @json_decode(file_get_contents($apiUri), true)['rates'][$currency];
    } catch (Error $error) {
        throw new Error($error,'Error occured while tried to get fixed amount');
    }
    if ($currency == 'EUR' or $rate == 0) {
        $fixedAmount = $amount;
    } else {
        $fixedAmount = $amount / $rate;
    }
    return $fixedAmount;
}