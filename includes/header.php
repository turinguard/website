<?php
$currentPage = $currentPage ?? '';
?>
<body>
  <header class="site-header"><div class="header-inner wrap">
    <a class="brand" href="/" aria-label="Turinguard, home"><img src="assets/images/logo.png" alt="" width="36" height="32"><span>TURINGUARD</span></a>
    <details class="mobile-nav"><summary aria-label="Navigatie openen"><span></span><span></span><span></span></summary><nav aria-label="Mobiele hoofdnavigatie">
      <a href="/"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?>>Home</a>
      <a href="/diensten"<?= $currentPage === 'diensten' ? ' aria-current="page"' : '' ?>>Diensten</a>
      <a href="/over-ons"<?= $currentPage === 'over-ons' ? ' aria-current="page"' : '' ?>>Over ons</a>
      <a href="/contact"<?= $currentPage === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a>
      <a class="button button-small" href="/intake"<?= $currentPage === 'intake' ? ' aria-current="page"' : '' ?>>Gratis intake</a>
    </nav></details>
    <nav class="desktop-nav" aria-label="Hoofdnavigatie">
      <a href="/"<?= $currentPage === 'home' ? ' aria-current="page"' : '' ?>>Home</a>
      <a href="/diensten"<?= $currentPage === 'diensten' ? ' aria-current="page"' : '' ?>>Diensten</a>
      <a href="/over-ons"<?= $currentPage === 'over-ons' ? ' aria-current="page"' : '' ?>>Over ons</a>
      <a href="/contact"<?= $currentPage === 'contact' ? ' aria-current="page"' : '' ?>>Contact</a>
      <a class="button button-small" href="/intake"<?= $currentPage === 'intake' ? ' aria-current="page"' : '' ?>>Gratis intake</a>
    </nav>
  </div></header>
  <main>
