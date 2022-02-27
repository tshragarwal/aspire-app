<?php

namespace App\Http\Controllers;

use App\Services\LoanService;
use App\Http\Requests\RepaymentRequest;
use Illuminate\Http\Response;

class RepaymentScheduleController extends Controller
{
    /**
     * @var $loanService
     */
    private $_loanService;

    /**
     * Loan controller constructor.
     */
    public function __construct()
    {
        $this->_loanService = new LoanService();
    }
    
    public function __invoke(RepaymentRequest $request): Response
    {
        return $this->_loanService->loanRepayment($request);
    }

}
