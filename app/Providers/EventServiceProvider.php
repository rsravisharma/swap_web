<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        \App\Events\MessageSentEvent::class => [
            \App\Listeners\StoreMessageListener::class,
        ],
        \App\Events\MessageReadEvent::class => [
            // Add listeners if needed for read receipts
        ],
        \App\Events\TypingIndicatorEvent::class => [
            // Add listeners if needed for typing processing
        ],
        // Add other events as you create them
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();
        
        // Optional: Add manual event registration here if needed
        // Event::listen(SomeEvent::class, SomeListener::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false; // Set to true if you want automatic discovery
    }
}
