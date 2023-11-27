<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    use HasFactory;

    protected $with = ['author:id,name'];

    protected $fillable = [
        'text',
        'game_id',
        'correct',
        'created_by',
    ];

    protected $casts = [
        'correct' => 'boolean',
    ];

    public function game()
    {
        return $this->belongsTo(Game::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function info()
    {
        return [
            'id' => $this->id,
            'text' => $this->text,
            'correct' => $this->correct,
            'updated_at' => $this->updated_at,
            'author' => [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ],
        ];
    }
}
