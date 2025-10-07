@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Modifier le deck : {{ $deck->name }}</h1>

    <form id="deckEditForm" method="POST" action="{{ route('decks.update', $deck) }}">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="name" class="block font-semibold mb-2">Nom du deck</label>
                <input type="text" name="name" id="name" value="{{ $deck->name }}" class="w-full border rounded px-4 py-2" required>
            </div>
            <div>
                <label for="description" class="block font-semibold mb-2">Description</label>
                <input type="text" name="description" id="description" value="{{ $deck->description }}" class="w-full border rounded px-4 py-2">
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-semibold">Cartes du deck</h2>
            <div class="text-gray-700">
                Total sélectionné : <span id="editSelectedCount" class="font-bold">0</span> / 40–60
            </div>
        </div>

        <div class="overflow-x-auto border rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-3 py-2">Nom</th>
                        <th class="px-3 py-2">Type</th>
                        <th class="px-3 py-2 text-center">Niveau</th>
                        <th class="px-3 py-2 text-center">ATK</th>
                        <th class="px-3 py-2 text-center">DEF</th>
                        <th class="px-3 py-2 text-center">Qté dispo</th>
                        <th class="px-3 py-2 text-center">Qté dans deck</th>
                        <th class="px-3 py-2 text-center">Inclure</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $card)
                        @php
                            $inDeck   = isset($deckCards) && $deckCards->has($card->id);
                            $deckQty  = isset($deckCards) ? ($deckCards[$card->id] ?? 0) : 0;
                            $maxEdit  = min(3, $card->available_quantity + $deckQty);
                            $disableNew = ($card->available_quantity <= 0) && !$inDeck;
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $card->name }}</td>
                            <td class="px-3 py-2">{{ $card->card_type }}</td>
                            <td class="text-center px-3 py-2">{{ $card->level ?? '-' }}</td>
                            <td class="text-center px-3 py-2">{{ $card->atk ?? '-' }}</td>
                            <td class="text-center px-3 py-2">{{ $card->def ?? '-' }}</td>
                            <td class="text-center px-3 py-2 font-bold {{ $card->available_quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ $card->available_quantity }}
                            </td>
                            <td class="text-center px-3 py-2">
                                <input 
                                    type="number"
                                    name="quantities[{{ $card->id }}]"
                                    value="{{ $deckQty }}"
                                    min="0"
                                    max="{{ $maxEdit }}"
                                    class="border rounded px-2 py-1 w-16 text-center edit-quantity"
                                    {{ $disableNew ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center px-3 py-2">
                                <input 
                                    type="checkbox"
                                    class="edit-checkbox"
                                    name="cards[]"
                                    value="{{ $card->id }}"
                                    {{ $inDeck ? 'checked' : '' }}
                                    {{ $disableNew ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="editDeckError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mt-4"></div>

        <div class="mt-6 text-right">
            <button type="submit" id="editSubmitButton" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>

<script>
    // Éléments
    const editForm = document.getElementById('deckEditForm');
    const editCheckboxes = document.querySelectorAll('.edit-checkbox');
    const editQuantities = document.querySelectorAll('.edit-quantity');
    const editCounter = document.getElementById('editSelectedCount');
    const editErrorBox = document.getElementById('editDeckError');

    function sanitizeQty(input) {
        const min = parseInt(input.min || '0', 10);
        const max = parseInt(input.max || '3', 10);
        let v = parseInt(input.value || (min || 0), 10);
        if (isNaN(v)) v = min || 0;
        if (v < min) v = min;
        if (v > max) v = max;
        input.value = v;
    }

    function updateEditTotal() {
        let total = 0;
        editCheckboxes.forEach(cb => {
            if (cb.checked && !cb.disabled) {
                const row = cb.closest('tr');
                const input = row.querySelector('.edit-quantity');
                if (input && !input.disabled) {
                    sanitizeQty(input);
                    total += parseInt(input.value || '0', 10);
                }
            }
        });
        editCounter.textContent = total;
        return total;
    }

    // Synchronisation checkbox <-> quantité
    editCheckboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const row = cb.closest('tr');
            const input = row.querySelector('.edit-quantity');
            if (!input) return;

            if (cb.checked) {
                // si coché, s'assurer d'avoir au moins 1 (si possible)
                const min = parseInt(input.min || '0', 10);
                if (parseInt(input.value || '0', 10) === 0 && (parseInt(input.max || '0', 10) > 0)) {
                    input.value = Math.max(1, min);
                }
                input.disabled = false;
            } else {
                // si décoché, mettre la quantité à 0 (elle ne sera pas prise côté serveur)
                input.value = 0;
            }
            updateEditTotal();
        });
    });

    editQuantities.forEach(input => {
        input.addEventListener('input', () => {
            sanitizeQty(input);
            updateEditTotal();
        });
        // Init
        sanitizeQty(input);
    });

    // Init compteur
    updateEditTotal();

    editForm.addEventListener('submit', (e) => {
        const total = updateEditTotal();
        if (total < 40 || total > 60) {
            e.preventDefault();
            editErrorBox.textContent = 'Le deck doit contenir entre 40 et 60 cartes (somme des quantités cochées).';
            editErrorBox.classList.remove('hidden');
            editErrorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            editErrorBox.classList.add('hidden');
        }
    });
</script>
@endsection
