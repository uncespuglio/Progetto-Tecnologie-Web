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