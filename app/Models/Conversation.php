<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\TextUI\XmlConfiguration\Groups;

class Conversation extends Model
{
    use HasFactory;

    protected $table = 'conversations';
    // type 0 : single chat; 1 group chat
    protected $fillable = [
        'chat_id', 'user_id', 'group_id', 'own_id', 'type', 'is_read', 'is_delete'
    ];

    public function chat(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Chat::class, 'chat_id');
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function own(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'own_id');
    }

    public function group(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Groups::class, 'group_id');
    }
}
