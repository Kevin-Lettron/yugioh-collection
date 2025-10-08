@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <!-- En-tête du deck -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold">{{ $deck->name }}</h1>
                    <p class="text-gray-600">{{ $deck->description ?? 'Aucune description' }}</p>
                </div>

                @if(!$readOnly)
                    <a href="{{ route('decks.edit', $deck) }}"
                       class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold transition">
                        ✏️ Modifier le deck
                    </a>
                @endif
            </div>

            <!-- Message lecture seule -->
            @if($readOnly)
                <div class="mb-6 p-4 bg-yellow-100 border border-yellow-300 text-yellow-800 rounded-md">
                    ⚠️ Ce deck appartient à 
                    <strong>
                        <a href="{{ route('users.show', $deck->user) }}" 
                           class="text-blue-600 hover:underline">
                            {{ $deck->user->name }}
                        </a>
                    </strong>. 
                    Vous le consultez en <strong>lecture seule</strong>.
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-6">

                <!-- Sidebar filtres -->
                <div class="w-full lg:w-1/4 p-4 bg-gray-100 border border-gray-300 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Filtres</h3>

                    @php
                        // Types disponibles = ceux présents dans le deck
                        $typeOptions = isset($typeOptions)
                            ? $typeOptions
                            : $cards->pluck('card_type')->filter()->unique()->sort()->values();
                    @endphp

                    <form method="GET" action="{{ route('decks.show', $deck) }}" class="space-y-4">
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

                        <!-- Niveau -->
                        <div>
                            <label for="level" class="block text-gray-700">Niveau exact</label>
                            <input type="number" name="level" id="level" value="{{ request('level') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3" placeholder="ex : 4">
                        </div>

                        <!-- ATK -->
                        <div>
                            <label for="atk" class="block text-gray-700">ATK min</label>
                            <input type="number" name="atk" id="atk" value="{{ request('atk') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3">
                        </div>

                        <!-- DEF -->
                        <div>
                            <label for="def" class="block text-gray-700">DEF min</label>
                            <input type="number" name="def" id="def" value="{{ request('def') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3">
                        </div>

                        <!-- Rareté -->
                        <div>
                            <label for="rarity" class="block text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity" class="w-full border-gray-300 rounded-md py-2 px-3">
                                <option value="">Toutes</option>
                                @php
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
                                @foreach($rarities as $r)
                                    <option value="{{ $r }}" {{ request('rarity') == $r ? 'selected' : '' }}>
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
                            <a href="{{ route('decks.show', $deck) }}"
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 w-full py-2 rounded text-center font-semibold transition">
                                Réinitialiser les filtres
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Liste des cartes -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <form method="GET" action="{{ route('decks.show', $deck) }}" class="flex gap-2 w-full max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Rechercher une carte..." class="flex-1 border-gray-300 rounded-md py-2 px-4">

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
                            {{ $cards->sum('pivot.quantity') }} carte(s) dans le deck
                        </div>
                    </div>

                    @if($cards->isEmpty())
                        <p class="text-gray-600">Aucune carte ne correspond à vos filtres.</p>
                    @else
                        <div class="overflow-x-auto border border-gray-300 rounded-lg">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-800 text-white text-sm">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Nom</th>
                                        <th class="px-3 py-2 text-left">Type</th>
                                        <th class="px-3 py-2 text-left">Niveau</th>
                                        <th class="px-3 py-2 text-left">ATK</th>
                                        <th class="px-3 py-2 text-left">DEF</th>
                                        <th class="px-3 py-2 text-left">Rareté</th>
                                        <th class="px-3 py-2 text-center">Exemplaires</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cards as $card)
                                        <tr class="border-t hover:bg-gray-50">
                                            <td class="px-3 py-2">{{ $card->name }}</td>
                                            <td class="px-3 py-2">{{ $card->card_type ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->level ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->atk ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->def ?? '-' }}</td>
                                            <td class="px-3 py-2">{{ $card->rarity ?? '-' }}</td>
                                            <td class="px-3 py-2 text-center font-semibold text-gray-800">
                                                x{{ $card->pivot->quantity }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

            <div class="mt-6 text-center">
                <a href="{{ route('decks.index') }}"
                   class="text-blue-600 hover:underline font-semibold">
                    ← Retour à mes decks
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
