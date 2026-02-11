<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GeminiChatHistory extends Model
{
    protected $table = 'gemini_chat_history';

    protected $fillable = [
        'usuario_id',
        'mode',
        'messages',
    ];

    protected $casts = [
        'messages' => 'array',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class);
    }
}
