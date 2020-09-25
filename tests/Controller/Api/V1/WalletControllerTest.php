<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Request;

class WalletControllerTest extends WebTestCase
{
    public function testTransferSuccessful(): void
    {
        $title = 'some text';
        $this->client->request(Request::METHOD_POST, '/api/v1/create-wallet', [], [], $this->getAuthHeader(), \json_encode([
            'title' => $title,
        ]));

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        $data = $responseBody['data'];
        self::assertArrayHasKey('id', $data);
        self::assertArrayHasKey('title', $data);
        self::assertArrayHasKey('number', $data);
        self::assertSame($title, $data['title']);
    }
}
