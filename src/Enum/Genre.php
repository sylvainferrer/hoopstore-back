<?php

namespace App\Enum;

enum Genre: string
{
    case Homme   = 'h';
    case Femme   = 'f';
    case Enfant  = 'e';
    case Unisex  = 'u';

    public function label(): string
    {
        return match ($this) {
            self::Homme  => 'Homme',
            self::Femme  => 'Femme',
            self::Enfant => 'Enfant',
            self::Unisex => 'Unisex',
        };
    }
}