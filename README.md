# Schedular 📚

Une application complète de gestion d'emplois du temps pour universités, construite avec Laravel 12 et Livewire 3. Gérez efficacement les cours, les salles, les enseignants et les groupes d'étudiants avec détection intelligente des conflits d'horaires.

## Installation Rapide

Suivez ces étapes simples pour démarrer le projet sur votre machine :

### 1. Installation

Clonez le projet et installez les dépendances :

```bash
# Installation des dépendances PHP
composer install

# Installation des dépendances JavaScript
npm install
```

### 2. Configuration

Configurez votre environnement :

```bash
# Copiez le fichier d'exemple
cp .env.example .env

# Générez la clé d'application
php artisan key:generate
```

Créez la base de données SQLite (ou configurez MySQL/PostgreSQL dans `.env`) :

```bash
touch database/database.sqlite
```

### 3. Base de données

Préparez la base de données :

```bash
# Lancez les migrations et les seeders
php artisan migrate --seed
```

### 4. Lancement

Démarrez les serveurs :

```bash
# Serveur Laravel
php artisan serve

# Compilation des assets (dans un autre terminal)
npm run dev
```

Accédez à l'application via `http://localhost:8000`.

---

## 📊 Comprendre les Relations de la Base de Données

👉 **[Voir le diagramme interactif de la base de données](https://dbdiagram.io/d/timetable-management-674f2080e9daa85aca85b6ec)**

Voici une explication simplifiée pour vous aider à naviguer dans les données :

### 1. Utilisateurs & Rôles

* **Users** : C'est la table centrale pour l'authentification.
* Chaque `User` a un rôle spécifique : **Admin**, **Teacher** ou **Student**.
* Les enseignants et étudiants sont gérés via la table `users` avec des rôles différenciés.

### 2. Cours et Enseignement

* **Courses** : Représente les matières/units d'enseignement avec leurs quotas horaires.
* Chaque cours peut avoir plusieurs **Sessions** (séances de cours).
* Les enseignants sont assignés aux cours via la table pivot `course_teacher`.

### 3. Planification des Cours (Cœur du système)

* **CourseSessions** : Représente une séance de cours planifiée.
* Elle lie un **Cours**, une **Salle**, un **Enseignant**, et un **Groupe** d'étudiants.
* Chaque session a un **type** (CM, TD, TP) et des **horaires** définis.

### 4. Gestion des Conflits

* Le système détecte automatiquement les conflits d'horaires.
* Un conflit peut être : **salle déjà occupée**, **enseignant indisponible**, ou **groupe déjà en cours**.
* Les quotas horaires par type de cours sont également vérifiés.

## Comptes de Démonstration

- **Admin**: admin@example.com / password
- **Enseignant**: teacher@example.com / password
- **Étudiant**: student@example.com / password

## Fonctionnalités Clés

### 🔍 Détection de Conflits Intelligente

- **Conflits de salles** : Empêche la double réservation d'une salle
- **Conflits d'enseignants** : Vérifie la disponibilité des professeurs
- **Conflits de groupes** : S'assure qu'un groupe n'a pas deux cours simultanés
- **Chevauchements partiels** : Détecte même les conflits d'horaires partiels

### 📊 Gestion des Quotas Horaires

- Respect des quotas par type de cours (CM, TD, TP)
- Calcul automatique des heures déjà planifiées
- Blocage si le quota est dépassé

### 🎯 Services Métier

- **ConflictDetectionService** : Détection complète des conflits
- **SessionCreationService** : Création sécurisée des sessions
- **TimeSlot** : Objet valeur pour la gestion des créneaux horaires

### 🧪 Tests Automatisés

- Suite de tests complète avec PHPUnit
- Tests unitaires pour les services métier
- Tests d'intégration pour les fonctionnalités admin
- Approche TDD (Test-Driven Development)

## Architecture Technique

### Stack Technologique

- **Backend** : Laravel 12, PHP 8.3
- **Frontend** : Livewire 3, Flux UI, Tailwind CSS 4
- **Base de données** : SQLite (développement), MySQL/PostgreSQL (production)
- **Tests** : PHPUnit 11
- **Authentification** : Laravel Fortify

### Structure des Dossiers

```
app/
├── Http/Controllers/Admin/    # CRUD admin
├── Livewire/                  # Composants Livewire
├── Models/                    # Modèles Eloquent
├── Services/                  # Logique métier
└── ValueObjects/              # Objets valeur
```

## Commandes Utiles

```bash
# Exécuter les tests
php artisan test

# Formatter le code
vendor/bin/pint

# Vider le cache
php artisan optimize:clear

# Créer un modèle complet
php artisan make:model NomDuModele -mfs

# Créer un test
php artisan make:test MonTest --phpunit
```

## Développement avec Hot Reload

Pour le développement avec rechargement automatique :

```bash
"composer run dev
```

Cela lance simultanément :

- Serveur Laravel (`php artisan serve`)
- Compilation Vite avec hot-reload (`npm run dev`)

## Licence

Ce projet est un exercice pédagogique pour l'apprentissage du TDD avec Laravel et la gestion des conflits dans les systèmes de planification.
