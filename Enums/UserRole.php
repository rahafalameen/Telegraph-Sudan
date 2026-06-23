<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN  = 'admin';
    case WRITER = 'writer';
    case EDITOR = 'editor'; // Future: review/approve without full admin access

    public function label(): string
    {
        return match($this) {
            self::ADMIN  => __('roles.admin'),
            self::WRITER => __('roles.writer'),
            self::EDITOR => __('roles.editor'),
        };
    }

    public function canPublish(): bool
    {
        return in_array($this, [self::ADMIN, self::EDITOR]);
    }

    public function canManageUsers(): bool
    {
        return $this === self::ADMIN;
    }
}
