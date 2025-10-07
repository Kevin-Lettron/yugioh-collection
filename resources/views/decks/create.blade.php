@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto py-10">
    <h1 class="text-3xl font-bold mb-6">Créer un nouveau deck</h1>

    <form id="deckForm" method="POST" action="{{ route('decks.store') }}">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <div>
                <label for="name" class="block font-semibold mb-2">Nom du deck</label>
                <input type="text" name="name" id="name" class="w-full border rounded px-4 py-2" required>
            </div>
            <div>
                <label for="description" class="block font-semibold mb-2">Description</label>
                <input type="text" name="description" id="description" class="w-full border rounded px-4 py-2">
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
            <h2 class="text-xl font-semibold">Sélectionne tes cartes</h2>
            <div class="text-gray-700">
                Total sélectionné : <span id="selectedCount" class="font-bold">0</span> / 40–60
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
                        <th class="px-3 py-2 text-center">Qté à ajouter</th>
                        <th class="px-3 py-2 text-center">Ajouter</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cards as $card)
                        @php
                            $maxAdd = min(3, $card->available_quantity);
                            $disabled = $card->available_quantity <= 0;
                        @endphp
                        <tr class="border-b hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $card->name }}</td>
                            <td class="px-3 py-2">{{ $card->card_type }}</td>
                            <td class="text-center px-3 py-2">{{ $card->level ?? '-' }}</td>
                            <td class="text-center px-3 py-2">{{ $card->atk ?? '-' }}</td>
                            <td class="text-center px-3 py-2">{{ $card->def ?? '-' }}</td>
                            <td class="text-center px-3 py-2 font-bold {{ $disabled ? 'text-red-600' : 'text-green-600' }}">
                                {{ $card->available_quantity }}
                            </td>
                            <td class="text-center px-3 py-2">
                                <input 
                                    type="number"
                                    name="quantities[{{ $card->id }}]"
                                    value="{{ $disabled ? 0 : 1 }}"
                                    min="{{ $disabled ? 0 : 1 }}"
                                    max="{{ $maxAdd }}"
                                    class="border rounded px-2 py-1 w-16 text-center quantity-input"
                                    {{ $disabled ? 'disabled' : '' }}>
                            </td>
                            <td class="text-center px-3 py-2">
                                <input 
                                    type="checkbox"
                                    class="card-checkbox"
                                    name="cards[]"
                                    value="{{ $card->id }}"
                                    {{ $disabled ? 'disabled' : '' }}>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div id="deckError" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mt-4"></div>

        <div class="mt-6 text-right">
            <button type="submit" id="submitButton" class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded font-semibold">
                Enregistrer le deck
            </button>
        </div>
    </form>
</div>

<script>
    // Sélection des éléments
    const form = document.getElementById('deckForm');
    const checkboxes = document.querySelectorAll('.card-checkbox');
    const qtyInputs = document.querySelectorAll('.quantity-input');
    const counter = document.getElementById('selectedCount');
    const errorBox = document.getElementById('deckError');

    function sanitizeQty(input) {
        const min = parseInt(input.min || '0', 10);
        const max = parseInt(input.max || '3', 10);
        let v = parseInt(input.value || (min || 0), 10);
        if (isNaN(v)) v = min || 0;
        if (v < min) v = min;
        if (v > max) v = max;
        input.value = v;
    }

    function updateTotal() {
        let total = 0;
        checkboxes.forEach(cb => {
            if (cb.checked && !cb.disabled) {
                const row = cb.closest('tr');
                const input = row.querySelector('.quantity-input');
                if (input && !input.disabled) {
                    sanitizeQty(input);
                    total += parseInt(input.value || '0', 10);
                }
            }
        });
        counter.textContent = total;
        return total;
    }

    // Cohérence checkbox <-> quantité
    checkboxes.forEach(cb => {
        cb.addEventListener('change', () => {
            const row = cb.closest('tr');
            const input = row.querySelector('.quantity-input');
            if (!input) return;

            if (cb.checked) {
                if (parseInt(input.value || '0', 10) === 0) {
                    input.value = Math.max(1, parseInt(input.min || '1', 10));
                }
                input.disabled = false;
            } else {
                // On ne désactive pas l'input pour conserver la valeur, mais on le met à 0
                input.value = 0;
            }
            updateTotal();
        });
    });

    qtyInputs.forEach(input => {
        input.addEventListener('input', () => {
            sanitizeQty(input);
            updateTotal();
        });
        // Init
        sanitizeQty(input);
    });

    // Init compteur
    updateTotal();

    form.addEventListener('submit', (e) => {
        const total = updateTotal();
        if (total < 40 || total > 60) {
            e.preventDefault();
            errorBox.textContent = 'Le deck doit contenir entre 40 et 60 cartes (sommes des quantités cochées).';
            errorBox.classList.remove('hidden');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            errorBox.classList.add('hidden');
        }
    });
</script>
@endsection
