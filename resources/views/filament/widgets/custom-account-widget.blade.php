<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                {{-- Custom avatar --}}
                <img
                    src="{{ $avatarUrl }}"
                    alt="Avatar"
                    class="w-12 h-12 rounded-full border-2 " style="width: 3rem; height: 3rem;"
                >

                {{-- Welcome text --}}
                <div  style="margin-left: 1rem;">
                    <div class="font-bold text-lg">
                        স্বাগতম
                    </div>
                    <div class="text-primary-600">
                        {{ $user->name }}
                    </div>
                </div>
            </div>

            {{-- Sign out --}}
            <form method="POST" action="{{ route('filament.admin.auth.logout') }}">
                @csrf
                <button
                    type="submit"
                    class="flex items-center space-x-1 text-sm font-bold text-gray-700 hover:text-red-600 border border-gray-300 rounded-lg px-3 py-2 transition-colors duration-200"
                >
                    <x-filament::icon icon="heroicon-o-arrow-left-on-rectangle" class="w-5 h-5" />
                    <span>Sign out</span>
                </button>
            </form>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

