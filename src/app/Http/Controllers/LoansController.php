<?php

namespace App\Http\Controllers;

use App\Services\LoanService;
use App\Http\Requests\LoanRequest;
use App\Http\Requests\LoanApproveRequest;
use Illuminate\Http\Response;

class LoansController extends Controller
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(): Response
    {
        return $this->_loanService->getAllLoans();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(LoanRequest $request): Response
    {
        return $this->_loanService->loanRequest($request);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id): Response
    {
        return $this->_loanService->fetchLoan($id);
    }

    /**
     * Approve the loan request
     * 
     * @param LoanApproveRequest $request
     * @return \Illuminate\Http\Response
     */
    public function approve(LoanApproveRequest $request): Response
    {
        return $this->_loanService->approveLoan($request);
    }
}
