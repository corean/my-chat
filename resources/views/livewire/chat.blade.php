<div class="dark:bg-zinc-800">
    <div class="relative mb-6 w-full">
        <flux:heading size="xl" level="1" class="dark:text-white">{{ __('Chat') }}</flux:heading>
        <flux:subheading size="lg" class="mb-6 dark:text-gray-300">{{ __('Chat with your friends') }}</flux:subheading>
        <flux:separator variant="subtle" class="dark:border-zinc-700"/>
    </div>

    <div class="flex h-[800px] text-sm border dark:border-zinc-700 rounded-xl shadow overflow-hidden bg-white dark:bg-zinc-800">
        <!-- Left: User List -->
        <div class="w-[20rem] border-r dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900">
            <div class="p-4 font-bold text-gray-700 dark:text-gray-300 border-b dark:border-zinc-700">Users</div>
            <div class="divide-y dark:divide-zinc-700">

                @foreach ($users as $user)
                    <div wire:click="selectUser({{ $user->id }})"
                         wire:key="user-{{ $user->id }}"
                         class="p-3 cursor-pointer hover:bg-blue-100 dark:hover:bg-zinc-700 transition {{ $selectedUser->id === $user->id ? 'bg-blue-50 dark:bg-zinc-700 font-semibold' : '' }}"
                    >
                        <div class="text-gray-800 dark:text-gray-200">{{ $user->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->email }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Right: Chat Section -->
        <div class="w-full flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b dark:border-zinc-700 bg-gray-50 dark:bg-zinc-900">
                <div class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $selectedUser->name }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $selectedUser->email }}</div>
            </div>

            <!-- Messages -->
            <div class="flex-1 p-4 overflow-y-auto space-y-2 bg-gray-50 dark:bg-zinc-800">
                @foreach ($messages as $message)
                    <div class="flex {{ $message->sender_id === Auth::id() ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-xs px-4 py-2 rounded-2xl shadow {{ $message->sender_id === Auth::id() ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-zinc-700 text-gray-800 dark:text-gray-200' }}">
                            {{ $message->message }}
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="typing-indicator" class="px-4 pb-1 text-xs text-gray-400 dark:text-gray-500 italic"></div>

            <!-- Input -->
            <form wire:submit="submit"
                  class="p-4 border-t dark:border-zinc-700 bg-white dark:bg-zinc-800 flex items-center gap-2">
                <input
                        wire:model.live="newMessage"
                        type="text"
                        class="flex-1 border border-gray-300 dark:border-zinc-600 rounded-full px-4 py-2 text-sm focus:outline-none focus:ring focus:ring-blue-300 dark:focus:ring-blue-700 bg-white dark:bg-zinc-700 text-gray-900 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400"
                        placeholder="Type your message..."/>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white text-sm px-4 py-2 rounded-full transition">
                    Send
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('userTyping', (event) => {
            console.log(event);
            window.Echo.private(`chat.${event.selectedUserId}`).whisper('typing', {
                userId: event.userId,
                userName: event.userName
            });
        })

        window.Echo.private(`chat.{{ $loginId }}`).listenForWhisper('typing', (data) => {
            let t = document.getElementById('typing-indicator')
            t.innerText = `${data.userName} is typing...`;

            setTimeout(() => {
                t.innerText = '';
            }, 2000);
        });

    })
</script>