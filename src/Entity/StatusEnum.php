<?php

namespace App\Entity;

enum StatusEnum: string
{
    case AVAILABLE = 'available';
    case ISSUED = 'issued';
    case FAULTY = 'faulty';
    case IN_REPAIR = 'in_repair';
    case WRITTEN_OFF = 'written_off';
} 