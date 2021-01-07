<?php

namespace App;

use App\Jobs\RunGame;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = [];
    protected $attributes = [
        'transmissions' => '[]',
    ];
    protected $appends = [
        'cities',
        'players',
    ];
    protected $casts = [
        'transmissions' => 'array',
    ];

    protected static function booted()
    {
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
        RunGame::dispatch($this)->delay(now()->addSeconds(5));
    }
}
