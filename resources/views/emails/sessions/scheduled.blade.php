<x-mail::message>
# Nouvelle séance de cours planifiée

Bonjour,

Une nouvelle séance de cours a été ajoutée à votre emploi du temps :

- **Cours :** {{ $session->course->name }} ({{ $session->course->code }})
- **Type :** {{ $session->type }}
- **Date :** {{ $session->start_time->format('d/m/Y') }}
- **Horaire :** {{ $session->start_time->format('H:i') }} - {{ $session->end_time->format('H:i') }}
- **Salle :** {{ $session->classroom->name }}
- **Enseignant :** {{ $session->teacher->name }}

<x-mail::button :url="config('app.url')">
Voir mon emploi du temps
</x-mail::button>

Merci,<br>
L'administration de {{ config('app.name') }}
</x-mail::message>
