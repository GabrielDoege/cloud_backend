<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class EquipamentoApiClient
{
    public static function acionarBomba(string $ip, int $porta, int $tempo): array
    {
        $url = "http://{$ip}:{$porta}/bomba?tempo={$tempo}";
        $client = new Client([
            'timeout' => 10,
        ]);
        try {
            $response = $client->get($url);
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }

    public static function pararBomba(string $ip, int $porta): array
    {
        $url = "http://{$ip}:{$porta}/bomba/parar";
        $client = new Client([
            'timeout' => 10,
        ]);
        try {
            $response = $client->get($url);
            return [
                'success' => true,
                'status' => $response->getStatusCode(),
                'body' => $response->getBody()->getContents(),
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ];
        }
    }
}
