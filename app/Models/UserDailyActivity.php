<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'activity_date'])]
class UserDailyActivity extends Model
{
    use HasFactory;

    protected $table = 'user_daily_activity';

    protected function casts(): array
    {
        return [
            'activity_date' => 'date:Y-m-d',
        ];
    }
}
