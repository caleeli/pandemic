<?php

namespace App\Jobs;

use App\Events\UpdateMap;
use App\Game;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunGame implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $gameId;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Game $game)
    {
        $this->gameId = $game->getKey();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $game = Game::find($this->gameId);
        if (!$game) {
            // Game was closed
            return;
        }
        // Game timeout
        if (now()->subMinutes(30)->greaterThan($game->created_at)) {
            $game->delete();
            return;
        }
        // No active players
        if (count($game->players) == 0) {
            $game->delete();
            return;
        }
        $game->cities->keyBy('id');
        $transmissions = [];
        foreach ($game->cities as $city) {
            $cuarentena = ($city->pivot->artifacts['cuarentena'] ?? 0) > $game->time;
            if ($city->pivot->infection > 0 && $city->pivot->infection < 10 && !$cuarentena && rand(1, 100) <= $game->infectividad) {
                $city->pivot->infection = min(10, $city->pivot->infection + 1);
            }
            if ($city->pivot->infection > 0 && rand(1, 100) > $game->resistencia) {
                $city->pivot->infection = max(0, $city->pivot->infection - 1);
            }
            // Transmision por fronteras
            $cerrarFronteras = ($city->pivot->artifacts['cerrarFronteras'] ?? 0) > $game->time;
            if ($cerrarFronteras) {
                continue;
            }
            for ($i = 0, $l = $city->pivot->infection * 1; $i < $l; $i++) {
                if (rand(1, 100) > $game->transmision) {
                    continue;
                }
                $index = $city->connections[array_rand($city->connections)];
                $c = $game->cities[$index];
                $cerrarFronteras = ($c->pivot->artifacts['cerrarFronteras'] ?? 0) > $game->time;
                if ($cerrarFronteras) {
                    continue;
                }
                $c->pivot->infection = min(10, $c->pivot->infection + 1);
                $c->pivot->save();
                $transmissions[] = [
                    'city1' => $city->id,
                    'city2' => $c->id,
                ];
            }
            $city->pivot->save();
        }
        $game->time++;
        $game->transmissions = $transmissions;
        if ($game->time % 5 === 0) {
            $game->addRandomCityToPlayers(1);
        }
        $game->save();
        UpdateMap::dispatch($game);
        ///
        $game->runGame();
    }
}
