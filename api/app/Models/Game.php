<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'active',
        'question',
        'created_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function match()
    {
        return $this->belongsTo(QuizMatch::class, 'match_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winned_by');
    }

    public function scopeActive($query)
    {
        return $query->with('author')->where('active', true)->orderByDesc('updated_at');
    }

    public function reservations()
    {
        return $this->hasMany(Answer::class)->whereNull('correct')->orderBy('updated_at');
    }

    public function answers()
    {
        return $this->hasMany(Answer::class)->whereNotNull('correct')->orderByDesc('updated_at');
    }

    public function status()
    {
        if (! $this->active) {
            return 'no_game';
        }

        if (! $this->over) {
            if (count($this->reservations) == 0) {
                return 'game_started';
            } else {
                if (is_null($this->reservations[0]->text)) {
                    return 'answer_reserved';
                } else {
                    return 'answer_given';
                }
            }
        } else {
            if (is_null($this->winner)) {
                return 'game_over';
            } else {
                if (! is_null($this->match->winner)) {
                    return 'match_winned';
                }

                return 'game_winned';
            }
        }
    }

    public function info()
    {
        $this->load(['answers', 'winner', 'author']);

        $history = [];
        $this->reservations->each(function ($a) use (&$history) {
            $history[] = $a->info();
        });
        $this->answers->each(function ($a) use (&$history) {
            $history[] = $a->info();
        });

        $data = [
            'question' => $this->question,
            'winner' => ! is_null($this->winner) ? [
                'id' => $this->winner->id,
                'name' => $this->winner->name,
            ] : null,
            'created_at' => $this->created_at,
            'author' => [
                'name' => $this->author->name,
            ],
            'answers' => $history,
            'blocked' => $this->answers()->where('correct', false)->pluck('created_by'),
        ];

        return $data;
    }
}
