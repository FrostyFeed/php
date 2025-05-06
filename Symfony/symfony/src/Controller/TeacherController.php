<?php

use App\Entity\Teacher;
use App\Repository\TeacherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/teachers')]
class TeacherController extends AbstractController
{
    private EntityManagerInterface $em;
    private TeacherRepository $teacherRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em,
        TeacherRepository $teacherRepository,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ) {
        $this->em = $em;
        $this->teacherRepository = $teacherRepository;
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    #[Route('', name: 'api_teacher_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->teacherRepository->findAll());
    }

    #[Route('', name: 'api_teacher_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        try {
            /** @var Teacher $teacher */
            $teacher = $this->serializer->deserialize($request->getContent(), Teacher::class, 'json');

            $errors = $this->validator->validate($teacher);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            $this->em->persist($teacher);
            $this->em->flush();
            return $this->json($teacher, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id<\d+>}', name: 'api_teacher_show', methods: ['GET'])]
    public function show(Teacher $teacher): JsonResponse 
    {
        return $this->json($teacher);
    }

    #[Route('/{id<\d+>}', name: 'api_teacher_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Teacher $teacher): JsonResponse
    {
        try {
            $this->serializer->deserialize($request->getContent(), Teacher::class, 'json', ['object_to_populate' => $teacher]);

            $errors = $this->validator->validate($teacher);
            if (count($errors) > 0) {
                return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            
            $this->em->flush();
            return $this->json($teacher);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Invalid data: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id<\d+>}', name: 'api_teacher_delete', methods: ['DELETE'])]
    public function delete(Teacher $teacher): JsonResponse
    {
        $this->em->remove($teacher);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}