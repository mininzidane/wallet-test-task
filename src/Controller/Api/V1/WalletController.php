<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Entity\Wallet;
use App\Service\WalletCreator;
use Nelmio\ApiDocBundle\Annotation\Model as SWGModel;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WalletController extends AbstractController
{
    /**
     * @var WalletCreator
     */
    private $walletCreator;
    /**
     * @var NormalizerInterface|SerializerInterface
     */
    private $serializer;

    public function __construct(WalletCreator $walletCreator, SerializerInterface $serializer)
    {
        $this->walletCreator = $walletCreator;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/create-wallet", name="api_create_wallet", methods={"POST"})
     *
     * @SWG\Tag(name="Wallet")
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
     *         @SWG\Property(property="title", type="string", description="Wallet title"),
     *     )
     * )
     * @SWG\Response(response=400, description="Incorrect request")
     * @SWG\Response(response=403, description="Unauthenticated response")
     * @SWG\Response(response=500, description="Internal error")
     * @SWG\Response(
     *     response=200,
     *     description="Created wallet",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(
     *             property="data",
     *             ref=@SWGModel(type=Wallet::class, groups={"wallet_details"})
     *          ),
     *     )
     * )
     */
    public function create(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = \json_decode($request->getContent(), true);
        $title = $data['title'] ?? '';
        if ($title === '') {
            return $this->errorResponse('Title must be set', Response::HTTP_BAD_REQUEST);
        }

        $wallet = $this->walletCreator->create($user, $title);

        if ($wallet === null) {
            return $this->errorResponse('Could not create wallet');
        }

        return $this->successResponse([
            'data' => $this->serializer->normalize($wallet, 'json', ['groups' => 'wallet_details'])
        ]);
    }
}
