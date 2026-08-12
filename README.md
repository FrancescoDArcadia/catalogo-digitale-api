# Catalogo Digitale — API REST

API REST didattica sviluppata con Laravel 13, realizzata come progetto di 
approfondimento pratico del framework. Gestisce un catalogo di opere, autori, 
categorie e tag, con autenticazione e autorizzazione basata su ruoli.

## Stack tecnico

- Laravel 13
- MySQL
- Laravel Sanctum (autenticazione API)
- Pest 4 (testing)
- Docker / Laravel Sail (ambiente di sviluppo)

## Funzionalità

- CRUD completo su Opere, Autori, Categorie, Tag
- Autenticazione via token (Sanctum) con registrazione/login/logout
- Autorizzazione basata su ruoli tramite Policy
- Ricerca e filtri combinabili (per autore, categoria, tag, anno, testo libero)
- Paginazione, rate limiting, gestione centralizzata delle eccezioni
- Suite di test automatici (Pest)

## Setup locale

\`\`\`bash
cp .env.example .env
./vendor/bin/sail up -d
./vendor/bin/sail artisan key:generate
./vendor/bin/sail artisan migrate --seed
\`\`\`

## Testing

\`\`\`bash
./vendor/bin/sail artisan test
\`\`\`