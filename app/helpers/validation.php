<?php
/** app/helpers/validation.php - server-side validation helpers. */

function postStr(string $key): string
{
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? trim($_POST[$key]) : '';
}

function getStr(string $key): string
{
    return (isset($_GET[$key]) && is_string($_GET[$key])) ? trim($_GET[$key]) : '';
}

/** Passwords are not trimmed. */
function postPassword(string $key): string
{
    return (isset($_POST[$key]) && is_string($_POST[$key])) ? $_POST[$key] : '';
}

function validEmail(string $email): bool
{
    return strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/** Phone: optional +, then 9-15 digits (spaces/dashes removed beforehand). */
function validPhone(string $phone): bool
{
    return preg_match('/^\+?[0-9]{9,15}$/', $phone) === 1;
}

function normalizePhone(string $phone): string
{
    return preg_replace('/[\s-]/', '', $phone);
}

function validDateString(string $date): bool
{
    $dt = DateTime::createFromFormat('Y-m-d', $date);
    return $dt !== false && $dt->format('Y-m-d') === $date;
}

function validPassword(string $password): bool
{
    return strlen($password) >= 8 && strlen($password) <= 100
        && preg_match('/[A-Za-z]/', $password) === 1
        && preg_match('/[0-9]/', $password) === 1;
}

/** Escape % and _ so user text is matched literally inside a LIKE pattern. */
function likeTerm(string $text): string
{
    return '%' . addcslashes($text, '%_\\') . '%';
}

/**
 * Validate username/email/name/phone/address. Adds messages to $errors and
 * returns the cleaned values.
 */
function validateCustomerFields(array &$errors): array
{
    $d = [
        'username'  => postStr('username'),
        'email'     => strtolower(postStr('email')),
        'full_name' => postStr('full_name'),
        'phone'     => normalizePhone(postStr('phone')),
        'address'   => postStr('address'),
    ];
    if (preg_match('/^[A-Za-z0-9 _.\-]{3,100}$/', $d['username']) !== 1) {
        $errors[] = 'Username must be 3-100 characters (letters, numbers, spaces, . _ -).';
    }
    if (!validEmail($d['email'])) {
        $errors[] = 'Invalid email address.';
    }
    if (strlen($d['full_name']) < 2 || strlen($d['full_name']) > 150) {
        $errors[] = 'Full name is required (2-150 characters).';
    }
    if (!validPhone($d['phone'])) {
        $errors[] = 'Invalid phone number. Use 9-15 digits, optionally starting with +.';
    }
    if (strlen($d['address']) < 5 || strlen($d['address']) > 500) {
        $errors[] = 'Address is required (5-500 characters).';
    }
    return $d;
}

/** Validate pickup/delivery dates (Y-m-d strings). */
function validateOrderDates(string $pickup, string $delivery, bool $checkPast, array &$errors): void
{
    $ok = true;
    if (!validDateString($pickup)) {
        $errors[] = 'Please enter a valid pickup date.';
        $ok = false;
    }
    if (!validDateString($delivery)) {
        $errors[] = 'Please enter a valid delivery date.';
        $ok = false;
    }
    if (!$ok) {
        return;
    }
    if ($checkPast && $pickup < date('Y-m-d')) {
        $errors[] = 'Pickup date cannot be in the past.';
    }
    if ($delivery <= $pickup) {
        $errors[] = 'Delivery date must be later than pickup date.';
    }
}

/** Read the posted item rows (item_type[], quantity[], unit_price[]) as plain strings. */
function readItemRows(): array
{
    $types  = $_POST['item_type'] ?? [];
    $qtys   = $_POST['quantity'] ?? [];
    $prices = $_POST['unit_price'] ?? [];
    if (!is_array($types) || !is_array($qtys) || !is_array($prices)) {
        return [];
    }
    $rows = [];
    foreach ($types as $i => $type) {
        $rows[] = [
            'item_type'  => is_string($type) ? trim($type) : '',
            'quantity'   => (isset($qtys[$i]) && is_string($qtys[$i])) ? trim($qtys[$i]) : '',
            'unit_price' => (isset($prices[$i]) && is_string($prices[$i])) ? trim($prices[$i]) : '',
        ];
    }
    return array_slice($rows, 0, 50);
}

/**
 * Validate item rows and return clean items. Customers always get the catalogue price;
 * administrators ($adminPrices = true) may set the unit price.
 */
function validateItemRows(array $rows, array $catalog, bool $adminPrices, array &$errors): array
{
    $before = count($errors);
    $items  = [];
    foreach ($rows as $n => $r) {
        if ($r['item_type'] === '' && $r['quantity'] === '') {
            continue; // blank row
        }
        $label = 'Item ' . ($n + 1);
        if (!array_key_exists($r['item_type'], $catalog)) {
            $errors[] = $label . ': please choose a valid item type.';
            continue;
        }
        if (!ctype_digit($r['quantity']) || (int) $r['quantity'] < 1 || (int) $r['quantity'] > 1000) {
            $errors[] = $label . ': quantity must be a whole number greater than zero (maximum 1000).';
            continue;
        }
        $price = (float) $catalog[$r['item_type']];
        if ($adminPrices) {
            if (!is_numeric($r['unit_price']) || (float) $r['unit_price'] < 0 || (float) $r['unit_price'] > 1000000) {
                $errors[] = $label . ': unit price must be a number that is not negative.';
                continue;
            }
            $price = round((float) $r['unit_price'], 2);
        }
        $items[] = ['item_type' => $r['item_type'], 'quantity' => (int) $r['quantity'], 'unit_price' => $price];
    }
    if (count($items) === 0 && count($errors) === $before) {
        $errors[] = 'Add at least one laundry item.';
    }
    return $items;
}
