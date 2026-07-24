<?php

namespace App\Enums;

enum PlacementTestStatus: string
{
    case Pending = 'pending';
    case Analyzed = 'analyzed';
    case Failed = 'failed';
}
