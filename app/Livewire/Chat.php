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

    public function mount(): void
    {
        $this->users = User::where('id', '!=', Auth::id())->latest()->get();
        $this->selectedUser = $this->users->first();
        $this->loadMessages();
        $this->loginId = Auth::id();
    }

    public function selectUser($userId): void
    {
        $this->selectedUser = User::find($userId);
        
        $this->loadMessages();

        $this->dispatch('chat-message-added');
    }

    public function loadMessages(): void
    {
        $this->messages = ChatMessage::query()
            ->where(function ($q) {
                $q
                    ->where('sender_id', Auth::id())
                    ->where('receiver_id', $this->selectedUser?->id);
            })
            ->orWhere(function ($q) {
                $q
                    ->where('sender_id', $this->selectedUser->id)
                    ->where('receiver_id', Auth::id());
            })
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function submit(): void
    {
        if ( ! $this->newMessage) {
            return;
        }

        $message = ChatMessage::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $this->selectedUser->id,
            'message' => $this->newMessage,
        ]);

        $this->messages->push($message);

        $this->newMessage = '';

        broadcast(new MessageSent($message));

        $this->dispatch('chat-message-added');
    }

    public function updatedNewMessage($value): void
    {
        $this->dispatch(
            'userTyping',
            userId: $this->loginId,
            userName: Auth::user()->name,
            selectedUserId: $this->selectedUser->id,
        );
    }

    public function getListeners(): array
    {
        return [
            "echo-private:chat.{$this->loginId},MessageSent" => 'newChatMessageNotification',
        ];
    }

    public function newChatMessageNotification($message): void
    {
        if ($message['sender_id'] === $this->selectedUser->id) {
            $messageObject = ChatMessage::find($message['id']);
            $this->messages->push($messageObject);
        }

        $this->dispatch('chat-message-added');
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.chat');
    }
}
 