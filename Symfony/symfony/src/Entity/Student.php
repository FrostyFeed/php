<?php


use App\Repository\StudentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: StudentRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Student
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['student:read', 'grade:read'])] 
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['student:read', 'grade:read'])]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 255)]
    #[Groups(['student:read', 'grade:read'])]
    private ?string $lastName = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\Date]
    #[Groups(['student:read'])]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['student:read'])]
    private ?string $classGroup = null;

    #[ORM\OneToMany(mappedBy: 'student', targetEntity: Grade::class, orphanRemoval: true)]
    #[Groups(['student:read_relations'])]
    private Collection $grades;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['student:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['student:read'])]
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

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }

    public function getClassGroup(): ?string
    {
        return $this->classGroup;
    }

    public function setClassGroup(?string $classGroup): static
    {
        $this->classGroup = $classGroup;
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
            $grade->setStudent($this);
        }
        return $this;
    }

    public function removeGrade(Grade $grade): static
    {
        if ($this->grades->removeElement($grade)) {
            if ($grade->getStudent() === $this) {
                $grade->setStudent(null);
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
        $qb = $this->createQueryBuilder('s');

        if (!empty($filters['id'])) {
            $qb->andWhere('s.id = :id')
               ->setParameter('id', $filters['id']);
        }

        if (!empty($filters['firstName'])) {
            $qb->andWhere('s.firstName LIKE :firstName')
               ->setParameter('firstName', '%' . $filters['firstName'] . '%');
        }

        if (!empty($filters['lastName'])) {
            $qb->andWhere('s.lastName LIKE :lastName')
               ->setParameter('lastName', '%' . $filters['lastName'] . '%');
        }

        if (!empty($filters['dateOfBirth'])) {
            $qb->andWhere('s.dateOfBirth = :dateOfBirth')
               ->setParameter('dateOfBirth', new \DateTime($filters['dateOfBirth']));
        }
        if (!empty($filters['dateOfBirth_from'])) {
            $qb->andWhere('s.dateOfBirth >= :dateOfBirth_from')
               ->setParameter('dateOfBirth_from', new \DateTime($filters['dateOfBirth_from']));
        }
        if (!empty($filters['dateOfBirth_to'])) {
            $qb->andWhere('s.dateOfBirth <= :dateOfBirth_to')
               ->setParameter('dateOfBirth_to', new \DateTime($filters['dateOfBirth_to']));
        }
         if (isset($filters['dateOfBirth_is_null']) && $filters['dateOfBirth_is_null'] == 'true') {
            $qb->andWhere('s.dateOfBirth IS NULL');
        }


        if (isset($filters['classGroup'])) {
             if ($filters['classGroup'] === null || $filters['classGroup'] === 'null') {
                 $qb->andWhere('s.classGroup IS NULL');
            } else {
                $qb->andWhere('s.classGroup LIKE :classGroup')
                   ->setParameter('classGroup', '%' . $filters['classGroup'] . '%');
            }
        }

        if (!empty($filters['createdAt_from'])) {
            $qb->andWhere('s.createdAt >= :createdAt_from')
               ->setParameter('createdAt_from', new \DateTimeImmutable($filters['createdAt_from']));
        }
        if (!empty($filters['createdAt_to'])) {
            $qb->andWhere('s.createdAt <= :createdAt_to')
               ->setParameter('createdAt_to', new \DateTimeImmutable($filters['createdAt_to']));
        }

        if (!empty($filters['updatedAt_from'])) {
            $qb->andWhere('s.updatedAt >= :updatedAt_from')
               ->setParameter('updatedAt_from', new \DateTimeImmutable($filters['updatedAt_from']));
        }
        if (!empty($filters['updatedAt_to'])) {
            $qb->andWhere('s.updatedAt <= :updatedAt_to')
               ->setParameter('updatedAt_to', new \DateTimeImmutable($filters['updatedAt_to']));
        }

        return $qb->orderBy('s.id', 'ASC')->getQuery()->getResult();
    }
}