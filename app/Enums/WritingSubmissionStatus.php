<?php

namespace App\Enums;

enum WritingSubmissionStatus: string
{
    case Pending = 'pending';
    case Corrected = 'corrected';
    case Failed = 'failed';
}
