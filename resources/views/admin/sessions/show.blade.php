<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Session Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-transparent overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-medium">Détails de la Session</h3>
                    <a href="{{ route('admin.sessions.index') }}" class="text-blue-600 hover:text-blue-800">
                        ← Retour aux Sessions
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Course Information -->
                    <div class="border rounded-lg p-4 dark:border-gray-600">
                        <h4 class="text-md font-semibold mb-3">Cours</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Code:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->course->code }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nom:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->course->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Crédits:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->course->credits }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Classroom Information -->
                    <div class="border rounded-lg p-4 dark:border-gray-600">
                        <h4 class="text-md font-semibold mb-3">Salle</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nom:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->classroom->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Bâtiment:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->classroom->building }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Capacité:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->classroom->capacity }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Teacher Information -->
                    <div class="border rounded-lg p-4 dark:border-gray-600">
                        <h4 class="text-md font-semibold mb-3">Enseignant</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nom:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->teacher->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">E-mail:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->teacher->email }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Group Information -->
                    <div class="border rounded-lg p-4 dark:border-gray-600">
                        <h4 class="text-md font-semibold mb-3">Groupe</h4>
                        <div class="space-y-2">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Nom:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->group->name }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Étudiants:</span>
                                <span class="ml-2 text-sm font-medium">{{ $session->group->students_count ?? 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Information -->
                    <div class="border rounded-lg p-4 dark:border-gray-600 md:col-span-2">
                        <h4 class="text-md font-semibold mb-3">Horaire</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Date:</span>
                                <div class="mt-1 text-sm font-medium">{{ $session->start_time->format('F j, Y') }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Heure de début:</span>
                                <div class="mt-1 text-sm font-medium">{{ $session->start_time->format('g:i A') }}</div>
                            </div>
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Heure de fin:</span>
                                <div class="mt-1 text-sm font-medium">{{ $session->end_time->format('g:i A') }}</div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Durée:</span>
                            <span class="ml-2 text-sm font-medium">
                                {{ $session->start_time->diffInMinutes($session->end_time) }} minutes
                            </span>
                        </div>
                    </div>

                    <!-- Session Type -->
                    <div class="border rounded-lg p-4 dark:border-gray-600 md:col-span-2">
                        <h4 class="text-md font-semibold mb-3">Type de Session</h4>
                        <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full 
                             @if($session->type === 'lecture') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                             @elseif($session->type === 'lab') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                             @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200
                            @endif">
                            {{ ucfirst($session->type) }}
                        </span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-6 flex justify-end space-x-4">
                    <a href="{{ route('admin.sessions.edit', $session) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded">
                        Modifier la Session
                    </a>
                    <form action="{{ route('admin.sessions.destroy', $session) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette session ?')">
                            Supprimer la Session
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>