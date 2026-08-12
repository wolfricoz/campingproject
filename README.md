# Syntec Camping (Stage Project)

Dit project is gemaakt voor het beproeven van bekwaamheid. Bij de Syntec Camping worden
cabines uitgehuurd aan particulieren die van hun vakantie willen genieten.

## Requirements
- PHP 8.2
- Composer (https://getcomposer.org/)
- Node.js + npm (https://nodejs.org/en/download)



## Setup: 
1. Clone het project van GitHub met `git clone https://github.com/wolfricoz/campingproject.git`

2. Kopieer .env.example naar .env, dit is je environment file; dit houdt alle environment variabelen bij!

3. Daarna zal je het project moeten installeren met het volgende commando:
`composer install` of `.\composer.phar install`
Dit zal alle php libraries downloaden en klaar zetten voor gebruik.

4. Genereer de project key met `php artisan key:generate`

5. Vul de env in met de e-mail gegevens, en indien gewenst kan je sqlite omzetten naar mysql.

6. Nadat je de libraries hebt geïnstalleerd moeten we de database opzetten, om dit op te zetten zonder test data kan je het volgende commando gebruiken:
`php artisan migrate`
en als je dit met test data wilt, dan kan dat als volgt:
`php artisan migrate --seed`

7. Voor het opslaan van foto's moeten we ook de storage linken met `php artisan storage:link`

8. Daarna moet je `npm install` draaien, dit zorgt ervoor dat de javascript libraries worden gedownload.

## Starten
Om de testomgeving te starten doe je de volgende commando's:
`php artisan serve` en `npm run dev`

Je moet ook de queue starten om de e-mails te versturen omdat ze daarin worden opgeslagen.
Voor de queue: `php artisan queue:work`

## Inloggen
Beheerder:  admin@syntec-camping.nl / admin
Klant:      test@example.com / user


## Tests
Om te testen draai: 
php artisan test
vendor/bin/pint --test 
vendor/bin/phpstan analyse
