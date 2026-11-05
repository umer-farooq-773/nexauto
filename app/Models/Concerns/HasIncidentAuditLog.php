<?php

namespace App\Models\Concerns;

use App\Models\User;

trait HasIncidentAuditLog
{
    /**
     * Relationship to the user who created this incident record.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship to the user who approved this incident record.
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'client_approved_by');
    }

    /**
     * Get formatted creator full name.
     */
    public function getCreatedByNameAttribute(): string
    {
        if ($this->creator) {
            $name = trim($this->creator->full_name);
            return !empty($name) ? $name : ($this->creator->name ?? 'N/A');
        }

        if (!empty($this->created_by)) {
            $user = User::query()->find($this->created_by);
            if ($user) {
                $name = trim($user->full_name);
                return !empty($name) ? $name : ($user->name ?? 'N/A');
            }
        }

        // Fallback for goods inspection if inspector_name is set
        if (!empty($this->inspector_name)) {
            return $this->inspector_name;
        }

        return 'System';
    }

    /**
     * Get formatted creator role name.
     */
    public function getCreatedByRoleAttribute(): string
    {
        $user = $this->creator;

        if (!$user && !empty($this->created_by)) {
            $user = User::query()->find($this->created_by);
        }

        if ($user) {
            return $user->role_display_name;
        }

        if (!empty($this->inspector_name)) {
            return 'Inspector';
        }

        return 'System';
    }

    /**
     * Get formatted approver full name.
     */
    public function getApprovedByNameAttribute(): string
    {
        if ($this->approver) {
            $name = trim($this->approver->full_name);
            return !empty($name) ? $name : ($this->approver->name ?? 'N/A');
        }

        if (!empty($this->client_approved_by)) {
            $user = User::query()->find($this->client_approved_by);
            if ($user) {
                $name = trim($user->full_name);
                return !empty($name) ? $name : ($user->name ?? 'N/A');
            }
            return (string) $this->client_approved_by;
        }

        return 'N/A';
    }

    /**
     * Get formatted approver role name.
     */
    public function getApprovedByRoleAttribute(): string
    {
        $user = $this->approver;

        if (!$user && !empty($this->client_approved_by)) {
            $user = User::query()->find($this->client_approved_by);
        }

        if ($user) {
            return $user->role_display_name;
        }

        return 'Client Director';
    }
}
