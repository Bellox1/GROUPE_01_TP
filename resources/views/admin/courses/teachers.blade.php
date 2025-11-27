<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Assignations d'Enseignants
        </h2>
    </x-slot>

    <div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-transparent overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900 dark:text-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h3 class="text-lg font-medium">Assignations d'Enseignants</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Cours: {{ $course->name }} ({{ $course->code }})</p>
                    </div>
                    <a href="{{ route('admin.courses.index') }}" class="text-blue-600 hover:text-blue-800">
                        ← Retour aux Cours
                    </a>
                </div>

                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                @endif

                <!-- Assigned Teachers -->
                <div class="mb-8">
                    <h4 class="text-md font-semibold mb-4">Enseignants Assignés</h4>
                    @if($assignedTeachers->count() > 0)
                        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                            @foreach($assignedTeachers as $teacher)
                                <div class="border rounded-lg p-4 dark:border-gray-600">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-medium">{{ $teacher->name }}</p>
                                            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $teacher->email }}</p>
                                        </div>
                                        <form action="{{ route('admin.courses.teachers.remove', [$course, $teacher]) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm" onclick="return confirm('Retirer cet enseignant ?')">
                                                Retirer
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Aucun enseignant assigné pour le moment.</p>
                    @endif
                </div>

                <!-- Available Teachers -->
                <div>
                    <h4 class="text-md font-semibold mb-4">Enseignants Disponibles</h4>
                    @if($availableTeachers->count() > 0)
                        <form action="{{ route('admin.courses.teachers.assign', $course) }}" method="POST">
                            @csrf
                            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                                @foreach($availableTeachers as $teacher)
                                    <label class="border rounded-lg p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 dark:border-gray-600">
                                        <div class="flex items-center">
                                            <input type="radio" name="teacher_id" value="{{ $teacher->id }}" class="mr-3">
                                            <div>
                                                <p class="font-medium">{{ $teacher->name }}</p>
                                                <p class="text-sm text-gray-600 dark:text-gray-400">{{ $teacher->email }}</p>
                                            </div>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @if($availableTeachers->count() > 0)
                                <div class="mt-4">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                                        Assigner l'Enseignant Sélectionné
                                    </button>
                                </div>
                            @endif
                        </form>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Aucun enseignant disponible à assigner.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
</x-layouts.app>