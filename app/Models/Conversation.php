<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
  use HasFactory;

  protected $table = 'conversations';
  protected $fillable = ['chat_room_id', 'message', 'read_status'];

  public function chatRoom()
  {
    return $this->belongsTo(ChatRoom::class, 'chat_room_id');
  }
}
