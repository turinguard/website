# Turinguard website-overzicht

## Structuur

De website draait als eenvoudige PHP-site zonder framework of Composer-package.

- `index.php`, `diensten.php`, `over-ons.php`, `contact.php` en `intake.php` zijn de pagina's.
- `includes/header.php` bevat de gedeelde header, het logo en de navigatie.
- `includes/footer.php` bevat de gedeelde footer.
- `includes/mailer.php` is een kleine SMTP-client met STARTTLS en SMTP AUTH.
- `includes/config.php` bevat lokale SMTP-instellingen en staat in `.gitignore`.
- `includes/config.php.example` is de veilige template om instellingen van de hosting over te nemen.

Een logo- of navigatiewijziging hoeft daardoor nog maar op één plek in `includes/header.php` te gebeuren. Een wijziging in de footer gebeurt in `includes/footer.php`.

## Installeren op de hosting

1. Zorg dat PHP 8.0 of nieuwer beschikbaar is. De PHP-extensies `openssl`, `mbstring` en `session` zijn nodig.
2. Maak op de hosting een `config.php` aan op een plek die niet publiek toegankelijk is. Stel daarna de environment variable `TURINGUARD_CONFIG` in op het absolute pad naar dat bestand. Als dat niet kan, kopieer `includes/config.php.example` naar `includes/config.php`; dat bestand staat in `.gitignore` en wordt door een correcte PHP-configuratie niet als broncode uitgeleverd.
3. Vul de SMTP-host, poort, gebruikersnaam, wachtwoord, afzender en ontvanger van de mailprovider in. Gebruik de generieke voorbeeldwaarden nooit in productie.
4. Upload de `.php`-bestanden, de map `includes`, `assets`, `sitemap.xml` en `robots.txt`.
5. Open `intake.php` en test een formulierverzending. Controleer ook de spamfolder.

Gebruik meestal poort `587` met STARTTLS. Poort `465` met implicit TLS wordt ook ondersteund. `smtp_ehlo` staat standaard op `localhost`, omdat sommige hostingproviders een eigen domeinnaam in EHLO weigeren. Pas die waarde alleen aan als je provider expliciet een andere EHLO-naam voorschrijft. De exacte SMTP-host en logingegevens komen van de mailprovider of hostingpartij. Gebruik als afzender een adres op hetzelfde domein om SPF/DKIM/DMARC en aflevering niet onnodig te verstoren. De serverlog vermeldt bij een fout de SMTP-fase, maar nooit het wachtwoord.

De SMTP-login (`smtp_username`) is het account waarmee wordt verzonden. De `recipient` is de mailbox waarin intakeaanvragen binnenkomen en mag daarvan verschillen. In de productieconfiguratie staat die op `info@turinguard.nl`; controleer bij problemen ook de spamfolder en eventuele server-side mailfilters.

## Lokaal testen

Start in deze map een tijdelijke PHP-server:

```sh
php -S localhost:8000
```

Open daarna <http://localhost:8000/>. Een echte formulierverzending werkt alleen als `includes/config.php` geldige SMTP-gegevens bevat.

## Belangrijke keuzes

- PHP includes zijn gekozen omdat de hosting PHP ondersteunt en de site geen buildstap nodig heeft.
- Er is geen framework of externe mailpackage toegevoegd. De SMTP-client gebruikt PHP's socket- en TLS-functionaliteit.
- Het formulier gebruikt POST, server-side validatie, een CSRF-token en een verborgen honeypotveld.
- Apache blokkeert directory listings, beperkt requests tot 64 KB en stuurt securityheaders zoals HSTS, CSP, `nosniff`, frame-blocking en een beperkte Permissions Policy.
- SMTP TLS valideert het servercertificaat; alleen poort 587 (STARTTLS) en 465 (implicit TLS) zijn toegestaan.
- SMTP-wachtwoorden worden niet in git opgeslagen; `includes/config.php` is genegeerd via `.gitignore`.
- `config.php.example` bevat bewust geen echt domein, e-mailadres of providernaam. Concrete infrastructuurgegevens horen in de hostingconfiguratie, niet in een publieke repository.
- De publieke URLs gebruiken extensieloze routes zoals `/diensten` en `/intake`; Apache herschrijft die intern naar PHP. Oude `.php`-URL's krijgen een 301-redirect.

## Onderhoud

Pas bij een nieuwe pagina de navigatie-links in `includes/header.php` en eventueel de footer-links in `includes/footer.php` aan. Voeg de extensieloze publieke URL ook toe aan `sitemap.xml`.

De favicon-link gebruikt eerst `assets/images/logo.ico` en daarna het bestaande `assets/images/logo.png` als fallback. Plaats een geldig `logo.ico` in die map als je de ICO-versie wilt gebruiken; op dit moment bevat de repository alleen `logo.png`.

## Security-checklist voor livegang

- Roteer het SMTP-wachtwoord zodra het ooit buiten de hostingconfiguratie zichtbaar is geweest.
- Controleer dat `includes/config.php` niet in git staat en bij voorkeur buiten de publieke webroot staat.
- Controleer op de live site dat HTTP naar HTTPS redirecteert en dat de responseheaders aanwezig zijn.
- Controleer dat `/includes/config.php`, `/.git/`, `/.env` en `/README.md` geen inhoud uitleveren.
- Test het formulier met normale invoer, ongeldige e-mail, te lange invoer en meerdere snelle inzendingen.
- Zet foutmeldingen op de productieomgeving uit (`display_errors=Off`) en controleer serverlogs zonder secrets te loggen.
