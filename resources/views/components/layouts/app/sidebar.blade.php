<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group heading="Plateforme" class="grid">
                    <flux:navlist.item icon="home" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Tableau de bord</flux:navlist.item>
                </flux:navlist.group>

                @if(auth()->user()->role === 'admin')
                    <flux:navlist.group heading="Administration" class="grid">
                        <flux:navlist.item icon="building-office" :href="route('admin.classrooms.index')" :current="request()->routeIs('admin.classrooms.*')" wire:navigate>Salles</flux:navlist.item>
                        <flux:navlist.item icon="book-open" :href="route('admin.courses.index')" :current="request()->routeIs('admin.courses.*')" wire:navigate>Cours</flux:navlist.item>
                        <flux:navlist.item icon="users" :href="route('admin.groups.index')" :current="request()->routeIs('admin.groups.*')" wire:navigate>Groupes</flux:navlist.item>
                        <flux:navlist.item icon="calendar-days" :href="route('admin.sessions.index')" :current="request()->routeIs('admin.sessions.*')" wire:navigate>Sessions</flux:navlist.item>
                        <flux:navlist.item icon="calendar" :href="route('admin.calendar.index')" :current="request()->routeIs('admin.calendar.*')" wire:navigate>Calendrier</flux:navlist.item>
                    </flux:navlist.group>
                @elseif(auth()->user()->role === 'teacher')
                    <flux:navlist.group heading="Enseignement" class="grid">
                        <flux:navlist.item icon="calendar" :href="route('teacher.schedule')" :current="request()->routeIs('teacher.schedule')" wire:navigate>Mon emploi du temps</flux:navlist.item>
                    </flux:navlist.group>
                 @elseif(auth()->user()->role === 'student')
                     <flux:navlist.group heading="Étudiant" class="grid">
                         <flux:navlist.item icon="calendar" :href="route('student.schedule')" :current="request()->routeIs('student.schedule')" wire:navigate>Mon emploi du temps</flux:navlist.item>
                    </flux:navlist.group>
                @endif
            </flux:navlist>

            <!-- Desktop User Info -->
            <div class="hidden lg:block mt-auto">
                <div class="flex items-center gap-2 px-3 py-2 text-sm">
                    <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                        <span
                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                        >
                            {{ auth()->user()->initials() }}
                        </span>
                    </span>

                    <div class="grid flex-1 text-start text-sm leading-tight">
                        <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                        <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                    </div>

                    <flux:dropdown position="top" align="start">
                        <flux:button variant="ghost" size="sm" icon="chevrons-up-down" />

                        <flux:menu class="w-[220px]">
                            <flux:menu.radio.group>
                                <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Paramètres</flux:menu.item>
                            </flux:menu.radio.group>

                            <flux:menu.separator />

                            <form method="POST" action="{{ route('logout') }}" class="w-full">
                                @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            Se déconnecter
                        </flux:menu.item>
                            </form>
                        </flux:menu>
                    </flux:dropdown>
                </div>
            </div>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span
                                        class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                    >
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>Paramètres</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            Se déconnecter
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
