<?php

namespace OGame;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserTech extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users_tech';

    /**
     * Get the user that owns this tech record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo('OGame\User');
    }
}
