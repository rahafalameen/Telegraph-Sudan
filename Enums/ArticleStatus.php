<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case DRAFT     = 'draft';
    case PENDING   = 'pending';   // Submitted by writer, awaiting review
    case APPROVED  = 'approved';  // Approved by admin, ready to publish
    case PUBLISHED = 'published';
    case REJECTED  = 'rejected';
    case SCHEDULED = 'scheduled';

    public function label(): string
    {
        return match($this) {
            self::DRAFT     => __('statuses.draft'),
            self::PENDING   => __('statuses.pending'),
            self::APPROVED  => __('statuses.approved'),
            self::PUBLISHED => __('statuses.published'),
            self::REJECTED  => __('statuses.rejected'),
            self::SCHEDULED => __('statuses.scheduled'),
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT     => 'gray',
            self::PENDING   => 'yellow',
            self::APPROVED  => 'blue',
            self::PUBLISHED => 'green',
            self::REJECTED  => 'red',
            self::SCHEDULED => 'purple',
        };
    }
}
