# 🎯 LA PERLE BERBÈRE - Gestion de Rendez-vous

Système complet de gestion de rendez-vous en ligne pour un store, développé en PHP procédural avec MySQL, jQuery et Bootstrap.

---

## 📋 Table des matières

1. [Installation](#-installation)
2. [Configuration](#-configuration)
3. [Structure du projet](#-structure-du-projet)
4. [Fonctionnalités](#-fonctionnalités)
5. [Utilisation](#-utilisation)

---

## 🚀 Installation

### Prérequis

- **PHP 7.4+** avec extensions MySQLi/PDO
- **MySQL 5.7+**
- **Serveur web** (Apache, Nginx, etc.)
- **Laragon** (recommandé) ou équivalent

### Étapes d'installation

#### 1️⃣ Placer les fichiers

```bash
# Les fichiers doivent être dans:
C:\laragon\www\laperleberbere\
```

#### 2️⃣ Créer la base de données

**Via phpMyAdmin :**
- Ouvrir phpMyAdmin (http://localhost/phpmyadmin)
- Aller à l'onglet "SQL"
- Copier-coller le contenu de `setup.sql`
- Cliquer sur "Exécuter"

**Ou via ligne de commande :**
```bash
mysql -u root -p < setup.sql
```

#### 3️⃣ Vérifier la configuration

Éditer le fichier `config/db.php` et vérifier :

```php
define('DB_HOST', 'localhost');   // Hôte de la base
define('DB_USER', 'root');        // Utilisateur MySQL
define('DB_PASSWORD', '');        // Mot de passe (vide par défaut sur Laragon)
define('DB_NAME', 'laperleberbere'); // Nom de la base
```

#### 4️⃣ Accéder à l'application

- **Accueil** : http://localhost/laperleberbere/
- **Client** : http://localhost/laperleberbere/client/
- **Admin** : http://localhost/laperleberbere/admin/

---

## ⚙️ Configuration

### Variables d'environnement

Fichier : `config/db.php`

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'laperleberbere');
define('DB_CHARSET', 'utf8mb4');
```

### Utiliser un logo personnalisé

Remplacer l'image : `/images/logo.jpg`

Les dimensions recommandées : **200x200px** (ratio carré)

---

## 📁 Structure du projet

```
laperleberbere/
├── config/
│   └── db.php                 # Configuration BD + fonctions utilitaires
├── admin/
│   ├── index.php              # Tableau de bord des rendez-vous
│   ├── manage_slots.php       # Gestion des créneaux disponibles
│   ├── api_appointments.php   # API pour actions rendez-vous
│   └── api_slots.php          # API pour gestion créneaux
├── client/
│   ├── index.php              # Prise de rendez-vous
│   └── api_booking.php        # API de réservation
├── assets/
│   ├── css/
│   │   └── style.css          # Styles personnalisés
│   └── js/
│       ├── admin.js           # Fonctions admin JavaScript
│       └── client.js          # Fonctions client JavaScript
├── images/
│   └── logo.jpg               # Logo du site
├── index.php                  # Page d'accueil
└── setup.sql                  # Script création base de données
```

---

## 🎨 Fonctionnalités

### 👤 Partie Cliente

✅ **Calendrier interactif**
- Navigation par flèches
- Affichage des dates disponibles
- Sélection simplifiée

✅ **Formulaire de réservation**
- Champs obligatoires (Nom, Prénom, Email, Téléphone)
- Raison de la visite (optionnel)
- Validation côté client et serveur
- Messages d'erreur détaillés

✅ **Confirmation**
- Modal de confirmation avec résumé
- Email de confirmation (préparé pour intégration)

### 👨‍💼 Partie Administrateur

✅ **Tableau de bord**
- Liste de tous les rendez-vous
- Pagination (10 par page)
- Recherche multi-champs (nom, email, téléphone)
- Statut des rendez-vous (En attente / Validé)

✅ **Détails des rendez-vous**
- Vue complète de toutes les informations
- Modal d'affichage détaillé

✅ **Actions sur rendez-vous**
- ✓ Valider un rendez-vous
- 🗑️ Supprimer complètement
- 🔄 Libérer le créneau (supprimer seulement la réservation)

✅ **Gestion des créneaux**
- ➕ Ajouter des créneaux (date + heure)
- 🗑️ Supprimer un créneau
- 🔄 Libérer un créneau réservé
- Liste complète avec statut

---

## 💻 Utilisation

### Pour les clients

1. Accéder à http://localhost/laperleberbere/client/
2. Sélectionner une date dans le calendrier
3. Choisir un créneau disponible
4. Remplir le formulaire
5. Confirmer la réservation

### Pour l'administrateur

1. Accéder à http://localhost/laperleberbere/admin/
2. Consulter la liste des rendez-vous
3. Valider ou gérer les rendez-vous
4. Accéder à "Créneaux" pour ajouter/modifier les disponibilités

---

## 🔐 Sécurité

✅ **Protections implémentées :**
- Requêtes paramétrées (PDO) contre les injections SQL
- Sanitization des entrées utilisateur (`htmlspecialchars`)
- Validation côté client ET serveur
- Gestion des transactions pour l'intégrité des données

---

## 📊 Structure de la base de données

### Table: `disponibilites`
```sql
id          INT (Clé primaire)
date        DATE
heure       TIME
statut      ENUM ('libre', 'réservé')
created_at  TIMESTAMP
```

### Table: `rendezvous`
```sql
id                INT (Clé primaire)
nom               VARCHAR(100)
prenom            VARCHAR(100)
email             VARCHAR(150)
telephone         VARCHAR(20)
raison            TEXT (Optionnel)
disponibilite_id  INT (Clé étrangère)
statut            ENUM ('en attente', 'validé')
created_at        TIMESTAMP
```

---

## 🌐 Stack technique

- **Backend** : PHP 7.4+ (procédural)
- **Base de données** : MySQL 5.7+
- **Frontend** : jQuery 3.6 + Bootstrap 5.3
- **Datepicker** : jQuery UI Datepicker
- **API** : AJAX (JSON)

---

## 📝 Notes importantes

1. **Email** : Actuellement, les emails ne sont pas envoyés. Pour l'implémenter :
   ```php
   use PHPMailer\PHPMailer\PHPMailer;
   // Intégrer PHPMailer ou équivalent
   ```

2. **Données de test** : La base inclut des créneaux de test pour avril 2026

3. **Responsive** : Entièrement responsive (mobile, tablette, desktop)

4. **Langues** : 100% français

---

## 🐛 Dépannage

### Base de données non trouvée
```
Erreur: Erreur de connexion à la base de données
```
✅ **Solution** : Vérifier les identifiants dans `config/db.php`

### Créneaux non affichés
```
Aucun créneau disponible
```
✅ **Solution** : Ajouter des créneaux via l'admin > Créneaux

### Images non chargées
```
Logo non visible
```
✅ **Solution** : Placer `logo.jpg` dans le dossier `/images`

---

## 📞 Support

Pour toute question ou amélioration, consultez le code source commenté en français.

---

**Version** : 1.0  
**Date** : Avril 2026  
**Statut** : ✅ Prêt pour production  
