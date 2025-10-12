<?php
namespace App\Enums;

use EmreYarligan\EnumConcern\EnumConcern;

enum Location: string
{
    use EnumConcern;

    case GELAM = 'GL';
    case TANGGULANGIN = 'TA';
    case BULANG = 'BL';
    case GRAHAMAS = 'GM';

    public static function labels(): array
    {
       return [
           'GL' => 'Gelam',
           'TA' => 'Tanggulangin',
           'BL' => 'Bulang',
           'GM' => 'Graha Mas',
       ];
    }
}