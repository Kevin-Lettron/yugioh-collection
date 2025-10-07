@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Mes Decks</h1>

    <!-- Barre de recherche -->
    <form method="GET" action="{{ route('decks.index') }}" class="mb-6 flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un deck..."
               class="flex-1 border-gray-300 rounded-md shadow-sm py-2 px-4 focus:ring-blue-500 focus:border-blue-500">
        <button type="submit"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded transition">
            Rechercher
        </button>
    </form>

    <!-- Message de succès -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <!-- Bouton de création -->
    <a href="{{ route('decks.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded mb-6 inline-block">
        + Créer un nouveau deck
    </a>

    <!-- Liste des decks -->
    <div class="mt-8">
        @forelse($decks as $deck)
            <div class="bg-white shadow rounded p-4 mb-4 flex justify-between items-center border border-gray-200">
                <div>
                    <h2 class="text-xl font-semibold">{{ $deck->name }}</h2>
                    <p class="text-gray-600">{{ $deck->description ?: 'Aucune description' }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('decks.show', $deck) }}"
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 rounded transition">
                        Voir
                    </a>

                    <a href="{{ route('decks.edit', $deck) }}"
                       class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded transition">
                        Modifier
                    </a>

                    <!-- Bouton ouverture modale -->
                    <button type="button"
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded transition"
                            onclick="openModal({{ $deck->id }}, '{{ addslashes($deck->name) }}')">
                        Supprimer
                    </button>
                </div>
            </div>
        @empty
            <p class="text-gray-600">Aucun deck créé pour le moment.</p>
        @endforelse
    </div>
</div>

<!-- ✅ Modale de confirmation -->
<div id="deleteModal" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6">
        <h3 class="text-2xl font-semibold text-gray-800 mb-4">Supprimer le deck</h3>
        <p class="text-gray-600 mb-6">
            Êtes-vous sûr de vouloir supprimer le deck <span id="deckName" class="font-semibold text-gray-900"></span> ?
            <br><span class="text-red-600 font-semibold">Cette action est irréversible.</span>
        </p>

        <div class="flex justify-end gap-3">
            <button onclick="closeModal()" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold">
                Annuler
            </button>
            <form id="deleteForm" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-semibold">
                    Supprimer définitivement
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    function openModal(deckId, deckName) {
        const modal = document.getElementById('deleteModal');
        const form = document.getElementById('deleteForm');
        const nameSpan = document.getElementById('deckName');

        nameSpan.textContent = deckName;
        form.action = `/decks/${deckId}`;
        modal.classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('deleteModal').classList.add('hidden');
    }

    // Fermer la modale si on clique à l’extérieur
    document.getElementById('deleteModal').addEventListener('click', (e) => {
        if (e.target.id === 'deleteModal') closeModal();
    });
</script>
@endsection
