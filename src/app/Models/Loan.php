<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'loan_term',
        'status',
        'approved_by',
        'approved_at'
    ];

    public function repaymentSchedules(): HasMany
    {
        return $this->hasMany(RepaymentSchedule::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function approver(): BelongsTo {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }
}
