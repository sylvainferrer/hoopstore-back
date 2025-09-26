<?php

namespace App\Enum;

enum Genre: string
{
    case Homme   = 'H';
    case Femme   = 'F';
    case Enfant  = 'E';
    case Unisex  = 'U';

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