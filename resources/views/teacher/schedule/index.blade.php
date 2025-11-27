<x-layouts.app>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('My Teaching Schedule') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-6">Weekly Schedule</h3>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Monday</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Tuesday</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Wednesday</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Thursday</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Friday</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @php
                                    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
                                    $timeSlots = [
                                        ['start' => '08:00', 'end' => '09:00'],
                                        ['start' => '09:00', 'end' => '10:00'],
                                        ['start' => '10:00', 'end' => '11:00'],
                                        ['start' => '11:00', 'end' => '12:00'],
                                        ['start' => '12:00', 'end' => '13:00'],
                                        ['start' => '13:00', 'end' => '14:00'],
                                        ['start' => '14:00', 'end' => '15:00'],
                                        ['start' => '15:00', 'end' => '16:00'],
                                        ['start' => '16:00', 'end' => '17:00'],
                                        ['start' => '17:00', 'end' => '18:00'],
                                        ['start' => '18:00', 'end' => '19:00'],
                                    ];
                                @endphp
                                @foreach($timeSlots as $slot)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $slot['start'] }} - {{ $slot['end'] }}</td>
                                        @foreach($days as $index => $day)
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                @php
                                                    $dayNumber = $index + 1; // 1=Monday
                                                    $slotSessions = $sessions->filter(function($session) use ($dayNumber, $slot) {
                                                        return $session->day_of_week == $dayNumber &&
                                                               $session->start_time < $slot['end'] &&
                                                               $session->end_time > $slot['start'];
                                                    });
                                                @endphp
                                                @if($slotSessions->count() > 0)
                                                    @foreach($slotSessions as $session)
                                                        <div class="bg-purple-100 dark:bg-purple-900 p-2 rounded mb-1">
                                                            <div class="text-sm font-medium">{{ $session->course->name }}</div>
                                                            <div class="text-xs">{{ $session->start_time }} - {{ $session->end_time }}</div>
                                                            <div class="text-xs">{{ $session->classroom->name }} - {{ $session->group->name }}</div>
                                                        </div>
                                                    @endforeach
                                                @else
                                                    <div class="text-gray-400">-</div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>