<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\User;
use PHPUnit\Framework\Attributes\DataProvider;
use App\Entity\Media;


class MediaControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }


    #[DataProvider('filterProvider')]

    public function testFilterMedia(array $queryParams, string $role, int $expectedStatus, int $expectedCount)
    {
        $this->logIn($role);
        $this->client->request('GET', '/media/filter', $queryParams);

        // Check the response status
        $this->assertEquals($expectedStatus, $this->client->getResponse()->getStatusCode());

        if ($expectedStatus === Response::HTTP_OK) {
            // Decode JSON response
            $responseData = json_decode($this->client->getResponse()->getContent(), true);

            // Check pagination and data structure
            $this->assertArrayHasKey('data', $responseData);
            $this->assertArrayHasKey('totalItems', $responseData);
            $this->assertArrayHasKey('currentPage', $responseData);
            $this->assertArrayHasKey('itemsPerPage', $responseData);

            // Assert the number of media items returned matches expected count
            $this->assertCount($expectedCount, $responseData['data']);
        }
    }

    public function filterProvider(): array
    {
        return [
            'Admin with no filters' => [
                'queryParams' => [],
                'role' => 'ROLE_ADMIN',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 10
            ],
            'Moderator with file type filter' => [
                'queryParams' => ['type' => 'image'],
                'role' => 'ROLE_MODERATOR',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 5
            ],
            'User with name filter' => [
                'queryParams' => ['name' => 'document'],
                'role' => 'ROLE_USER',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 2
            ],
            'User with tag filter' => [
                'queryParams' => ['tag' => 'important'],
                'role' => 'ROLE_USER',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 3
            ],
            'User with date filter for last 24 hours' => [
                'queryParams' => ['date' => '24hours'],
                'role' => 'ROLE_USER',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 1
            ],
            'Admin with size filter for large files' => [
                'queryParams' => ['size' => 'large'],
                'role' => 'ROLE_ADMIN',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 4
            ],
            'User with pagination on page 2' => [
                'queryParams' => ['page' => 2],
                'role' => 'ROLE_USER',
                'expectedStatus' => Response::HTTP_OK,
                'expectedCount' => 5
            ],
        ];
    }
    #[DataProvider('userRoleProvider')]
    public function testGetMedia(string $role, int $expectedStatus, int $expectedCount): void
    {
        // Log in as a user with the specified role
        $this->logIn($role);

        // Send a GET request to the /media endpoint
        $this->client->request('GET', '/media');

        // Check the response status
        $this->assertEquals($expectedStatus, $this->client->getResponse()->getStatusCode());

        if ($expectedStatus === Response::HTTP_OK) {
            // Decode JSON response
            $mediaData = json_decode($this->client->getResponse()->getContent(), true);

            // Assert the number of media items returned
            $this->assertCount($expectedCount, $mediaData);
        }
    }

    public static function userRoleProvider(): array
    {
        return [
            'Admin should see all media' => ['ROLE_ADMIN', Response::HTTP_OK, 10],
            'Moderator should see all media' => ['ROLE_MODERATOR', Response::HTTP_OK, 10],
            'Regular user should see only their media' => ['ROLE_USER', Response::HTTP_OK, 3],
        ];
    }

    private function logIn(string $role): void
    {
        $userRepo = static::getContainer()->get('doctrine')->getRepository(User::class);
        $user = $userRepo->findOneBy(['role' => $role]);

        if ($user) {
            $this->client->loginUser($user);
        } else {
            throw new \Exception("User with role $role not found in the database.");
        }
    }
}
