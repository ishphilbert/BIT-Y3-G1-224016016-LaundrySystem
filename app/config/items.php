<?php
/**
 * app/config/items.php
 * Catalogue of laundry item types and their current unit prices.
 *
 * Kept as a PHP array (not a 5th table) so the database stays at the four required
 * entities. When an order is placed, the price is COPIED into order_items.unit_price,
 * so later price changes never alter old orders.
 * Prices are in RWF; change them here to suit your currency.
 */
return [
    'Shirt'    => 500.00,
    'Trousers' => 700.00,
    'Dress'    => 1200.00,
    'Jacket'   => 1800.00,
    'Suit'     => 3000.00,
    'Bedsheet' => 1000.00,
    'Blanket'  => 2500.00,
    'Towel'    => 600.00,
];
