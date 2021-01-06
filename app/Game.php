<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    protected $guarded = [];
    protected $appends = [
        'cities',
        'players',
    ];

    protected static function booted()
    {
        static::created(function ($game) {
            foreach (City::all() as $city) {
                $state = new State([
                    'city_id' => $city->getKey(),
                    'game_id' => $game->getKey(),
                    'infection' => 0,
                ]);
                $state->save();
            }
        });
    }

    public function cities()
    {
        return $this->belongsToMany(City::class, 'states')
            ->withPivot('infection');
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
}