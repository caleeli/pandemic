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
        \Log::info('trigger RunGame');
        $this->gameId = $game->getKey();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \Log::info('handle RunGame');
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
        foreach ($game->cities as $city) {
            $artifacts = $city->pivot->artifacts ?? [];
            if ($city->pivot->infection > 0 && $city->pivot->infection < 10) {
                $city->pivot->infection = min(10, $city->pivot->infection + 1);
                $artifacts['message'] = '+';
            }
            $rnd = \random_int(0, $city->pivot->infection);
            if ($rnd > 3) {
                $index = $city->connections[array_rand($city->connections)];
                $c = $game->cities[$index];
                $c->pivot->infection = min(10, $c->pivot->infection + 1);
                $c->pivot->save();
            }
            $city->pivot->artifacts = $artifacts;
            $city->pivot->save();
        }
        $game->time++;
        $game->save();
        UpdateMap::dispatch($game);
        ///
        $game->runGame();
    }
}
