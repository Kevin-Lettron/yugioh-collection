@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <h2 class="text-2xl font-bold mb-6">Ajouter une carte Yu-Gi-Oh!</h2>

            {{-- Champ de saisie du code d'édition --}}
            <div class="mb-4">
                <label for="card-set" class="block text-gray-700 font-semibold mb-2">
                    Code d'édition (en bas à droite de l'image)
                </label>
                <input type="text" id="card-set"
                       placeholder="Ex : LDK2-FRY10, LOB-001, SDBE-FR001"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                       required>
                <p class="text-sm text-gray-500 mt-1">
                    Entrez uniquement le code visible en bas à droite du cadre de l'image de la carte.
                    Fonctionne avec tous les types de cartes : monstres, magies et pieges.
                </p>
            </div>

            <div class="flex justify-between items-center mt-6">
                <a href="{{ route('cards.index') }}" class="text-gray-600 hover:text-gray-800 underline">
                    ← Retour à la collection
                </a>
                <button id="search-btn" class="text-white font-semibold py-2 px-4 rounded transition" style="background: #3ca5ff; border-radius: 50px;">
                    🔍 Rechercher la carte
                </button>
            </div>

            {{-- Indicateur de chargement --}}
            <div id="loading-spinner" class="hidden mt-6 text-center text-gray-600">
                ⏳ Recherche de la carte en cours...
            </div>

            {{-- Zone d’aperçu --}}
            <div id="card-preview" class="hidden mt-8 border-t pt-6">
                <h3 class="text-xl font-semibold mb-4">Aperçu de la carte</h3>
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                    <img id="card-image" src="" alt="Carte Yu-Gi-Oh" class="w-48 rounded shadow-md border">
                    <div class="text-sm leading-relaxed">
                        <p><strong>Nom :</strong> <span id="card-name"></span></p>
                        <p><strong>Type :</strong> <span id="card-type"></span></p>
                        <p><strong>Level :</strong> <span id="card-level"></span></p> <!-- Affichage du level -->
                        <p><strong>ATK :</strong> <span id="card-atk"></span> / 
                           <strong>DEF :</strong> <span id="card-def"></span></p>
                        <p><strong>Rareté :</strong> <span id="card-rarity"></span></p>
                        <p><strong>Prix :</strong> <span id="card-price"></span> €</p>
                    </div>
                </div>

                <button id="save-btn"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition mt-6" style="background: #3ca5ff; border-radius: 50px;">
                    Enregistrer dans ma collection
                </button>
            </div>

        </div>
    </div>
</div>

{{-- Script pour l'appel AJAX --}}
<script>
document.getElementById('search-btn').addEventListener('click', async () => {
    const setCode = document.getElementById('card-set').value.trim().toUpperCase();

    if (!setCode) {
        return alert('Veuillez entrer le code d\'edition de la carte.');
    }

    console.log('Recherche pour', setCode);

    const preview = document.getElementById('card-preview');
    const loader = document.getElementById('loading-spinner');
    const btn = document.getElementById('search-btn');

    preview.classList.add('hidden');
    loader.classList.remove('hidden');
    btn.disabled = true;

    try {
        const res = await fetch(`/api/card/${encodeURIComponent(setCode)}`);
        const data = await res.json();

        if (!res.ok || data.error) {
            alert(data.error || "Aucune carte trouvee pour ce code.");
            loader.classList.add('hidden');
            btn.disabled = false;
            return;
        }

        // Mise à jour de l'aperçu
        document.getElementById('card-image').src = data.image ?? '';
        document.getElementById('card-name').textContent = data.name ?? 'Inconnue';
        document.getElementById('card-type').textContent = data.type ?? 'Inconnu';
        document.getElementById('card-level').textContent = data.level ?? '-';
        document.getElementById('card-atk').textContent = data.atk ?? '-';
        document.getElementById('card-def').textContent = data.def ?? '-';
        document.getElementById('card-rarity').textContent = data.rarity ?? 'N/A';
        document.getElementById('card-price').textContent = data.price ?? '---';

        // Stocker le set code pour l'enregistrement
        document.getElementById('save-btn').setAttribute('data-code', setCode);
        document.getElementById('save-btn').setAttribute('data-level', data.level);

        preview.classList.remove('hidden');
    } catch (e) {
        console.error(e);
        alert("Erreur lors de la recherche de la carte. Verifiez votre connexion Internet.");
    } finally {
        loader.classList.add('hidden');
        btn.disabled = false;
    }
});


// Enregistrement via POST
document.getElementById('save-btn').addEventListener('click', async (e) => {
    const code = e.target.getAttribute('data-code');
    const level = e.target.getAttribute('data-level');

    if (!code) return alert('Aucune carte a enregistrer.');

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    const res = await fetch(`{{ route('cards.store') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify({ codes: code, level: level })
    });

    if (res.ok) {
        alert('Carte ajoutee a votre collection !');
        window.location.href = "{{ route('cards.index') }}";
    } else {
        const err = await res.text();
        console.error(err);
        alert('Erreur lors de l\'enregistrement.');
    }
});
</script>
@endsection
