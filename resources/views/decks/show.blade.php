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
                    <form method="GET" class="space-y-4">
                        <div>
                            <label for="type" class="block text-gray-700">Type</label>
                            <select name="type" id="type" class="w-full border-gray-300 rounded-md py-2 px-3">
                                <option value="">Tous</option>
                                <option value="Normal" {{ request('type')=='Normal' ? 'selected' : '' }}>Normal</option>
                                <option value="Effect" {{ request('type')=='Effect' ? 'selected' : '' }}>Effect</option>
                                <option value="Fusion" {{ request('type')=='Fusion' ? 'selected' : '' }}>Fusion</option>
                                <option value="Ritual" {{ request('type')=='Ritual' ? 'selected' : '' }}>Ritual</option>
                                <option value="Synchro" {{ request('type')=='Synchro' ? 'selected' : '' }}>Synchro</option>
                                <option value="XYZ" {{ request('type')=='XYZ' ? 'selected' : '' }}>XYZ</option>
                                <option value="Link" {{ request('type')=='Link' ? 'selected' : '' }}>Link</option>
                            </select>
                        </div>

                        <div>
                            <label for="level" class="block text-gray-700">Niveau</label>
                            <input type="number" name="level" id="level" value="{{ request('level') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3">
                        </div>

                        <div>
                            <label for="atk" class="block text-gray-700">ATK min</label>
                            <input type="number" name="atk" id="atk" value="{{ request('atk') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3">
                        </div>

                        <div>
                            <label for="def" class="block text-gray-700">DEF min</label>
                            <input type="number" name="def" id="def" value="{{ request('def') }}"
                                class="w-full border-gray-300 rounded-md py-2 px-3">
                        </div>

                        <div>
                            <label for="rarity" class="block text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity" class="w-full border-gray-300 rounded-md py-2 px-3">
                                <option value="">Toutes</option>
                                <option value="Ultra Rare" {{ request('rarity')=='Ultra Rare' ? 'selected' : '' }}>Ultra Rare</option>
                                <option value="Secret Rare" {{ request('rarity')=='Secret Rare' ? 'selected' : '' }}>Secret Rare</option>
                                <option value="Super Rare" {{ request('rarity')=='Super Rare' ? 'selected' : '' }}>Super Rare</option>
                                <option value="Common" {{ request('rarity')=='Common' ? 'selected' : '' }}>Common</option>
                            </select>
                        </div>

                        <button type="submit"
                                class="bg-blue-600 hover:bg-blue-700 text-white w-full py-2 rounded mt-4">
                            Appliquer les filtres
                        </button>
                    </form>
                </div>

                <!-- Liste des cartes du deck -->
                <div class="flex-1 border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between items-center mb-4">
                        <form method="GET" class="flex gap-2 w-full max-w-md">
                            <input type="text" name="search" value="{{ request('search') }}"
                                placeholder="Rechercher une carte..." class="flex-1 border-gray-300 rounded-md py-2 px-4">
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
