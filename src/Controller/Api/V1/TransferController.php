<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\WalletRepository;
use App\Service\FundTransfer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TransferController extends AbstractController
{
    /**
     * @Route(
     *     "/transfer",
     *     name="api_v1_transfer",
     *     methods={"GET"},
     *     requirements={"walletNumberFrom"="\d+","walletNumberTo"="\d+","amount"="\d+"}
     * )
     */
    public function transfer(
        Request $request,
        UserRepository $userRepository,
        WalletRepository $walletRepository,
        FundTransfer $fundTransfer
    ): JsonResponse
    {
        $walletNumberFrom = $request->get('walletNumberFrom');
        $walletNumberTo = $request->get('walletNumberTo');
        $amount = (float) $request->get('amount');
        if ($amount <= 0) {
            return $this->errorResponse('Incorrect amount', Response::HTTP_BAD_REQUEST);
        }

        /** @var User $user */
        $user = $this->getUser();

        $walletFrom = $userRepository->getWalletByOwner($user, $walletNumberFrom);
        if ($walletFrom === null) {
            return $this->errorResponse('Wallet to transfer from not found', Response::HTTP_NOT_FOUND);
        }

        $walletTo = $walletRepository->findOneBy(['number' => $walletNumberTo]);
        if ($walletTo === null) {
            return $this->errorResponse('Destination wallet not found', Response::HTTP_NOT_FOUND);
        }

        $code = $fundTransfer->transfer($walletFrom, $walletTo, $amount);
        if ($code !== FundTransfer::CODE_SUCCESS) {
            return $this->errorResponse(FundTransfer::CODE_LABEL_MAP[$code]);
        }

        return $this->successResponse('Transfer successful');
    }
}
