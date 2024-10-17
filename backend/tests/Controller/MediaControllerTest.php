<?php

namespace App\Tests\Controller;

use App\Controller\MediaController;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class MediaControllerTest extends WebTestCase
{
    private $controller;
    private $entityManager;
    private $slugger;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->slugger = $this->createMock(SluggerInterface::class);
        $this->controller = new MediaController($this->entityManager, $this->slugger);
    }

    public function testUploadMediaSuccess()
    {
        $user = new User();
        $this->entityManager->method('getRepository')
            ->willReturn($this->getUserRepository($user));


        $file = new UploadedFile(
            '/path/to/file.mp4',
            'file.mp4',
            'video/mp4',
            null,
            true
        );

        $request = new Request();
        $request->files->set('file', $file);

        shell_exec('echo "Infected files: 0" > /dev/null');

        $response = $this->controller->uploadMedia($request, $this->entityManager, $this->slugger);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['success' => 'file created'], json_decode($response->getContent(), true));
    }

    public function testUploadMediaNoFile()
    {
        $request = new Request();

        $response = $this->controller->uploadMedia($request, $this->entityManager, $this->slugger);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'No file provided or invalid file'], json_decode($response->getContent(), true));
    }

    public function testUploadMediaUnsupportedFileType()
    {
        $user = new User();
        $this->entityManager->method('getRepository')
            ->willReturn($this->getUserRepository($user));

        $file = new UploadedFile(
            '/path/to/file.txt',
            'file.txt',
            'text/plain',
            null,
            true
        );

        $request = new Request();
        $request->files->set('file', $file);

        $response = $this->controller->uploadMedia($request, $this->entityManager, $this->slugger);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(['error' => 'Unsupported file type'], json_decode($response->getContent(), true));
    }


    private function getUserRepository($user)
    {
        $userRepository = $this->createMock(\Doctrine\Persistence\ObjectRepository::class);
        $userRepository->method('find')
            ->willReturn($user);
        return $userRepository;
    }
}
