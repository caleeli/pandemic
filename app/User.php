<?php

namespace App;

use App\Events\UpdateMap;
use App\Traits\HasMenus;
use App\Traits\ModelValidation;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasMenus;
    use Notifiable;
    use ModelValidation;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'avatar', 'role', 'provider', 'provider_id',
    ];

    protected $attributes = [
        'role' => 'admin',
        'owned_cities' => '[]',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'avatar' => 'array',
        'city_id' => 'int',
        'owned_cities' => 'array',
    ];

    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['password'] = Hash::make($value);
        }
    }

    public function roleObject()
    {
        return $this->belongsTo(Role::class, 'role', 'role');
    }

    public function validation()
    {
        return [
            'name' => 'required',
            'email' => 'required|email',
            'role' => 'required',
        ];
    }

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Ciudad donde se encuentra el usuario
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function resetGame()
    {
        if ($this->game) {
            //$this->game->delete();
        }
        $game = new Game();
        $game->save();
        $this->game_id = $game->getKey();
        $cities = [];
        foreach ($game->cities as $city) {
            $cities[] = $city->id;
            $city->pivot->infection = max(0, \random_int(-2, 2));
            $city->pivot->artifacts = [];
            $city->pivot->save();
        }
        $i = \random_int(0, count($game->cities)-1);
        $city = $game->cities[$i];
        $this->city_id = $city->getKey();
        $this->save();
        $game->runGame();
        foreach (User::all() as $user) {
            $user->game_id = $game->id;
            $owned_cities = [];
            for ($i=0; $i<3; $i++) {
                $idx = array_rand($cities);
                $owned_cities[] = $cities[$idx];
                \array_splice($cities, $idx, 1);
            }
            $user->owned_cities = $owned_cities;
            $user->save();
        }
        UpdateMap::dispatch($game);
        return [
            'game_id' => $game->getKey(),
        ];
    }

    /// HABILIDADES
    public function cerrarFronteras($city, $time)
    {
        if (!$this->game) {
            return;
        }
        $game = $this->game;
        $city = $game->cities->where('id', $city)->first();
        $a = $city->pivot->artifacts;
        $a['cerrarFronteras'] = $game->time + $time;
        $city->pivot->artifacts = $a;
        $city->pivot->save();
        UpdateMap::dispatch($game);
    }

    public function cuarentena($city, $time)
    {
        if (!$this->game) {
            return;
        }
        $game = $this->game;
        $city = $game->cities->where('id', $city)->first();
        $a = $city->pivot->artifacts;
        $a['cuarentena'] = $game->time + $time;
        $city->pivot->artifacts = $a;
        $city->pivot->save();
        UpdateMap::dispatch($this->game);
    }

    public function volarA($user, $city, $cobrar)
    {
        if (!$this->game) {
            return;
        }
        $user = User::find($user);
        if (!$cobrar || in_array($cobrar, $user->owned_cities)) {
            $user->city_id = $city;
            if ($cobrar) {
                $owned_cities = $user->owned_cities;
                array_splice($owned_cities, \array_search($cobrar, $owned_cities), 1);
                $user->owned_cities = $owned_cities;
            }
            $user->save();
            UpdateMap::dispatch($this->game);
        }
    }
}
