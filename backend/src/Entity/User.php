<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['media_read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;


    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Groups(['media_read'])]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $quota = null;

    #[ORM\Column]
    private ?int $used_storage = null;

    #[ORM\Column]
    private ?int $infectedFileCount = null;

    #[ORM\Column]
    private ?bool $isLocked = null;

    #[ORM\OneToMany( targetEntity: Media::class , mappedBy: 'user')]
    private Collection $media;


    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        //$roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    public function getQuota(): ?int
    {
        return $this->quota;
    }

    public function setQuota(int $quota): static
    {
        $this->quota = $quota;

        return $this;
    }

    public function __construct() {
        $this->media = new ArrayCollection();
    }

    public function getMedia(): Collection {
        return $this->media;
    }

    public function addMedia(Media $media): static {
        if (!$this->media->contains($media)) {
            $this->media->add($media);
            $media->setUser($this); // Set user reference in Media
        }

        return $this;
    }

    public function removeMedia(Media $media): static {
        if ($this->media->removeElement($media)) {
            if ($media->getUser() === $this) {
                $media->setUser(null); // Set user reference in Media to null
            }
        }

        return $this;
    }


    public function getUsedStorage(): ?string
    {
        return $this->used_storage;
    }

    public function setUsedStorage(?string $used_storage): static
    {
        $this->used_storage = $used_storage;

        return $this;
    }

    public function getInfectedFileCount(): ?int
    {
        return $this->infectedFileCount;
    }

    public function setInfectedFileCount(int $infectedFileCount): static
    {
        $this->infectedFileCount = $infectedFileCount;

        return $this;
    }

    public function isLocked(): ?bool
    {
        return $this->isLocked;
    }

    public function setLocked(bool $isLocked): static
    {
        $this->isLocked = $isLocked;

        return $this;
    }
}
