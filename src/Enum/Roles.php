<?php

namespace App\Enum;

enum Roles: string
{
   case ROLE_ADMIN = 'admin';
   case ROLE_USER = 'user';
   case ROLE_MANAGER = 'manager';

}
