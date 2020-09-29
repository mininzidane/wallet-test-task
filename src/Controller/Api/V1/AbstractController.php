<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function successResponse(array $data = [], $responseCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $responseCode);
    }

    public function errorResponse(array $data = [], $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}
