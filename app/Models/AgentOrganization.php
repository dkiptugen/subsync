<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AgentOrganization extends Pivot
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $table = 'agent_organization';

    protected $guarded = [];
}
