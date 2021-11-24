<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConversationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('conversations', function (Blueprint $table) {
      $table->id();
      $table->foreignId('chat_room_id')
        ->constrained('chat_rooms')
        ->cascadeOnUpdate()
        ->cascadeOnDelete();
      $table->text('message')->nullable();
      $table->tinyInteger('read_status')
        ->default(0)
        ->comment('0 = unread, 1 = read');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('conversations');
  }
}
