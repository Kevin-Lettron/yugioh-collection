<x-app-layout>
    <div class="max-w-3xl mx-auto mt-10 bg-white shadow rounded-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $user->name }}</h1>
                <p class="text-gray-500 text-sm">{{ $user->email }}</p>
            </div>

            @if (Auth::id() !== $user->id)
                <div>
                    @if ($isFollowing)
                        <form action="{{ route('users.unfollow', $user) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Ne plus suivre
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.follow', $user) }}" method="POST">
                            @csrf
                            <button class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium">
                                Suivre
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex justify-around text-center border-t pt-4">
            <a href="{{ route('users.collection', $user) }}"
               class="block w-1/2 py-3 text-blue-600 font-semibold hover:bg-blue-50 rounded-lg transition">
                Voir sa collection
            </a>
            <a href="{{ route('users.decks', $user) }}"
               class="block w-1/2 py-3 text-blue-600 font-semibold hover:bg-blue-50 rounded-lg transition">
                Voir ses decks
            </a>
        </div>
    </div>
</x-app-layout>
