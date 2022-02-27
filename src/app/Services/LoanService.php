<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Loan;
use App\Http\Requests\LoanRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RepaymentRequest;
use Illuminate\Database\QueryException;
use App\Http\Requests\LoanApproveRequest;
use Illuminate\Http\Response as HttpResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * class UserService
 */

 class LoanService extends BaseService
 {
     # Messages
     const APPLICATION_SUBMITTED_SUCCESSFULLY = 'Loan application submitted successfully.';
     const LOAN_DETAILS_NOT_FOUND = 'Unable to get requested loan details.';
     const LOAN_APPROVED = 'Loan has been approved.';
     const INTERNAL_SERVER_ERROR = 'Facing some technical issue. Please contact site admin.';
     const LOAN_AMOUNT_INSUFFICIENT = 'Repayment amount is insufficient.';

     /**
      * Fetch all logged-in user loan(s).
      * Fetch all loans if user is admin.
      * 
      * @return \Illuminate\Http\Response
      */
     public function getAllLoans(): HttpResponse
     {
        $loans = Auth::user()->is_admin ? 
                Loan::orderBy('id', 'desc')->get() :
                auth()->user()->loans;

        return $this->sendReponse($loans);
     }

     /**
      * Fetch loan details against loan id.
      *
      * @param Int $id
      * @param \Illuminate\Http\Response
      */
     public function fetchLoan(int $id): HttpResponse
     {
        $loan = Auth::user()->is_admin ? 
        Loan::with('repaymentSchedules')->find($id) :
        Loan::with('repaymentSchedules')->where('id', $id)->whereUserId(Auth::user()->id)->first();

        if(!$loan) {
            return $this->sendReponse([], self::LOAN_DETAILS_NOT_FOUND, Response::HTTP_OK, 'FAIL');
        }

        return $this->sendReponse($loan);
     }
     

     /**
      * Create loan request.
      *
      * @param LoanRequest $request
      * @return \Illuminate\Http\Response
      */
     public function loanRequest(LoanRequest $request): HttpResponse
     {
        $loan = auth()->user()
                ->loans()
                ->create($request->validated());
        return $this->sendReponse($loan, self::APPLICATION_SUBMITTED_SUCCESSFULLY, Response::HTTP_CREATED);
     }


     /**
      * Approve loan request.
      *
      * @param LoanApproveRequest $request
      * @return \Illuminate\Http\Response
      */
     public function approveLoan(LoanApproveRequest $request): HttpResponse
     {
        $loan = Loan::find($request->validated('loan_id'));

        if(!$loan) {
            return $this->sendReponse([], self::LOAN_DETAILS_NOT_FOUND, Response::HTTP_OK, 'FAIL');
        }

        if($loan->status === 'pending') {
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

                return $this->sendReponse([], self::LOAN_APPROVED);
            } catch (QueryException $e) {
                DB::rollBack();
                return $this->sendReponse([], self::INTERNAL_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR, 'FAIL');
            } 
        }

        return $this->sendReponse([], 'Loan approval failed because loan status is ' . strtoupper($loan->status));
    }

    public function loanRepayment(RepaymentRequest $request): HttpResponse
    {
        $loan = Loan::where('id', $request->validated('loan_id'))
                ->whereUserId(Auth::user()->id)->first();

        if(!$loan) {
            return $this->sendReponse([], self::LOAN_DETAILS_NOT_FOUND, Response::HTTP_UNAUTHORIZED, 'FAIL');
        }

        //validate repayment amount
        if((float)$request->validated('amount') < ((float)$loan->amount / (int) $loan->loan_term)) {
            return $this->sendReponse([], self::LOAN_AMOUNT_INSUFFICIENT, Response::HTTP_OK, 'FAIL');
        }

        if($loan->status === 'approved') {
            try{
                DB::beginTransaction();

                $paidTerm = 0;
            
                $repaymentSchedules = $loan->repaymentSchedules;
                foreach($repaymentSchedules as $repaymentSchedule) {
                    if($repaymentSchedule->status === 'paid') {
                        $paidTerm++;
                        continue;
                    }
                    if($repaymentSchedule->status === 'pending') {
                        $repaymentSchedule->paid_amount = $request->validated('amount');
                        $repaymentSchedule->status = 'paid';
                        $repaymentSchedule->save();
                        $paidTerm++;
                        break;
                    }
                }

                $message = 'Loan re-payment is successful.';

                if($paidTerm === (int)$loan->loan_term) {
                    $loan->status = 'paid';
                    $loan->save();

                    $message .= ' You have paid your loan successfully.';
                }
                
                DB::commit();

                return $this->sendReponse([], $message);
            } catch (QueryException $e) {
                DB::rollBack();
                return $this->sendReponse([], self::INTERNAL_SERVER_ERROR, Response::HTTP_INTERNAL_SERVER_ERROR, 'FAIL');
            }
        }

        return $this->sendReponse([], 'Repayment request failed because loan status is ' . strtoupper($loan->status), Response::HTTP_OK, 'FAIL');
    }
    
 }