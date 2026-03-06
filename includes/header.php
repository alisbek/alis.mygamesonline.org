<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';

$currentLang = getCurrentLang();
$lang = loadLang($currentLang);
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= __('meta.description') ?>">
    <title><?= __('meta.title') ?></title>
    <link rel="canonical" href="<?= SITE_URL . $_SERVER['REQUEST_URI'] ?>">
    <?php
    // Get the current page path without language prefix for hreflang alternates
    $requestPath = $_SERVER['REQUEST_URI'];
    $langCodes = array_keys(LANGUAGES);
    $hreflangPath = preg_replace('/^\/(' . implode('|', $langCodes) . ')(\/|$)/', '/', $requestPath);
    $hreflangPath = ($hreflangPath === '/') ? '' : $hreflangPath;
    ?>
    <?php foreach (LANGUAGES as $code => $name): ?>
        <?php if ($code !== $currentLang): ?>
    <link rel="alternate" hreflang="<?= $code ?>" href="<?= url($hreflangPath, $code) ?>">
        <?php endif; ?>
    <?php endforeach; ?>
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script>var SITE_URL = <?= json_encode(SITE_URL) ?>;</script>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="header-content">
                <a href="<?= url() ?>" class="logo">
                    <span class="logo-text">Feltee</span>
                </a>
                
                <button class="mobile-menu-toggle" aria-label="Menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <nav class="nav">
                    <a href="<?= url() ?>" class="nav-link <?= $currentPage === 'index' ? 'active' : '' ?>"><?= __('nav.home') ?></a>
                    <a href="<?= url('/products.php') ?>" class="nav-link <?= $currentPage === 'products' || $currentPage === 'product' ? 'active' : '' ?>"><?= __('nav.products') ?></a>
                    <a href="<?= url('/about.php') ?>" class="nav-link <?= $currentPage === 'about' ? 'active' : '' ?>"><?= __('nav.about') ?></a>
                    <a href="<?= url('/contact.php') ?>" class="nav-link <?= $currentPage === 'contact' ? 'active' : '' ?>"><?= __('nav.contact') ?></a>
                </nav>
                
                <div class="header-actions">
                    <div class="lang-switcher">
                        <?php foreach (LANGUAGES as $code => $name): ?>
                            <?php if ($code === $currentLang): ?>
                                <span class="lang-current"><?= strtoupper($code) ?></span>
                            <?php else: ?>
                                <a href="<?= url($hreflangPath, $code) ?>" class="lang-link"><?= strtoupper($code) ?></a>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <a href="<?= url('/cart.php') ?>" class="cart-link">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="9" cy="21" r="1"></circle>
                            <circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <?php $cartCount = getCartCount(); ?>
                        <?php if ($cartCount > 0): ?>
                            <span class="cart-count"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <main class="main">