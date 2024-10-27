<?php

namespace App\Enum;

enum TicketStatus: string
{
    case READY = 'READY';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';
}
