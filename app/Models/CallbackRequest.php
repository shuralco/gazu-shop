<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallbackRequest extends Model
{
    use HasFactory;

    public const STATUS_NEW = 'new';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_SPAM = 'spam';

    public const STATUSES = [
        self::STATUS_NEW         => 'Новий',
        self::STATUS_IN_PROGRESS => 'В роботі',
        self::STATUS_DONE        => 'Оброблено',
        self::STATUS_SPAM        => 'Спам',
    ];

    protected $fillable = [
        'name',
        'phone',
        'source',
        'status',
        'notes',
        'referrer_url',
        'ip_address',
        'user_agent',
    ];

    public static function statusLabel(string $status): string
    {
        return self::STATUSES[$status] ?? $status;
    }
}
