<?php

namespace App\Message;

use App\Entity\User;

class LoginNotificationMessage
{

    private string $email;
    private string $ip;
    private string $userAgent;
    private User $user;

    public function __construct(string $email, string $ip, string $userAgent, User $user)
    {
        $this->email = $email;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
        $this->user = $user;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getUserAgent(): string
    {
        return $this->userAgent;
    }

    public function getUser(): User
    {
        return $this->user;
    }

}
