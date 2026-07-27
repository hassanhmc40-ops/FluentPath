<?php

namespace App\Enums;

enum WritingSubmissionStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Corrected = 'corrected';
    case Failed = 'failed';
}
