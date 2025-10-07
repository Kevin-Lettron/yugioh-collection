@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <h2 class="text-2xl font-bold mb-6 text-center">
                Collection de {{ $user->name }}
            </h2>

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- FILTRES -->
                <div class="w-full bg-gray-100 border border-gray-300 rounded-lg p-4">
                    <h3 class="text-xl font-semibold mb-4">Filtres</h3>

                    <div class="mb-4">
                        <label class="block text-gray-700">Type de carte</label>
                        <select id="filterType" class="w-full border-gray-300 rounded-md py-2 px-4">
                            <option value="">Tous les types</option>
                            <option value="Normal">Normal</option>
                            <option value="Effect">Effect</option>
                            <option value="Fusion">Fusion</option>
                            <option value="Ritual">Ritual</option>
                            <option value="Synchro">Synchro</option>
                            <option value="XYZ">XYZ</option>
                            <option value="Link">Link</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Rang de monstre</label>
                        <input type="number" id="filterLevel" placeholder="Niveau" class="w-full border-gray-300 rounded-md py-2 px-4" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">ATK minimum</label>
                        <input type="number" id="filterAtk" placeholder="ATK min" class="w-full border-gray-300 rounded-md py-2 px-4" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">DEF minimum</label>
                        <input type="number" id="filterDef" placeholder="DEF min" class="w-full border-gray-300 rounded-md py-2 px-4" />
                    </div>

                    <div class="mb-4">
                        <label class="block text-gray-700">Rareté</label>
                        <select id="filterRarity" class="w-full border-gray-300 rounded-md py-2 px-4">
                            <option value="">Toutes les raretés</option>
                            <option value="Ultra Rare">Ultra Rare</option>
                            <option value="Secret Rare">Secret Rare</option>
                            <option value="Super Rare">Super Rare</option>
                            <option value="Common">Common</option>
                        </select>
                    </div>

                    <button type="button" id="applyFilters" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded w-full transition">
                        Appliquer les filtres
                    </button>
                </div>

                <!-- TABLEAU DES CARTES -->
                <div class="lg:col-span-3">
                    <!-- Recherche -->
                    <div class="mb-4">
                        <div class="flex gap-2">
                            <input type="text" id="searchInput" placeholder="Rechercher une carte..." class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
                            <button type="button" id="searchButton" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">Rechercher</button>
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="overflow-x-auto">
                        <table id="cardsTable" class="w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="px-4 py-2">Nom</th>
                                    <th class="px-4 py-2">Type</th>
                                    <th class="px-4 py-2">Niveau</th>
                                    <th class="px-4 py-2">ATK</th>
                                    <th class="px-4 py-2">DEF</th>
                                    <th class="px-4 py-2">Rareté</th>
                                    <th class="px-4 py-2">Quantité possédée</th>
                                </tr>
                            </thead>
                            <tbody id="cardsBody"></tbody>
                        </table>
                    </div>

                    <div class="flex justify-center items-center mt-4">
                        <div id="pagination" class="flex gap-2"></div>
                    </div>

                    <div class="mt-6 text-center">
                        <a href="{{ route('users.show', $user) }}" class="text-blue-600 hover:underline">
                            ← Retour au profil de {{ $user->name }}
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const allCards = @json($cards);
    const cardsPerPage = 10;
    let currentPage = 1;
    let filteredCards = [...allCards];

    const tableBody = document.getElementById('cardsBody');
    const pagination = document.getElementById('pagination');

    // 🧮 Fonction d'affichage du tableau
    function renderTable() {
        tableBody.innerHTML = '';
        const start = (currentPage - 1) * cardsPerPage;
        const end = start + cardsPerPage;
        const pageCards = filteredCards.slice(start, end);

        pageCards.forEach(card => {
            const row = document.createElement('tr');
            row.classList.add('hover:bg-gray-100');
            row.innerHTML = `
                <td class="px-4 py-2 font-semibold">${card.name}</td>
                <td class="px-4 py-2">${card.card_type}</td>
                <td class="px-4 py-2">${card.level ?? '-'}</td>
                <td class="px-4 py-2">${card.atk ?? '-'}</td>
                <td class="px-4 py-2">${card.def ?? '-'}</td>
                <td class="px-4 py-2">${card.rarity ?? '-'}</td>
                <td class="px-4 py-2 text-center">${Number(card.nm_exemplaire ?? 0)}</td>`;
            tableBody.appendChild(row);
        });

        renderPagination();
    }

    // 📜 Pagination
    function renderPagination() {
        pagination.innerHTML = '';
        const totalPages = Math.ceil(filteredCards.length / cardsPerPage);
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

    // 🎯 Application des filtres
    function applyFilters() {
        const type = document.getElementById('filterType').value;
        const level = parseInt(document.getElementById('filterLevel').value);
        const atk = parseInt(document.getElementById('filterAtk').value);
        const def = parseInt(document.getElementById('filterDef').value);
        const rarity = document.getElementById('filterRarity').value;
        const search = document.getElementById('searchInput').value.toLowerCase();

        filteredCards = allCards.filter(c => {
            return (!type || c.card_type === type)
                && (!rarity || c.rarity === rarity)
                && (!level || (c.level ?? 0) >= level)
                && (!atk || (c.atk ?? 0) >= atk)
                && (!def || (c.def ?? 0) >= def)
                && (!search || c.name.toLowerCase().includes(search));
        });

        currentPage = 1;
        renderTable();
    }

    document.getElementById('applyFilters').addEventListener('click', applyFilters);
    document.getElementById('searchButton').addEventListener('click', applyFilters);

    renderTable();
});
</script>
@endsection
