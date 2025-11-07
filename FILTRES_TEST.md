# Test des filtres d'infractions

## Exemples d'utilisation des nouveaux filtres

### 1. Toutes les infractions (sans filtre)
```bash
GET /api/infraction
```

### 2. Filtrer par écurie (Red Bull = id: 1)
```bash
GET /api/infraction?ecurie_id=1
```

### 3. Filtrer par pilote (Verstappen = id: 1)  
```bash
GET /api/infraction?pilote_id=1
```

### 4. Filtrer par date exacte
```bash
GET /api/infraction?date=2024-05-26
```

### 5. Filtrer par période
```bash
GET /api/infraction?date_debut=2024-01-01&date_fin=2024-12-31
```

### 6. Filtrer par course (recherche partielle)
```bash
GET /api/infraction?course=Monaco
```

### 7. Filtrer par type de sanction
```bash
# Amendes seulement
GET /api/infraction?type=amende

# Pénalités seulement  
GET /api/infraction?type=penalite

# Amendes ET pénalités combinées
GET /api/infraction?type=mixte
```

### 8. Combinaisons de filtres
```bash
# Red Bull + pénalités + 2024
GET /api/infraction?ecurie_id=1&type=penalite&date_debut=2024-01-01&date_fin=2024-12-31

# Monaco + amendes
GET /api/infraction?course=Monaco&type=amende

# Verstappen + période spécifique
GET /api/infraction?pilote_id=1&date_debut=2024-05-01&date_fin=2024-05-31
```

### 9. Pagination
```bash
# Page 2, 5 résultats par page
GET /api/infraction?page=2&limit=5

# Première page, 50 résultats max
GET /api/infraction?limit=50
```

### 10. Statistiques et filtres disponibles
```bash
GET /api/infraction/stats
```

## Format de réponse avec filtres

```json
{
  "infractions": [...],
  "pagination": {
    "page": 1,
    "limit": 20,
    "total": 45,
    "total_pages": 3
  },
  "filtres_appliques": {
    "ecurie_id": "1",
    "pilote_id": null,
    "date": null,
    "date_debut": "2024-01-01",
    "date_fin": "2024-12-31",
    "course": null,
    "type": "penalite"
  }
}
```

## Test rapide avec curl

```bash
# Démarrer le serveur
php -S localhost:8000 -t public

# Tester les statistiques  
curl http://localhost:8000/api/infraction/stats

# Tester un filtre
curl "http://localhost:8000/api/infraction?ecurie_id=1&limit=5"
```