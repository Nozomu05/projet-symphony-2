# Documentation API - Routes Écuries

## Routes disponibles pour modifier les pilotes d'une écurie

### 1. **Lister toutes les écuries avec leurs pilotes**
- **URL**: `GET /api/ecurie`
- **Description**: Récupère toutes les écuries avec la liste de leurs pilotes

**Exemple de réponse** :
```json
[
  {
    "id": 1,
    "nom": "Red Bull Racing",
    "moteur": "Honda RBPT",
    "pilotes": [
      {
        "id": 1,
        "prenom": "Max",
        "nom": "Verstappen",
        "role": "Pilote titulaire",
        "points_license": 12
      }
    ]
  }
]
```

### 2. **Afficher une écurie spécifique**
- **URL**: `GET /api/ecurie/{id}`
- **Description**: Récupère les détails d'une écurie avec ses pilotes

**Exemple** : `GET /api/ecurie/1`

### 3. **Modifier les pilotes d'une écurie**
- **URL**: `PUT /api/ecurie/{id}/pilotes` ou `PATCH /api/ecurie/{id}/pilotes`
- **Description**: Met à jour les informations des pilotes d'une écurie

**Exemple de données à envoyer** :
```json
{
  "pilotes": [
    {
      "id": 1,
      "prenom": "Max",
      "nom": "Verstappen",
      "role": "Pilote titulaire",
      "points_license": 10,
      "date": "1997-09-30"
    },
    {
      "prenom": "Nouveau",
      "nom": "Pilote",
      "role": "Pilote de réserve",
      "points_license": 0,
      "date": "2000-01-01"
    }
  ]
}
```

### 4. **Ajouter un nouveau pilote à une écurie**
- **URL**: `POST /api/ecurie/{id}/pilotes/add`
- **Description**: Ajoute un nouveau pilote à une écurie

**Exemple de données** :
```json
{
  "prenom": "Nouveau",
  "nom": "Pilote",
  "role": "Pilote de réserve",
  "points_license": 0,
  "date": "2000-01-01"
}
```

### 5. **Supprimer un pilote d'une écurie**
- **URL**: `DELETE /api/ecurie/{ecurieId}/pilotes/{piloteId}`
- **Description**: Supprime un pilote d'une écurie

**Exemple** : `DELETE /api/ecurie/1/pilotes/3`

## Exemples d'utilisation avec curl

### Lister les écuries :
```bash
curl -X GET http://localhost:8000/api/ecurie
```

### Modifier les pilotes de l'écurie 1 :
```bash
curl -X PUT http://localhost:8000/api/ecurie/1/pilotes \
  -H "Content-Type: application/json" \
  -d '{
    "pilotes": [
      {
        "id": 1,
        "prenom": "Max",
        "nom": "Verstappen",
        "role": "Pilote titulaire",
        "points_license": 15,
        "date": "1997-09-30"
      }
    ]
  }'
```

### Ajouter un pilote à l'écurie 1 :
```bash
curl -X POST http://localhost:8000/api/ecurie/1/pilotes/add \
  -H "Content-Type: application/json" \
  -d '{
    "prenom": "Daniel",
    "nom": "Ricciardo",
    "role": "Pilote de test",
    "points_license": 5,
    "date": "1989-07-01"
  }'
```

### Supprimer le pilote 3 de l'écurie 1 :
```bash
curl -X DELETE http://localhost:8000/api/ecurie/1/pilotes/3
```

## Codes de réponse

- `200 OK` : Opération réussie
- `201 Created` : Pilote créé avec succès
- `400 Bad Request` : Données invalides
- `404 Not Found` : Écurie ou pilote non trouvé
- `500 Internal Server Error` : Erreur serveur

---

# Routes Infractions - Amende et Pénalités

## Routes pour infliger des amendes et pénalités

### 1. **Infliger une infraction à une écurie**
- **URL**: `POST /api/infraction/ecurie/{id}`
- **Description**: Enregistre une amende et/ou pénalité pour une écurie

**Exemple de données** :
```json
{
  "nom_de_la_course": "Grand Prix de Monaco 2024",
  "description": "Dépassement de budget plafond",
  "date": "2024-05-26",
  "penalite": 10,
  "amende": "50000.00"
}
```

### 2. **Infliger une infraction à un pilote**
- **URL**: `POST /api/infraction/pilote/{id}`
- **Description**: Enregistre une amende et/ou pénalité pour un pilote

**Exemple de données** :
```json
{
  "nom_de_la_course": "Grand Prix de Monaco 2024",
  "description": "Conduite dangereuse en qualifications",
  "date": "2024-05-26",
  "penalite": 3,
  "amende": "5000.00"
}
```

### 3. **Route générale pour infractions**
- **URL**: `POST /api/infraction/{type}/{id}`
- **Description**: Route générale où type = "ecurie" ou "pilote"

### 4. **Lister toutes les infractions avec filtres**
- **URL**: `GET /api/infraction`
- **Description**: Récupère toutes les infractions avec filtres avancés

**Filtres disponibles** :
- `ecurie_id` : ID de l'écurie (ex: `?ecurie_id=1`)
- `pilote_id` : ID du pilote (ex: `?pilote_id=1`) 
- `date` : Date exacte (ex: `?date=2024-05-26`)
- `date_debut` : Date de début (ex: `?date_debut=2024-01-01`)
- `date_fin` : Date de fin (ex: `?date_fin=2024-12-31`)
- `course` : Nom de course (recherche partielle) (ex: `?course=Monaco`)
- `type` : Type de sanction - `amende`, `penalite`, `mixte` (ex: `?type=amende`)
- `page` : Numéro de page (ex: `?page=2`)
- `limit` : Nombre par page (max 100) (ex: `?limit=50`)

**Exemples d'URL** :
```
GET /api/infraction?ecurie_id=1
GET /api/infraction?pilote_id=1&type=penalite
GET /api/infraction?date_debut=2024-01-01&date_fin=2024-12-31
GET /api/infraction?course=Monaco&type=amende
GET /api/infraction?ecurie_id=1&date=2024-05-26&page=1&limit=10
```

### 5. **Historique des infractions d'une écurie**
- **URL**: `GET /api/infraction/ecurie/{id}/historique`
- **Description**: Récupère l'historique des infractions d'une écurie

### 6. **Historique des infractions d'un pilote**
- **URL**: `GET /api/infraction/pilote/{id}/historique`
- **Description**: Récupère l'historique des infractions d'un pilote

### 7. **Statistiques et filtres disponibles**
- **URL**: `GET /api/infraction/stats`
- **Description**: Récupère les statistiques globales et les filtres disponibles

**Réponse inclut** :
- Statistiques générales (total infractions, amendes, pénalités)
- Liste des écuries disponibles pour filtrage
- Liste des pilotes disponibles pour filtrage  
- Liste des courses disponibles
- Années disponibles
- Exemples d'utilisation des filtres

## Exemples d'utilisation avec curl

### Infliger une amende à l'écurie Red Bull (id=1) :
```bash
curl -X POST http://localhost:8000/api/infraction/ecurie/1 \
  -H "Content-Type: application/json" \
  -d '{
    "nom_de_la_course": "GP Bahrain 2024",
    "description": "Dépassement limite aérodynamique",
    "date": "2024-03-02",
    "amende": "25000.00"
  }'
```

### Infliger une pénalité à Max Verstappen (id=1) :
```bash
curl -X POST http://localhost:8000/api/infraction/pilote/1 \
  -H "Content-Type: application/json" \
  -d '{
    "nom_de_la_course": "GP Bahrain 2024", 
    "description": "Dépassement par l extérieur des limites",
    "date": "2024-03-02",
    "penalite": 5
  }'
```

### Infliger amende ET pénalité :
```bash
curl -X POST http://localhost:8000/api/infraction/pilote/2 \
  -H "Content-Type: application/json" \
  -d '{
    "nom_de_la_course": "GP Monaco 2024",
    "description": "Contact avec barrière + langage inapproprié",
    "date": "2024-05-26",
    "penalite": 2,
    "amende": "1500.00"
  }'
```

### Voir l'historique des infractions de Ferrari (id=3) :
```bash
curl -X GET http://localhost:8000/api/infraction/ecurie/3/historique
```

### Exemples avec les nouveaux filtres :

### Lister toutes les infractions de Red Bull :
```bash
curl -X GET "http://localhost:8000/api/infraction?ecurie_id=1"
```

### Infractions de Max Verstappen avec pénalités seulement :
```bash
curl -X GET "http://localhost:8000/api/infraction?pilote_id=1&type=penalite"
```

### Infractions du Grand Prix de Monaco 2024 :
```bash
curl -X GET "http://localhost:8000/api/infraction?course=Monaco&date_debut=2024-05-01&date_fin=2024-05-31"
```

### Toutes les amendes de l'année 2024 (page 1, 10 résultats) :
```bash
curl -X GET "http://localhost:8000/api/infraction?type=amende&date_debut=2024-01-01&date_fin=2024-12-31&page=1&limit=10"
```

### Obtenir les statistiques et filtres disponibles :
```bash
curl -X GET http://localhost:8000/api/infraction/stats
```

## Champs requis

### Pour toutes les infractions :
- `nom_de_la_course` : Nom de la course/événement
- `description` : Description de l'infraction
- `date` : Date de l'infraction (format: YYYY-MM-DD)

### Optionnels (au moins un requis) :
- `penalite` : Nombre de points de pénalité (integer)
- `amende` : Montant de l'amende (string/decimal)

---

## Pour tester localement

1. Démarrer le serveur Symfony :
```bash
php -S localhost:8000 -t public
```

2. Utiliser curl, Postman, ou tout autre client HTTP pour tester les routes