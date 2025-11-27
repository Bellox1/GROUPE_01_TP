<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Créer un Cours
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-transparent overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.courses.store') }}" class="space-y-6">
                        @csrf

                        <flux:input name="code" label="Code" :value="old('code')" required autofocus />

                        <flux:input name="name" label="Nom" :value="old('name')" required />

                        <flux:textarea name="description" label="Description" :value="old('description')" />

                        <flux:input type="number" name="credits" label="Crédits" :value="old('credits')" required />

                        <div class="flex items-center justify-end">
                            <flux:button variant="primary" type="submit">
                                Créer
                            </flux:button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
