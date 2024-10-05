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

        // Test initial state
        $this->assertNull($metadata->getId());
        $this->assertNull($metadata->getFileId());
        $this->assertNull($metadata->getDataType());
        $this->assertNull($metadata->getValue());
    }

    public function testSettersAndGetters()
    {
        $metadata = new Metadata();
        $media = new Media();

        // Test setting and getting file_id
        $metadata->setFileId($media);
        $this->assertSame($media, $metadata->getFileId());

        // Test setting and getting data_type
        $metadata->setDataType('Resolution');
        $this->assertEquals('Resolution', $metadata->getDataType());

        // Test setting and getting value
        $metadata->setValue('1920x1080');
        $this->assertEquals('1920x1080', $metadata->getValue());
    }

    public function testMediaRelation()
    {
        $metadata = new Metadata();
        $media = new Media();

        // Test if file_id can be set
        $metadata->setFileId($media);
        $this->assertSame($media, $metadata->getFileId());

        // Test if we can nullify the relation
        $metadata->setFileId(null);
        $this->assertNull($metadata->getFileId());
    }
}
