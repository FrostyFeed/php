<?php


use App\Repository\LessonRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: LessonRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Lesson
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['lesson:read', 'grade:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['lesson:read', 'grade:read'])] 
    private ?Subject $subject = null;

    #[ORM\ManyToOne(inversedBy: 'lessons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['lesson:read', 'grade:read'])] 
    private ?Teacher $teacher = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    #[Assert\Type(\DateTimeInterface::class)]
    #[Groups(['lesson:read', 'grade:read'])]
    private ?\DateTimeInterface $lessonDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['lesson:read', 'grade:read'])]
    private ?string $topic = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['lesson:read'])]
    private ?string $homework = null;

    #[ORM\OneToMany(mappedBy: 'lesson', targetEntity: Grade::class, orphanRemoval: true)]
    #[Groups(['lesson:read_relations'])]
    private Collection $grades;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['lesson:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['lesson:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->grades = new ArrayCollection();
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
     public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('l');

        if (!empty($filters['id'])) {
            $qb->andWhere('l.id = :id')
               ->setParameter('id', $filters['id']);
        }

        if (!empty($filters['subject'])) { // Assuming subject ID is passed
            $qb->andWhere('l.subject = :subject_id')
               ->setParameter('subject_id', $filters['subject']);
        }

        if (!empty($filters['teacher'])) { // Assuming teacher ID is passed
            $qb->andWhere('l.teacher = :teacher_id')
               ->setParameter('teacher_id', $filters['teacher']);
        }

        if (!empty($filters['lessonDate'])) {
            $qb->andWhere('l.lessonDate = :lessonDate')
               ->setParameter('lessonDate', new \DateTime($filters['lessonDate']));
        }
        if (!empty($filters['lessonDate_from'])) {
            $qb->andWhere('l.lessonDate >= :lessonDate_from')
               ->setParameter('lessonDate_from', new \DateTime($filters['lessonDate_from']));
        }
        if (!empty($filters['lessonDate_to'])) {
            $qb->andWhere('l.lessonDate <= :lessonDate_to')
               ->setParameter('lessonDate_to', new \DateTime($filters['lessonDate_to']));
        }

        if (!empty($filters['topic'])) {
            $qb->andWhere('l.topic LIKE :topic')
               ->setParameter('topic', '%' . $filters['topic'] . '%');
        }

        if (isset($filters['homework'])) {
            if ($filters['homework'] === null || $filters['homework'] === 'null') {
                 $qb->andWhere('l.homework IS NULL');
            } else {
                 $qb->andWhere('l.homework LIKE :homework')
                    ->setParameter('homework', '%' . $filters['homework'] . '%');
            }
        }

        if (!empty($filters['createdAt_from'])) {
            $qb->andWhere('l.createdAt >= :createdAt_from')
               ->setParameter('createdAt_from', new \DateTimeImmutable($filters['createdAt_from']));
        }
        if (!empty($filters['createdAt_to'])) {
            $qb->andWhere('l.createdAt <= :createdAt_to')
               ->setParameter('createdAt_to', new \DateTimeImmutable($filters['createdAt_to']));
        }

        if (!empty($filters['updatedAt_from'])) {
            $qb->andWhere('l.updatedAt >= :updatedAt_from')
               ->setParameter('updatedAt_from', new \DateTimeImmutable($filters['updatedAt_from']));
        }
        if (!empty($filters['updatedAt_to'])) {
            $qb->andWhere('l.updatedAt <= :updatedAt_to')
               ->setParameter('updatedAt_to', new \DateTimeImmutable($filters['updatedAt_to']));
        }

        return $qb->orderBy('l.id', 'ASC')->getQuery()->getResult();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setSubject(?Subject $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function getTeacher(): ?Teacher
    {
        return $this->teacher;
    }

    public function setTeacher(?Teacher $teacher): static
    {
        $this->teacher = $teacher;
        return $this;
    }

    public function getLessonDate(): ?\DateTimeInterface
    {
        return $this->lessonDate;
    }

    public function setLessonDate(\DateTimeInterface $lessonDate): static
    {
        $this->lessonDate = $lessonDate;
        return $this;
    }

    public function getTopic(): ?string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): static
    {
        $this->topic = $topic;
        return $this;
    }

    public function getHomework(): ?string
    {
        return $this->homework;
    }

    public function setHomework(?string $homework): static
    {
        $this->homework = $homework;
        return $this;
    }

    /**
     * @return Collection<int, Grade>
     */
    public function getGrades(): Collection
    {
        return $this->grades;
    }

    public function addGrade(Grade $grade): static
    {
        if (!$this->grades->contains($grade)) {
            $this->grades->add($grade);
            $grade->setLesson($this);
        }
        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            if ($grade->getLesson() === $this) {
                $grade->setLesson(null);
            }
        }
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
}