<?php

namespace App\Http\Controllers;

use App\Events\GameStatus;
use App\Models\Game;
use App\Models\QuizMatch;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function status()
    {
        $game = Game::active()->first();
        if (! is_null($game)) {
            return [
                'scoreboard' => $game->match->getTotalScoreboard(),
                'match' => $game->match->info(),
                'status' => $game->status(),
                'game' => $game->info(),
            ];
        } else {
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
        }
    }

    public function create(Request $request)
    {
        if (! auth()->user()->admin) {
            return response('You can\'t ask questions, dude!', 403);
        }

        if (Game::active()->count() > 0) {
            return response('Game already started', 403);
        }

        $request->validate([
            'question' => 'required',
        ]);

        $match = QuizMatch::active()->first();
        if (is_null($match)) {
            $match = new QuizMatch();
            $match->save();
        }

        $game = new Game();
        $game->question = $request->question;
        $game->match()->associate($match);
        $game->author()->associate(auth()->user());
        $game->save();
        $game->refresh();

        broadcast(new GameStatus($game));

        return response('Game started!', 200);
    }

    public function over(Request $request)
    {
        $game = Game::active()->first();

        if (is_null($game)) {
            return response('Game is already over', 403);
        }

        $game->winned_by = $request->winner;
        $game->over = true;
        $game->save();

        broadcast(new GameStatus($game));

        return response('Game is over!', 200);
    }

    public function close()
    {
        $game = Game::active()->first();

        if (is_null($game)) {
            return response('Game is already closed', 403);
        }

        $game->active = false;
        $game->over = true;
        $game->save();

        broadcast(new GameStatus($game));

        return response('Game closed!', 200);
    }
}
