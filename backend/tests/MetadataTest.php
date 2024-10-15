<?php

namespace App\Tests\Entity;

use App\Entity\Media;
use App\Entity\Metadata;
use PHPUnit\Framework\TestCase;

class MetadataTest extends TestCase
{
    public function testMetadataCreation()
    {
        $metadata = new Metadata();

        $this->assertNull($metadata->getId());
        $this->assertNull($metadata->getFileId());
        $this->assertNull($metadata->getDataType());
        $this->assertNull($metadata->getValue());
    }

    public function testSettersAndGetters()
    {
        $metadata = new Metadata();
        $media = new Media();

        $metadata->setFileId($media);
        $this->assertSame($media, $metadata->getFileId());

        $metadata->setDataType('Resolution');
        $this->assertEquals('Resolution', $metadata->getDataType());

        $metadata->setValue('1920x1080');
        $this->assertEquals('1920x1080', $metadata->getValue());
    }

    public function testMediaRelation()
    {
        $metadata = new Metadata();
        $media = new Media();

        $metadata->setFileId($media);
        $this->assertSame($media, $metadata->getFileId());

        $metadata->setFileId(null);
        $this->assertNull($metadata->getFileId());
    }
}
