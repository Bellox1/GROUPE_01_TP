<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Classrooms') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-transparent overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between mb-6">
                        <h3 class="text-lg font-medium">Liste des Salles</h3>
                        <a href="{{ route('admin.classrooms.create') }}" class="bg-gray-800 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded">
                            Ajouter une Salle
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-transparent">
                             <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Nom</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Type</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Capacité</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-transparent divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($classrooms as $classroom)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $classroom->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $classroom->type }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $classroom->capacity }}</td>
                                     <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.classrooms.edit', $classroom) }}" class="text-indigo-600 hover:text-indigo-900 mr-4">Modifier</a>
                                        <form action="{{ route('admin.classrooms.destroy', $classroom) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Êtes-vous sûr ?')">Supprimer</button>
                                        </form>
                                     </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
