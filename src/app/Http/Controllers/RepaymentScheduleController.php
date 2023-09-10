<?php

namespace App\Http\Controllers;

use App\Services\LoanService;
use App\Http\Requests\RepaymentRequest;
use App\Http\Resources\LoanResource;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RepaymentScheduleController extends Controller
{
    use HttpResponses;
    /**
     * Loan controller constructor.
     */
    public function __construct(
        protected LoanService $_loanService
    )
    {
        $this->_loanService = new LoanService();
    }
    
    public function __invoke(RepaymentRequest $request): JsonResponse
    {
        try{
            $requestData = $request->validated();
            
            $loanDetails = $this->_loanService->loanRepayment($requestData['loan_id'], $requestData['amount']);

            return $this->success(
                new LoanResource($loanDetails), 
                LOAN_APPLICATION_SUBMITTED_SUCCESSFULLY, 
                Response::HTTP_OK);
        } catch(\Exception $e) {
            return $this->error([], $e->getMessage(), $e->getCode());
        }
    }

}
