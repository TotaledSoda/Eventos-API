<?php

namespace App\Services;

use GuzzleHttp\Client;
use RuntimeException;

class PayPalService
{
    protected Client $client;
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;

    public function __construct()
    {
        $this->clientId     = config('paypal.client_id');
        $this->clientSecret = config('paypal.client_secret');
        $mode               = config('paypal.mode', 'sandbox');

        $this->baseUrl = $mode === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'http_errors' => false,
        ]);
    }

    /**
     * Obtener access_token de PayPal.
     */
    public function getAccessToken(): string
    {
        $response = $this->client->post('/v1/oauth2/token', [
            'auth' => [$this->clientId, $this->clientSecret],
            'form_params' => [
                'grant_type' => 'client_credentials',
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (empty($data['access_token'])) {
            throw new RuntimeException('No se pudo obtener access_token de PayPal.');
        }

        return $data['access_token'];
    }

    /**
     * Crear orden en PayPal.
     */
    public function createOrder(float $amount, string $currency, string $returnUrl, string $cancelUrl): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->client->post('/v2/checkout/orders', [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ],
            'json' => [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => strtoupper($currency),
                            'value'         => number_format($amount, 2, '.', ''),
                        ],
                    ],
                ],
                'application_context' => [
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ],
            ],
        ]);

        $data = json_decode((string) $response->getBody(), true);

        if (empty($data['id'])) {
            throw new RuntimeException('No se pudo crear la orden en PayPal.');
        }

        return $data;
    }

    /**
     * Capturar una orden una vez aprobada.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $accessToken = $this->getAccessToken();

        $response = $this->client->post("/v2/checkout/orders/{$paypalOrderId}/capture", [
            'headers' => [
                'Authorization' => "Bearer {$accessToken}",
                'Content-Type'  => 'application/json',
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }
}
