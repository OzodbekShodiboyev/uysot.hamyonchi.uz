<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSequence extends Model
{
    public $timestamps = false;

    protected $fillable = ['block_id', 'year', 'last_sequence'];

    public function block(): BelongsTo
    {
        return $this->belongsTo(Block::class);
    }
}
