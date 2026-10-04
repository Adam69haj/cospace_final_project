# 🏢 CoSpace

**Système de gestion de salles & réservations** — application web PHP/MySQL à destination des équipes.

Elle permet à un administrateur de gérer les salles, les employés et les réservations,
tandis que les employés et les membres peuvent réserver des créneaux depuis leur espace personnel.

---

## 🚀 Fonctionnalités

- **3 rôles utilisateurs** : `administrateur`, `employé`, `membre`
- **Authentification** : connexion / inscription avec mots de passe hashés
- **CRUD complet** (admin) : salles & employés
- **Réservations** : choix de la salle, de la date et du créneau horaire
- **Règle de 24h** : une réservation ne peut être annulée que 24h avant le créneau
- **Calendrier** : vue mensuelle des réservations avec modales de détail
- **QR codes** : génération de QR code de prise de possession de la salle
- **Dashboard admin** : statistiques (graphiques Chart.js) + avis des utilisateurs
- **Thème clair/sombre** persistant dans le navigateur
- **Messages flash** temporaires (succès / erreurs)

## 🧱 Architecture

```
index.php              ← point d'entrée + routeur (?page=...)
connexion/database.php ← constantes DB
models/                ← couche SQL (UserModel, RoomModel, ReservationModel, ...)
controllers/           ← logique métier (Auth, Admin, Room, Employee, Reservation, ...)
views/                 ← templates HTML (admin / member / auth / profile / calendar)
public/css/style.css   ← styles (thème clair/sombre)
public/js/app.js       ← JS global (sidebar, alertes, thème, validation réservation)
```

**Pattern** : MVC léger — les modèles sont des classes avec des méthodes statiques,
les contrôleurs gèrent les requêtes HTTP et les validations,
les vues sont des fichiers PHP qui appellent `require` sur le layout (header/footer).

## ⚙️ Installation

### 1. Prérequis

- PHP 7.4+
- MySQL (ici sur le port `3308`)
- Un serveur web (WAMP, XAMPP, MAMP, ou PHP built-in)

### 2. Base de données

Créez une base de données MySQL nommée `bd_cospace` et exécutez le schéma
disponible dans le dossier `sql/` (à créer selon votre environnement).

> ⚠️ Les identifiants de base de données sont dans `connexion/database.php`.
> Ce fichier est ignoré par `.gitignore` pour des raisons de sécurité.

### 3. Lancer le projet

```bash
php -S localhost:8080
``puis ouvrez `http://localhost:8080` dans votre navigateur.

### 4. Comptes de test (visibles sur la page de connexion)

| Rôle       | Identifiant       | Mot de passe   |
|------------|-------------------|----------------|
| Admin      | `admin`           | `admin123`     |
| Employé    | `jean.dupont`     | `employe123`   |
| Membre     | `marie@gmail.com` | `membre123`    |

## 📁 Arborescence

```
cospace_final_project/
├── index.php
├── .gitignore
├── README.md
├── connexion/
│   └── database.php
├── controllers/
│   ├── AuthController.php
│   ├── AdminController.php
│   ├── RoomController.php
│   ├── EmployeeController.php
│   ├── ReservationController.php
│   ├── MemberController.php
│   ├── ProfileController.php
│   └── CalendarController.php
├── models/
│   ├── UserModel.php
│   ├── EmployeeModel.php
│   ├── RoomModel.php
│   ├── CreneauxModel.php
│   └── ReservationModel.php
├── views/
│   ├── auth/
│   ├── admin/
│   ├── member/
│   ├── calendar/
│   └── profile/
├── public/
│   ├── css/style.css
│   ├── js/app.js
│   └── uploads/profiles/
└── sql/
```

## 🛡️ Sécurité

- Mots de passe hashés avec `password_hash()` / `password_verify()`
- Requêtes SQL préparées (PDO)
- Contrôle d'accès par rôle sur chaque page (`requireRole`)
- Fichiers sensibles exclus du dépôt via `.gitignore`

## 📝 Licence

Projet éducatif — libre d'usage.