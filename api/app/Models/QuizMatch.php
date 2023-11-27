<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class QuizMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('active', true)->orderByDesc('updated_at');
    }

    public function games()
    {
        return $this->hasMany(Game::class, 'match_id')->orderByDesc('created_at');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winned_by');
    }

    public function info()
    {
        $this->load(['winner']);
        $winner = ! is_null($this->winner) ? [
            'id' => $this->winner->id,
            'name' => $this->winner->name,
        ] : null;
        $data = [
            'winner' => $winner,
            'scoreboard' => $this->scoreboard(),
        ];

        return $data;
    }

    public function scoreboard()
    {
        $query = "with wins as (
            select
                winned_by as id,
                1 as wins
            from
                games
            where
                match_id = {$this->id}
                and winned_by is NOT NULL
            )
            select
                id,
                SUM(wins) as games
            from
                wins
            group by
                id";

        $expression = DB::raw($query);

        return DB::select($expression->getValue(DB::connection()->getQueryGrammar()));
    }

    public function getTotalScoreboard()
    {
        $query = '
            with wins as (
                select
                        winned_by as id,
                        1 as wins
                from
                        matches
                where
                    winned_by is NOT NULL
                )
            select
                id,
                SUM(wins) as wins
            from
                wins
            group by id
            order by wins desc
        ';

        $expression = DB::raw($query);

        return DB::select($expression->getValue(DB::connection()->getQueryGrammar()));
    }
}
