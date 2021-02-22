<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\User;
use App\Chat;
use App\Friendlist;
use App\PersonalChatroom;
use App\FriendRequest;
use RuntimeException;

class HomeController extends Controller
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

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }


    public function fetchAllNotifications () {

        $result = [];

        // fetch all unresponded friend requests this user receives
        $friendRequests = FriendRequest::where('receiver_id', Auth::user()->id)
                                        ->where('read_at', null)
                                        ->get();

        if ($friendRequests->count() > 0) {
            foreach($friendRequests as $req) {
                // get the sender's user info
                $sender = User::find($req->sender_id);

                // put the result inside the array to be sent as a HTTP response
                $result[] = [
                    'notif' => $req,
                    'sender' => $sender
                ];
            }
        } else {
            return ['error' => 'You don\'t have any friend request yet.'];
        }

        return $result;
    }


    public function fetchAllFriends () {

        $friends_id = [];

        $query = Friendlist::where('first_user', Auth::user()->id)
        ->orWhere('second_user', Auth::user()->id)
        ->get();

        if ($query) {
            foreach ($query as $user_id) {
                if ($user_id->first_user == Auth::user()->id) {
                    $friends_id[] = $user_id->second_user;
                } else {
                    $friends_id[] = $user_id->first_user;
                }
            }
            
            $friends = User::whereIn('id', $friends_id)->orderBy('name', 'asc')->get(['id', 'name', 'email', 'photo']);

            return $friends;
        } else {
            return ['error' => 'You dont have any friends.'];
        }
    }


    public function fetchAllRecentChats () {

        // prepare an array to contain all of the responses
        $result = [];

        // get the chatroom record from this user
        $query = PersonalChatroom::where('first_user', Auth::user()->id)
        ->orWhere('second_user', Auth::user()->id)
        ->get();

        if ($query) {
            foreach ($query as $chatroom) {

                // get the interlocutor's data
                if ($chatroom->first_user == Auth::user()->id) {
                    $friend = User::where('id', $chatroom->second_user)->first();
                } else {
                    $friend = User::where('id', $chatroom->first_user)->first();
                }

                // get the most recent chat from the chatroom
                $latestChat = Chat::where('room_id', $chatroom['room_id'])
                                    ->latest()
                                    ->first();
                            
                // check how many unread chats
                $unreadChats = Chat::where('user_id', $friend['id'])
                                    ->where('room_id', $chatroom['room_id'])
                                    ->where('read_at', null)
                                    ->count();

                // if they already have chatted before
                if ($latestChat) {
                    $result[] = [
                        'friend' => $friend,
                        'chat' => $latestChat,
                        'unread' => $unreadChats
                    ];
                    $result = array_reverse(array_values(Arr::sort($result, function ($key) {
                        return $key['chat']['created_at'];
                    })));
                } else {
                    // if this is the first time they create the chatroom
                    $result[] = [
                        'friend' => $friend,
                        'chat' => [
                            'message' => null,
                            'created_at' => null,
                            'updated_at' => null,
                        ],
                        'unread' => 0
                    ];
                }
            }
    
            
            return $result;
        } else {
            return ['error' => 'You dont have any chat yet.'];
        }
    }


    public function markAsRead (Request $request) {

        if ($request->target_model == 'chat') {

            // set a value to the 'read_at' column
            Chat::where('id', $request->target_id)
                ->update(['read_at' => now()]);

            return ['message' => 'Chat with id ' . $request->target_id . ' has been updated.'];
        }
        
        if ($request->target_model == 'notification') {
            FriendRequest::where('id', $request->target_id)
                        ->update(['read_at' => now()]);

            return ['message' => 'Notification with id ' . $request->target_id . ' has been updated.'];
        }
    }


    public function updateProfile (Request $request) {   

        $savedFilename = null;

        // file checking
        if (!$request->hasFile('new_photo')) {
            // use old photo
            $savedFilename = $request->old_photo;
        } else {
            $file = $request->file('new_photo');

            // check error status
            // proceed if UPLOAD_ERR_OK is present.
            switch($file->getError()) {
                case UPLOAD_ERR_OK: 
                    break;
                case UPLOAD_ERR_NO_FILE:
                    throw new RuntimeException('File not found.');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    throw new RuntimeException('Filesize limit exceeded.');
                    break;
                default:
                    throw new RuntimeException('Unknown error when uploading.');
                    break;
            }

            // limit the file size to 2MB (megabytes)
            if ($file->getSize() > 2000000) {
                throw new RuntimeException('Error! Maximum uploaded filesize is 2MB.');
            }

            // check the file extension
            if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png'])) {
                throw new RuntimeException('Error! Extension not supported.');
            }

            /**
             * if the uploaded file passed all the checkpoints,
             * then create an unique filename to be saved in the disk storage and database.
            */
            $savedFilename = hash('haval160,4', Auth::user()->id . pathinfo($file->getFilename(), PATHINFO_FILENAME));
            $savedFilename .= '.' . $file->getClientOriginalExtension();

            // DELETE THE CURRENT AVATAR IN THE STORAGE
            if ($request->old_photo != 'default.png') {
                Storage::delete('avatars/' . $request->old_photo);
            }

            // store the file into disk storage
            $file->storeAs('avatars', $savedFilename);
        }

        // save the user name & file name into database
        User::where('id', Auth::user()->id)
            ->update([
                'name' => $request->user_name,
                'photo' => $savedFilename
            ]);
        
        $user = User::find(Auth::user()->id);

        return [
            'user' => $user,
            'status' => 'Your profile has been updated.'
        ];
    }
}
