<?php

namespace App\Listeners;

use App\Events\NewLoanApplication;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendMailAdminNewLoanApplication
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
    public function handle(NewLoanApplication $event): void
    {
        $loan = $event->loanDetails;
        
        // send email to admin 
    }
}
