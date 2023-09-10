<?php

namespace Tests\Feature;

use App\Models\Loan;
use App\Models\RepaymentSchedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoanRepaymentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var user
     */
    private $_user;

    /**
     * @var adminUser
     */
    private $_adminUser;

    /**
     * @var userLoan
     */
    private $_userLoan;

    public function setUp(): void
    {
        parent::setUp();

        $this->_user = $this->createUser();
        $this->_adminUser = $this->createUser(['is_admin' => true]);

        $this->_userLoan = $this->createLoan(['user_id' => $this->_user->id]);

        $this->authUser($this->_adminUser);
        $this->postJson(route('loan.approve', ['loan_id' => $this->_userLoan->id]));
    }
    
    public function test_user_repay_first_term_amount()
    {
        $this->authUser($this->_user);
        $amount = sprintf('%0.2f', $this->_userLoan->amount / $this->_userLoan->loan_term);

        $this->postJson(route('loan.repayment'), ['amount' => $amount, 'loan_id' => $this->_userLoan->id])
                    ->assertOk()
                    ->json();

        $repayment = RepaymentSchedule::where('loan_id', $this->_userLoan->id)->first();

        $this->assertEquals(sprintf('%0.2f', $repayment->repayment_amount), $amount);
        $this->assertEquals('paid', $repayment->status);
    }

    public function test_user_repay_all_term_amount()
    {
        $this->authUser($this->_user);

        $termAmount = round($this->_userLoan->amount / $this->_userLoan->loan_term, 2);

        for($i = 0; $i < $this->_userLoan->loan_term; $i++) {
            $response = $this->postJson(route('loan.repayment'), ['amount' => sprintf('%0.2f', $termAmount), 'loan_id' => $this->_userLoan->id])
            ->assertOk()
            ->json();
        }
        $loan = Loan::find($this->_userLoan->id)->first();
        
        $this->assertDatabaseHas('loans', ['id' => $this->_userLoan->id, 'status' => 'paid']);
    }

    public function test_user_repay_less_term_amount()
    {
        $this->authUser($this->_user);
        $amount = round($this->_userLoan->amount / $this->_userLoan->loan_term, 2);

        $response = $this->postJson(route('loan.repayment'), ['amount' => sprintf('%0.2f', $amount - 2), 'loan_id' => $this->_userLoan->id])
                    ->assertOk()
                    ->json();

        $this->assertEquals('failed', $response['status']);
        $this->assertEquals('Loan amount is insufficient for repayment.', $response['message']);

    }
    
    public function test_user_try_to_pay_other_user_term_amount()
    {
        $this->withExceptionHandling();

        $user = $this->createUser();
        $this->authUser($user);
        $amount = round($this->_userLoan->amount / $this->_userLoan->loan_term, 2);

        $response = $this->postJson(route('loan.repayment'), ['amount' => sprintf('%0.2f', $amount), 'loan_id' => $this->_userLoan->id])
                    ->assertNotFound()
                    ->json();
                
        $this->assertEquals('failed', $response['status']);
    }
}
