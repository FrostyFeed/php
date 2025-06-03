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
use Symfony\Component\Security\Http\Attribute\IsGranted; 

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
    #[IsGranted(User::ROLE_CLIENT)]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);
        if ($itemsPerPage <= 0) $itemsPerPage = 10;
        $itemsPerPage = min($itemsPerPage, 100);

        $filters = $request->query->all();
        unset($filters['page'], $filters['itemsPerPage'], $filters['sortBy'], $filters['sortOrder']);

        $sortBy = $request->query->get('sortBy', 'id');
        $sortOrder = $request->query->get('sortOrder', 'ASC');

        $paginator = $this->teacherRepository->findByFiltersWithPagination(
            $filters,
            $page,
            $itemsPerPage,
            $sortBy,
            $sortOrder
        );

        $teachers = [];
        foreach ($paginator as $teacher) {
            $teachers[] = $teacher;
        }

        return $this->json([
            'items' => $teachers,
            'totalItems' => count($paginator),
            'currentPage' => $page,
            'itemsPerPage' => $itemsPerPage,
            'totalPages' => ceil(count($paginator) / $itemsPerPage),
        ], Response::HTTP_OK, [], ['groups' => 'teacher:read']); 
    }
    #[Route('', name: 'api_teacher_store', methods: ['POST'])]
    #[IsGranted(User::ROLE_CLIENT)]
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
    #[IsGranted(User::ROLE_CLIENT)]
    public function show(Teacher $teacher): JsonResponse 
    {
        return $this->json($teacher);
    }

    #[Route('/{id<\d+>}', name: 'api_teacher_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted(User::ROLE_CLIENT)]
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
    #[IsGranted(User::ROLE_CLIENT)]
    public function delete(Teacher $teacher): JsonResponse
    {
        $this->em->remove($teacher);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}