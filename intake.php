<?php
declare(strict_types=1);
session_start();
require __DIR__ . '/includes/mailer.php';

$formError = '';
$formSent = false;
if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $postedToken = (string) ($_POST['csrf_token'] ?? '');
  $name = trim((string) ($_POST['name'] ?? ''));
  $company = trim((string) ($_POST['company'] ?? ''));
  $email = trim((string) ($_POST['email'] ?? ''));
  $topic = trim((string) ($_POST['topic'] ?? ''));
  $details = trim((string) ($_POST['details'] ?? ''));

  if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
    $formError = 'Je formulier kon niet worden gecontroleerd. Probeer het opnieuw.';
  } elseif (!empty($_POST['website'] ?? '')) {
    $formError = 'Je formulier kon niet worden verzonden.';
  } elseif ($name === '' || $company === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $formError = 'Vul je naam, bedrijf en een geldig e-mailadres in.';
  } else {
    try {
      $configPath = getenv('TURINGUARD_CONFIG') ?: __DIR__ . '/includes/config.php';
      if (!is_file($configPath)) {
        throw new RuntimeException('SMTP configuration file is missing.');
      }
      $config = require $configPath;
      $body = "Naam: $name\nBedrijf: $company\nE-mail: $email\nOnderwerp: $topic\n\nToelichting:\n$details";
      smtp_send($config, $email, 'Nieuw intakeverzoek via turinguard.nl', $body);
      $formSent = true;
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } catch (Throwable $exception) {
      error_log($exception->getMessage());
      $formError = 'Versturen is helaas niet gelukt. Mail ons rechtstreeks via info@turinguard.nl.';
    }
  }
}
?>
<!doctype html>
<html lang="nl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090b0e">
    <meta name="author" content="Turinguard">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="description" content="Plan een gratis intake bij Turinguard voor cybersecurity, een security-scan, netwerkhardening of e-mailbeveiliging voor jouw MKB-bedrijf.">
    <title>Turinguard | Gratis cybersecurity intake</title>
    <link rel="canonical" href="https://turinguard.nl/intake.php">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="assets/styling/styles.css">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Turinguard">
    <meta property="og:url" content="https://turinguard.nl/intake.php">
    <meta property="og:title" content="Turinguard | Gratis cybersecurity intake">
    <meta property="og:description" content="Plan een gratis intake bij Turinguard voor cybersecurity, een security-scan, netwerkhardening of e-mailbeveiliging voor jouw MKB-bedrijf.">
    <meta property="og:image" content="https://turinguard.nl/logo.png">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Turinguard | Gratis cybersecurity intake">
    <meta name="twitter:description" content="Plan een gratis intake bij Turinguard voor cybersecurity, een security-scan, netwerkhardening of e-mailbeveiliging voor jouw MKB-bedrijf.">
    <meta name="twitter:image" content="https://turinguard.nl/logo.png">
  </head>
  <?php $currentPage = 'intake'; require __DIR__ . '/includes/header.php'; ?>
      <section class="page-heading"><div class="wrap">
        <p class="eyebrow">gratis intake / eerste stap</p>
        <h1>Vertel ons wat er speelt. Dan denken we met je mee.</h1>
        <p class="page-lead">De kennismaking duurt ongeveer 30 minuten en is vrijblijvend. Geen verkooppraatje: we luisteren, stellen een paar vragen en geven je een eerste beeld van wat zinvol kan zijn.</p>
      </div></section>
      <section class="wrap intake-page">
        <div class="intake-context"><p class="eyebrow">wat je kunt verwachten</p><ul class="contact-points"><li><span aria-hidden="true">→</span> Je spreekt rechtstreeks met Alberto of Max</li><li><span aria-hidden="true">→</span> We reageren binnen twee werkdagen</li><li><span aria-hidden="true">→</span> Je zit nergens aan vast</li></ul><p>Heb je liever direct contact? Mail ons via <a href="mailto:info@turinguard.nl">info@turinguard.nl</a>.</p></div>
        <?php if ($formSent): ?><p class="form-success" role="status">Bedankt. Je aanvraag is verzonden; we nemen binnen twee werkdagen contact met je op.</p><?php endif; ?>
        <?php if ($formError !== ''): ?><p class="form-error" role="alert"><?= htmlspecialchars($formError, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?>
        <form class="intake-form" action="intake.php" method="post">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
          <label class="form-trap" aria-hidden="true">Website<input tabindex="-1" autocomplete="off" name="website"></label>
          <div class="form-grid">
            <label>Naam<input required name="name" autocomplete="name" value="<?= htmlspecialchars($name ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
            <label>Bedrijf<input required name="company" autocomplete="organization" value="<?= htmlspecialchars($company ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
            <label class="form-wide">E-mail<input required type="email" name="email" autocomplete="email" value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>"></label>
            <label class="form-wide">Waar kunnen we je mee helpen?
              <select name="topic"><option>Gratis intake / security-scan</option><option>Adviesrapport</option><option>Hardening &amp; implementatie</option><option>Beheer &amp; monitoring</option><option>Website- en e-mailbeveiliging</option></select>
            </label>
            <label class="form-wide">Waar loop je tegenaan?<textarea name="details" rows="4" placeholder="Een paar zinnen is genoeg."><?= htmlspecialchars($details ?? '', ENT_QUOTES, 'UTF-8') ?></textarea></label>
          </div>
          <button class="button" type="submit">Verstuur intakeverzoek <span aria-hidden="true">↗</span></button>
          <p class="form-note">Je gegevens worden alleen gebruikt om contact op te nemen over je aanvraag.</p>
        </form>
      </section>
    </main>
<?php require __DIR__ . '/includes/footer.php'; ?>
