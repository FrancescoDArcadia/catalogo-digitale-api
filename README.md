# Catalogo Digitale — API REST

API REST sviluppata con Laravel 13, realizzata come progetto di approfondimento pratico del framework. Gestisce un catalogo di opere, autori, categorie e tag, con autenticazione JWT + refresh token, autorizzazione basata su ruoli, ricerca/filtri combinabili e suite di test automatici.

## Stack tecnico

- Laravel 13.23
- MySQL
- JWT (`php-open-source-saver/jwt-auth`) per l'access token
- Refresh token opaco, persistito su DB, distribuito via cookie httpOnly
- Pest 4 (testing)
- Docker / Laravel Sail (ambiente di sviluppo)

## Autenticazione

L'API usa un'architettura a due token, pensata per essere consumata da un frontend SPA separato (es. Angular su un'altra porta/dominio):

- **Access token**: JWT, valido **15 minuti**, restituito nel body della risposta di login/registrazione/refresh. Va inviato come header `Authorization: Bearer {access_token}` su ogni richiesta verso rotte protette. Non va mai persistito lato client (solo in memoria).
- **Refresh token**: stringa opaca, salvata hashata su DB, distribuita al client tramite cookie **httpOnly** (non leggibile da JavaScript), valido 30 giorni, **monouso** (rotazione automatica ad ogni utilizzo).

### Endpoint

| Metodo | Rotta | Descrizione | Autenticazione richiesta |
|---|---|---|---|
| POST | `/api/register` | Registrazione nuovo utente | — |
| POST | `/api/login` | Login | — (rate limited: 5/min per email+IP) |
| POST | `/api/refresh` | Rinnova l'access token usando il cookie `refresh_token` | Cookie `refresh_token` (rate limited: 6/min per IP) |
| POST | `/api/logout` | Invalida il refresh token corrente | — |
| GET | `/api/me` | Dati dell'utente autenticato | Access token |

Risposta di login/registrazione/refresh:

```json
{
  "access_token": "...",
  "token_type": "bearer",
  "expires_in": 900,
  "user": { "id": 1, "name": "...", "email": "...", "role": "user" }
}
```

### Note per un client SPA (es. Angular)

- Ogni richiesta deve includere `withCredentials: true` (o equivalente) affinché il cookie `refresh_token` venga inviato/ricevuto correttamente in un contesto cross-origin.
- CORS è configurato con `supports_credentials: true` e origine esplicita (vedi `config/cors.php` e variabile `FRONTEND_URL` in `.env`).
- Quando l'access token scade (401 su una richiesta autenticata), il client deve chiamare `/api/refresh` e ripetere la richiesta originale con il nuovo token, senza richiedere un nuovo login.

## Autorizzazione

Ogni utente ha un campo `role`: `user` (default) o `admin`. Tutti gli utenti autenticati possono leggere le risorse; solo gli `admin` possono creare, modificare o cancellare Opere, Autori e Categorie (applicato tramite Policy Laravel).

## Risorse

Tutte seguono il pattern REST standard (`index`, `store`, `show`, `update`, `destroy`), protette dall'access token:

- `/api/categories` — Categorie
- `/api/authors` — Autori
- `/api/works` — Opere (con relazioni verso autore, categoria e tag)
- `/api/tags` — Tag

### Ricerca e filtri su `/api/works`

Combinabili via query string: `search` (titolo), `author_id`, `category_id`, `tag_id` / `tag`, `tags` (lista di ID, AND), `year_from`, `year_to`, `sort` (es. `-publication_year`), `per_page`, `page`.

## Formato delle risposte

- Risorsa singola: `{ "data": {...} }`
- Lista: `{ "data": [...] }`
- Lista paginata: `{ "data": [...], "links": {...}, "meta": {...} }`

## Formato degli errori

| Status | Significato |
|---|---|
| 422 | Validazione fallita — `{ "message": "...", "errors": { "campo": [...] } }` |
| 401 | Non autenticato |
| 403 | Non autorizzato |
| 404 | Risorsa non trovata |
| 409 | Conflitto (es. cancellazione autore con opere collegate) |
| 429 | Rate limit superato |

## Setup locale

```bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan jwt:secret
./vendor/bin/sail artisan migrate --seed
```

Variabili d'ambiente rilevanti in `.env`:

```
JWT_TTL=15
FRONTEND_URL=http://localhost:4200
SANCTUM_STATEFUL_DOMAINS=localhost:4200
```

## Testing

```bash
./vendor/bin/sail artisan test
```

Suite scritta con Pest 4: copre CRUD, autorizzazione via Policy, validazione, e il flusso di autenticazione (registrazione, login, refresh, rotazione del refresh token).
