<?php

namespace App\Enum;

enum FileType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
    case DOCUMENT = 'document';
    case ARCHIVE = 'archive';
}
