<?php

namespace Banjarmasinkota\PintarSSO\Traits;

use App\Models\PintarAccount;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait HasPintarAccount
{
    public function pintar_account(): HasOne
    {
       return $this->hasOne(PintarAccount::class);
    }
}
