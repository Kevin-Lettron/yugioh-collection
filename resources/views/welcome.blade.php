@extends('layouts.app-guest')

@section('content')
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yu-Gi-Oh! Collection</title>
</head>
<body class="bg-gray-100">

    <div class="flex flex-col items-center justify-center h-[80vh] text-center px-4">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">
            Bienvenue sur Yu-Gi-Oh! Collection
        </h1>

        <p class="text-lg text-gray-600 mb-6 max-w-xl">
            Crée ton compte et commence à gérer ta collection de cartes Yu-Gi-Oh! dès maintenant.
            Ajoute, classe et consulte tes cartes facilement, le tout depuis ton espace personnel.
        </p>

        <a href="{{ route('login') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition">
            Commencer
        </a>
    </div>

</body>
</html>
@endsection
