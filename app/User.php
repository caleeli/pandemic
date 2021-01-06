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
        $game = $this->game;
        if (!$game) {
            $game = new Game();
            $game->save();
            $this->game_id = $game->getKey();
        }
        foreach ($game->cities as $city) {
            $city->pivot->infection = max(0, \random_int(-2, 2));
            $city->pivot->save();
        }
        $i = \random_int(0, count($game->cities)-1);
        $city = $game->cities[$i];
        $this->city_id = $city->getKey();
        $this->save();
        UpdateMap::dispatch();
    }
}
