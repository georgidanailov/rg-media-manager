<?php

namespace App\Entity;

use App\Enum\FileType;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user_id = null;

    #[ORM\Column(length: 255)]
    private ?string $file_name = null;

    #[ORM\Column(length: 500)]
    private ?string $storage_path = null;

    #[ORM\Column]
    private ?int $file_size = null;

    #[ORM\Column(enumType: FileType::class)]
    private ?FileType $file_type = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(length: 500, nullable: true)]
    private ?string $thumbnail_path = null;

    /**
     * @var Collection<int, Metadata>
     */
    #[ORM\OneToMany(targetEntity: Metadata::class, mappedBy: 'file_id')]
    private Collection $metadata_type;

    public function __construct()
    {
        $this->metadata_type = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getFileName(): ?string
    {
        return $this->file_name;
    }

    public function setFileName(string $file_name): static
    {
        $this->file_name = $file_name;

        return $this;
    }

    public function getStoragePath(): ?string
    {
        return $this->storage_path;
    }

    public function setStoragePath(string $storage_path): static
    {
        $this->storage_path = $storage_path;

        return $this;
    }

    public function getFileSize(): ?int
    {
        return $this->file_size;
    }

    public function setFileSize(int $file_size): static
    {
        $this->file_size = $file_size;

        return $this;
    }

    public function getFileType(): ?FileType
    {
        return $this->file_type;
    }

    public function setFileType(FileType $file_type): static
    {
        $this->file_type = $file_type;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getThumbnailPath(): ?string
    {
        return $this->thumbnail_path;
    }

    public function setThumbnailPath(?string $thumbnail_path): static
    {
        $this->thumbnail_path = $thumbnail_path;

        return $this;
    }

    /**
     * @return Collection<int, Metadata>
     */
    public function getMetadatatype(): Collection
    {
        return $this->metadata_type;
    }

    public function addName(Metadata $name): static
    {
        if (!$this->metadata_type->contains($name)) {
            $this->metadata_type->add($name);
            $name->setFileId($this);
        }

        return $this;
    }

    public function removeName(Metadata $name): static
    {
        if ($this->metadata_type->removeElement($name)) {
            // set the owning side to null (unless already changed)
            if ($name->getFileId() === $this) {
                $name->setFileId(null);
            }
        }

        return $this;
    }
}
