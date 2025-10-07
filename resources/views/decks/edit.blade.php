@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-screen-2xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">

            <h2 class="text-2xl font-bold mb-6">Modifier le deck : {{ $deck->name }}</h2>

            <form action="{{ route('decks.update', $deck) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                    <!-- Sidebar des filtres -->
                    <div class="w-full bg-gray-100 border border-gray-300 rounded-lg p-4">
                        <h3 class="text-xl font-semibold mb-4">Filtres</h3>

                        <!-- Type -->
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

                        <!-- Niveau -->
                        <div class="mb-4">
                            <label for="level" class="block text-gray-700">Rang de monstre</label>
                            <input type="number" name="level" id="level" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="Niveau du monstre" />
                        </div>

                        <!-- ATK -->
                        <div class="mb-4">
                            <label for="atk" class="block text-gray-700">ATK minimum</label>
                            <input type="number" name="atk" id="atk" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="ATK minimum" />
                        </div>

                        <!-- DEF -->
                        <div class="mb-4">
                            <label for="def" class="block text-gray-700">DEF minimum</label>
                            <input type="number" name="def" id="def" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4" placeholder="DEF minimum" />
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

                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition w-full" style="background: #3ca5ff; border-radius: 50px;">
                            Appliquer les filtres
                        </button>
                    </div>

                    <!-- Contenu principal -->
                    <div class="lg:col-span-3">
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-gray-700 font-semibold">Nom du deck</label>
                                <input type="text" name="name" value="{{ $deck->name }}" class="w-full border-gray-300 rounded-md py-2 px-4" required>
                            </div>
                            <div>
                                <label class="block text-gray-700 font-semibold">Description</label>
                                <input type="text" name="description" value="{{ $deck->description }}" class="w-full border-gray-300 rounded-md py-2 px-4">
                            </div>
                        </div>

                        <!-- Barre de recherche -->
                        <form action="{{ route('decks.edit', $deck) }}" method="GET" class="mb-4">
                            <div class="flex gap-2">
                                <input type="text" name="search" value="{{ request()->query('search') }}" placeholder="Rechercher une carte" class="flex-1 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded transition">
                                    Rechercher
                                </button>
                            </div>
                        </form>

                        @error('cards')
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                                {{ $message }}
                            </div>
                        @enderror

                        <div class="overflow-x-auto">
                            <table class="w-full border-collapse border border-gray-300">
                                <thead class="bg-gray-800 text-white">
                                    <tr>
                                        <th class="px-4 py-2">Nom</th>
                                        <th class="px-4 py-2">Type</th>
                                        <th class="px-4 py-2">Niveau</th>
                                        <th class="px-4 py-2">ATK</th>
                                        <th class="px-4 py-2">DEF</th>
                                        <th class="px-4 py-2">Qté dispo</th>
                                        <th class="px-4 py-2">Qté dans deck</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cards as $card)
                                        <tr class="hover:bg-gray-100">
                                            <td class="px-4 py-2">{{ $card->name }}</td>
                                            <td class="px-4 py-2">{{ $card->card_type }}</td>
                                            <td class="px-4 py-2">{{ $card->level ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $card->atk ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $card->def ?? '-' }}</td>
                                            <td class="px-4 py-2 font-semibold {{ $card->available_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                {{ $card->available_quantity }}
                                            </td>
                                            <td class="px-4 py-2">
                                                <input type="number" name="quantities[{{ $card->id }}]" min="0" max="{{ $card->available_quantity }}" value="{{ $card->selected_quantity ?? 0 }}" class="w-16 border rounded px-2 py-1 text-center quantity-input" data-card-id="{{ $card->id }}">
                                                <input type="hidden" name="cards[]" value="{{ $card->id }}">
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <p class="mt-3 text-sm text-gray-700 text-right">
                            Total sélectionné : <span id="selected-total">0</span> / 40–60
                        </p>

                        <div class="mt-6 text-right">
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                                Mettre à jour le deck
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const quantityInputs = document.querySelectorAll('.quantity-input');
    const totalSpan = document.getElementById('selected-total');

    function updateTotal() {
        let total = 0;
        quantityInputs.forEach(input => {
            total += parseInt(input.value) || 0;
        });
        totalSpan.textContent = total;
    }

    quantityInputs.forEach(input => {
        input.addEventListener('input', function () {
            const max = parseInt(this.max);
            let value = parseInt(this.value) || 0;
            if (value < 0) this.value = 0;
            if (value > max) this.value = max;
            updateTotal();
        });
    });

    updateTotal();
});
</script>
@endsection
