<?php

namespace Tests\Feature;

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
        $this->patchJson(route('loan.approve', ['loan_id' => $this->_userLoan->id]));
    }
    
    public function test_user_repay_first_term_amount()
    {
        $this->authUser($this->_user);

        $response = $this->postJson(route('loan.repayment'), ['amount' => $this->_userLoan->amount / $this->_userLoan->loan_term, 'loan_id' => $this->_userLoan->id])
                    ->assertOk()
                    ->json();

        $this->assertDatabaseHas('repayment_schedules', ['loan_id' => $this->_userLoan->id, 'status' => 'paid']);
    }

    public function test_user_repay_all_term_amount()
    {
        $this->authUser($this->_user);

        $termAmount = $this->_userLoan->amount / $this->_userLoan->loan_term;

        for($i = 0; $i < $this->_userLoan->loan_term; $i++) {
            
            $response = $this->postJson(route('loan.repayment'), ['amount' => $termAmount, 'loan_id' => $this->_userLoan->id])
            ->assertOk()
            ->json();
        }
        
        $this->assertDatabaseHas('loans', ['id' => $this->_userLoan->id, 'status' => 'paid']);
    }

    public function test_user_repay_less_term_amount()
    {
        $this->authUser($this->_user);

        $response = $this->postJson(route('loan.repayment'), ['amount' => ($this->_userLoan->amount / $this->_userLoan->loan_term) - 2, 'loan_id' => $this->_userLoan->id])
                    ->assertOk()
                    ->json();

        $this->assertEquals('FAIL', $response['status']);
        $this->assertEquals('Repayment amount is insufficient.', $response['message']);

    }
    
    public function test_user_try_to_pay_other_user_term_amount()
    {
        $this->withExceptionHandling();

        $user = $this->createUser();
        $this->authUser($user);

        $response = $this->postJson(route('loan.repayment'), ['amount' => $this->_userLoan->amount / $this->_userLoan->loan_term, 'loan_id' => $this->_userLoan->id])
                    ->assertUnauthorized()
                    ->json();
                
        $this->assertEquals('FAIL', $response['status']);
    }
}
