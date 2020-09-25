<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Repository\WalletRepository;
use App\Service\TransactionService;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    /**
     * @Route("/transfer", name="api_v1_transfer", methods={"POST"})
     *
     * @SWG\Tag(name="Transfer")
     * @SWG\Parameter(
     *     name="X-AUTH-TOKEN",
     *     in="header",
     *     description="Auth token",
     *     type="string",
     * ),
     * @SWG\Parameter(
     *     type="json",
     *     name="body",
     *     in="body",
     *     type="string",
     *     description="JSON payload",
     *     format="application/json",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="walletNumberFrom", type="string", description="Number of wallet to transfer from"),
     *         @SWG\Property(property="walletNumberTo", type="string", description="Number of wallet to transfer to"),
     *         @SWG\Property(property="amount", type="integer", description="Amount of transfer"),
     *     )
     * )
     * @SWG\Response(response=400, description="Incorrect request")
     * @SWG\Response(response=403, description="Unauthenticated response")
     * @SWG\Response(response=404, description="Entity not found")
     * @SWG\Response(
     *     response=200,
     *     description="Transfer successful",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(
     *             property="message",
     *             type="string"
     *          ),
     *     )
     * )
     */
    public function transfer(
        Request $request,
        WalletRepository $walletRepository,
        TransactionService $fundTransfer
    ): JsonResponse
    {
        $data = \json_decode($request->getContent(), true);
        $walletNumberFrom = $data['walletNumberFrom'] ?? '';
        $walletNumberTo = $data['walletNumberTo'] ?? '';
        $amount = (int)($data['amount'] ?? 0);

        if ($amount <= 0) {
            return $this->errorResponse('Incorrect amount', Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $walletFrom = $walletRepository->getByOwner($user, $walletNumberFrom);

        if ($walletFrom === null) {
            return $this->errorResponse('Wallet to transfer from not found', Response::HTTP_NOT_FOUND);
        }

        $walletTo = $walletRepository->findOneBy(['number' => $walletNumberTo]);

        if ($walletTo === null) {
            return $this->errorResponse('Destination wallet not found', Response::HTTP_NOT_FOUND);
        }

        try {
            $fundTransfer->transfer($walletFrom, $walletTo, $amount);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage());
        }

        return $this->successResponse(['message' => 'Transfer successful']);
    }
}
