<?php

use App\Events\LoanApplicationApproved;
use App\Events\NewLoanApplication;
use App\Events\RepaymentSuccessful;
use App\Models\Loan;
use App\Models\RepaymentSchedule;
use App\Models\User;
use App\Services\LoanService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LoanServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $_loanService;
    protected $_user;

    protected $_admin;

    public function setUp(): void
    {
        parent::setUp();
        $this->_loanService = new LoanService();
        $this->_user = User::factory()->create();
        $this->_admin = User::factory()->create(['is_admin' => true]);
    }

    public function testGetMyLoan()
    {
        Auth::login($this->_user);

        // Create some loans for the user and admin
        Loan::factory()->times(3)->create(['user_id' => $this->_user->id]);
        Loan::factory()->times(2)->create(['user_id' => $this->_admin->id]);

        $loans = $this->_loanService->getMyLoan();

        // Assert that the number of loans returned is 3 (only user's loans, not admin's)
        $this->assertCount(3, $loans);
    }

    public function testGetLoanDetails()
    {
        Auth::login($this->_user);

        // Create a loan for the user
        $loan = Loan::factory()->create(['user_id' => $this->_user->id]);

        // Call the getLoanDetails method with the loan ID
        $result = $this->_loanService->getLoanDetails($loan->id);

        // Assert that the returned loan matches the created loan
        $this->assertEquals($loan->id, $result->id);
    }

    public function testNewLoanRequest()
    {
        Event::fake();
        Auth::login($this->_user);

        // Create loan data
        $loanData = [
            'amount' => 1000,
            'loan_term' => 12,
            'status' => 'pending',
        ];

        // Call the newLoanRequest method
        $loan = $this->_loanService->newLoanRequest($loanData);

        // Assert that the loan is created and belongs to the user
        $this->assertDatabaseHas('loans', [
            'id' => $loan->id,
            'user_id' => $this->_user->id,
            'amount' => $loanData['amount'],
            'loan_term' => $loanData['loan_term'],
            'status' => $loanData['status'],
        ]);

        // Assert that the NewLoanApplication event was dispatched
        Event::assertDispatched(NewLoanApplication::class, function ($event) use ($loan) {
            return $event->loanDetails->id === $loan->id;
        });
    }

    public function testApproveLoan()
    {
        Event::fake();
        Auth::login($this->_admin);

        // Create a loan with 'pending' status
        $loan = Loan::factory()->create(['status' => 'pending']);

        // Call the approveLoan method
        $approvedLoan = $this->_loanService->approveLoan($loan->id);

        $this->assertEquals('approved', $approvedLoan->status);
        $this->assertEquals($this->_admin->id, $approvedLoan->approved_by);
        $this->assertNotNull($approvedLoan->approved_at);
        $this->assertCount($loan->loan_term, $approvedLoan->repaymentSchedules);

        // Assert that LoanApplicationApproved event is dispatched
        Event::assertDispatched(LoanApplicationApproved::class, function ($event) use ($approvedLoan) {
            return $event->loanDetails->id === $approvedLoan->id;
        });
    }

    public function testLoanRepayment()
    {
        Event::fake();

        // Apply for loan
        Auth::login($this->_user);

        // Create a loan for the user with 'approved' status
        $loan = Loan::factory()->create([
            'status' => 'pending',
            'user_id' => $this->_user->id,
        ]);

        // loan approved by admin
        Auth::login($this->_admin);
        $this->_loanService->approveLoan($loan->id);

        // customer repay first EMI
        Auth::login($this->_user);
        $repaidLoan = $this->_loanService->loanRepayment($loan->id, $loan->amount / $loan->loan_term);

        $this->assertEquals('approved', $repaidLoan->status);
        $this->assertEquals('paid', $repaidLoan->repaymentSchedules->first()->status);

        // Assert that RepaymentSuccessful event is dispatched
        Event::assertDispatched(RepaymentSuccessful::class, function ($event) use ($repaidLoan) {
            return $event->loanDetails->id === $repaidLoan->id;
        });
    }

    public function testApproveNonPendingLoan()
    {
        Auth::login($this->_admin);

        // Create a loan with 'approved' status
        $loan = Loan::factory()->create(['status' => 'approved']);

        // Call the approveLoan method on a non-pending loan
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Requested loan is either approved or paid.');
        $this->_loanService->approveLoan($loan->id);
    }

    public function testApproveNonExistentLoan()
    {
        Auth::login($this->_admin);

        // Loan ID that doesn't exist
        $loanId = 999;

        // Call the approveLoan method on a non-existent loan
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to get requested loan details.');
        $this->_loanService->approveLoan($loanId);
    }

    public function testLoanRepaymentWithInvalidLoanId()
    {
        Auth::login($this->_user);

        // Loan ID that doesn't exist
        $loanId = 999;

        // Call the loanRepayment method with an invalid loan ID
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unable to get requested loan details.');
        $this->_loanService->loanRepayment($loanId, 100);
    }

    public function testLoanRepaymentWithInsufficientAmount()
    {
        Auth::login($this->_user);

        // Create a loan for the user with 'approved' status
        $loan = Loan::factory()->create([
            'status' => 'approved',
            'user_id' => $this->_user->id,
        ]);

        // Call the loanRepayment method with an insufficient amount
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Loan amount is insufficient for repayment');
        $this->_loanService->loanRepayment($loan->id, $loan->amount / $loan->loan_term - 1);
    }

    public function testLoanRepaymentWithNegativeAmount()
    {
        Auth::login($this->_user);

        // Create a loan for the user with 'approved' status
        $loan = Loan::factory()->create([
            'status' => 'approved',
            'user_id' => $this->_user->id,
        ]);

        // Call the loanRepayment method with a negative amount
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Please provide valid loan ID and amount.');
        $this->_loanService->loanRepayment($loan->id, -100);
    }

    public function testLoanRepaymentWithInvalidLoanStatus()
    {
        Auth::login($this->_user);

        // Create a loan for the user with 'paid' status
        $loan = Loan::factory()->create([
            'status' => 'paid',
            'user_id' => $this->_user->id,
        ]);

        // Call the loanRepayment method on a paid loan
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Loan is either not approved or fully paid.');
        $this->_loanService->loanRepayment($loan->id, 100);
    }

    public function testGetLoanDetailsWithDifferentUser()
    {
        Auth::login($this->_user);
        $user2 = User::factory()->create();

        // Create a loan for user1 with 'approved' status
        $loan1 = Loan::factory()->create([
            'status' => 'approved',
            'user_id' => $user2->id,
        ]);

        // Attempt to retrieve loan details of user1
        $loanDetails = $this->_loanService->getLoanDetails($loan1->id);
        $this->assertEquals(null, $loanDetails);
    }

}
