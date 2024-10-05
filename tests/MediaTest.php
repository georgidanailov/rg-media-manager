<?php


namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\User;
use App\Entity\Metadata;
use App\Enum\FileType;
use PHPUnit\Framework\TestCase;
use DateTime;

class MediaTest extends TestCase
{
    public function testMediaCreation()
    {
        $media = new Media();

        $this->assertNull($media->getId());
        $this->assertNull($media->getUserId());
        $this->assertNull($media->getFileName());
        $this->assertNull($media->getStoragePath());
        $this->assertNull($media->getFileSize());
        $this->assertNull($media->getFileType());
        $this->assertNull($media->getCreatedAt());
        $this->assertNull($media->getThumbnailPath());
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $media->getMetadatatype());
        $this->assertCount(0, $media->getMetadatatype());
    }

    public function testSettersAndGetters()
    {
        $media = new Media();
        $user = new User();
        $createdAt = new DateTime();
        $metadata = new Metadata();

        $media->setUserId($user);
        $this->assertSame($user, $media->getUserId());

        $media->setFileName('sample-file.jpg');
        $this->assertEquals('sample-file.jpg', $media->getFileName());

        $media->setStoragePath('/uploads/sample-file.jpg');
        $this->assertEquals('/uploads/sample-file.jpg', $media->getStoragePath());

        $media->setFileSize(2048);
        $this->assertEquals(2048, $media->getFileSize());

        $fileType = FileType::IMAGE;
        $media->setFileType($fileType);
        $this->assertEquals($fileType, $media->getFileType());

        $media->setCreatedAt($createdAt);
        $this->assertSame($createdAt, $media->getCreatedAt());

        $media->setThumbnailPath('/uploads/thumbnails/sample-file-thumb.jpg');
        $this->assertEquals('/uploads/thumbnails/sample-file-thumb.jpg', $media->getThumbnailPath());

        $media->addName($metadata);
        $this->assertCount(1, $media->getMetadatatype());
        $this->assertSame($metadata, $media->getMetadatatype()->first());

        $media->removeName($metadata);
        $this->assertCount(0, $media->getMetadatatype());
    }

    public function testAddRemoveMetadata()
    {
        $media = new Media();
        $metadata = new Metadata();

        $media->addName($metadata);
        $this->assertCount(1, $media->getMetadatatype());
        $this->assertTrue($media->getMetadatatype()->contains($metadata));

        $media->removeName($metadata);
        $this->assertCount(0, $media->getMetadatatype());
        $this->assertFalse($media->getMetadatatype()->contains($metadata));
    }
}
