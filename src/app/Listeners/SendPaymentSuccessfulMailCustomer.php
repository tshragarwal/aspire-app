<?php

namespace App\Listeners;

use App\Events\RepaymentSuccessful;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentSuccessfulMailCustomer
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(RepaymentSuccessful $event): void
    {
        //
    }
}
