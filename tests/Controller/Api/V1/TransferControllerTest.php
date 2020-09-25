<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Request;

class TransferControllerTest extends WebTestCase
{
    public function testTransferSuccessful(): void
    {
        $this->client->request(Request::METHOD_POST, '/api/v1/transfer', [], [], $this->getAuthHeader(), \json_encode([
            'walletNumberFrom' => '1234567890',
            'walletNumberTo' => '2345678901',
            'amount' => '100',
        ]));

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        self::assertSame('Transfer successful', $responseBody['message']);
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testTransferError(string $walletNumberFrom, string $walletNumberTo, $amount, string $responseText): void
    {
        $this->client->request(Request::METHOD_POST, '/api/v1/transfer', [], [], $this->getAuthHeader(), \json_encode([
            'walletNumberFrom' => $walletNumberFrom,
            'walletNumberTo' => $walletNumberTo,
            'amount' => $amount,
        ]));

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        self::assertSame($responseBody['message'], $responseText);
    }

    public function paramsProvider() :array
    {
        return [
            ['1234567890', '2345678901', 99999999, 'Insufficient Funds'],
            ['1234567890', '2345678901', 0, 'Incorrect amount'],
            ['1234567890', '2345678901', -22, 'Incorrect amount'],
            ['1234567890', '2345678901', 'test', 'Incorrect amount'],
            ['99999', '2345678901', 100, 'Wallet to transfer from not found'],
            ['1234567890', '99999', 100, 'Destination wallet not found'],
        ];
    }
}
