<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ExternalAccount
 *
 * @package App\Models
 */
class ExternalAccount extends Model
{
    use HasTimestamps;

    protected $table = 'external_accounts';
    /**
     * @var array
     */
    protected $fillable = [
        'user_id',
        'name',
        'scopes',
        'provider_name',
        'provider_id',
        'token',
        'secret',
        'refresh_token',
        'expires_at'
    ];

    protected $casts = [
        'expires_at' => 'datetime'
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
