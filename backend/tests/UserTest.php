<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation()
    {
        $user = new User();

        // Test initial state
        $this->assertNull($user->getId());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getPassword());
        $this->assertNull($user->getName());
        $this->assertNull($user->getQuota());
        $this->assertNull($user->getUsedStorage());
        $this->assertCount(0, $user->getRoles());  // No roles by default
    }

    public function testSettersAndGetters()
    {
        $user = new User();

        $user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $user->getEmail());

        $user->setName('John Doe');
        $this->assertEquals('John Doe', $user->getName());

        $user->setQuota(500);
        $this->assertEquals(500, $user->getQuota());

        $user->setUsedStorage(100);
        $this->assertEquals(100, $user->getUsedStorage());

        $user->setPassword('hashedpassword');
        $this->assertEquals('hashedpassword', $user->getPassword());
    }

    public function testRoles()
    {
        $user = new User();

        $this->assertCount(0, $user->getRoles());

        $user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], $user->getRoles());

        $user->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $user->getRoles());

        $user->setRoles(['ROLE_ADMIN', 'ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN'], array_unique($user->getRoles()));
    }

    public function testEraseCredentials()
    {
        $user = new User();
        $user->eraseCredentials();

        // Since eraseCredentials does not currently modify anything,
        // we can only assert that it was called and that the entity remains unchanged
        $this->assertNull($user->getPassword()); // Assuming there's no temporary password stored
    }

    public function testUserIdentifier()
    {
        $user = new User();
        $user->setEmail('user@example.com');
        $this->assertEquals('user@example.com', $user->getUserIdentifier());
    }
}
