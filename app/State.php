<?php

namespace App;

use Illuminate\Database\Eloquent\Relations\Pivot;

class State extends Pivot
{
    protected $table = 'states';
    protected $fillable = [
        'infection',
        'artifacts',
    ];
    protected $attributes = [
        'artifacts' => '[]',
    ];
    protected $casts = [
        'infection' => 'int',
        'artifacts' => 'array',
    ];

    public function messageAddInfection($c)
    {
        $artifacts = $this->artifacts;
        $artifacts['addInfection'] = ($artifacts['addInfection'] ?? 0) + $c;
        $artifacts['message'] = "+" . $artifacts['addInfection'];
        $this->artifacts = $artifacts;
}
}
