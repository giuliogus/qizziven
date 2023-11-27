<?php

namespace App\Http\Controllers;

use App\Events\GameStatus;
use App\Models\Answer;
use App\Models\Game;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function create()
    {
        $game = Game::active()->first();
        if (is_null($game)) {
            return response('Game ended!', 404);
        }

        $answer = new Answer();
        $answer->author()->associate(auth()->user());
        $answer->game()->associate($game);
        $answer->save();

        $game->refresh();

        broadcast(new GameStatus($game));

        return response('Answer reserved!', 200);
    }

    public function update(Request $request)
    {
        $game = Game::active()->first();
        if (is_null($game)) {
            return response('Game ended!', 404);
        }

        $answer = $game->reservations()
            ->where('created_by', auth()->user()->id)
            ->orderBy('created_at')
            ->first();

        if (is_null($answer)) {
            return response('Answer not found!', 404);
        }

        $answer->text = $request->answer;
        $answer->save();
        $game->refresh();

        broadcast(new GameStatus($game));

        return response('Answer inserted!', 200);
    }

    public function evaluate(Request $request)
    {
        $game = Game::active()->first();
        if (is_null($game)) {
            return response('Game ended!', 404);
        }

        $answer = $game->reservations()->where('id', $request->answer)->first();
        if (is_null($answer)) {
            return response('Answer not found!', 404);
        }

        if ($request->correct) {
            $game->winner()->associate($answer->author);
            $game->over = true;
            $game->save();
            $scores = $game->match->scoreboard();
            if (count($scores) > 0 && $scores[0]->games >= env('GAMES_PER_MATCH')) {
                $game->match->active = false;
                $game->match->winned_by = $scores[0]->id;
                $game->match->save();
            }
        }
        $answer->correct = $request->correct;
        $answer->save();
        $game->refresh();

        broadcast(new GameStatus($game));

        return response('Answer evaluated!', 200);
    }
}
