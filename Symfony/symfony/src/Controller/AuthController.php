<?php

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends AbstractController
{
    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['email']) || !isset($data['password']) || !isset($data['role'])) {
            return $this->json(['message' => 'Missing email, password or role'], Response::HTTP_BAD_REQUEST);
        }
        
        $allowedRoles = [User::ROLE_CLIENT, User::ROLE_MANAGER, User::ROLE_ADMIN];
        if (!in_array(strtoupper($data['role']), array_map('strtoupper', $allowedRoles))) {
            return $this->json(['message' => 'Invalid role specified. Allowed roles: ROLE_CLIENT, ROLE_MANAGER, ROLE_ADMIN.'], Response::HTTP_BAD_REQUEST);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setPassword($passwordHasher->hashPassword($user, $data['password']));
        $user->setRoles([strtoupper($data['role'])]); 

        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = []; foreach ($errors as $error) { $errorMessages[] = $error->getMessage(); }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User registered successfully', 'userId' => $user->getId()], Response::HTTP_CREATED);
    }
}