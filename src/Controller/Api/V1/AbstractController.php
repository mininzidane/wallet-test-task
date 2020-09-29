<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    protected function successResponse(array $data = [], $responseCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse($data, $responseCode);
    }

    protected function errorResponse(array $data = [], $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }

    protected function flattenViolationErrors(string $field, ConstraintViolationListInterface $violationList): array
    {
        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violationList as $key => $violation) {
            $errors[$field][$key] = $violation->getMessage();
        }

        return $errors;
    }
}
