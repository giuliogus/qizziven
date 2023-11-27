<?php

namespace App\Events;

use App\Models\Game;
use App\Models\QuizMatch;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class GameStatus implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;

    /**
     * Create a new event instance.
     */
    public function __construct(Game $game)
    {
        $this->game = $game;
    }

    public function broadcastWith()
    {
        if (! $this->game->active && $this->game->over) {
            $match = QuizMatch::active()->first();
            $matchModel = is_null($match) ? new QuizMatch() : $match;

            return [
                'scoreboard' => $matchModel->getTotalScoreboard(),
                'match' => ! is_null($match) ? $match->info() : null,
                'status' => 'no_game',
                'game' => [
                    'answers' => [],
                    'blocked' => [],
                ],
            ];
        } else {
            return [
                'scoreboard' => $this->game->match->getTotalScoreboard(),
                'match' => $this->game->match->info(),
                'status' => $this->game->status(),
                'game' => $this->game->info(),
            ];
        }
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('quiz'),
        ];
    }
}
