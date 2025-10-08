@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Sidebar des filtres -->
                <div class="w-full lg:w-1/4 p-4 bg-gray-100 border border-gray-300 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Filtres</h3>

                    @php
                        // Types disponibles = injectés par le contrôleur
                        $typeOptions = isset($availableTypes)
                            ? $availableTypes
                            : collect();

                        // Liste de raretés commune
                        $rarities = [
                            "Common", "Short Print", "Super Short Print", "Rare", "Super Rare",
                            "Ultra Rare", "Secret Rare", "Parallel Rare", "Ultimate Rare",
                            "Ghost Rare", "Gold Rare", "Premium Gold Rare", "Platinum Rare",
                            "Prismatic Secret Rare", "Collector's Rare", "Starlight Rare",
                            "Quarter Century Secret Rare", "Starfoil Rare", "Shatterfoil Rare",
                            "Duel Terminal Normal Parallel Rare", "Duel Terminal Rare Parallel Rare",
                            "Duel Terminal Super Parallel Rare", "Duel Terminal Ultra Parallel Rare",
                            "Platinum Secret Rare", "Mosaic Rare", "10000 Secret Rare",
                            "Ghost/Gold Rare", "Gold Secret Rare"
                        ];
                    @endphp

                    <form method="GET" action="{{ route('cards.index') }}" class="space-y-4">
                        <!-- Type -->
                        <div>
                            <label for="type" class="block text-gray-700">Type</label>
                            <select name="type" id="type" class="w-full border-gray-300 rounded-md py-2 px-3">
                                <option value="">Tous</option>
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
                                   class="w-full border-gray-300 rounded-md py-2 px-3" placeholder="ex : 4" />
                        </div>

                        <!-- ATK min -->
                        <div>
                            <label for="atk" class="block text-gray-700">ATK min</label>
                            <input type="number" name="atk" id="atk" value="{{ request('atk') }}"
                                   class="w-full border-gray-300 rounded-md py-2 px-3" />
                        </div>

                        <!-- DEF min -->
                        <div>
                            <label for="def" class="block text-gray-700">DEF min</label>
                            <input type="number" name="def" id="def" value="{{ request('def') }}"
                                   class="w-full border-gray-300 rounded-md py-2 px-3" />
                        </div>

                        <!-- Rareté -->
                        <div>
                            <label for="rarity" class="block text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity" class="w-full border-gray-300 rounded-md py-2 px-3">
                                <option value="">Toutes</option>
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
                                    class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded transition">
                                Appliquer les filtres
                            </button>
                            <a href="{{ route('cards.index') }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 w-full py-2 rounded text-center font-semibold transition">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Liste des cartes -->
                <div class="flex-1 p-4 bg-white border border-gray-200 rounded-lg">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                        <!-- Barre de recherche -->
                        <form method="GET" action="{{ route('cards.index') }}" class="flex gap-2 w-full md:max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nom / Code / ucard_id..."
                                   class="flex-1 border-gray-300 rounded-md py-2 px-4" />

                            <!-- garder les filtres actifs -->
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="level" value="{{ request('level') }}">
                            <input type="hidden" name="atk" value="{{ request('atk') }}">
                            <input type="hidden" name="def" value="{{ request('def') }}">
                            <input type="hidden" name="rarity" value="{{ request('rarity') }}">

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">
                                Rechercher
                            </button>
                        </form>

                        <div class="text-gray-700 font-semibold">
                            @php
                                // Somme des exemplaires sur la page courante
                                $totalEx = collect($cards->items())->sum(function($c){ return (int)($c->nm_exemplaire ?? 0); });
                            @endphp
                            {{ $totalEx ?: ($cards->total() ?? ($cards->count() ?? 0)) }} carte(s) dans la collection
                        </div>
                    </div>

                    @if($cards->isEmpty())
                        <p class="text-gray-600">Aucune carte ne correspond à vos filtres.</p>
                    @else
                        <div class="overflow-x-auto border border-gray-300 rounded-lg">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-800 text-white text-sm">
                                    <tr>
                                        <th class="px-3 py-2 text-left">uCard ID</th>
                                        <th class="px-3 py-2 text-left">Set</th>
                                        <th class="px-3 py-2 text-left">Nom</th>
                                        <th class="px-3 py-2 text-left">Type</th>
                                        <th class="px-3 py-2 text-left">Niveau</th>
                                        <th class="px-3 py-2 text-left">ATK</th>
                                        <th class="px-3 py-2 text-left">DEF</th>
                                        <th class="px-3 py-2 text-left">Rareté</th>
                                        <th class="px-3 py-2 text-left">Prix (€)</th>
                                        <th class="px-3 py-2 text-center">Exemplaires</th>
                                        <th class="px-3 py-2 text-center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cards as $card)
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-2">{{ $card->ucard_id }}</td>
                                            <td class="px-3 py-2">{{ $card->set_code ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->name }}</td>
                                            <td class="px-3 py-2">{{ $card->card_type ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->level ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->atk ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->def ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->rarity ?? '-' }}</td>
                                            <td class="px-3 py-2">
                                                @if(isset($card->price) && $card->price !== null)
                                                    {{ number_format($card->price, 2, ',', ' ') }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-3 py-2 text-center font-semibold text-gray-800">
                                                {{ $card->nm_exemplaire ?? '1' }}
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                <div class="flex justify-center gap-2">
                                                    <a href="{{ route('cards.edit', $card) }}"
                                                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1.5 px-3 rounded text-sm transition">
                                                        Modifier
                                                    </a>
                                                    <form action="{{ route('cards.destroy', $card) }}" method="POST" onsubmit="return confirm('Supprimer cette carte ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-block bg-red-600 hover:bg-red-700 text-white font-semibold py-1.5 px-3 rounded text-sm transition">
                                                            Supprimer
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination + résumé -->
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mt-4">
                            <div class="text-sm text-gray-600">
                                @php
                                    $from  = $cards->firstItem() ?? 0;
                                    $to    = $cards->lastItem() ?? 0;
                                    $total = $cards->total() ?? $cards->count();
                                @endphp
                                Affichage de {{ $from }} à {{ $to }} sur {{ $total }} résultat{{ $total > 1 ? 's' : '' }}
                            </div>
                            <div class="flex justify-center">
                                {{ $cards->onEachSide(1)->links('pagination::tailwind') }}
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
