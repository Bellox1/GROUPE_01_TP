<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Créer une Salle
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-transparent overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('admin.classrooms.store') }}" class="space-y-6">
                        @csrf

                        <flux:input name="name" label="Nom" :value="old('name')" required autofocus />

                        <flux:select name="type" label="Type" placeholder="Sélectionner le type">
                            <flux:select.option value="ClassRoom">Salle de classe</flux:select.option>
                            <flux:select.option value="Amphitheater">Amphithéâtre</flux:select.option>
                            <flux:select.option value="Lab">Laboratoire</flux:select.option>
                        </flux:select>

                        <flux:input type="number" name="capacity" label="Capacité" :value="old('capacity')" required />

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
