<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChatRoom extends Model
{
  use HasFactory, SoftDeletes;

  protected $table = 'chat_rooms';
  protected $fillable = ['sender_id', 'receiver_id'];
  protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

  public function conversations()
  {
    return $this->hasMany(Conversation::class, 'chat_room_id');
  }

  public function sender()
  {
    return $this->belongsTo(User::class, 'sender_id');
  }

  public function receiver()
  {
    return $this->belongsTo(User::class, 'receiver_id');
  }
}
