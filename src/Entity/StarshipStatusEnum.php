<?php

namespace App\Entity;

enum StarshipStatusEnum: string
{
    case WAITING = 'waiting';
    case IN_PROGRESS = 'in progress';
    case COMPLETED = 'completed';
}
