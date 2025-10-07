<x-app-layout>
    <div class="max-w-5xl mx-auto mt-10 bg-white shadow rounded-lg p-6">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">
            Decks de {{ $user->name }}
        </h1>

        @if ($decks->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($decks as $deck)
                    <div class="border rounded-lg p-4 bg-gray-50 hover:bg-gray-100 transition">
                        <h3 class="font-bold text-lg">{{ $deck->name }}</h3>
                        <p class="text-gray-600 text-sm mt-1">
                            {{ $deck->description ?? 'Aucune description' }}
                        </p>
                        <a href="{{ route('decks.show', $deck) }}"
                           class="text-blue-500 hover:underline text-sm mt-2 inline-block">
                            Voir le deck
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-center text-gray-500 mt-4">Aucun deck pour le moment.</p>
        @endif

        <div class="text-center mt-8">
            <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:underline">
                ← Retour au profil
            </a>
        </div>
    </div>
</x-app-layout>
