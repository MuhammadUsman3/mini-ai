<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class OpenAIController extends Controller
{
    public function index()
    {
        $messages = Message::orderBy('created_at', 'asc')->get();
        return view('openai.index')->with('messages', $messages);
    }

    public function sendMessage(Request $request)
    {
        $userMessage = $request->input('message');
        $personality = $request->input('personality');

        $prompts = [
            'formal' => "Formal prompt: " . $userMessage,
            'friendly' => "Hey there! " . $userMessage,
            'humorous' => "Why did the chicken cross the road? " . $userMessage,
        ];

        $prompt = $prompts[$personality] ?? $userMessage;

        $client = \OpenAI::Client(env('OPENAI_API_KEY'));

        $response = $client->completions()->create([
            'model' => 'gpt-3.5-turbo-instruct',
            'prompt' => $prompt,
        ]);

        $botResponse = $response['choices'][0]['text'] ?? 'Sorry, I couldn\'t understand.';
        $chatHistory[] = ['user' => $userMessage, 'bot' => trim($botResponse)];

        $message = new Message();
        $message->user_id = auth()->id();
        $message->user_message = trim($userMessage);
        $message->bot_response = trim($botResponse);
        $message->save();

        return response()->json(['success' => true, 'message' => 'Message sent successfully', 'history' => $chatHistory]);
    }

    public function searchMessages(Request $request)
    {
        $searchTerm = $request->input('searchTerm');
        $userId = auth()->id();

        // Query messages that match the search term for the authenticated user
        $searchResults = Message::where('user_id', $userId)
            ->where(function ($query) use ($searchTerm) {
                $query->where('user_message', 'like', "%$searchTerm%")
                    ->orWhere('bot_response', 'like', "%$searchTerm%");
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['success' => true, 'searchResults' => $searchResults]);
    }
}
