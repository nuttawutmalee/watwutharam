<?php
namespace App\Api\Constants;

class RoleConstants extends EnumType
{
    const DEVELOPER = 'Developer';
    const ADMINISTRATOR = 'Administrator';
    const EDITORIAL = 'Editorial';

    public function getFields()
    {
        return [
            'DEVELOPER',
            'ADMINISTRATOR',
            'EDITORIAL'
        ];
    }
}