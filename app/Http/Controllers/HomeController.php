<?php

namespace App\Http\Controllers;

use App\Jobs\SendMessage;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // Bosh sahifa uchun
    public function index()
    {
        $user = User::where('id', auth()->id())->select([
            'id',
            'name',
            'email',
        ])->first();

        return view('home', [
            'user' => $user,
        ]);
    }


    public function users(): JsonResponse
    {
        $users = User::where('id', '!=', auth()->id())
        ->select('id', 'name', 'email')
            ->get();

        return response()->json($users);
    }

    public function getUser($id){
        $users=User::with('messages')->find($id);
        return response()->json($users);
    }

    // Xabarlarni qaytaradi
    public function messages(): JsonResponse
    {
        $messages = Message::with('sender')->get()->append('time');

        return response()->json($messages);
    }


    public function message(Request $request): JsonResponse
    {
        $message = Message::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $request->get('receiver_id'),
            'text' => $request->get('text'),
        ]);
        SendMessage::dispatch($message);

        return response()->json([
            'success' => true,
            'message' => "Message created and job dispatched.",
        ]);
    }
    public function getMessages($receiver_id): JsonResponse
    {
        $user = auth()->user();

        // Hozirgi foydalanuvchi va tanlangan foydalanuvchi o'rtasidagi xabarlarni olish
        $messages = Message::where(function ($query) use ($user, $receiver_id) {
            $query->where('sender_id', $user->id)
                ->where('receiver_id', $receiver_id)
                ->orWhere('sender_id', $receiver_id)
                ->where('receiver_id', $user->id);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();
        $message = new Message();
        $message->sender_id = $user->id;
        $message->receiver_id = $request->receiver_id;
        $message->text = $request->text;
        $message->save();

        return response()->json($message); // Yangi xabarni qaytarish
    }
}
