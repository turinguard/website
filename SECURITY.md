# Securitymaatregelen

## Huidige bescherming

- HTTPS-beleid via HSTS, plus `nosniff`, frame-blocking, Referrer Policy, Permissions Policy en Content Security Policy.
- Directory listings zijn uitgeschakeld.
- De map `includes/` is niet rechtstreeks toegankelijk.
- Configuratie-, `.env`-, git-, log- en README-bestanden worden door Apache geblokkeerd.
- Requests zijn beperkt tot 64 KB.
- SMTP gebruikt TLS met certificaatvalidatie. Alleen poort 587 (STARTTLS) en 465 (implicit TLS) zijn toegestaan.
- SMTP-wachtwoorden staan niet in git en horen bij voorkeur buiten de publieke webroot.
- Het intakeformulier gebruikt CSRF-bescherming, een honeypotveld, sessiecookies met `HttpOnly`, `Secure` en `SameSite=Lax`, server-side validatie en vaste keuzewaarden.
- Invoer heeft maximale lengtes en wordt veilig ge-escaped bij HTML-output.
- Alleen `GET` en `POST` zijn toegestaan.

## Rate limiting en bots

Er is een beperkte applicatielimiet: maximaal 3 succesvolle intakeverzendingen per sessie binnen 10 minuten. Dit remt herhaald klikken en eenvoudige bots, maar is geen volwaardige bot- of IP-bescherming. Een bot kan nieuwe sessies starten of via meerdere IP-adressen werken.

Voor echte productie-rate limiting hoort de volgende laag bij de hosting of WAF te liggen:

- Cloudflare Rate Limiting of een vergelijkbare hosting-WAF op `/intake`.
- Eventueel Cloudflare Turnstile als bots ondanks de honeypot en CSRF blijven binnenkomen.
- Een server-side limiet op IP, bijvoorbeeld maximaal 5 POST-verzoeken per 10 minuten, met aandacht voor gedeelde bedrijfsnetwerken en proxies.

De sessielimiet alleen moet niet als bescherming tegen een gerichte volumetrische aanval worden beschouwd.

## Live checklist

- Roteer SMTP-wachtwoorden die ooit buiten de hostingconfiguratie zichtbaar waren.
- Houd `includes/config.php` buiten de publieke webroot en controleer dat het niet in git staat.
- Controleer dat HTTP naar HTTPS redirecteert.
- Controleer responseheaders met een security-header scanner.
- Controleer dat `/.env`, `/.git/`, `/includes/config.php` en `/README.md` geen inhoud uitleveren.
- Zet `display_errors=Off` en logging naar een niet-publieke locatie.
- Test normale, ongeldige, te lange en herhaalde formulierinzendingen.
- Controleer SPF, DKIM en DMARC voor de verzendende domeinen.
- Controleer periodiek PHP, Apache en de hostingomgeving op updates.

## Incidenten melden

Meld beveiligingsproblemen via het adres in `.well-known/security.txt`. Deel geen SMTP-wachtwoorden of andere secrets in een issue of publieke pull request.
