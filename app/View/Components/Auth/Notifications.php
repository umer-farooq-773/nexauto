<?php

namespace App\View\Components\Auth;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Notifications extends Component
{
    public $notifications;
    public $unreadCount;

    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        $user = auth()->user();

        $this->notifications = $user
            ?->unreadNotifications()
            ->latest()
            ->take(10)
            ->get() ?? collect();

        // Total unread count (not capped at 10) for the badge
        $this->unreadCount = $user
            ?->unreadNotifications()
            ->count() ?? 0;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.auth.notifications');
    }
}
