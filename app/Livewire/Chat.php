<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\ChatMessage;
use App\Events\MessageSent;

class Chat extends Component
{
    public $users;
    public $selectedUser;
    public $newMessage;
    public $messages;
    public $authId;
    public $loginId;

    public function mount()
    {
        $this->users = User::where('id', '!=', Auth::id())->latest()->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
        $this->authId = Auth::id();
        $this->loginId = $this->selectedUser->id;
    }

    public function selectUser($userId)
    {
        $this->selectedUser = User::find($userId);
        $this->loadMessages(); 
    }

    public function loadMessages()
    {
        $this->messages = ChatMessage::query()
        ->where(function($q) {
            $q->where('sender_id', Auth::id())
            ->where('receiver_id', $this->selectedUser->id);
        })
        ->orWhere(function($q) {
            $q->where('sender_id', $this->selectedUser->id)
            ->where('receiver_id', Auth::id());
        })
        ->latest()
        ->get();
    }

    public function submit()
    {
        if (!$this->newMessage) return;

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->messages->push($message);

        $this->newMessage = '';

        event(new MessageSent($message));
    }

    public function updatedNewMessage($value)
    {
        $this->dispatch('userTyping', userId: $this->loginId, userName: Auth::id(), selectedUserId: $this->selectedUser->id);
    }

    public function getListeners()
    {
        return [
            "echo-private:chat.{$this->loginId},MessageSent" => 'newChatMessageNotification',
        ];
    }

    public function newChatMessageNotification($message)
    {
        if ($message['sender_id'] === $this->selectedUser->id) {
            $messageObject = ChatMessage::find($message['id']);
            $this->messages->push($messageObject);
        }
    }

    public function render()
    {
        return view('livewire.chat', [
            'messages' => [],
            // 'receiver' => auth()->user(),
        ]);
    }
}
 