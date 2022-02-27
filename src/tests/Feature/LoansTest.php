<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoansTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var userOne
     */
    private $_userOne;

    /**
     * @var userTwo
     */
    private $_userTwo;

    /**
     * @var adminUser
     */
    private $_adminUser;
    

    public function setUp(): void
    {       
        parent::setUp();
        // creating user
        $this->_userOne = $this->createUser();
        $this->_userTwo = $this->createUser();
        $this->_adminUser = $this->createUser(['is_admin' => true]);
    }
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_fetch_loan_list_with_admin_login()
    {
        //creating loan
        $this->createLoan(['user_id' => $this->_userOne->id]);
        $this->createLoan(['user_id' => $this->_userTwo->id]);

        $this->authUser($this->_adminUser);

        $response = $this->getJson(route('loan.list'))
                    ->assertOk()
                    ->json();

        $this->assertEquals(2, count($response['data']));
        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function test_fetch_loan_list_with_non_admin_user()
    {
        //creating loan
        $this->createLoan(['user_id' => $this->_userOne->id]);
        $this->authUser($this->_userOne);

        $response = $this->getJson(route('loan.list'))
                    ->assertOk()
                    ->json();
                    
        $this->assertEquals(1, count($response['data']));
        $this->assertEquals('SUCCESS', $response['status']);
    }

    public function test_fetch_user_two_loan_details()
    {
        //creating loan
        $loan = $this->createLoan(['user_id' => $this->_userTwo->id]);
        $this->authUser($this->_userTwo);

        $response = $this->getJson(route('loan.show', $loan->id))
                    ->assertOk()
                    ->json();
                    
        $this->assertEquals('SUCCESS', $response['status']);
        $this->assertEquals($this->_userTwo->id, $response['data']['user_id']);
    }

    public function test_user_one_try_to_fetch_another_user_loan_details()
    {
        $loan = $this->createLoan(['user_id' => $this->_userTwo->id]);
        $this->authUser($this->_userOne);

        $response = $this->getJson(route('loan.show', $loan->id))
                    ->assertOk()
                    ->json();
        
        $this->assertEquals('FAIL', $response['status']);
        $this->assertEquals(0, count($response['data']));
    }

    public function test_user_apply_for_loan_without_amount_validation()
    {
        $this->withExceptionHandling();

        $this->authUser($this->_userOne);

        $response = $this->postJson(route('loan.apply'), [
            'loan_term' => 4
        ])->assertStatus(422)
        ->json();

        $this->assertArrayHasKey('amount', $response['errors']);
    }

    public function test_user_one_apply_for_loan()
    {
        $this->authUser($this->_userOne);

        $response = $this->postJson(route('loan.apply'), [
            'amount' => 40,
            'loan_term' => 4
        ])->assertCreated()
        ->json();

        $this->assertEquals('SUCCESS', $response['status']);
        $this->assertDatabaseHas('loans', ['user_id' => $this->_userOne->id, 'amount' => 40, 'loan_term' => 4]);
    }

    public function test_admin_approve_user_loan()
    {
        $loan = $this->createLoan(['user_id' => $this->_userTwo->id]);
        $this->authUser($this->_adminUser);
        
        $response = $this->patchJson(route('loan.approve', ['loan_id' => $loan->id]))
                    ->assertOk()
                    ->json();

        $this->assertEquals('SUCCESS', $response['status']);
        $this->assertDatabaseHas('loans', ['user_id' => $loan->user_id, 'status' => 'approved']);
        $this->assertDatabaseCount('repayment_schedules', $loan->loan_term);
    }

    public function test_a_non_admin_user_try_to_approve_loan()
    {
        $loan = $this->createLoan(['user_id' => $this->_userTwo->id]);
        $this->authUser($this->_userOne);
        
        $response = $this->patchJson(route('loan.approve', ['loan_id' => $loan->id]))
                    ->assertUnauthorized()
                    ->json();
        
        $this->assertEquals('FAIL', $response['status']);
    }

}
