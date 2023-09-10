<?php

namespace App\Services;

use App\Events\LoanApplicationApproved;
use App\Events\NewLoanApplication;
use App\Events\RepaymentSuccessful;
use Carbon\Carbon;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpFoundation\Response;

/**
 * class UserService
 */

 class LoanService
 {
     /**
      * Fetch all logged-in user loan(s).
      * Fetch all loans if user is admin.
      * 
      * @return Loan[]
      */
     public function getMyLoan()
     {
        $loans = Auth::user()->is_admin ? 
                Loan::orderBy('id', 'desc')->get() :
                auth()->user()->loans;

        return $loans;
     }

     /**
      * Fetch loan details against loan id.
      *
      * @param int $id
      * @param Loan | null
      */
     public function getLoanDetails(int $id): Loan | null
     {
        $loan = Auth::user()->is_admin ? 
        Loan::with('repaymentSchedules')->find($id) :
        Loan::with('repaymentSchedules')->where('id', $id)->whereUserId(Auth::user()->id)->first();
        
        return $loan;
     }
     

     /**
      * Create loan request.
      *
      * @param array
      * @return Loan
      */
     public function newLoanRequest(array $data): Loan
     {
        try{
            $loanDetails = Auth::user()->loans()->create($data);
            event(new NewLoanApplication($loanDetails));
            return $loanDetails;
        } catch (QueryException $e) {
            report($e);
            throw new \Exception(INTERNAL_SERVER_ERROR);
        }
     }


     /**
      * Approve loan.
      *
      * @param int $loanID
      * @return Loan
      */
     public function approveLoan(int $loanID): Loan
     {
        $loan = Loan::find($loanID);

        if(!$loan) {
            throw new \Exception(LOAN_DETAILS_NOT_FOUND);
        }

        if($loan->status !== 'pending') {
            throw new \Exception(LOAN_STATUS_NOT_PENDING);
        }


        try{
            DB::beginTransaction();

            $loan->status = 'approved';
            $loan->approved_by = Auth::user()->id;
            $loan->approved_at = Carbon::now();

            $loan->save();

            //Adding repayment schedule
            $termAmount = $loan->amount / $loan->loan_term;

            for($i = 1; $i <= $loan->loan_term; $i++) {
                $loan->repaymentSchedules()->create([
                    'repayment_amount' => $termAmount,
                    'scheduled_on' => Carbon::now()->addDays(7 * $i)
                ]);
            }
            DB::commit();

            event(new LoanApplicationApproved($loan));
            return $loan;
        } catch (QueryException $e) {
            DB::rollBack();
            throw new \Exception(INTERNAL_SERVER_ERROR);
        } 
    }

    /**
     * Loan repayment
     * 
     * @param int $loanID
     * @param float $amount
     * 
     * @return Loan
     */
    public function loanRepayment(int $loanID, float $amount): Loan
    {
        if($loanID < 1 || $amount < 1) {
            throw new \Exception(INVALID_AMOUNT_LOANID, Response::HTTP_OK);
        }

        $loan = Loan::where('id', $loanID)
                ->whereUserId(Auth::user()->id)->first();

        if(!$loan) {
            throw new \Exception(LOAN_DETAILS_NOT_FOUND, Response::HTTP_NOT_FOUND);
        }

        if($loan->status === 'paid' || $loan->status === 'pending') {
            throw new \Exception(LOAN_STATUS_PENDING_PAID, Response::HTTP_OK);
        }

        // Amount check
        if($amount < (float) ($loan->amount / $loan->loan_term)) {
            throw new \Exception(LOAN_AMOUNT_INSUFFICIENT, Response::HTTP_OK);
        }
        
        try{
            $repaymentSchedules = $loan->repaymentSchedules;
            foreach($repaymentSchedules as $repaymentSchedule) {
                if($repaymentSchedule->status === 'pending') {
                    $repaymentSchedule->paid_amount = $amount;
                    $repaymentSchedule->status = 'paid';
                    $repaymentSchedule->save();
                    break;
                }
            }

            event(new RepaymentSuccessful($loan));
            return $loan;
        } catch (QueryException $e) {
            report($e);
            throw new \Exception(INTERNAL_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
 }