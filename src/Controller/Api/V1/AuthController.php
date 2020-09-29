<?php

declare(strict_types=1);

namespace App\Controller\Api\V1;

use App\Repository\UserRepository;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("/login", name="api_get_token", methods={"POST"})
     *
     * @SWG\Tag(name="Auth")
     * @SWG\Parameter(
     *     type="json",
     *     name="body",
     *     in="body",
     *     type="string",
     *     description="JSON payload",
     *     format="application/json",
     *     @SWG\Schema(
     *         type="object",
     *         @SWG\Property(property="username", type="string", description="Username"),
     *         @SWG\Property(property="password", type="string", description="Password"),
     *     )
     * )
     * @SWG\Response(response=400, description="Incorrect request")
     * @SWG\Response(response=404, description="User not found")
     * @SWG\Response(
     *     response=200,
     *     description="Auth success",
     *     @SWG\Schema(
     *         type="object",
     *          @SWG\Property(
     *             property="token",
     *             type="string"
     *          ),
     *     )
     * )
     */
    public function login(
        Request $request,
        UserPasswordEncoderInterface $userPasswordEncoder,
        UserRepository $userRepository
    ): JsonResponse
    {
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $user = $userRepository->findOneBy(['username' => $username]);

        if ($user === null) {
            return $this->errorResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        if (!$userPasswordEncoder->isPasswordValid($user, $password)) {
            return $this->errorResponse(['error' => 'Password incorrect'], Response::HTTP_BAD_REQUEST);
        }

        return $this->successResponse(['token' => $user->getApiKey()]);
    }
}
