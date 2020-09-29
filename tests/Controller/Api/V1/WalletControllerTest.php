<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Request;

class WalletControllerTest extends WebTestCase
{
    public function testCreateSuccessful(): void
    {
        $title = 'some text';
        $this->client->request(Request::METHOD_POST, '/api/v1/wallet', [], [], $this->getAuthHeader(), \json_encode([
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

    public function testCreateError(): void
    {
        $title = '';
        $this->client->request(Request::METHOD_POST, '/api/v1/wallet', [], [], $this->getAuthHeader(), \json_encode([
            'title' => $title,
        ]));

        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);
        $data = $responseBody['error'];
        self::assertSame([
            'title' => [
                'This value is too short. It should have 5 characters or more.',
                'This value should not be blank.'
            ]
        ], $data);
    }
}
