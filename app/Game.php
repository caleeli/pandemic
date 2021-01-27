<?php

namespace App;

use App\Jobs\RunGame;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = [];
    protected $attributes = [
        'transmissions' => '[]',
        'type' => 'demo',
    ];
    protected $appends = [
        'cities',
        'players',
    ];
    protected $casts = [
        'transmissions' => 'array',
        'propiedades' => 'array',
    ];

    protected static function booted()
    {
        static::saving(function (Game $game) {
            $fnType = "fn{$game->type}";
            $game->$fnType();
        });
        static::created(function ($game) {
            foreach (City::all() as $city) {
                $state = new State([
                    'infection' => 0,
                ]);
                $state->city_id = $city->getKey();
                $state->game_id = $game->getKey();
                $state->save();
            }
        });
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'states')
            ->using(State::class)
            ->withPivot('infection', 'artifacts');
    }

    public function states()
    {
        return $this->hasMany(State::class);
    }

    public function players()
    {
        return $this->hasMany(User::class);
    }

    public function getCitiesAttribute()
    {
        return $this->cities()->get();
    }

    public function getPlayersAttribute()
    {
        return $this->players()->get();
    }

    public function runGame()
    {
        RunGame::dispatch($this)->delay(now()->addSeconds(6));
    }

    public function addRandomCityToPlayers($count = 1, $clear = false, $max = 5)
    {
        $cities = [];
        foreach ($this->cities as $city) {
            if (empty($city->pivot->artifacts['owner'])) {
                $cities[] = $city;
            }
        }
        for ($i=0; $i < $count; $i++) {
            foreach ($this->players as $player) {
                if ($i === 0 && $clear) {
                    $player->owned_cities = [];
                }
                if (count($player->owned_cities) >= $max) {
                    continue;
                }
                while (true) {
                    $index = array_rand($cities);
                    $city = $cities[$index];
                    if ($player->addOwnedCity($city)) {
                        $player->save();
                        $city->pivot->save();
                        \array_splice($cities, $index, 1);
                        break;
                    }
                }
            }
        }
    }

    public function fnDemo()
    {
        $this->infectividad = min(100, 20 + $this->time);
        $this->resistencia = min(100, 60 + $this->time);
        $this->transmision = min(100, 50 + $this->time);
    }

    public function addPropiedad($key, $value)
    {
        $propiedades = $this->propiedades;
        $propiedades[$key] = $value;
        $this->propiedades = $propiedades;
    }
}
