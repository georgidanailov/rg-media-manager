<?php

namespace App\Entity;

use App\Enum\FileType;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Attribute\Ignore;


#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['media_read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'media')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['media_read'])]
    private ?User $user = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media_read'])]
    private ?string $file_name = null;



    #[ORM\Column(length: 500)]
    #[Groups(['media_read'])]
    private ?string $storage_path = null;

    #[ORM\Column]
    #[Groups(['media_read'])]
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

    /**
     * @var Collection<int, Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, mappedBy: 'media')]
    #[Groups(['media_read'])]
    private Collection $tags;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $deleted_at = null;


    public function __construct()
    {
        $this->metadata_type = new ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
            $tag->addMedium($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        if ($this->tags->removeElement($tag)) {
            $tag->removeMedium($this);
        }

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

}
