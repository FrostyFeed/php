<?php
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
class DemoController extends AbstractController
{
    #[Route('/api/hello', name: 'api_hello_world', methods: ['GET'])] 
    public function helloWorld(): JsonResponse
    {
        $data = [
            'message' => 'Привіт, Світ!',
            'description' => 'Це простий JSON-відповідь від Symfony контролера.',
            'timestamp' => new \DateTime(),
        ];

        return $this->json($data);
    }
}