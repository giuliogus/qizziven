<?php

use App\Models\QuizMatch;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('quiz', function (User $user): bool|array {
    if (Auth::check()) {
        $match = QuizMatch::active()->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'admin' => $user->admin,
            'games' => 0,
            'matches' => 0,
        ];
    } else {
        return false;
    }
});
