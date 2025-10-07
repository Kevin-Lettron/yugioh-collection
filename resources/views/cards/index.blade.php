@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Sidebar des filtres -->
                <div class="w-full lg:w-1/4 p-4 bg-gray-100 border border-gray-300 rounded-lg">
                    <h3 class="text-xl font-semibold mb-4">Filtres</h3>
                    <form action="{{ route('cards.index') }}" method="GET">
                        <!-- Type de carte -->
                        <div class="mb-4">
                            <label for="type" class="block text-gray-700">Type de carte</label>
                            <select name="type" id="type" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
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

                        <!-- Rang de monstre -->
                        <div class="mb-4">
                            <label for="level" class="block text-gray-700">Rang de monstre</label>
                            <input type="number" name="level" id="level" value="{{ request()->query('level') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="Rang de monstre" />
                        </div>

                        <!-- ATK -->
                        <div class="mb-4">
                            <label for="atk" class="block text-gray-700">ATK minimum</label>
                            <input type="number" name="atk" id="atk" value="{{ request()->query('atk') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="ATK minimum" />
                        </div>

                        <!-- DEF -->
                        <div class="mb-4">
                            <label for="def" class="block text-gray-700">DEF minimum</label>
                            <input type="number" name="def" id="def" value="{{ request()->query('def') }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="DEF minimum" />
                        </div>

                        <!-- Rareté -->
                        <div class="mb-4">
                            <label for="rarity" class="block text-gray-700">Rareté</label>
                            <select name="rarity" id="rarity" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
                                <option value="">Toutes les raretés</option>
                                <option value="Ultra Rare">Ultra Rare</option>
                                <option value="Secret Rare">Secret Rare</option>
                                <option value="Super Rare">Super Rare</option>
                                <option value="Common">Common</option>
                            </select>
                        </div>

                        <!-- Bouton de recherche -->
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition w-full" style="background: #3ca5ff; border-radius: 50px;">
                            Appliquer les filtres
                        </button>
                    </form>
                </div>

                <!-- Liste des cartes -->
                <div class="flex-1 p-4 bg-white border border-gray-200 rounded-lg overflow-x-visible">
                    <h2 class="text-2xl font-bold mb-6">Ma collection de cartes Yu-Gi-Oh!</h2>

                    <!-- Barre de recherche -->
                    <div class="mb-4">
                        <form action="{{ route('cards.index') }}" method="GET">
                            <div class="flex gap-2">
                                <input type="text" name="search" value="{{ request()->query('search') }}" placeholder="Rechercher par nom, ID ou série" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" />
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">
                                    Rechercher
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Message de succès -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Liste des cartes -->
                    @if($cards->isEmpty())
                        <p class="text-gray-600">Aucune carte enregistrée pour le moment.</p>
                    @else
                        <div class="overflow-x-visible">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">ID</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">Série</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-2/12">Nom</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-2/12">Type</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">Level</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">ATK</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">DEF</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-2/12">Rareté</th>
                                        <th class="px-4 py-2 border border-gray-300 text-left w-1/12">Prix (€)</th>
                                        <th class="px-4 py-2 border border-gray-300 text-center w-2/12">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($cards as $card)
                                        <tr class="hover:bg-gray-100">
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->ucard_id }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->set_code ?? '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->name }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->card_type }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->level ?? '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->atk ?? '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->def ?? '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->rarity ?? '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300">{{ $card->price ? number_format($card->price, 2, ',', ' ') : '-' }}</td>
                                            <td class="px-4 py-2 border border-gray-300 text-center whitespace-nowrap">
                                                <div class="flex justify-center gap-2">
                                                    <a href="{{ route('cards.edit', $card) }}"
                                                       class="inline-block w-24 text-center bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 rounded text-sm transition">
                                                        Modifier
                                                    </a>
                                                    <form action="{{ route('cards.destroy', $card) }}" method="POST" onsubmit="return confirm('Supprimer cette carte ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-block w-24 text-center bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded text-sm transition">
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
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
