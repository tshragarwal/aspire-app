<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class LoanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $loanDetails = [
            'loan_id' => $this->id,
            'amount' => $this->amount,
            'term' => $this->loan_term,
            'status' => $this->status,
        ];

        if($this->status !== 'pending') {
            $loanDetails['approved_on'] = $this->approved_at;
            
            if(Auth::user()->is_admin){
                $loanDetails['approved_by'] = [
                    'approver_id' => $this->approved_by,
                    'approver_name' => $this->approver->name
                ];
            }

            $loanDetails['repayment_schedule'] = LoanRepaymentResource::collection($this->repaymentSchedules);
        }
        $loanDetails['customer_details'] = [
            'customer_id' => $this->user_id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];

        return $loanDetails;
    }
}
