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
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validation;

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
        $walletNumberFrom = $request->request->get('walletNumberFrom');
        $walletNumberTo = $request->request->get('walletNumberTo');
        $amount = $request->request->getInt('amount', 0);

        $validator = Validation::createValidator();
        $violations = $validator->validate($amount, [
            new Positive(['message' => 'Incorrect amount'])
        ]);

        if (0 !== \count($violations)) {
            $errors = [];
            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $errors['amount'][] = $violation->getMessage();
            }

            return $this->errorResponse(['error' => $errors], Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $walletFrom = $walletRepository->getByOwner($user, $walletNumberFrom);

        if ($walletFrom === null) {
            return $this->errorResponse(['error' => 'Wallet to transfer from not found'], Response::HTTP_NOT_FOUND);
        }

        $walletTo = $walletRepository->findOneBy(['number' => $walletNumberTo]);

        if ($walletTo === null) {
            return $this->errorResponse(['error' => 'Destination wallet not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $fundTransfer->transfer($walletFrom, $walletTo, $amount);
        } catch (\Throwable $e) {
            return $this->errorResponse(['error' => $e->getMessage()]);
        }

        return $this->successResponse(['message' => 'Transfer successful']);
    }
}
