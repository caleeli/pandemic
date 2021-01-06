<?php

namespace App;

use App\Events\UpdateMap;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $guarded = [];

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
            UpdateMap::dispatch();
            $res = $closest->toArray();
            return $res;
        } else {
            $city = new City(compact('x', 'y', 'color'));
            $city->save();
            UpdateMap::dispatch();
            return $city->toArray();
        }
    }

    public function resetGame()
    {
        City::update([
            'infection' => 0,
        ]);
    }
}
