<?php
function __($key) {
    global $lang;
    return $lang[$key] ?? $key;
}

function getCurrentLang() {
    if (isset($_GET['lang']) && array_key_exists($_GET['lang'], LANGUAGES)) {
        $_SESSION['lang'] = $_GET['lang'];
    }
    if (!isset($_SESSION['lang'])) {
        $_SESSION['lang'] = DEFAULT_LANG;
    }
    return $_SESSION['lang'];
}

function loadLang($langCode) {
    $file = __DIR__ . '/../lang/' . $langCode . '.php';
    if (file_exists($file)) {
        return include $file;
    }
    return include __DIR__ . '/../lang/' . DEFAULT_LANG . '.php';
}

function url($path = '', $lang = null) {
    $lang = $lang ?? getCurrentLang();
    // Strip any existing language prefix from the path
    $langCodes = array_keys(LANGUAGES);
    $pattern = '/^\/(' . implode('|', $langCodes) . ')(\/|$)/';
    $path = preg_replace($pattern, '/', $path);
    // Normalize double slashes and trailing slash for root
    $path = ($path === '/') ? '' : $path;
    $prefix = ($lang === DEFAULT_LANG) ? '' : '/' . $lang;
    return SITE_URL . $prefix . $path;
}

function redirect($path) {
    header('Location: ' . $path);
    exit;
}

function sanitize($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    return number_format($price, 0, '', ' ') . ' ' . FELTEE_CURRENCY_SYMBOL;
}

function csrfField() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
}

function verifyCsrf() {
    return isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

function getCart() {
    return $_SESSION['cart'] ?? [];
}

function getCartTotal() {
    $total = 0;
    foreach (getCart() as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

function getCartCount() {
    return array_sum(array_column(getCart(), 'quantity'));
}