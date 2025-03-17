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

## Supported Languages

| Code | Language  | Status      |
|------|-----------|-------------|
| fr   | Français  | ✅ Complete |
| en   | English   | ✅ Complete |
| es   | Español   | ✅ Complete |
| zh   | 中文      | ✅ Complete |
| sw   | Kiswahili | ✅ Complete |

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
```
