<nav class="bg-teal-800 text-white shadow-md">
    <div class="max-w-7xl mx-auto px-6 sm:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo / Nom -->
            <div class="flex items-center">
                <a href="{{ url('/') }}" class="text-2xl font-semibold hover:text-gray-200 transition">
                    Yu-Gi-Oh! Collection
                </a>
            </div>

            <!-- Bouton Login -->
            <div class="flex items-center">
                <a href="{{ route('login') }}" 
                   class="bg-blue-500 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200">
                    Login
                </a>
            </div>
        </div>
    </div>
</nav>
