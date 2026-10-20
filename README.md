# Turinguard

Website van Turinguard: cybersecurityadvies en praktisch IT-beheer voor Nederlandse MKB-bedrijven.

## Projectdocumentatie

- [Website-overzicht](WEBSITE-OVERZICHT.md): structuur, installatie, SMTP en dagelijks onderhoud.
- [Securitymaatregelen](SECURITY.md): huidige beveiligingsmaatregelen, beperkingen en live checklist.
- [Security policy](.well-known/security.txt): meldpunt voor beveiligingsproblemen.

## Techniek

De website is een eenvoudige PHP-site zonder framework of Composer-dependencies. Apache verzorgt de extensieloze URL's via `.htaccess`. De intake gebruikt een eigen SMTP-client met TLS.

## Snel starten

1. Zorg voor PHP 8.0+ met `openssl`, `mbstring` en `session`.
2. Configureer SMTP volgens [WEBSITE-OVERZICHT.md](WEBSITE-OVERZICHT.md).
3. Test lokaal met `php -S localhost:8000` of upload naar de PHP-hosting.

Controleer vóór productie de security-checklist in [SECURITY.md](SECURITY.md).
