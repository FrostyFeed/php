<?php


use App\Repository\SubjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: SubjectRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Subject
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['subject:read', 'lesson:read', 'grade:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 255)]
    #[Groups(['subject:read', 'lesson:read', 'grade:read'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['subject:read'])]
    private ?string $description = null;

    #[ORM\OneToMany(mappedBy: 'subject', targetEntity: Lesson::class, orphanRemoval: true)]
    #[Groups(['subject:read_relations'])]
    private Collection $lessons;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['subject:read'])]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: 'datetime_immutable', options: ["default" => "CURRENT_TIMESTAMP"])]
    #[Groups(['subject:read'])]
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
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
            $lesson->setSubject($this);
        }
        return $this;
    }

    public function removeLesson(Lesson $lesson): static
    {
        if ($this->lessons->removeElement($lesson)) {
            if ($lesson->getSubject() === $this) {
                $lesson->setSubject(null);
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
        $qb = $this->createQueryBuilder('sub'); // 's' is already used by Student

        if (!empty($filters['id'])) {
            $qb->andWhere('sub.id = :id')
               ->setParameter('id', $filters['id']);
        }

        if (!empty($filters['name'])) {
            $qb->andWhere('sub.name LIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (isset($filters['description'])) {
            if ($filters['description'] === null || $filters['description'] === 'null') {
                 $qb->andWhere('sub.description IS NULL');
            } else {
                $qb->andWhere('sub.description LIKE :description')
                   ->setParameter('description', '%' . $filters['description'] . '%');
            }
        }

        if (!empty($filters['createdAt_from'])) {
            $qb->andWhere('sub.createdAt >= :createdAt_from')
               ->setParameter('createdAt_from', new \DateTimeImmutable($filters['createdAt_from']));
        }
        if (!empty($filters['createdAt_to'])) {
            $qb->andWhere('sub.createdAt <= :createdAt_to')
               ->setParameter('createdAt_to', new \DateTimeImmutable($filters['createdAt_to']));
        }

        if (!empty($filters['updatedAt_from'])) {
            $qb->andWhere('sub.updatedAt >= :updatedAt_from')
               ->setParameter('updatedAt_from', new \DateTimeImmutable($filters['updatedAt_from']));
        }
        if (!empty($filters['updatedAt_to'])) {
            $qb->andWhere('sub.updatedAt <= :updatedAt_to')
               ->setParameter('updatedAt_to', new \DateTimeImmutable($filters['updatedAt_to']));
        }

        return $qb->orderBy('sub.id', 'ASC')->getQuery()->getResult();
    }
}