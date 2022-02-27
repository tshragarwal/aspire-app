<?php

namespace Tests\Unit;

use App\Models\Loan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_has_many_loan()
    {
        $user = $this->createUser();
        $loan = $this->createLoan(['user_id' => $user->id]);

        $this->assertInstanceOf(Loan::class, $user->loans->first());
    }
}
