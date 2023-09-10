<?php

namespace App\Listeners;

use App\Events\RepaymentSuccessful;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class CheckLoanRepaidAndMailCustomer
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
        $loanDetails = $event->loanDetails;

        $repaymentSchedule = $loanDetails->repaymentSchedules;
        $isLoanPaid = true;
        foreach($repaymentSchedule as $schedule) {
            if($schedule->status === 'pending') {
                $isLoanPaid = false;
                break;
            }
        }
        if($isLoanPaid) {
            $loanDetails->status = 'paid';
            $loanDetails->save();

            // send mail to customer
        }
    }
}
