# 🏎️ F1 Management API - Tests
**Group:** Giulian PERRIN et Minh Hoang Anh TRAN
**URL:** `http://localhost/projet/B3_IN/public/api`  
**Comptes:** `admin@f1.com` / `admin123` (ADMIN) | `user@f1.com` / `user123` (USER)

## 🔑 Authentification JWT
```http
POST /login
{"username":"admin@f1.com","password":"admin123"}
```

## 1️⃣ Entités Doctrine + Fixtures (3+ écuries, 3+ pilotes)
```http
GET /ecurie
Authorization: Bearer {{TOKEN}}
```

## 2️⃣ Modifier pilotes d'une écurie
```http
PUT /ecurie/1/pilotes
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"pilotes":[{"id":1,"prenom":"Max","nom":"Verstappen","points_license":15,"date_naissance":"1997-09-30"}]}
```

## 3️⃣ Infliger infractions (ADMIN uniquement)
### Pénalité pilote
```http
POST /infraction/pilote/1
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"GP Monaco","description":"Conduite dangereuse","date":"2024-05-26","penalite":5}
```

### Amende écurie
```http
POST /infraction/ecurie/1
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"GP Silverstone","description":"Dépassement budget","date":"2024-07-07","amende":"50000.00"}
```

### Pénalité + Amende
```http
POST /infraction/pilote/2
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"GP Bahrain","description":"Accident + antisportif","date":"2024-03-02","penalite":6,"amende":"10000.00"}
```

## 4️⃣ Liste infractions + Filtres
```http
GET /infraction                              # Toutes
GET /infraction?ecurie_id=1                  # Par écurie
GET /infraction?pilote_id=1                  # Par pilote
GET /infraction?date_debut=2024-01-01&date_fin=2024-12-31  # Par date
GET /infraction?ecurie_id=1&pilote_id=2&date_debut=2024-06-01  # Combinés
Authorization: Bearer {{TOKEN}}
```

## 5️⃣ Service suspension automatique (< 12 points)
```http
POST /infraction/pilote/3
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"Test Suspension","description":"Grosse infraction","date":"2024-11-07","penalite":8}
```

Puis vérifier :
```http
GET /ecurie/1
Authorization: Bearer {{ADMIN_TOKEN}}
```

## 6️⃣ Sécurité JWT + Rôle ADMIN
### Test échec USER (doit retourner 403)
```http
POST /infraction/pilote/1
Authorization: Bearer {{USER_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"Test","description":"Non autorisé","date":"2024-11-07","penalite":3}
```

### Test succès ADMIN (doit retourner 201)
```http
POST /infraction/pilote/1
Authorization: Bearer {{ADMIN_TOKEN}}
Content-Type: application/json

{"nom_de_la_course":"Test Admin","description":"Autorisé","date":"2024-11-07","penalite":3}
```

## ✅ Validation
- [ ] Entités ORM créées
- [ ] Fixtures 3+ écuries, 3+ pilotes  
- [ ] Route PUT /ecurie/{id}/pilotes
- [ ] Routes POST infractions pilote/écurie
- [ ] Filtres écurie/pilote/date
- [ ] Service suspension < 12 points
- [ ] JWT fonctionnel
- [ ] Rôle ADMIN exclusif pour infractions