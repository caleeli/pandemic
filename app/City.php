<?php

namespace App;

use App\Events\UpdateMap;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class City extends Model
{
    protected $guarded = [];
    protected $attributes = [
        'connections' => '[]',
    ];
    protected $casts = [
        'x' => 'int',
        'y' => 'int',
        'points' => 'int',
        'connections' => 'array',
    ];
    protected $appends = [
        //'state',
    ];

    public function addCity($x, $y, $color)
    {
        $closest = City::whereRaw('((x-:x) * (x-:x) + (y-:y) * (y-:y)) <= 94', compact('x', 'y'))
            ->orderByRaw('((x-:x) * (x-:x) + (y-:y) * (y-:y)) ASC')
            ->first();
        if ($closest) {
            $closest->x = round(($closest->x + $x) / 2);
            $closest->y = round(($closest->y + $y) / 2);
            $closest->points = \min(6, $closest->points + 1);
            $closest->save();
            $res = $closest->toArray();
            return $res;
        } else {
            $city = new City(compact('x', 'y', 'color'));
            $city->save();
            return $city->toArray();
        }
    }
}
