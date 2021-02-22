<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Events\ReceiveMessage;
use App\Events\SendNotification;
use App\User;
use App\Chat;
use App\Friendlist;
use App\PersonalChatroom;
use App\FriendRequest;

class FriendController extends Controller
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


    public function find (Request $request) {
        
        $data = User::where('id', $request->id)->first(['id', 'name', 'email', 'photo']);

        if ($data) {
            return [
                'user' => $data,
                'status' => 1
            ];
        } else {
            return ['status' => 0];
        }
    }


    public function sendFriendRequest (Request $request) {

        if (Auth::user()->id == $request->id) {
            return;
        }
        
        // check whether if they already have become friend or not
        $areTheyFriend = Friendlist::whereIn('first_user', [Auth::user()->id, $request->id])
                                    ->whereIn('second_user', [Auth::user()->id, $request->id])
                                    ->exists();

        // check whether if this user still has an unresponded friend request towards the targeted user
        $isDeclined = FriendRequest::where('sender_id', Auth::user()->id)
                                    ->where('receiver_id', $request->id)
                                    ->where('type', 'friend-request')
                                    ->where('read_at', null)
                                    ->exists();
                                    
        /**
         * A friend request can only be sent when they are not friends
         * AND there is no unresponded friend request towards the targeted user.
        */
        if (!$areTheyFriend && !$isDeclined) {

            // save the friend request into DB
            $payload = FriendRequest::create([
                'sender_id' => Auth::user()->id,
                'receiver_id' => $request->id,
                'type' => 'friend-request',
                'message' => 'You have got a new friend request.'
            ]);

            // send the friend request to the targeted user in real-time
            broadcast(new SendNotification([
                'sender' => Auth::user(),
                'notification' => $payload
            ]));

            $user = User::find($request->id);
    
            return [
                'sender' => Auth::user()->id,
                'receiver' => $user,
                'status' => 'A friend request has been sent to ' . $user->name . '.'
            ];
        } else {
            return [
                'error' => 1,
                'status' => 'Failed to send a friend request.'
            ];
        }
    }

    
    public function addFriend (Request $request) {

        if (Auth::user()->id == $request->target_id) {
            return false;
        }

        if ($request->accept) {

            // check whether if they are friend
            $areTheyFriend = Friendlist::whereIn('first_user', [Auth::user()->id, $request->id])
                                    ->whereIn('second_user', [Auth::user()->id, $request->id])
                                    ->count();

            if ($areTheyFriend == 0) {
                
                // save their friendship record in the DB
                Friendlist::create([
                    'first_user' => Auth::user()->id,
                    'second_user' => $request->target_id
                ]);
    
                /**
                 * update the friend request's state from unresponded to responded
                 * by giving a datetime value to the 'read_at' column.
                */
                FriendRequest::where('id', $request->notification_id)
                            ->update([ 'read_at' => now() ]);

                
                // save notification message into DB
                $makeNotif = FriendRequest::create([
                    'sender_id' => Auth::user()->id,
                    'receiver_id' => $request->target_id,
                    'type' => 'friend-request-accepted',
                    'message' => Auth::user()->name . ' has accepted your friend request.'
                ]);

                // notify the targeted user that their friend request has been accepted
                broadcast(new SendNotification([
                    'sender' => Auth::user(),
                    'notification' => $makeNotif
                ]));
    
                $data = User::find($request->target_id);
    
                return $data;
            } else {

                return ['error' => 'You are already friend.'];
            }
        } else {

            // update the friend request state
            FriendRequest::where('id', $request->notification_id)
                        ->delete();

            return ['error' => 'Rejected.'];
        }
    }

    
    public function unfriend (Request $request) {

        if (Auth::check() && $request->csrf_token == csrf_token()) {
            $room_id = PersonalChatroom::whereIn('first_user', [Auth::user()->id, $request->target])
                                        ->whereIn('second_user', [Auth::user()->id, $request->target])
                                        ->first();

            if (Chat::where('room_id', $room_id['room_id'])->exists()) {

                // delete all chats from both sides (if any)
                Chat::where('room_id', $room_id['room_id'])->delete();
            }
            
            if ($room_id) {
                
                // delete the specific chatroom (if present)
                PersonalChatroom::where('id', $room_id['id'])->delete();
            }
            
            // delete relationship from friendlist table
            Friendlist::whereIn('first_user', [Auth::user()->id, $request->target])
                        ->whereIn('second_user', [Auth::user()->id, $request->target])
                        ->delete();


            $user = User::find($request->target);

            return ['status' => 'You are no longer friend with ' . $user->name . '.'];
            
        } else {
            return [
                'error' => 1,
                'status' => 'Authentication needed.'
            ];
        }
    }
}
