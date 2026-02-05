# UniRide (Progetto Tecnologie Web)

Applicazione PHP + MySQL per la condivisione di passaggi tra studenti UNIBO.

## Requisiti

- XAMPP per macOS (Apache + MariaDB/MySQL)
- PHP 8.x (quello incluso in XAMPP va bene)
- Browser moderno

## Installazione su XAMPP (macOS)

1. Copia la cartella del progetto in:
	- `/Applications/XAMPP/xamppfiles/htdocs/Progetto-Tecnologie-Web`

2. Avvia XAMPP Control Panel e accendi:
	- **Apache**
	- **MySQL / MariaDB**

3. (Consigliato) Crea il database importando lo schema:
	- Apri `http://localhost/phpmyadmin`
	- Vai su **Importa**
	- Seleziona [db/db.sql](db/db.sql)
	- Conferma

4. Apri l’app nel browser:
	- `http://localhost/Progetto-Tecnologie-Web/?p=home`

## Configurazione database (opzionale)

Di default l’app usa questi parametri:

- `DB_HOST=localhost`
- `DB_PORT=3306`
- `DB_NAME=uniride`
- `DB_USER=root`
- `DB_PASS=` (vuota)

Se vuoi cambiarli, crea un file `.env` nella root del progetto (stessa cartella di `index.php`), per esempio:

```env
DB_HOST=localhost
DB_PORT=3306
DB_NAME=uniride
DB_USER=root
DB_PASS=
```

Nota: all’avvio l’app esegue automaticamente le migrazioni “soft” e crea le tabelle mancanti (vedi [db/database.php](db/database.php)).

## Come si usa

### Utente

- **Registrazione**: `?p=register`
  - Email ammessa: `@studio.unibo.it` (controllo live lato client + controllo lato server)
  - Password: minimo 8 caratteri (controllo live lato client + controllo lato server)
- **Login**: `?p=login`
- **Cerca passaggi**: `?p=search`
- **Dettaglio passaggio**: `?p=ride&id=<ID>`
- **Richiedi posto / gestisci richiesta**: dal dettaglio passaggio
- **Pubblica passaggio**: `?p=ride_create` (con tappe)
- **I miei passaggi**: `?p=my_rides` (In programma / Storico)
- **Profilo**: `?p=profile` (modifica dati, storico viaggi, feedback ricevuti/inviati)

### Admin

L’admin ha un pannello dedicato:

- `?p=admin`

Funzioni principali:

- **Gestione passaggi** (crea/modifica/elimina, anche scegliendo un driver)
- **Gestione utenti** (modifica/elimina)
- **Gestione prenotazioni** (aggiungi/elimina, cambia stato, riallinea posti)
- **Gestione feedback** (inserisci/modifica/elimina, anche feedback manuali)

## Script JavaScript

Gli script sono caricati globalmente dal layout:

- [js/uniride-ui.js](js/uniride-ui.js): widget tappe (aggiungi/rimuovi)
- [js/validate-email-studio.js](js/validate-email-studio.js): validazione live email `@studio.unibo.it` in registrazione
- [js/validate-password-length.js](js/validate-password-length.js): validazione live password (min 8 caratteri) in registrazione

## Note e troubleshooting

- Se vedi una pagina bianca/errore DB: controlla che MySQL sia avviato e che il database `uniride` esista.
- Se modifichi CSS/JS e non vedi i cambiamenti: il progetto usa cache-busting via `filemtime`, quindi basta un refresh normale.

## Repository

Il footer dell’app punta alla repository GitHub del progetto:

- https://github.com/uncespuglio/Progetto-Tecnologie-Web/tree/main