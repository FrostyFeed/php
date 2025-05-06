<?php

use App\Entity\Grade;
use App\Repository\GradeRepository;
use App\Repository\StudentRepository;
use App\Repository\LessonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;


#[Route('/api/grades')]
class GradeController extends AbstractController
{
    private EntityManagerInterface $em;
    private GradeRepository $gradeRepository;
    private StudentRepository $studentRepository;
    private LessonRepository $lessonRepository;
    private SerializerInterface $serializer;
    private ValidatorInterface $validator;

    public function __construct(
        EntityManagerInterface $em, GradeRepository $gr, StudentRepository $sr, 
        LessonRepository $lr, SerializerInterface $s, ValidatorInterface $v
    ) {
        $this->em = $em; $this->gradeRepository = $gr; $this->studentRepository = $sr;
        $this->lessonRepository = $lr; $this->serializer = $s; $this->validator = $v;
    }

    #[Route('', name: 'api_grade_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->gradeRepository->findAll(), Response::HTTP_OK, [], ['groups' => 'grade:read']);
    }

    #[Route('', name: 'api_grade_store', methods: ['POST'])]
    public function store(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
         if (json_last_error() !== JSON_ERROR_NONE) {
             return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['student_id']) || !isset($data['lesson_id']) || !isset($data['grade_value']) || !isset($data['date_given'])) {
             return $this->json(['error' => 'Missing required fields: student_id, lesson_id, grade_value, date_given'], Response::HTTP_BAD_REQUEST);
        }
        
        $grade = new Grade();
        
        $student = $this->studentRepository->find($data['student_id']);
        if (!$student) return $this->json(['error' => 'Student not found'], Response::HTTP_NOT_FOUND);
        
        $lesson = $this->lessonRepository->find($data['lesson_id']);
        if (!$lesson) return $this->json(['error' => 'Lesson not found'], Response::HTTP_NOT_FOUND);

        $grade->setStudent($student);
        $grade->setLesson($lesson);
        $grade->setGradeValue($data['grade_value']);
         try {
            $grade->setDateGiven(new \DateTime($data['date_given']));
        } catch (\Exception $e) {
             return $this->json(['error' => 'Invalid date_given format'], Response::HTTP_BAD_REQUEST);
        }
        $grade->setComment($data['comment'] ?? null);

        $errors = $this->validator->validate($grade);
        if (count($errors) > 0) return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);

        try {
            $this->em->persist($grade);
            $this->em->flush();
        } catch (UniqueConstraintViolationException $e) {
            return $this->json(['error' => 'A grade for this student and lesson already exists.'], Response::HTTP_CONFLICT);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Could not save grade: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
        
        return $this->json($grade, Response::HTTP_CREATED, [], ['groups' => 'grade:read']);
    }
    
    #[Route('/{id<\d+>}', name: 'api_grade_show', methods: ['GET'])]
    public function show(Grade $grade): JsonResponse
    {
        return $this->json($grade, Response::HTTP_OK, [], ['groups' => 'grade:read']);
    }

    #[Route('/{id<\d+>}', name: 'api_grade_update', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, Grade $grade): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
             return $this->json(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }
        if (isset($data['grade_value'])) $grade->setGradeValue($data['grade_value']);
        if (array_key_exists('comment', $data)) $grade->setComment($data['comment']);
        if (isset($data['date_given'])) {
            try { $grade->setDateGiven(new \DateTime($data['date_given'])); }
            catch (\Exception $e) { return $this->json(['error' => 'Invalid date_given format'], Response::HTTP_BAD_REQUEST); }
        }

        $errors = $this->validator->validate($grade);
        if (count($errors) > 0) return $this->json($errors, Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->em->flush();
        return $this->json($grade, Response::HTTP_OK, [], ['groups' => 'grade:read']);
    }

    #[Route('/{id<\d+>}', name: 'api_grade_delete', methods: ['DELETE'])]
    public function delete(Grade $grade): JsonResponse
    {
        $this->em->remove($grade);
        $this->em->flush();
        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}