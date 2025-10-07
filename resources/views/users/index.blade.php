<x-app-layout>
    <div class="max-w-4xl mx-auto mt-10">
        <h1 class="text-2xl font-bold mb-6 text-center">Rechercher des utilisateurs</h1>

        <!-- Champ de recherche -->
        <form action="{{ route('users.index') }}" method="GET" class="mb-8 flex gap-2">
            <input type="text" name="query" value="{{ $query ?? '' }}"
                   placeholder="Rechercher par pseudo ou email..."
                   class="flex-1 border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <button type="submit" class="bg-blue-500 text-white px-4 rounded-lg hover:bg-blue-600">
                Rechercher
            </button>
        </form>

        <!-- Résultats -->
        @if ($users->count() > 0)
            <div class="space-y-4">
                @foreach ($users as $user)
                    <div class="flex justify-between items-center border-b pb-3">
                        <div>
                            <a href="{{ route('users.show', $user) }}" class="font-bold text-lg text-blue-600 hover:underline">
                                {{ $user->name }}
                            </a>
                            <div class="text-gray-500 text-sm">{{ $user->email }}</div>
                        </div>

                        <div>
                            @if (Auth::user()->isFollowing($user))
                                <form action="{{ route('users.unfollow', $user) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-500 hover:text-red-700 font-semibold">Ne plus suivre</button>
                                </form>
                            @else
                                <form action="{{ route('users.follow', $user) }}" method="POST">
                                    @csrf
                                    <button class="text-blue-500 hover:text-blue-700 font-semibold">Suivre</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $users->links('pagination::simple-default') }}
            </div>
        @else
            <p class="text-gray-600 text-center">Aucun utilisateur trouvé.</p>
        @endif
    </div>
</x-app-layout>
