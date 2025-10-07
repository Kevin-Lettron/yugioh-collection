@extends('layouts.app')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <h2 class="text-2xl font-bold mb-6">Modifier la carte : {{ $card->name }}</h2>

            <form action="{{ route('cards.update', $card) }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Champ pour modifier le prix -->
                <div class="mb-4">
                    <label for="price" class="block text-gray-700 font-semibold mb-2">Prix (€)</label>
                    <input type="text" name="price" value="{{ old('price', $card->price) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4 mb-2" placeholder="Entrez le prix">
                </div>

                <!-- Champ pour modifier le nombre d'exemplaires -->
                <div class="mb-4">
                    <label for="nm_exemplaire" class="block text-gray-700 font-semibold mb-2">Nombre d'exemplaires</label>
                    <input type="number" name="nm_exemplaire" value="{{ old('nm_exemplaire', $card->nm_exemplaire) }}" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 py-2 px-4 mb-2" placeholder="Entrez le nombre d'exemplaires" min="1">
                </div>

                <!-- Bouton de mise à jour -->
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded transition" style="background: #3ca5ff; border-radius: 50px;">
                    Mettre à jour
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
