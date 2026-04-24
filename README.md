# 🗓️ Schedular - Système de Gestion d'Emploi du Temps Universitaire

**Schedular** est une plateforme avancée conçue pour optimiser la planification académique. Elle permet de gérer intelligemment les cours, les salles, les enseignants et les groupes d'étudiants tout en garantissant l'absence de conflits logistiques et le respect des contraintes pédagogiques.

---

## 🔥 Fonctionnalités Maîtresses

*   **🔍 Détection de Conflits Intelligente** : Algorithme temps réel empêchant la double réservation de salles, l'indisponibilité des enseignants ou le chevauchement de cours pour un groupe.
*   **📊 Gestion Rigoureuse des Quotas** : Validation automatique du volume horaire par type de séance (CM, TD, TP) pour garantir la conformité aux maquettes pédagogiques.
*   **📧 Notifications Automatisées** : Système d'alerte par email (mis en file d'attente) informant instantanément les étudiants de toute modification de leur emploi du temps.
*   **🧩 Architecture Modulaire** : Utilisation intensive de **Value Objects** et de **Services dédiés** pour une logique métier claire et testable.

---

## 🧪 Excellence Technique & TDD

Ce projet a été développé selon la méthodologie **TDD (Test-Driven Development)**, garantissant une base de code stable avec **102 tests passés avec succès**.

### Focus sur les Missions d'Évaluation :
#### ✅ Mission 1 : Maîtrise des Quotas Horaires
- **Logique** : Calcul dynamique du temps consommé par rapport au quota défini dans le modèle `Course`.
- **Validation** : Blocage immédiat avec message d'erreur explicite en cas de dépassement.
- **Tests** : `QuotaServiceTest.php` (Validation des limites CM/TD/TP).

#### ✅ Mission 2 : Système de Notification Student-First
- **Performance** : Utilisation des **Queues Laravel** pour ne pas impacter la réactivité de l'interface.
- **Emailing** : Templates Markdown élégants incluant tous les détails de la séance (Salle, Enseignant, Horaires).
- **Tests** : `SessionNotificationTest.php` (Validation des destinataires et du contenu).

---

## 🛠️ Stack Technologique

*   **Framework PHP** : [Laravel 12](https://laravel.com/)
*   **Frontend Dynamique** : [Livewire 3](https://livewire.laravel.com/) & [Tailwind CSS](https://tailwindcss.com/)
*   **Base de Données** : SQLite (Dev) / Prêt pour MySQL/PostgreSQL
*   **Tests Automatisés** : PHPUnit 11
*   **Authentification** : Laravel Fortify (Security-First)

---

## 🚀 Installation & Déploiement

### 1. Prérequis
*   PHP 8.3+ & Composer
*   Node.js 20+

### 2. Configuration Rapide
```bash
# Installation des dépendances
composer install && npm install

# Environnement & Sécurité
cp .env.example .env
php artisan key:generate

# Base de données & Données de démo
touch database/database.sqlite
php artisan migrate --seed
```

### 3. Lancement des services
```bash
# Serveur principal & Assets (Hot Reload)
composer run dev
```
Accès : `http://localhost:8000`

---

## 👥 Comptes de Test
| Rôle | Email | Mot de passe |
| :--- | :--- | :--- |
| **Admin** | admin@example.com | `password` |
| **Enseignant** | teacher@example.com | `password` |
| **Étudiant** | student@example.com | `password` |

---

## 📄 Licence
Ce projet est un exercice pédagogique sous licence MIT.
