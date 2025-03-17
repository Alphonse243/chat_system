# Documentation API du Système de Chat

## Authentification
Toutes les requêtes doivent inclure un token de session valide.

## Points d'entrée API

### Utilisateurs

#### Création d'un utilisateur
```http
POST /api/users
Content-Type: application/json

{
    "username": "string",
    "name": "string",
    "email": "string",
    "password": "string",
    "bio": "string"
}
```

#### Mise à jour du statut
```http
PUT /api/users/{id}/status
Content-Type: application/json

{
    "status": "online|offline|away|busy"
}
```

### Conversations

#### Création d'une conversation
```http
POST /api/conversations
Content-Type: application/json

{
    "type": "private|group",
    "name": "string|null",
    "participants": [1, 2, 3]
}
```

#### Envoi d'un message
```http
POST /api/conversations/{id}/messages
Content-Type: application/json

{
    "content": "string",
    "type": "text|image|file|voice",
    "file_url": "string|null"
}
```

## Codes d'erreur
- 400: Requête invalide
- 401: Non authentifié
- 403: Non autorisé
- 404: Ressource non trouvée
- 500: Erreur serveur
