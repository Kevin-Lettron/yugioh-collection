<x-app-layout>
    <div class="max-w-4xl mx-auto mt-10 px-4">
        <h1 class="text-3xl font-extrabold mb-8 text-center text-gray-800">
            Mes abonnements
        </h1>

        @if ($following->count() > 0)
            <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
                @foreach ($following as $user)
                    <div class="flex justify-between items-center p-4 hover:bg-gray-50 transition">
                        <!-- Infos utilisateur -->
                        <div>
                            <a href="{{ route('users.show', $user) }}"
                               class="font-semibold text-lg text-blue-600 hover:text-blue-800 transition">
                                {{ $user->name }}
                            </a>
                            <div class="text-gray-500 text-sm">{{ $user->email }}</div>
                        </div>

                        <!-- Bouton Ne plus suivre -->
                        <form action="{{ route('users.unfollow', $user) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <button
                                class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-md text-sm font-medium transition">
                                Ne plus suivre
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-600 mt-10 bg-white shadow rounded-lg p-8">
                <p class="text-lg font-medium">Vous ne suivez encore personne</p>
                <p class="text-sm text-gray-400 mt-2">
                    Trouve des utilisateurs depuis la page <a href="{{ route('users.index') }}" class="text-blue-600 hover:underline">Rechercher</a>
                    pour commencer à suivre leur collection et leurs decks.
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
