<?php

namespace App\Providers;

use App\Events\LoanApplicationApproved;
use App\Events\NewLoanApplication;
use App\Events\RepaymentSuccessful;
use App\Listeners\CheckLoanRepaidAndMailCustomer;
use App\Listeners\SendMailAdminNewLoanApplication;
use App\Listeners\SendMailCustomerLoanApproved;
use App\Listeners\SendPaymentSuccessfulMailCustomer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
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
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NewLoanApplication::class => [
            SendMailAdminNewLoanApplication::class,
        ],
        LoanApplicationApproved::class => [
            SendMailCustomerLoanApproved::class
        ],
        RepaymentSuccessful::class => [
            SendPaymentSuccessfulMailCustomer::class,
            CheckLoanRepaidAndMailCustomer::class
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
