<?php


namespace App\Enum;

enum NotificationType: string
{
case FILE_MODIFICATION = 'file_modification';
case FILE_UPLOAD = 'file_upload';
case VIRUS_DETECTION = 'virus_detection';
case DIFFERENT_DEVICE_LOGIN = 'different_device_login';
}
