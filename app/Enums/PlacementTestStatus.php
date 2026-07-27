<?php

namespace App\Enums;

enum PlacementTestStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Analyzed = 'analyzed';
    case Failed = 'failed';
}
