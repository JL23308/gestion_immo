# API Gestion Immobilière

API REST pour la gestion d'appartements et de baux locatifs, développée avec CakePHP 5.

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![CakePHP](https://img.shields.io/badge/CakePHP-5.x-D33C43?logo=cakephp&logoColor=white)](https://cakephp.org/)
[![SQLite](https://img.shields.io/badge/SQLite-3-003B57?logo=sqlite&logoColor=white)](https://www.sqlite.org/)

## Table des matières

- [Fonctionnalités](#fonctionnalités)
- [Installation](#installation)
- [Configuration](#configuration)
- [Authentification](#authentification)
- [Endpoints](#endpoints)
- [Tests](#tests)
- [Format des réponses](#format-des-réponses)
- [Documentation interactive](#documentation-interactive)

---

## Fonctionnalités

- Authentification JWT avec clés RSA (RS256)
- Gestion d'appartements : CRUD complet avec filtres avancés
- Gestion de baux : Relations avec appartements et utilisateurs
- Pagination sur toutes les listes
- Cache pour optimisation des performances
- Réponses standardisées avec métadonnées
- Documentation interactive Swagger UI
- Tests automatisés avec Bruno

---

## Installation

### Prérequis

- PHP 8.1+
- Composer
- SQLite3
- Bruno (client API) : https://www.usebruno.com/

### Étapes d'installation

```bash
cd gestion_immo

# Installer les dépendances
composer install

# Configurer l'application
cp config/app_local.example.php config/app_local.php

# Générer les clés JWT
php bin/cake.php generate_keys

# Exécuter les migrations
php bin/cake.php migrations migrate

# Démarrer le serveur
php -S localhost:8765 -t webroot
```

Le serveur est accessible sur **http://localhost:8765**

---

## Configuration

### Base de données

SQLite configuré dans `config/app.php` :
```php
'default' => [
    'className' => Connection::class,
    'driver' => Sqlite::class,
    'database' => 'app.sqlite',
]
```

### JWT

Les clés RSA sont stockées dans :
- `config/jwt.key` (clé privée)
- `config/jwt.key.pub` (clé publique)

Générées avec `php bin/cake.php generate_keys`

---

## Authentification

Toutes les requêtes (sauf `/register` et `/login`) nécessitent un token JWT.

### 1. Inscription

```bash
curl -X POST http://localhost:8765/api/v1/users/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "SecurePass123!",
    "first_name": "John",
    "last_name": "Doe",
    "phone": "+33612345678"
  }'
```

### 2. Connexion

```bash
curl -X POST http://localhost:8765/api/v1/users/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "user@example.com",
    "password": "SecurePass123!"
  }'
```

**Réponse** :
```json
{
  "response": {
    "success": true,
    "message": "Login successful",
    "data": {
      "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
      "user": {
        "id": 1,
        "email": "user@example.com"
      }
    },
    "code": 200
  }
}
```

### 3. Utiliser le token

```bash
export TOKEN="<votre_token>"
curl http://localhost:8765/api/v1/apartments \
  -H "Authorization: Bearer $TOKEN"
```

---

## Endpoints

### Authentification (Public)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| POST | `/api/v1/users/register` | Inscription | Non |
| POST | `/api/v1/users/login` | Connexion | Non |

### Apartments

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/v1/apartments` | Liste avec filtres | Oui |
| POST | `/api/v1/apartments` | Créer | Oui |
| GET | `/api/v1/apartments/{id}` | Détails | Oui |
| PATCH | `/api/v1/apartments/{id}` | Modifier | Oui |
| DELETE | `/api/v1/apartments/{id}` | Supprimer | Oui |

#### Filtres disponibles

```
GET /api/v1/apartments?min_rent=1000&max_rent=2000&nb_rooms=3&energy_class=B&page=1&limit=10
```

- `min_rent`, `max_rent` : Fourchette de loyer
- `min_size`, `max_size` : Superficie (m²)
- `nb_rooms` : Nombre de pièces
- `nb_bathrooms` : Nombre de salles de bain
- `booked` : Disponibilité (true/false)
- `energy_class` : Classe énergétique (A, B, C, D, E, F, G)
- `address` : Recherche textuelle
- `page`, `limit` : Pagination

#### Exemple de création

```bash
curl -X POST http://localhost:8765/api/v1/apartments \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "address": "25 Rue de la Paix, 75002 Paris",
    "rent": 1800.00,
    "size": 85,
    "nb_rooms": 3,
    "nb_bathrooms": 2,
    "booked": false,
    "energy_class": "B",
    "description": "Appartement lumineux avec balcon"
  }'
```

### Leases (Baux)

| Méthode | Endpoint | Description | Auth |
|---------|----------|-------------|------|
| GET | `/api/v1/leases` | Liste avec filtres | Oui |
| POST | `/api/v1/leases` | Créer | Oui |
| GET | `/api/v1/leases/{id}` | Détails | Oui |
| PATCH | `/api/v1/leases/{id}` | Modifier | Oui |
| DELETE | `/api/v1/leases/{id}` | Supprimer | Oui |

#### Filtres disponibles

- `status` : Statut (active, expired, terminated)
- `apartment_id` : ID de l'appartement
- `user_id` : ID de l'utilisateur
- `start_date`, `end_date` : Période
- `page`, `limit` : Pagination

#### Auto-remplissage du loyer

Si `monthly_rent` n'est pas fourni, il est automatiquement rempli avec le loyer de l'appartement :

```bash
curl -X POST http://localhost:8765/api/v1/leases \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "apartment_id": 1,
    "user_id": 1,
    "start_date": "2026-03-01",
    "end_date": "2027-02-28",
    "deposit": 3000.00,
    "notes": "Bail de 12 mois renouvelable"
  }'
```

Le `monthly_rent` sera automatiquement égal au `rent` de l'appartement.

---

## Tests

### Avec Bruno (recommandé)

1. **Installer Bruno** : https://www.usebruno.com/
2. **Ouvrir la collection** : File → Open Collection → Sélectionner `bruno/`
3. **Sélectionner l'environnement** : Choisir "Local" dans le menu déroulant en haut à droite
4. **Lancer les tests** :
   - Collection complète : `Ctrl+Shift+R`
   - Requête individuelle : Sélectionner et `Ctrl+Enter`

**Ordre d'exécution recommandé** :
```
1. Auth/Register    → Crée un utilisateur
2. Auth/Login       → Obtient le token JWT (auto-stocké)
3. Apartments/...   → Teste les appartements
4. Leases/...       → Teste les baux
```

**Note** : À partir de la 2ème exécution, Register peut retourner 400 (utilisateur existe déjà). C'est normal !

Voir [bruno/docs.md](bruno/docs.md) pour le guide complet.

### Avec Bruno CLI

```bash
cd bruno
bru run --env Local
```

### Avec curl

Voir les exemples dans la section [Authentification](#authentification) et [Endpoints](#endpoints).

---

## Format des réponses

Toutes les réponses suivent ce format :

```json
{
  "response": {
    "success": true,
    "message": "Description",
    "data": { ... },
    "code": 200
  },
  "metadata": {
    "timestamp_start": "2026-02-05 10:00:00",
    "timestamp_end": "2026-02-05 10:00:01",
    "execution_time": "45.23ms",
    "pagination": {
      "page": 1,
      "limit": 20,
      "total": 50,
      "pages": 3
    }
  }
}
```

---

## Documentation interactive

Accédez à la documentation Swagger UI : **http://localhost:8765/api/v1/docs/swagger**

Vous pouvez :
- Voir tous les endpoints disponibles
- Tester les requêtes directement depuis l'interface
- Voir les schémas de données
- Télécharger la spécification OpenAPI

---

## Technologies

- **Framework** : CakePHP 5.x
- **Base de données** : SQLite
- **Authentification** : JWT avec Firebase JWT (RS256)
- **Documentation** : SwaggerBake (OpenAPI)
- **Client API** : Bruno
- **Cache** : CakePHP Cache

---

## Structure du projet

```
gestion_immo/
├── bruno/              # Collection de tests Bruno
│   ├── Auth/
│   ├── Apartments/
│   ├── Leases/
│   └── docs.md
├── config/
│   ├── routes.php      # Routes API
│   ├── swagger.yml     # Configuration Swagger
│   ├── jwt.key         # Clés RSA JWT
│   └── Migrations/     # Schéma DB
├── src/
│   ├── Controller/     # Endpoints API
│   ├── Model/          # Entités et Tables
│   └── Error/          # Gestion erreurs personnalisée
├── webroot/
│   ├── index.php
│   └── swagger.json    # Documentation générée
├── app.sqlite          # Base de données
└── README.md           # Ce fichier
```

---

## Commandes utiles

```bash
# Migrations
php bin/cake.php migrations status
php bin/cake.php migrations migrate
php bin/cake.php migrations rollback

# Cache
php bin/cake.php cache clear_all

# Régénérer la documentation Swagger
php bin/cake.php swagger bake

# Régénérer les clés JWT
php bin/cake.php generate_keys

# Reset base de données
rm app.sqlite
php bin/cake.php migrations migrate
```

---

## Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès (GET, PATCH, DELETE) |
| 201 | Créé (POST) |
| 400 | Erreur de validation |
| 401 | Non authentifié |
| 404 | Ressource non trouvée |
| 405 | Méthode non autorisée |
| 500 | Erreur serveur |

---

## Sécurité

- Authentification JWT avec algorithme RS256
- Clés privées/publiques RSA
- Tokens expirables (24h par défaut)
- Validation des données d'entrée
- Protection CSRF désactivée pour API REST
- CORS configuré

---

## Debugging

### Activer le mode debug

Dans `config/app_local.php` :
```php
'debug' => true,
```

### Vider le cache

```bash
php bin/cake.php cache clear_all
rm -rf tmp/cache/*
```

### Erreurs courantes

**"Unauthorized" (401)**
- Le token JWT a expiré (24h). Relancez /login.
- Le token est invalide ou manquant.

**"Not Found" (404)**
- L'ID utilisé n'existe pas.
- Vérifiez avec GET /apartments ou GET /leases.

**"Validation errors" (400)**
- Les données envoyées sont invalides.
- Consultez la réponse pour voir les champs concernés.

---

## Développement

### Ajouter un nouveau endpoint

1. Définir la route dans `config/routes.php`
2. Créer la méthode dans le contrôleur
3. Utiliser `formatResponse()` pour standardiser la réponse
4. Régénérer Swagger : `php bin/cake.php swagger bake`
5. Ajouter la requête dans Bruno avec tests

### Exemple de méthode de contrôleur

```php
public function index(): ?Response
{
    $query = $this->Apartments->find();
    
    // Appliquer filtres, pagination, etc.
    $apartments = $this->paginate($query);
    
    $response = [
        'success' => true,
        'message' => 'List of apartments',
        'data' => $apartments,
        'code' => 200,
    ];
    
    $formattedResponse = $this->formatResponse($response);
    $this->set($formattedResponse);
    $this->viewBuilder()->setOption('serialize', array_keys($formattedResponse));
    
    return null;
}
```

---

## Ressources

- [CakePHP Documentation](https://book.cakephp.org/5/en/index.html)
- [Bruno Documentation](https://docs.usebruno.com/)
- [JWT.io](https://jwt.io/)
- [OpenAPI Specification](https://swagger.io/specification/)
- [bruno/docs.md](bruno/docs.md) - Guide des tests Bruno

---

## Licence

Projet développé dans un cadre éducatif.

---

**Bon développement !**
