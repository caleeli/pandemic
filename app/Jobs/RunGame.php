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
            $rnd = \random_int(0, 2);
            if ($rnd) {
                continue;
            }
            $cuarentena = ($city->pivot->artifacts['cuarentena'] ?? 0) > $game->time;
            if ($city->pivot->infection > 0 && $city->pivot->infection < 10 && !$cuarentena) {
                $city->pivot->infection = min(10, $city->pivot->infection + 1);
                $city->pivot->save();
            }
            // Transmision por fronteras
            if (!empty($city->pivot->artifacts['cerrarFronteras'])) {
                continue;
            }
            for ($i = 0, $l = $city->pivot->infection * 1; $i < $l; $i++) {
                $rnd = \random_int(0, 2);
                if ($rnd) {
                    continue;
                }
                $index = $city->connections[array_rand($city->connections)];
                $c = $game->cities[$index];
                if (!empty($c->pivot->artifacts['cerrarFronteras'])) {
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
        $game->save();
        UpdateMap::dispatch($game);
        ///
        $game->runGame();
    }
}
