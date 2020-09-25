<?php

declare(strict_types=1);

namespace App\Tests\Controller\Api\V1;

use Symfony\Component\HttpFoundation\Request;

class WebTestCase extends \Symfony\Bundle\FrameworkBundle\Test\WebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\KernelBrowser
     */
    protected $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function getAuthHeader(): array
    {
        $this->client->request(Request::METHOD_POST, '/api/v1/login', [], [], [], \json_encode([
            'username' => 'root',
            'password' => '123456',
        ]));
        $response = $this->client->getResponse();
        $responseBody = \json_decode($response->getContent(), true);

        if ($responseBody === null || !\array_key_exists('token', $responseBody)) {
            throw new \Exception('Response does not contain token');
        }

        return ['HTTP_X_AUTH_TOKEN' => $responseBody['token']];
    }
}
