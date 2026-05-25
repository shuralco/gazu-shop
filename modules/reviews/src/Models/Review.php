<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'product_id',
        'author_name',
        'author_email',
        'rating',
        'comment',
        'status',
        'admin_reply',
        'admin_replied_at',
        'is_verified_purchase',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_verified_purchase' => 'boolean',
            'admin_replied_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d.m.Y');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'На модерації',
            self::STATUS_APPROVED => 'Схвалено',
            self::STATUS_REJECTED => 'Відхилено',
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'warning',
            self::STATUS_APPROVED => 'success',
            self::STATUS_REJECTED => 'danger',
            default => 'gray',
        };
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
        $this->product?->updateRatingFromReviews();
    }

    public function reject(): void
    {
        $this->update(['status' => self::STATUS_REJECTED]);
        $this->product?->updateRatingFromReviews();
    }

    public function setAdminReply(string $reply): void
    {
        $this->update([
            'admin_reply' => $reply,
            'admin_replied_at' => now(),
        ]);
    }

    /**
     * Determine the initial status for a new review based on moderation settings.
     */
    public static function determineInitialStatus(string $comment, bool $isVerifiedPurchase): string
    {
        $moderationEnabled = (bool) DisplaySetting::get('reviews_moderation_enabled', true);

        if (! $moderationEnabled) {
            return self::STATUS_APPROVED;
        }

        // Check profanity - always flag
        if (static::containsProfanity($comment)) {
            return self::STATUS_PENDING;
        }

        // Auto-approve verified purchases if setting enabled
        $autoApprove = (bool) DisplaySetting::get('reviews_auto_approve', false);
        if ($autoApprove && $isVerifiedPurchase) {
            return self::STATUS_APPROVED;
        }

        return self::STATUS_PENDING;
    }

    /**
     * Basic profanity check for Ukrainian/Russian common swear words.
     */
    public static function containsProfanity(string $text): bool
    {
        $profanityList = [
            // Ukrainian profanity
            'блять', 'бляд', 'сука', 'хуй', 'хуя', 'хує', 'піздец', 'пізд',
            'їбат', 'єбат', 'йобан', 'їбан', 'єбан', 'курв', 'дебіл',
            'мудак', 'мудил', 'гандон', 'залупа', 'шльондра', 'падлюка',
            // Russian profanity
            'блядь', 'ебать', 'ёбан', 'пизд', 'пидор', 'пидар',
            'нахуй', 'нахуя', 'похуй', 'заебал', 'заїбал',
            // English profanity
            'fuck', 'shit', 'asshole', 'bitch', 'bastard', 'dick', 'pussy',
        ];

        $lowerText = mb_strtolower($text);

        foreach ($profanityList as $word) {
            if (mb_strpos($lowerText, $word) !== false) {
                return true;
            }
        }

        return false;
    }
}
