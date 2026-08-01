<?php

namespace App\Enums;

enum RoadmapStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Generated = 'generated';
    case Failed = 'failed';
}
