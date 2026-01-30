<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EventType: int
{
    case CONCERT = 0;
    case SPECTACLE = 1;
    case CONFERENCE = 2;
    case WORKSHOP = 3;
    case FESTIVAL = 4;
    case THEATER = 5;
    case SPORT = 6;
    case EXHIBITION = 7;
    case PARTY = 8;
    case TRAINING = 9;
    case GALA = 10;
    case OTHER = 11;

    public function label(): string
    {
        return match ($this) {
            self::CONCERT => 'Concert',
            self::SPECTACLE => 'Spectacle',
            self::CONFERENCE => 'Conférence',
            self::WORKSHOP => 'Workshop',
            self::FESTIVAL => 'Festival',
            self::THEATER => 'Théâtre',
            self::SPORT => 'Sport',
            self::EXHIBITION => 'Exposition',
            self::PARTY => 'Soirée',
            self::TRAINING => 'Formation',
            self::GALA => 'Gala',
            self::OTHER => 'Autre',
        };
    }

}
