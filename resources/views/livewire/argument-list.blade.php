<div class="grid gap-4 md:gap-6">
    @if($user)
        <div class="px-2">
            @php
                $availableVotes = $user->getAvailableVotesForRfc($rfc);
            @endphp

            <span class="text-gray-600 tracking-wide">
                You have {{ $availableVotes }} {{ Str::plural('vote', $availableVotes) }} available
            </span>
        </div>
    @endif

    @if($userArgument)
        <x-argument-card.card
            :user="$user"
            :rfc="$rfc"
            :argument="$userArgument"
            :is-confirming-delete="$isConfirmingDelete"
            :is-editing="$isEditing"
        />
    @endif

    @foreach($arguments as $argument)
        @if ($userArgument?->is($argument))
            @continue
        @endif

        <x-argument-card.card
            :user="$user"
            :rfc="$rfc"
            :argument="$argument"
            :is-confirming-delete="$isConfirmingDelete"
            :is-editing="$isEditing"
        />
        @if($showingComments?->is($argument))
            <div class="grid gap-2">
                @foreach($argument->comments as $comment)
                    <div class="md:pl-24 pl-4">
                        <div class="bg-white p-4 rounded-md prose w-full max-w-full">
                            <span
                                class="font-bold">{{ $comment->user->name }}</span>: {{ $comment->body }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endforeach

    {{ $arguments->links('vendor.pagination.tailwind') }}
</div>
