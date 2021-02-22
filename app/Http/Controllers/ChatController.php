<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\SendChat;
use App\Events\ReceiveMessage;
use App\Events\SendNotification;
use App\User;
use App\Chat;
use App\Friendlist;
use App\PersonalChatroom;

class ChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    public function startChat (Request $request) {

        if (Auth::user()->id == $request->id) {
            return ['error' => 'Cannot talk to yourself.'];
        }

        // check whether if this user and the targeted user already has a chatroom
        $isExist = PersonalChatroom::whereIn('first_user', [Auth::user()->id, $request->id])
                                    ->whereIn('second_user', [Auth::user()->id, $request->id])
                                    ->count();

        /**
         * check whether if this user is still befriend with the targeted user.
         * just in case if the targeted user is trying to chat this user even though they're not friend anymore.
        */
        $isFriend = Friendlist::whereIn('first_user', [Auth::user()->id, $request->id])
                                    ->whereIn('second_user', [Auth::user()->id, $request->id])
                                    ->count();

        if ($isExist > 0 && $isFriend > 0) {
            // get the room id
            $room_id = PersonalChatroom::whereIn('first_user', [Auth::user()->id, $request->id])
                                        ->whereIn('second_user', [Auth::user()->id, $request->id])
                                        ->first('room_id');

                                        
            // update the state of interlocutor's chat from unread to read
            Chat::where('user_id', $request->id)
                ->where('room_id', $room_id['room_id'])
                ->where('read_at', null)
                ->update(['read_at' => now()]);
                                        
            // fetch all of the chats from this chatroom
            $chats = Chat::where('room_id', $room_id['room_id'])->orderBy('created_at', 'asc')->get();

            // create an array to be sent as a HTTP response
            $data = [];
            $data['room_id'] = $room_id['room_id'];
            $data['user'] = User::find($request->id);
            
            if (count($chats) > 0) {
                foreach ($chats as $chat) {
                    $data['messages'][] = [
                        'sender' => User::find($chat->user_id),
                        'message' => $chat->message,
                        'read_at' => $chat->read_at,
                        'time' => $chat->created_at
                    ];
                }    
            } else {
                $data['messages'] = [];
            }

            $data['exist'] = 1;
            

            return $data;
        } else {
            // if they don't have a chatroom, then create one.
            $room_id = Auth::user()->id . 'CHAT' . $request->id;
            PersonalChatroom::create([
                'room_id' => $room_id,
                'first_user' => Auth::user()->id,
                'second_user' => $request->id
            ]);

            $data = [];
            $data['room_id'] = $room_id;
            $data['user'] = User::find($request->id);
            $data['messages'] = [];
            $data['exist'] = 0;

            return $data;
        }
    }


    public function sendMessage (Request $request) {

        $payload = $request->user()->chats()->create([
            'room_id' => $request->room_id,
            'message' => $request->message
        ]);

        broadcast(new SendChat($payload->load('user')))->toOthers();
        broadcast(new ReceiveMessage($payload->load('user')))->toOthers();

        return ['status' => 'success'];
    }


    public function clearChat (Request $request) {

        if (Auth::check() && $request->csrf_token == csrf_token()) {
            Chat::where('room_id', $request->room_id)->delete();
        }
        return ['message' => 'Chats have been deleted successfully.'];
    }


    public function deleteAllChats (Request $request) {

        if (Auth::check() && $request->csrf_token == csrf_token()) {
            $room_id = PersonalChatroom::where('first_user', Auth::user()->id)
                                        ->orWhere('second_user', Auth::user()->id)
                                        ->get('room_id');

            
            // delete all chats that belong to this user
            $chat = Chat::whereIn('room_id', $room_id)->delete();

            // delete all chatrooms that contain this user
            PersonalChatroom::where('first_user', Auth::user()->id)
                            ->orWhere('second_user', Auth::user()->id)
                            ->delete();
            

            return [ 'status' => 'All chats have been deleted successfully.' ];

        } else {
            return [
                'error' => 1,
                'status' => 'Authentication needed.'
            ];
        }
    }

    
    public function deleteChatroom (Request $request) {

        if ($request->csrf_token != csrf_token()) {
            return;
        }

        // delete all chats in the particular chatroom
        Chat::where('room_id', $request->room_id)->delete();
        // delete the chatroom
        PersonalChatroom::where('room_id', $request->room_id)->delete();
        // return
        return ['status' => 'Chatroom has been deleted successfully.'];
    }
}
