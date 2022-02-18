<?php

namespace App\Http\Controllers;

use App\Events\MessageDelivered;
use App\Events\PrivateMessageSent;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MessageController extends Controller
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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('chat');
    }

    public function fetchUsers()
    {
        $user           = Auth::user();
        $messages       = Message::where('user_id', $user->id)->orWhere('receiver_id', $user->id)->get();
        $receiver_ids = $messages->map(function($item) {
            return $item->receiver_id;
        });
        $user_ids = $messages->map(function($item) {
            return $item->user_id;
        });
        $users = User::where('id', '!=', $user->id)->orderBy('name')->get();
        
        return response()->json([
            'users' => $users,
            'unseen_messages' => $messages
        ]);
    }

    public function fetchUser($user)
    {
        $user = User::find($user);
        return response()->json($user);
    }

    public function privateMessages(User $user)
    {
        $privateCommunication= Message::with('user')
        ->where(['user_id'=> auth()->id(), 'receiver_id'=> $user->id])
        ->orWhere(function($query) use($user){
            $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
        })
        ->get();
        
        return $privateCommunication;
    }

    public function getActiveFriend(User $user) {
        return $user;
    }

    public function updateLastSeen(User $user) {
        $currentTime = Carbon::now('GMT+5')->toDateTimeString();
        $user->update(['last_seen_at' => $currentTime]);
    }

    public function sendPrivateMessage(Request $request, User $user)
    {
        $input = $request->all();
        $input['receiver_id'] = $user->id;
        $input['seen'] = 0;
        $message = auth()->user()->messages()->create($input);
        broadcast(new PrivateMessageSent($message->load(['user', 'media'])))->toOthers();
        return response(['status'=>'Message private sent successfully', 'message'=>$message]);

    }

    public function sendMedia(Request $request)
    {
        $user = User::find($request->activeFriend);
        $files = $request->file('file');
        if ($request->hasFile('file')) {
            $message = Message::create([
                'user_id' => request()->user()->id,
                'receiver_id' => $user->id,
                'seen' => 0
            ]);
            foreach ($files as $file) {
                $message->addMedia($file)->toMediaCollection('chat');
            }
            broadcast(new PrivateMessageSent($message->load(['user', 'media'])))->toOthers();
            return response(['status'=>'success', 'message'=>'Files sent successfull', 'data' => $message]);
        }
        if(request()->has('file')) {
            
        }
        return;
    }

    public function downloadFile(Media $media)
    {
        try{
            return $media;
        }
        catch(\Exception $e) {
            return response()->json(['status'=>'error','message'=>$e->getMessage()]);
        }
    }

    public function seenPrivateMessages(User $user)
    {
        $privateCommunication = Message::with('user')
                                ->where(['user_id'=> $user->id, 'receiver_id'=> Auth::user()->id, 'seen' => 0])
                                ->update(['seen' => 1]);
        return response(['status'=>'Message private sent successfully','message'=>$privateCommunication]);

    }

    public function unSeenCounter() {
        $privateCommunication= Message::with('user')->where(['user_id'=> $user->id, 'receiver_id'=> Auth::user()->id, 'seen' => 0])->count();
    }

    //Trigger Message Seen
    public function updateMessageDeliveredStatuss(Message $message)
    {
        $message->update(['seen' => 1]);
        broadcast(new MessageDelivered($message));
        return response(['status'=>'Message private seen successfully','message'=>$message]);
    }

    public function deletePrivateMessage(User $user)
    {
        $privateCommunication= Message::with('user')
        ->where(['user_id'=> auth()->id(), 'receiver_id'=> $user->id])
        ->orWhere(function($query) use($user){
            $query->where(['user_id' => $user->id, 'receiver_id' => auth()->id()]);
        });
        foreach ($privateCommunication->get() as $key => $value) {
            if($value->media('chat')->first()) {
                $file = $value->getMedia('chat')->first();
                $file->delete();
            }
        }
        $privateCommunication->delete();
    }

    public function showChat() {
        return view('organization.apps.chat');
    }
}
