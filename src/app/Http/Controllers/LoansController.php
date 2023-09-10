<?php

namespace App\Http\Controllers;

use App\Services\LoanService;
use App\Http\Requests\LoanApproveRequest;
use App\Http\Requests\NewLoanRequest;
use App\Http\Resources\LoanResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class LoansController extends Controller
{
    use HttpResponses;
    /**
     * Loan controller constructor.
     */
    public function __construct(
        protected LoanService $_loanService
    )
    {}

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try{
            $loan = $this->_loanService->getMyLoan();
            $message = NO_LOAN;

            if(!empty($loan)) {
                $message = LOAN_LIST;
            }
            
            return $this->success( LoanResource::collection($loan), $message);
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JsonResponse
     */
    public function store(NewLoanRequest $request): JsonResponse
    {
        try{
            $loanData = $request->validated();
            return $this->success(
                $this->_loanService->newLoanRequest($loanData), 
                LOAN_APPLICATION_SUBMITTED_SUCCESSFULLY, 
                Response::HTTP_CREATED);
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $loanId): JsonResponse
    {
        try{
            $loanDetails = $this->_loanService->getLoanDetails($loanId);
            if(!empty($loanDetails)) {
                return $this->success(
                    new LoanResource($loanDetails), 
                    "Loan details"
                );
            } else {
                return $this->error(
                    [],
                    LOAN_DETAILS_NOT_FOUND,
                    Response::HTTP_NOT_FOUND
                );
            }
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Approve loan
     * 
     * @param LoanApproveRequest
     * @return JsonResponse
     */
    public function approve(LoanApproveRequest $request): JsonResponse
    {
        try{
            $data = $request->validated();
            $loanDetails = $this->_loanService->approveLoan($data['loan_id']);

            return $this->success(
                new LoanResource($loanDetails), 
                LOAN_APPLICATION_SUBMITTED_SUCCESSFULLY, 
                Response::HTTP_OK);
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
