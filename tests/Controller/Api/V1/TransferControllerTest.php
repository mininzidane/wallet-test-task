<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\V1;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

class TransferControllerTest extends WebTestCase
{
    private const HEADERS_AUTH = ['HTTP_X_AUTH_TOKEN' => '111122223333444455556666'];

    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testTransferSuccessful(): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v1/transfer', [
            'walletNumberFrom' => '1234567890',
            'walletNumberTo' => '2345678901',
            'amount' => '100',
        ], [], self::HEADERS_AUTH);

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        self::assertSame($responseBody['message'], 'Transfer successful');
    }

    /**
     * @dataProvider paramsProvider
     */
    public function testTransferError(string $walletNumberFrom, string $walletNumberTo, $amount, string $responseText): void
    {
        $this->client->request(Request::METHOD_GET, '/api/v1/transfer', [
            'walletNumberFrom' => $walletNumberFrom,
            'walletNumberTo' => $walletNumberTo,
            'amount' => $amount,
        ], [], self::HEADERS_AUTH);

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        self::assertSame($responseBody['message'], $responseText);
    }

    public function paramsProvider() :array
    {
        return [
            ['1234567890', '2345678901', 99999999, 'Insufficient funds'],
            ['1234567890', '2345678901', 0, 'Incorrect amount'],
            ['1234567890', '2345678901', -22, 'Incorrect amount'],
            ['1234567890', '2345678901', 'test', 'Incorrect amount'],
            ['99999', '2345678901', 100, 'Wallet to transfer from not found'],
            ['1234567890', '99999', 100, 'Destination wallet not found'],
        ];
    }
}
