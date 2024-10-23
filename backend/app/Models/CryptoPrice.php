<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CryptoPrice extends Model
{
    protected $table = 'crypto_prices';

    // Disable auto-incrementing ID
    public $incrementing = false;

    // Specify the primary key
    protected $primaryKey = ['symbol', 'last_updated_at'];

    // Disable timestamps if you do not need them (optional)
    public $timestamps = true;

    // Mass-assignable attributes
    protected $fillable = ['symbol', 'price', 'last_updated_at'];

    // Specify that 'last_updated_at' should be cast to a datetime
    protected $casts = [
        'last_updated_at' => 'datetime',
    ];

    /**
     * Override the default behavior of the primary key.
     */
    public function setKeysForSaveQuery($query)
    {
        $query->where('symbol', $this->getAttribute('symbol'))
              ->where('last_updated_at', $this->getAttribute('last_updated_at'));

        return $query;
    }
}
