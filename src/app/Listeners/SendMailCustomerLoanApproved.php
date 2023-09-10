<?php

namespace App\Listeners;

use App\Events\LoanApplicationApproved;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMailCustomerLoanApproved
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
    public function handle(LoanApplicationApproved $event): void
    {
        $loan = $event->loanDetails;

        // send mail to customer
    }
}
