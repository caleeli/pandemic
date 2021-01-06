<?php

namespace App\Observers;

use App\Events\UpdateMap;

class City
{
    public function saved()
    {
        //UpdateMap::dispatch();
    }
}
