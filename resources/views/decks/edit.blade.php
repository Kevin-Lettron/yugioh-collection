@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <h2 class="text-2xl font-bold mb-6">Modifier le deck</h2>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- ====== FILTRES (GET, formulaire indépendant) ====== -->
                <div class="w-full bg-gray-100 border border-gray-300 rounded-lg p-4">
                    <h3 class="text-xl font-semibold mb-4">Filtres</h3>

                    @php
                        $typeOptions = isset($availableTypes) ? $availableTypes : collect();
                        $rarities = [
                            "Common","Short Print","Super Short Print","Rare","Super Rare",
                            "Ultra Rare","Secret Rare","Parallel Rare","Ultimate Rare",
                            "Ghost Rare","Gold Rare","Premium Gold Rare","Platinum Rare",
                            "Prismatic Secret Rare","Collector's Rare","Starlight Rare",
                            "Quarter Century Secret Rare","Starfoil Rare","Shatterfoil Rare",
                            "Duel Terminal Normal Parallel Rare","Duel Terminal Rare Parallel Rare",
                            "Duel Terminal Super Parallel Rare","Duel Terminal Ultra Parallel Rare",
                            "Platinum Secret Rare","Mosaic Rare","10000 Secret Rare",
                            "Ghost/Gold Rare","Gold Secret Rare"
                        ];
                    @endphp

                    <form method="GET" action="{{ route('decks.edit', $deck) }}" class="space-y-4" id="filtersForm">
                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-gray-700">Type de carte</label>
                            <select name="type" id="type" class="w-full border-gray-300 rounded-md py-2 px-4">
                                <option value="">Tous les types</option>
                                @foreach($typeOptions as $t)
                                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Niveau exact -->
                        <div>
                            <label for="level" class="block text-gray-700">Niveau exact</label>
                            <input type="number" name="level" id="level" value="{{ request('level') }}"
                                   class="w-full border-gray-300 rounded-md py-2 px-4" placeholder="ex : 4">
                        </div>

                        <!-- ATK min -->
                        <div>
                            <label for="atk" class="block text-gray-700">ATK minimum</label>
                            <input type="number" name="atk" id="atk" value="{{ request('atk') }}"
                                   class="w-full border-gray-300 rounded-md py-2 px-4">
                        </div>

                        <!-- DEF min -->
                        <div>
                            <label for="def" class="block text-gray-700">DEF minimum</label>
                            <input type="number" name="def" id="def" value="{{ request('def') }}"
                                   class="w-full border-gray-300 rounded-md py-2 px-4">
                        </div>

                        <!-- Rareté -->
                        <div>
                            <label for="rarity" class="block text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity" class="w-full border-gray-300 rounded-md py-2 px-4">
                                <option value="">Toutes les raretés</option>
                                @foreach($rarities as $r)
                                    <option value="{{ $r }}" {{ request('rarity') === $r ? 'selected' : '' }}>
                                        {{ $r }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Boutons -->
                        <div class="flex flex-col gap-2 mt-4">
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded w-full transition">
                                Appliquer les filtres
                            </button>

                            <a href="{{ route('decks.edit', $deck) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded w-full text-center transition">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- ====== COLONNE PRINCIPALE ====== -->
                <div class="lg:col-span-3">
                    <!-- Barre de recherche (GET, indépendante) -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('decks.edit', $deck) }}" class="flex gap-2" id="searchForm">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Rechercher par nom / code / ucard_id..."
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">

                            <!-- conserver les filtres actifs -->
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="level" value="{{ request('level') }}">
                            <input type="hidden" name="atk" value="{{ request('atk') }}">
                            <input type="hidden" name="def" value="{{ request('def') }}">
                            <input type="hidden" name="rarity" value="{{ request('rarity') }}">

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">
                                Rechercher
                            </button>
                        </form>
                    </div>

                    <!-- ====== FORMULAIRE POST/PUT D’UPDATE DU DECK (SEUL FORMULAIRE POST) ====== -->
                    <form id="deckForm" action="{{ route('decks.update', $deck->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 font-semibold">Nom du deck</label>
                                <input type="text" name="name" value="{{ $deck->name }}"
                                       class="w-full border-gray-300 rounded-md py-2 px-4" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold">Description</label>
                                <input type="text" name="description" value="{{ $deck->description }}"
                                       class="w-full border-gray-300 rounded-md py-2 px-4">
                            </div>
                        </div>

                        @error('cards')
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="overflow-x-auto">
                            <table id="cardsTable" class="w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nom</th>
                                        <th class="px-4 py-2 text-left">Type</th>
                                        <th class="px-4 py-2 text-left">Niveau</th>
                                        <th class="px-4 py-2 text-left">ATK</th>
                                        <th class="px-4 py-2 text-left">DEF</th>
                                        <th class="px-4 py-2 text-left">Qté dispo</th>
                                        <th class="px-4 py-2 text-left">Qté dans deck</th>
                                    </tr>
                                </thead>
                                <tbody id="cardsBody"></tbody>
                            </table>
                        </div>

                        <div class="flex justify-between items-center mt-4">
                            <p class="text-sm text-gray-700">
                                Total sélectionné : <span id="selected-total">0</span> / 40–60
                            </p>
                            <div id="pagination" class="flex gap-2"></div>
                        </div>

                        <div class="mt-6 text-right">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Mettre à jour le deck
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Données fournies par le contrôleur
    const allCards = @json($cards); // déjà filtrées côté serveur
    const existingDeckCards = @json($deck->cards ?? []);
    const deckId = {{ $deck->id }};
    const LS_KEY = `deck_quantities_${deckId}`;

    // Pagination locale
    const cardsPerPage = 20;
    let currentPage = 1;
    let filteredCards = [...allCards];

    // Quantités sélectionnées (restaurées depuis localStorage si présent)
    const selectedQuantities = JSON.parse(localStorage.getItem(LS_KEY) || '{}');

    // Hydrate avec les quantités du deck existant à la première visite
    if (Object.keys(selectedQuantities).length === 0 && Array.isArray(existingDeckCards)) {
        existingDeckCards.forEach(card => {
            selectedQuantities[card.id] = card.pivot?.quantity || 0;
        });
    }

    const tableBody = document.getElementById('cardsBody');
    const totalSpan = document.getElementById('selected-total');
    const pagination = document.getElementById('pagination');

    function renderTable() {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * cardsPerPage;
        const end = start + cardsPerPage;
        const pageCards = filteredCards.slice(start, end);

        pageCards.forEach(card => {
            const selected = selectedQuantities[card.id] ?? 0;
            const row = document.createElement('tr');
            row.classList.add('hover:bg-gray-100');
            row.innerHTML = `
                <td class="px-4 py-2">${card.name}</td>
                <td class="px-4 py-2">${card.card_type ?? '-'}</td>
                <td class="px-4 py-2">${card.level ?? '-'}</td>
                <td class="px-4 py-2">${card.atk ?? '-'}</td>
                <td class="px-4 py-2">${card.def ?? '-'}</td>
                <td class="px-4 py-2 font-semibold ${Number(card.available_quantity || 0) > 0 ? 'text-green-600' : 'text-red-600'}">
                    ${card.available_quantity ?? 0}
                </td>
                <td class="px-4 py-2">
                    <input type="number" min="0" max="3" value="${selected}" data-card-id="${card.id}"
                        class="w-16 border rounded px-2 py-1 text-center quantity-input"
                        oninput="if (this.value > 3) this.value = 3;">
                </td>`;
            tableBody.appendChild(row);
        });

        renderPagination();
        updateTotal();
    }

    function renderPagination() {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredCards.length / cardsPerPage) || 1;
        for (let i = 1; i <= totalPages; i++) {
            const btn = document.createElement('button');
            btn.textContent = i;
            btn.className = `px-3 py-1 border rounded ${i === currentPage ? 'bg-blue-600 text-white' : 'bg-gray-200'}`;
            btn.addEventListener('click', () => {
                currentPage = i;
                renderTable();
            });
            pagination.appendChild(btn);
        }
    }

    function updateTotal() {
        document.querySelectorAll('.quantity-input').forEach(input => {
            const id = input.dataset.cardId;
            const val = parseInt(input.value) || 0;
            selectedQuantities[id] = val;
        });

        const total = Object.values(selectedQuantities).reduce((acc, qty) => acc + qty, 0);
        totalSpan.textContent = total;

        // Sauvegarde persistante (pour survivre aux rechargements liés aux filtres GET)
        localStorage.setItem(LS_KEY, JSON.stringify(selectedQuantities));
    }

    document.addEventListener('input', e => {
        if (e.target.classList.contains('quantity-input')) {
            const max = parseInt(e.target.max);
            let value = parseInt(e.target.value) || 0;
            if (value < 0) value = 0;
            if (value > max) value = max;
            e.target.value = value;

            const id = e.target.dataset.cardId;
            selectedQuantities[id] = value;
            updateTotal();
        }
    });

    // À la soumission : injecter les valeurs dans le formulaire POST
    document.getElementById('deckForm').addEventListener('submit', function() {
        // Nettoyage d'anciennes valeurs
        this.querySelectorAll('input[type="hidden"]:not([name="_token"]):not([name="_method"])').forEach(i => i.remove());

        Object.entries(selectedQuantities).forEach(([id, qty]) => {
            if (qty > 0) {
                const inputId = document.createElement('input');
                inputId.type = 'hidden';
                inputId.name = 'cards[]';
                inputId.value = id;
                this.appendChild(inputId);

                const inputQty = document.createElement('input');
                inputQty.type = 'hidden';
                inputQty.name = `quantities[${id}]`;
                inputQty.value = qty;
                this.appendChild(inputQty);
            }
        });
    });

    // Les filtres sont appliqués côté serveur -> on affiche juste la liste filtrée
    filteredCards = [...allCards];
    renderTable();
});
</script>
@endsection
