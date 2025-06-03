<?php


use App\Repository\GradeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GradeRepository::class)]
#[ORM\Table(name: 'grades')]
#[ORM\UniqueConstraint(name: 'student_lesson_unique', columns: ['student_id', 'lesson_id'])] 
#[ORM\HasLifecycleCallbacks]
class Grade
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['grade:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['grade:read'])]
    private ?Student $student = null;

    #[ORM\ManyToOne(inversedBy: 'grades')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['grade:read'])]
    private ?Lesson $lesson = null;

    #[ORM\Column(length: 50)] 
    #[Assert\NotBlank]
    #[Groups(['grade:read', 'student:read_relations', 'lesson:read_relations'])] 
    private ?string $gradeValue = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['grade:read'])]
    private ?string $comment = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Groups(['grade:read'])]
    private ?\DateTimeInterface $dateGiven = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['grade:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['grade:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAtValue(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudent(): ?Student
    {
        return $this->student;
    }

    public function setStudent(?Student $student): static
    {
        $this->student = $student;
        return $this;
    }

    public function getLesson(): ?Lesson
    {
        return $this->lesson;
    }

    public function setLesson(?Lesson $lesson): static
    {
        $this->lesson = $lesson;
        return $this;
    }

    public function getGradeValue(): ?string
    {
        return $this->gradeValue;
    }

    public function setGradeValue(string $gradeValue): static
    {
        $this->gradeValue = $gradeValue;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getDateGiven(): ?\DateTimeInterface
    {
        return $this->dateGiven;
    }

    public function setDateGiven(\DateTimeInterface $dateGiven): static
    {
        $this->dateGiven = $dateGiven;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
     public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('g');

        if (!empty($filters['id'])) {
            $qb->andWhere('g.id = :id')
               ->setParameter('id', $filters['id']);
        }

        if (!empty($filters['student'])) { // Assuming student ID is passed
            $qb->andWhere('g.student = :student_id')
               ->setParameter('student_id', $filters['student']);
        }

        if (!empty($filters['lesson'])) { // Assuming lesson ID is passed
            $qb->andWhere('g.lesson = :lesson_id')
               ->setParameter('lesson_id', $filters['lesson']);
        }

        if (!empty($filters['gradeValue'])) {
            $qb->andWhere('g.gradeValue LIKE :gradeValue')
               ->setParameter('gradeValue', '%' . $filters['gradeValue'] . '%');
        }

        if (isset($filters['comment'])) { // Allow filtering for empty string or null
            if ($filters['comment'] === null || $filters['comment'] === 'null') {
                 $qb->andWhere('g.comment IS NULL');
            } else {
                 $qb->andWhere('g.comment LIKE :comment')
                    ->setParameter('comment', '%' . $filters['comment'] . '%');
            }
        }

        if (!empty($filters['dateGiven'])) {
            $qb->andWhere('g.dateGiven = :dateGiven')
               ->setParameter('dateGiven', new \DateTime($filters['dateGiven']));
        }
        if (!empty($filters['dateGiven_from'])) {
            $qb->andWhere('g.dateGiven >= :dateGiven_from')
               ->setParameter('dateGiven_from', new \DateTime($filters['dateGiven_from']));
        }
        if (!empty($filters['dateGiven_to'])) {
            $qb->andWhere('g.dateGiven <= :dateGiven_to')
               ->setParameter('dateGiven_to', new \DateTime($filters['dateGiven_to']));
        }

        if (!empty($filters['createdAt_from'])) {
            $qb->andWhere('g.createdAt >= :createdAt_from')
               ->setParameter('createdAt_from', new \DateTimeImmutable($filters['createdAt_from']));
        }
        if (!empty($filters['createdAt_to'])) {
            $qb->andWhere('g.createdAt <= :createdAt_to')
               ->setParameter('createdAt_to', new \DateTimeImmutable($filters['createdAt_to']));
        }

        if (!empty($filters['updatedAt_from'])) {
            $qb->andWhere('g.updatedAt >= :updatedAt_from')
               ->setParameter('updatedAt_from', new \DateTimeImmutable($filters['updatedAt_from']));
        }
        if (!empty($filters['updatedAt_to'])) {
            $qb->andWhere('g.updatedAt <= :updatedAt_to')
               ->setParameter('updatedAt_to', new \DateTimeImmutable($filters['updatedAt_to']));
        }

        return $qb->orderBy('g.id', 'ASC')->getQuery()->getResult();
    }
}