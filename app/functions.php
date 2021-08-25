<?php

use Noodlehaus\Config;

function logger($message)
{
    $log_dirname = getConfig()->get('logPath');
    if (! file_exists($log_dirname)) {
        mkdir($log_dirname, 0777, true);
    }
    $log_file_data = $log_dirname . '/log_' . date('d-m-Y') . '.log';
    file_put_contents($log_file_data, date("d-m-Y H:i:s") . ' - ', FILE_APPEND);
    file_put_contents($log_file_data, $message . "\n", FILE_APPEND);
}

function makeGuid(): string
{
    $guid = bin2hex(openssl_random_pseudo_bytes(16));

    return vsprintf('%s%s-%s-%.4s-%.4s-%s%s%s', str_split($guid, 4));
}

function makeJson(array $data): string
{
    return json_encode($data, JSON_FORCE_OBJECT);
}

function getConfig(): Config
{
    return new Config('../config/config.php');
}

function makeInfoXml(array $id_articles): string
{
    $xml = '<?xml version="1.0" encoding="utf-16"?><card_code>';

    foreach ($id_articles as $art_pos) {
        $xml = $xml . $art_pos . ", ";
    }

    return $xml . "</card_code>";
}

function request()
{
  return json_decode(file_get_contents("php://input"), true);
}