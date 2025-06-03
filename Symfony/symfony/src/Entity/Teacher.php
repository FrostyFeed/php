<?php


use App\Repository\TeacherRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TeacherRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Teacher
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['teacher:read', 'lesson:read', 'grade:read'])] 
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['teacher:read', 'lesson:read', 'grade:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['teacher:read', 'lesson:read', 'grade:read'])]
    private ?string $lastName = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Email]
    #[Groups(['teacher:read'])]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['teacher:read'])]
    private ?string $specialization = null;

    #[ORM\OneToMany(mappedBy: 'teacher', targetEntity: Lesson::class, orphanRemoval: true)]
    #[Groups(['teacher:read_relations'])] 
    private Collection $lessons;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['teacher:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['teacher:read'])]
    private ?\DateTimeImmutable $updatedAt = null;

    public function __construct()
    {
        $this->lessons = new ArrayCollection();
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

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getSpecialization(): ?string
    {
        return $this->specialization;
    }

    public function setSpecialization(?string $specialization): static
    {
        $this->specialization = $specialization;
        return $this;
    }

    /**
     * @return Collection<int, Lesson>
     */
    public function getLessons(): Collection
    {
        return $this->lessons;
    }

    public function addLesson(Lesson $lesson): static
    {
        if (!$this->lessons->contains($lesson)) {
            $this->lessons->add($lesson);
            $lesson->setTeacher($this);
        }
        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getTeacher() === $this) {
                $lesson->setTeacher(null);
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
       public function findByFilters(array $filters): array
    {
        $qb = $this->createQueryBuilder('t');

        if (!empty($filters['id'])) {
            $qb->andWhere('t.id = :id')
               ->setParameter('id', $filters['id']);
        }

        if (!empty($filters['firstName'])) {
            $qb->andWhere('t.firstName LIKE :firstName')
               ->setParameter('firstName', '%' . $filters['firstName'] . '%');
        }

        if (!empty($filters['lastName'])) {
            $qb->andWhere('t.lastName LIKE :lastName')
               ->setParameter('lastName', '%' . $filters['lastName'] . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('t.email LIKE :email') // Could be exact match: ->andWhere('t.email = :email')
               ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (isset($filters['specialization'])) {
             if ($filters['specialization'] === null || $filters['specialization'] === 'null') {
                 $qb->andWhere('t.specialization IS NULL');
            } else {
                $qb->andWhere('t.specialization LIKE :specialization')
                   ->setParameter('specialization', '%' . $filters['specialization'] . '%');
            }
        }

        if (!empty($filters['createdAt_from'])) {
            $qb->andWhere('t.createdAt >= :createdAt_from')
               ->setParameter('createdAt_from', new \DateTimeImmutable($filters['createdAt_from']));
        }
        if (!empty($filters['createdAt_to'])) {
            $qb->andWhere('t.createdAt <= :createdAt_to')
               ->setParameter('createdAt_to', new \DateTimeImmutable($filters['createdAt_to']));
        }

        if (!empty($filters['updatedAt_from'])) {
            $qb->andWhere('t.updatedAt >= :updatedAt_from')
               ->setParameter('updatedAt_from', new \DateTimeImmutable($filters['updatedAt_from']));
        }
        if (!empty($filters['updatedAt_to'])) {
            $qb->andWhere('t.updatedAt <= :updatedAt_to')
               ->setParameter('updatedAt_to', new \DateTimeImmutable($filters['updatedAt_to']));
        }

        return $qb->orderBy('t.id', 'ASC')->getQuery()->getResult();
    }
}