<?php

use App\Entity\Lesson;
use App\Repository\LessonRepository;
use App\Repository\SubjectRepository; 
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted; 

#[Route('/api/lessons')]
class LessonController extends AbstractController
{
    private EntityManagerInterface $em;
    private LessonRepository $lessonRepository;
    private SubjectRepository $subjectRepository;
    private TeacherRepository $teacherRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em, LessonRepository $lr, SubjectRepository $sr, 
        TeacherRepository $tr, SerializerInterface $s, ValidatorInterface $v
    ) {
        $this->em = $em; $this->lessonRepository = $lr; $this->subjectRepository = $sr;
        $this->teacherRepository = $tr; $this->serializer = $s; $this->validator = $v;
    }


    #[Route('', name: 'api_lesson_index', methods: ['GET'])]
    #[IsGranted(User::ROLE_MANAGER)]
    public function index(Request $request): JsonResponse
    {
        $page = $request->query->getInt('page', 1);
        $itemsPerPage = $request->query->getInt('itemsPerPage', 10);
        if ($itemsPerPage <= 0) $itemsPerPage = 10;
        $itemsPerPage = min($itemsPerPage, 100);

        $filters = $request->query->all();
        unset($filters['page'], $filters['itemsPerPage'], $filters['sortBy'], $filters['sortOrder']);

        $sortBy = $request->query->get('sortBy', 'lessonDate');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        $paginator = $this->lessonRepository->findByFiltersWithPagination(
            $filters,
            $page,
            $itemsPerPage,
            $sortBy,
            $sortOrder
        );

        $lessons = [];
        foreach ($paginator as $lesson) {
            $lessons[] = $lesson;
        }

        return $this->json([
            'items' => $lessons,
            'totalItems' => count($paginator),
            'currentPage' => $page,
            'itemsPerPage' => $itemsPerPage,
            'totalPages' => ceil(count($paginator) / $itemsPerPage),
        ], Response::HTTP_OK, [], ['groups' => 'lesson:read']);
    }
    #[Route('', name: 'api_lesson_store', methods: ['POST'])]
    #[IsGranted(User::ROLE_MANAGER)] 
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }
        
        $lesson = new Lesson();
        
        if (!isset($data['subject_id']) || !isset($data['teacher_id']) || !isset($data['lesson_date']) || !isset($data['topic'])) {
             return $this->json(['error' => 'Missing required fields: subject_id, teacher_id, lesson_date, topic'], Response::HTTP_BAD_REQUEST);
        }

        $subject = $this->subjectRepository->find($data['subject_id']);
        if (!$subject) return $this->json(['error' => 'Subject not found'], Response::HTTP_NOT_FOUND);
        
        $teacher = $this->teacherRepository->find($data['teacher_id']);
        if (!$teacher) return $this->json(['error' => 'Teacher not found'], Response::HTTP_NOT_FOUND);

        $lesson->setSubject($subject);
        $lesson->setTeacher($teacher);
        try {
            $lesson->setLessonDate(new \DateTime($data['lesson_date']));
        } catch (\Exception $e) {
             return $this->json(['error' => 'Invalid lesson_date format'], Response::HTTP_BAD_REQUEST);
        }
        $lesson->setTopic($data['topic']);
        $lesson->setHomework($data['homework'] ?? null);

        $errors = $this->validator->validate($lesson);
        if (count($errors) > 0) return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->persist($lesson);
        $this->em->flush();
        return $this->json($lesson, Response::HTTP_CREATED, [], ['groups' => 'lesson:read']);
    }

    #[Route('/{id<\d+>}', name: 'api_lesson_show', methods: ['GET'])]
    #[IsGranted(User::ROLE_MANAGER)] 
    public function show(Lesson $lesson): JsonResponse 
    {
        return $this->json($lesson, Response::HTTP_OK, [], ['groups' => 'lesson:read']);
    }

    #[Route('/{id<\d+>}', name: 'api_lesson_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted(User::ROLE_MANAGER)] 
    public function update(Request $request, Lesson $lesson): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['subject_id'])) {
            $subject = $this->subjectRepository->find($data['subject_id']);
            if (!$subject) return $this->json(['error' => 'Subject not found for update'], Response::HTTP_NOT_FOUND);
            $lesson->setSubject($subject);
        }
        if (isset($data['lesson_date'])) {
             try { $lesson->setLessonDate(new \DateTime($data['lesson_date'])); }
             catch (\Exception $e) { return $this->json(['error' => 'Invalid lesson_date format'], Response::HTTP_BAD_REQUEST); }
        }
        if (isset($data['topic'])) $lesson->setTopic($data['topic']);
        if (array_key_exists('homework', $data)) $lesson->setHomework($data['homework']);


        $errors = $this->validator->validate($lesson);
        if (count($errors) > 0) return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->flush();
        return $this->json($lesson, Response::HTTP_OK, [], ['groups' => 'lesson:read']);
    }
    
    #[Route('/{id<\d+>}', name: 'api_lesson_delete', methods: ['DELETE'])]
    #[IsGranted(User::ROLE_MANAGER)] 
    public function delete(Lesson $lesson): JsonResponse
    {
        $this->em->remove($lesson);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}