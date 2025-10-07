@extends('layouts.app-guest')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">

    <div class="flex flex-col lg:flex-row gap-8 w-full max-w-6xl">
        <!-- Bloc inscription -->
        <div class="flex-1 bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Créer un compte</h2>

            <form method="POST" action="{{ route('register') }}">
                @csrf

                <!-- Nom -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium">Nom</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium">Mot de passe</label>
                    <input id="password" type="password" name="password" required
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirmation -->
                <div class="mb-6">
                    <label for="password_confirmation" class="block text-gray-700 font-medium">Confirmer le mot de passe</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                </div>

                <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
                    S'inscrire
                </button>
            </form>
        </div>

        <!-- Bloc connexion -->
        <div class="flex-1 bg-white p-8 rounded-lg shadow-md">
            <h2 class="text-2xl font-bold text-center mb-6 text-gray-800">Se connecter</h2>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- Email -->
                <div class="mb-4">
                    <label for="login_email" class="block text-gray-700 font-medium">Adresse e-mail</label>
                    <input id="login_email" type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                    @error('email')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label for="login_password" class="block text-gray-700 font-medium">Mot de passe</label>
                    <input id="login_password" type="password" name="password" required
                           class="w-full mt-1 px-3 py-2 border rounded-md focus:outline-none focus:ring focus:ring-blue-300">
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember + Mot de passe oublié -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember"
                               class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500">
                        <span class="ml-2 text-gray-600 text-sm">Se souvenir de moi</span>
                    </label>

                    <a href="{{ route('password.request') }}" class="text-sm text-blue-500 hover:underline">
                        Mot de passe oublié ?
                    </a>
                </div>

                <button type="submit"
                        class="w-full bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
                    Se connecter
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
