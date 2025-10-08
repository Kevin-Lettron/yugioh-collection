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

                    @php
                        // Types disponibles : ceux présents dans la collection de l'utilisateur
                        $typeOptions = isset($availableTypes)
                            ? $availableTypes
                            : (isset($cards) ? $cards->pluck('card_type')->filter()->unique()->sort()->values() : collect());

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

                    <form method="GET" action="{{ route('users.collection', $user) }}" class="space-y-4">
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

                            <a href="{{ route('users.collection', $user) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded w-full text-center transition">
                                Réinitialiser
                            </a>
                        </div>
                    </form>
                </div>

                <!-- TABLEAU DES CARTES -->
                <div class="lg:col-span-3">
                    <!-- Recherche -->
                    <div class="mb-4">
                        <form method="GET" action="{{ route('users.collection', $user) }}" class="flex gap-2">
                            <input type="text" name="search" value="{{ request('search') }}"
                                   placeholder="Nom / Code / ucard_id..."
                                   class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">

                            <!-- conserver les filtres actifs -->
                            <input type="hidden" name="type" value="{{ request('type') }}">
                            <input type="hidden" name="level" value="{{ request('level') }}">
                            <input type="hidden" name="atk" value="{{ request('atk') }}">
                            <input type="hidden" name="def" value="{{ request('def') }}">
                            <input type="hidden" name="rarity" value="{{ request('rarity') }}">

                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">
                                Rechercher
                            </button>
                        </form>
                    </div>

                    <!-- Résumé -->
                    <div class="flex items-center justify-between mb-3 text-sm text-gray-700">
                        @php
                            $from  = $cards->firstItem() ?? 0;
                            $to    = $cards->lastItem() ?? 0;
                            $total = $cards->total() ?? $cards->count();
                            // somme des exemplaires si dispo
                            $totalEx = collect($cards->items())->sum(fn($c) => (int)($c->nm_exemplaire ?? 0));
                        @endphp
                        <div>
                            Affichage de {{ $from }} à {{ $to }} sur {{ $total }} résultat{{ $total > 1 ? 's' : '' }}
                            @if($totalEx)
                                — {{ $totalEx }} exemplaire{{ $totalEx > 1 ? 's' : '' }} sur cette page
                            @endif
                        </div>
                        <div class="font-semibold">
                            Total collection : {{ number_format($collectionCount ?? 0, 0, ',', ' ') }} carte(s)
                        </div>
                    </div>

                    <!-- Tableau -->
                    <div class="overflow-x-auto border border-gray-300 rounded-lg">
                        <table class="w-full border-collapse border border-gray-300">
                            <thead class="bg-gray-800 text-white">
                                <tr>
                                    <th class="px-4 py-2 text-left">Nom</th>
                                    <th class="px-4 py-2 text-left">Type</th>
                                    <th class="px-4 py-2 text-left">Niveau</th>
                                    <th class="px-4 py-2 text-left">ATK</th>
                                    <th class="px-4 py-2 text-left">DEF</th>
                                    <th class="px-4 py-2 text-left">Rareté</th>
                                    <th class="px-4 py-2 text-center">Quantité</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cards as $card)
                                    <tr class="border-t hover:bg-gray-50">
                                        <td class="px-4 py-2 font-semibold">{{ $card->name }}</td>
                                        <td class="px-4 py-2">{{ $card->card_type ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $card->level ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $card->atk ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $card->def ?? '-' }}</td>
                                        <td class="px-4 py-2">{{ $card->rarity ?? '-' }}</td>
                                        <td class="px-4 py-2 text-center">{{ (int)($card->nm_exemplaire ?? 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-6 text-center text-gray-600">
                                            Aucune carte ne correspond à vos filtres.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="flex justify-center items-center mt-4">
                        {{ $cards->onEachSide(1)->links('pagination::tailwind') }}
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
@endsection
