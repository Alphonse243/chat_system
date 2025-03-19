# API Documentation

## Translation API Endpoints

### Change Language
Change la langue courante de l'application.

**URL** : `/ajax/change-language.php`

**Method** : `POST`

**Headers** :
```http
Content-Type: application/json
```

**Request Body** :
```json
{
    "lang": "string" // Code de langue (fr, en, es, zh, sw)
}
```

**Success Response** :
- **Code** : 200 OK
```json
{
    "success": true
}
```

**Error Response** :
- **Code** : 400 Bad Request
```json
{
    "success": false,
    "error": "Error message"
}
```

### Get Translations
Récupère toutes les traductions pour une langue donnée.

**URL** : `/ajax/get-translations.php`

**Method** : `GET`

**Query Parameters** :
- `lang` (required) : Code de la langue (fr, en, es, zh, sw)

**Success Response** :
- **Code** : 200 OK
```json
{
    "success": true,
    "data": {
        "app_name": "Translation",
        "home": "Translation",
        // ... autres traductions
    }
}
```

**Error Response** :
- **Code** : 400 Bad Request
```json
{
    "success": false,
    "error": "Error message"
}
```

## Real-time Messaging API Endpoints

### Send Message
Envoie un message à une conversation.

**URL**: `/api/messages`

**Method**: `POST`

**Headers**:
```http
Content-Type: application/json
```

**Request Body**:
```json
{
  "conversation_id": "integer",
  "sender_id": "integer",
  "content": "string",
  "message_type": "string (text, image, file, voice)",
  "file_url": "string (optional)"
}
```

**Success Response**:

-   **Code**: 200 OK

```json
{
    "success": true,
    "message_id": 123
}
```

**Error Response**:

-   **Code**: 400 Bad Request

```json
{
    "success": false,
    "error": "Error message"
}
```

### Get Messages
Récupère les messages d'une conversation.

**URL**: `/api/messages?conversation_id={conversation_id}`

**Method**: `GET`

**Query Parameters**:

-   `conversation_id` (required): ID de la conversation

**Success Response**:

-   **Code**: 200 OK

```json
{
    "success": true,
    "messages": [
        {
            "id": 1,
            "conversation_id": 1,
            "sender_id": 1,
            "content": "Hello!",
            "message_type": "text",
            "file_url": null,
            "created_at": "2024-01-01 12:00:00",
            "updated_at": "2024-01-01 12:00:00"
        },
        {
            "id": 2,
            "conversation_id": 1,
            "sender_id": 2,
            "content": "Hi!",
            "message_type": "text",
            "file_url": null,
            "created_at": "2024-01-01 12:01:00",
            "updated_at": "2024-01-01 12:01:00"
        }
    ]
}
```

**Error Response**:

-   **Code**: 400 Bad Request

```json
{
    "success": false,
    "error": "Error message"
}
```

## Voice Messaging API Endpoints

### Send Voice Message
Envoie un message vocal à une conversation.

**URL**: `/api/voice_messages`

**Method**: `POST`

**Headers**:
```http
Content-Type: multipart/form-data
```

**Request Body**:
```multipart/form-data
{
  "conversation_id": "integer",
  "sender_id": "integer",
  "audio": "file (audio/webm)"
}
```

**Success Response**:

-   **Code**: 200 OK

```json
{
    "success": true,
    "message_id": 456
}
```

**Error Response**:

-   **Code**: 400 Bad Request

```json
{
    "success": false,
    "error": "Error message"
}
```

## Profile API Endpoints

### Update Profile
Mise à jour du profil utilisateur.

**URL**: `/api/profile/{id}`

**Method**: `PUT`

**Headers**:
```http
Content-Type: application/json
```

**Request Body**:
```json
{
    "username": "string",
    "name": "string",
    "email": "string",
    "bio": "string",
    "avatar_url": "string"
}
```

**Success Response**:

-   **Code**: 200 OK

```json
{
    "success": true,
    "message": "Profile updated successfully"
}
```

**Error Response**:

-   **Code**: 400 Bad Request

```json
{
    "success": false,
    "error": "Error message"
}
```

## Supported Languages

| Code | Language  | Status      |
|------|-----------|-------------|
| fr   | Français  | ✅ Complete |
| en   | English   | ✅ Complete |
| es   | Español   | ✅ Complete |
| zh   | 中文      | ✅ Complete |
| sw   | Kiswahili | ✅ Complete |

## Dependencies

-   PHP 7.4+
-   MySQL
-   Composer
-   fakerphp/faker
-   Carbon

## Integration Example

```javascript
// Example de changement de langue
async function changeLanguage(langCode) {
    try {
        const response = await fetch('/ajax/change-language.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ lang: langCode })
        });
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}

// Example de récupération des traductions
async function getTranslations(langCode) {
    try {
        const response = await fetch(`/ajax/get-translations.php?lang=${langCode}`);
        const data = await response.json();
        return data.data;
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

// Example d'envoi de message
async function sendMessage(conversationId, senderId, content, messageType = 'text', fileUrl = null) {
    try {
        const response = await fetch('/api/messages', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                conversation_id: conversationId,
                sender_id: senderId,
                content: content,
                message_type: messageType,
                file_url: fileUrl
            })
        });
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}

// Example de récupération des messages
async function getMessages(conversationId) {
    try {
        const response = await fetch(`/api/messages?conversation_id=${conversationId}`);
        const data = await response.json();
        return data.messages;
    } catch (error) {
        console.error('Error:', error);
        return null;
    }
}

// Example d'envoi de message vocal
async function sendVoiceMessage(conversationId, senderId, audioFile) {
    try {
        const formData = new FormData();
        formData.append('conversation_id', conversationId);
        formData.append('sender_id', senderId);
        formData.append('audio', audioFile, 'voice.webm');

        const response = await fetch('/api/voice_messages', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}

// Example de mise à jour du profil
async function updateProfile(userId, username, name, email, bio, avatar_url) {
    try {
        const response = await fetch(`/api/profile/${userId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                name: name,
                email: email,
                bio: bio,
                avatar_url: avatar_url
            })
        });
        const data = await response.json();
        return data.success;
    } catch (error) {
        console.error('Error:', error);
        return false;
    }
}
```
