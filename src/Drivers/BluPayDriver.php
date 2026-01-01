<?php

namespace LidLike\BluPay\Drivers;

use GuzzleHttp\Client;
use LidLike\BluPay\DTO\TokenResponse;
use LidLike\BluPay\DTO\VerifyResponse;
use LidLike\BluPay\Exceptions\BluPayException;

class BluPayDriver
{
    private Client $http;

    public function __construct(private array $config)
    {
        $this->http = new Client([
            'timeout' => $this->config['timeout'] ?? 30,
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ]);
    }

    /**
     * Request token (action=token)
     * Token request is JSON POST and response is JSON.
     */
    public function requestToken(
        int $amount,
        string $resNum,
        string $redirectUrl,
        ?string $cellNumber = null,
        ?int $tokenExpiryInMin = null,
        ?string $hashedCardNumber = null,
        ?int $wage = null
    ): TokenResponse {
        $terminalId = $this->config['terminal_id'] ?? null;
        if (!$terminalId) {
            throw new BluPayException('BLUPAY_TERMINAL_ID is not set.');
        }

        $payload = array_filter([
            'action' => 'token',
            'TerminalId' => (string) $terminalId,
            'Amount' => $amount,
            'ResNum' => $resNum,
            'RedirectUrl' => $redirectUrl,
            'CellNumber' => $cellNumber,
            'TokenExpiryInMin' => $tokenExpiryInMin,
            'HashedCardNumber' => $hashedCardNumber,
            'Wage' => $wage,
        ], fn ($v) => $v !== null && $v !== '');

        $resp = $this->http->post($this->config['endpoints']['token'], [
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        // pg-neo behavior: the gateway URL may be returned in response headers
        $ipgUrlHeader = $resp->getHeaderLine('X-IPG-Url');

        $data = json_decode((string) $resp->getBody(), true) ?: [];
        $status = (int) ($data['status'] ?? -1);

        if ($status !== 1) {
            return new TokenResponse(
                success: false,
                token: null,
                errorCode: (string) ($data['errorCode'] ?? ''),
                errorDesc: (string) ($data['errorDesc'] ?? ''),
                ipgUrlFromHeader: $ipgUrlHeader ?: null
            );
        }

        return new TokenResponse(
            success: true,
            token: (string) ($data['token'] ?? ''),
            errorCode: null,
            errorDesc: null,
            ipgUrlFromHeader: $ipgUrlHeader ?: null
        );
    }

    /**
     * Build redirect URL (GET method) using SendToken endpoint.
     */
    public function redirectUrl(string $token): string
    {
        $base = rtrim($this->config['endpoints']['send'], '/');
        return $base . '?token=' . urlencode($token);
    }

    /**
     * VerifyTransaction (server-to-server)
     */
    public function verify(string $refNum): VerifyResponse
    {
        $terminalId = $this->config['terminal_id'] ?? null;
        if (!$terminalId) {
            throw new BluPayException('BLUPAY_TERMINAL_ID is not set.');
        }

        $payload = [
            'RefNum' => $refNum,
            'TerminalNumber' => (int) $terminalId,
        ];

        $resp = $this->http->post($this->config['endpoints']['verify'], [
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $data = json_decode((string) $resp->getBody(), true) ?: [];

        return new VerifyResponse(
            success: (bool) ($data['Success'] ?? false),
            resultCode: isset($data['ResultCode']) ? (int) $data['ResultCode'] : null,
            resultDescription: isset($data['ResultDescription']) ? (string) $data['ResultDescription'] : null,
            transactionDetail: (array) ($data['TransactionDetail'] ?? [])
        );
    }

    /**
     * ReverseTransaction (server-to-server)
     */
    public function reverse(string $refNum): VerifyResponse
    {
        $terminalId = $this->config['terminal_id'] ?? null;
        if (!$terminalId) {
            throw new BluPayException('BLUPAY_TERMINAL_ID is not set.');
        }

        $payload = [
            'RefNum' => $refNum,
            'TerminalNumber' => (int) $terminalId,
        ];

        $resp = $this->http->post($this->config['endpoints']['reverse'], [
            'body' => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
        ]);

        $data = json_decode((string) $resp->getBody(), true) ?: [];

        return new VerifyResponse(
            success: (bool) ($data['Success'] ?? false),
            resultCode: isset($data['ResultCode']) ? (int) $data['ResultCode'] : null,
            resultDescription: isset($data['ResultDescription']) ? (string) $data['ResultDescription'] : null,
            transactionDetail: (array) ($data['TransactionDetail'] ?? [])
        );
    }
}
