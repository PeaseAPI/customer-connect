<?php

namespace App\Providers;

use App\Events\ContractStatusChanged;
use App\Events\InvoiceCreated;
use App\Events\LeaveRequested;
use App\Events\LeaveStatusChanged;
use App\Events\LeadConverted;
use App\Events\PaymentReceived;
use App\Events\TaskAssigned;
use App\Listeners\SendContractStatusNotification;
use App\Listeners\SendLeaveRequestNotification;
use App\Listeners\SendLeaveStatusNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendTaskAssignedNotification;
use App\Models\Contract;
use App\Models\Task;
use App\Observers\ContractObserver;
use App\Observers\TaskObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TaskAssigned::class => [
            SendTaskAssignedNotification::class,
        ],
        LeaveRequested::class => [
            SendLeaveRequestNotification::class,
        ],
        LeaveStatusChanged::class => [
            SendLeaveStatusNotification::class,
        ],
        ContractStatusChanged::class => [
            SendContractStatusNotification::class,
        ],
        PaymentReceived::class => [
            SendPaymentReceivedNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Contract::observe(ContractObserver::class);
    }
}
