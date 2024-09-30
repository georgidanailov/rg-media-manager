<?php

namespace App\Entity;

use App\Enum\Roles;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(enumType: Roles::class)]
    private ?Roles $role = null;

    #[ORM\Column]
    private ?int $quota = null;

    #[ORM\Column(nullable: true)]
    private ?int $used_storage = null;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getRole(): ?Roles
    {
        return $this->role;
    }

    public function setRole(Roles $role): static
    {
        $this->role = $role;

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

    public function getUsedStorage(): ?int
    {
        return $this->used_storage;
    }

    public function setUsedStorage(?int $used_storage): static
    {
        $this->used_storage = $used_storage;

        return $this;
    }
}
