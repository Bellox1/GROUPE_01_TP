# Évaluation Pratique : Gestion d'Emploi du Temps

## 📝 Contexte
Vous travaillez sur une application Laravel existante de gestion d'emploi du temps universitaire. Votre mission est d'améliorer l'application en ajoutant des fonctionnalités critiques tout en respectant strictement la méthodologie **TDD (Test Driven Development)**.

**Durée :** 2h30.

## 🛠️ Règles du Jeu
1.  **TDD Obligatoire** : Pour chaque fonctionnalité, vous **devez** écrire le test **avant** le code.
    *   🔴 **RED** : Écrire un test qui échoue.
    *   🟢 **GREEN** : Écrire le code minimum pour faire passer le test.
    *   🔵 **REFACTOR** : Améliorer le code sans casser le test.
2.  **Git** : Vos commits doivent refléter cette démarche. Exemple de messages de commit :
    *   `test: add failing test for quota validation`
    *   `feat: implement quota validation for course sessions`
    *   `refactor: clean up conflict detection logic`
3.  **Autonomie** : À vous de définir les noms des classes, des tables et l'architecture technique la plus pertinente pour répondre au besoin.
4.  **IA & Internet** : Autorisés.

## ⚠️ Avertissement Important
Vous êtes tenus entièrement responsables du code que vous produisez.
**Une note de ZÉRO sera attribuée si vous n'êtes pas en mesure d'expliquer votre implémentation lors de la revue de code.**
L'utilisation de l'IA est un outil, pas une fin en soi. Vous devez comprendre chaque ligne commise.

---

## 🚀 Missions

### Mission 1 : Respect des Quotas Horaires

**Contexte Métier :**
Dans une université, une unité d'enseignement (Cours) possède un volume horaire défini. Par exemple : le cours "Bases de Données" peut être prévu pour **10h de CM** (Cours Magistral) et **20h de TD** (Travaux Dirigés).

**Objectif fonctionnel :**
Implémentez une sécurité qui empêche la planification d'une nouvelle séance si celle-ci fait dépasser le quota d'heures défini pour ce type de cours (CM, TD ou TP). Le système doit calculer le total des heures déjà planifiées pour ce cours et refuser l'ajout si le quota est atteint.

**Exigences techniques :**
*   Le système doit gérer les quotas par type de séance (CM, TD, TP)
*   Le calcul doit prendre en compte toutes les séances existantes
*   Un message d'erreur clair doit être retourné en cas de dépassement

**Exigence TDD :**
Vous devez prouver par des tests automatisés que :
*   L'ajout d'une séance est refusé lorsque le quota est atteint
*   L'ajout est accepté lorsqu'il reste des heures disponibles
*   Les différents types de cours (CM, TD, TP) sont gérés séparément

### Mission 2 : Envoi d'Emails aux Étudiants

**Contexte Métier :**
Lorsqu'un emploi du temps est ajouté ou modifié, les étudiants concernés doivent être informés par email pour qu'ils puissent se préparer à leurs cours.

**Objectif fonctionnel :**
Implémentez un système d'envoi d'emails automatique qui notifie tous les étudiants d'un groupe lorsqu'une nouvelle séance de cours est planifiée pour leur groupe.

**Exigences techniques :**
*   Envoyer un email à tous les étudiants du groupe concerné
*   Inclure les détails de la séance (cours, salle, horaire, enseignant)
*   Gérer les échecs d'envoi gracieusement
*   Utiliser les queues pour ne pas bloquer la création de séance

**Exigence TDD :**
Vous devez prouver par des tests automatisés que :
*   Les emails sont envoyés aux bons étudiants
*   Les détails de la séance sont corrects dans l'email
*   Les étudiants d'autres groupes ne reçoivent pas l'email
*   Le système gère les adresses email invalides
*   Les emails sont mis en file d'attente (queued)

---

## 📦 Livraison
À la fin des 2h30 :
1.  Assurez-vous que **tous** les tests (anciens et nouveaux) passent.
2.  Poussez votre code sur la branche rendue.
3.  Le dernier commit doit être : `final: evaluation submission`.

## 💡 Conseils
*   Commencez par bien comprendre les modèles existants (Cours, Salles, Séances)
*   N'oubliez pas de gérer les migrations si vous ajoutez des champs
*   Utilisez les factories pour vos tests
*   Testez les cas limites (horaires à la frontière, quotas à zéro)

Bon courage !
