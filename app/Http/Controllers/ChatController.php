<?php

namespace App\Http\Controllers;

use App\Models\ChatRoom;
use App\Models\Conversation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ChatController extends Controller
{
  /**
   * get list conversation
   * @return \Illuminate\Http\JsonResponse
   */
  public function listOfConversation(): \Illuminate\Http\JsonResponse
  {
    $userId = Auth::id();
    $chatRooms = ChatRoom::where('sender_id', $userId)
      ->orWhere('receiver_id', $userId)
      ->get();

    $data = [];
    foreach ($chatRooms as $chatRoom) {
      $conversation = Conversation::where('chat_room_id', $chatRoom->id);

      if ($userId == $chatRoom->sender_id) {
        $user = User::where('id', $chatRoom->receiver_id)->first();
      } else {
        $user = User::where('id', $chatRoom->sender_id)->first();
      }
      $data[] = [
        'list_conversation' => $user->name,
        'last_message' => $conversation->orderBy('id', 'desc')->first()->message,
        'unread_message' => $conversation->where('read_status', 0)->count()
      ];
    }

    return ResponseFormatter::success($data, "Success get data");
  }


  /**
   * send message to receiver
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function sendMessage(Request $request): \Illuminate\Http\JsonResponse
  {
    $validate = Validator::make($request->all(), [
      'phone_number_receiver' => 'required|regex:/^[0-9]*$/',
      'message' => 'required',
    ]);

    /* check if phone number and message is empty */
    if ($validate->fails()) {
      return ResponseFormatter::error(null, $validate->errors(), 500);
    }

    $phoneNumberReceiver = $request->phone_number_receiver;
    $message = $request->message;
    $senderId = Auth::id();
    $receiver = User::where('phone_number', $phoneNumberReceiver)->first();
    $chatRoomId = null;

    /* check if user send to yourself */
    if ($phoneNumberReceiver == Auth::user()->phone_number) {
      return ResponseFormatter::error(null, "Sorry, you cannot send to yourself");
    }

    /* check receiver by phone number is empty or not */
    if (is_null($receiver)) {
      return ResponseFormatter::error(null, "User not found", 404);
    }

    $chatRoom = ChatRoom::where('sender_id', $senderId)
      ->where('receiver_id', $receiver->id)
      ->first();

    /* check chat room is null or not */
    if (is_null($chatRoom)) {
      $chatRoom = ChatRoom::where('sender_id', $receiver->id)
        ->where('receiver_id', $senderId)
        ->first();

      /* check if user will to reply message or not */
      if (optional($chatRoom)->receiver_id == Auth::id()) {
        $chatRoomId = $chatRoom->id;
      } else {
        /* create chat room */
        $chatRoom = ChatRoom::create([
          'sender_id' => $senderId,
          'receiver_id' => $receiver->id
        ]);
        $chatRoomId = $chatRoom->id;
      }
    }

    $differentId = (!is_null($chatRoom)) ? $chatRoom->id : $chatRoomId;
    /* update conversation read status */
    Conversation::where('chat_room_id', $differentId)
      ->orderBy('id', 'desc')
      ->update(['read_status' => 1]);


    /* create conversation */
    $conversation = Conversation::create([
      'chat_room_id' => $differentId,
      'message' => $message
    ]);

    if ($conversation) {
      return ResponseFormatter::success($conversation, "Message has been sent successfully");
    } else {
      return ResponseFormatter::error(null, "Message failed to send");
    }
  }

  /**
   * reply message
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function replyMessage(Request $request): \Illuminate\Http\JsonResponse
  {
    $validate = Validator::make($request->all(), [
      'phone_number_receiver' => 'required|regex:/^[0-9]*$/',
      'message' => 'required',
    ]);

    /* check if phone number and message is empty */
    if ($validate->fails()) {
      return ResponseFormatter::error(null, $validate->errors(), 500);
    }

    $phoneNumberReceiver = $request->phone_number_receiver;
    $message = $request->message;
    $senderId = Auth::id();
    $receiver = User::where('phone_number', $phoneNumberReceiver)->first();

    /* check if user send to yourself */
    if ($phoneNumberReceiver == Auth::user()->phone_number) {
      return ResponseFormatter::error(null, "Sorry, you cannot send to yourself");
    }

    /* check receiver by phone number is empty or not */
    if (is_null($receiver)) {
      return ResponseFormatter::error(null, "User not found", 404);
    }

    $chatRoom = ChatRoom::where('sender_id', $senderId)
      ->where('receiver_id', $receiver->id)
      ->first();

    /* check chat room is null or not */
    if (is_null($chatRoom)) {
      $chatRoom = ChatRoom::where('sender_id', $receiver->id)
        ->where('receiver_id', $senderId)
        ->first();
    }

    /* check chat room is empty or not */
    if (is_null($chatRoom)) {
      return ResponseFormatter::error(null, "Chat room not found", 404);
    }

    /* update conversation read status */
    Conversation::where('chat_room_id', $chatRoom->id)
      ->orderBy('id', 'desc')
      ->update(['read_status' => 1]);

    /* create conversation */
    $conversation = Conversation::create([
      'chat_room_id' => $chatRoom->id,
      'message' => $message
    ]);

    if ($conversation) {
      return ResponseFormatter::success($conversation, "Message has been sent successfully");
    } else {
      return ResponseFormatter::error(null, "Message failed to send");
    }
  }
}
