<x-app-layout>
    <div class="max-w-4xl mx-auto mt-10 px-4">
        <h1 class="text-3xl font-extrabold mb-8 text-center text-gray-800">
            Mes abonnés
        </h1>

        @if ($followers->count() > 0)
            <div class="bg-white shadow rounded-lg divide-y divide-gray-200">
                @foreach ($followers as $follower)
                    <div class="flex justify-between items-center p-4 hover:bg-gray-50 transition">
                        <!-- Infos utilisateur -->
                        <div>
                            <a href="{{ route('users.show', $follower) }}"
                               class="font-semibold text-lg text-blue-600 hover:text-blue-800 transition">
                                {{ $follower->name }}
                            </a>
                            <div class="text-gray-500 text-sm">{{ $follower->email }}</div>
                        </div>

                        <!-- Boutons suivre / ne plus suivre -->
                        <div>
                            @if (Auth::user()->isFollowing($follower))
                                <form action="{{ route('users.unfollow', $follower) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button
                                        class="bg-red-500 hover:bg-red-600 text-white px-4 py-1.5 rounded-md text-sm font-medium transition">
                                        Ne plus suivre
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('users.follow', $follower) }}" method="POST">
                                    @csrf
                                    <button
                                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-1.5 rounded-md text-sm font-medium transition">
                                        Suivre
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-gray-600 mt-10 bg-white shadow rounded-lg p-8">
                <p class="text-lg font-medium">Aucun abonné pour le moment</p>
                <p class="text-sm text-gray-400 mt-2">
                    Ton profil apparaîtra ici lorsque d'autres utilisateurs te suivront.
                </p>
            </div>
        @endif
    </div>
</x-app-layout>
