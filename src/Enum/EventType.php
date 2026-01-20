<?php

namespace App\Enum;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum EventType: int implements TranslatableInterface
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

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return $translator->trans('event_type.' . strtolower($this->name), locale: $locale);
    }

}
