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

## Pour tester localement

1. Démarrer le serveur Symfony :
```bash
php -S localhost:8000 -t public
```

2. Utiliser curl, Postman, ou tout autre client HTTP pour tester les routes