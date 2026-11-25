-- database/seed.sql - sample data for testing. Import AFTER schema.sql.
-- Passwords are bcrypt hashes (see README for the plain-text demo passwords).
USE online_laundry_db;

INSERT INTO users (user_id, username, email, password_hash, role) VALUES
(1, 'System Admin', 'admin@laundry.test', '$2y$10$NmLaz82c.IMiK3/8mYweq.hdfjRhfbeDFEApI3rXiNg34vllzOUBy', 'admin'),
(2, 'Alice Staff', 'staff1@laundry.test', '$2y$10$uHpJji4QlBts50Jx9M08w.PYuv/bnXQ14qRRBscrzlsz/KNM4YHVK', 'staff'),
(3, 'Brian Staff', 'staff2@laundry.test', '$2y$10$z7BaOEL06Q7bN46exl7x1OyXHkQdMD5SKWQhoHIsUCorO7JfPIGYG', 'staff'),
(4, 'jane_uwase', 'jane@example.com', '$2y$10$jg/CR48sXf8591Cjm4K2fesXPv.62qjzbVddxLBpkIW/jUvOjQad6', 'customer'),
(5, 'eric_mugisha', 'eric@example.com', '$2y$10$V07Gg9tKWyQ3/J.3IXdND.jqE.qYeTTdDoU.tCycWC9TOBmYmyMY2', 'customer'),
(6, 'grace_ingabire', 'grace@example.com', '$2y$10$rR.m8Pa6OjIzSPlyvRXu/utf5gaPY0QOZ7LFy1NLr4Qx3WGIoCk4G', 'customer'),
(7, 'david_nkurunziza', 'david@example.com', '$2y$10$vHEqrBB6nk3nN95TRY1p4OuFeLlJIPRlbARyY6CSba/UO1Z7wQXTe', 'customer');

INSERT INTO customers (customer_id, user_id, full_name, phone, address) VALUES
(1, 4, 'Jane Uwase', '0788123456', 'KG 11 Ave 25, Kimironko, Kigali'),
(2, 5, 'Eric Mugisha', '0788234567', 'KN 4 St 12, Nyarugenge, Kigali'),
(3, 6, 'Grace Ingabire', '0788345678', 'KK 15 Rd 8, Kicukiro, Kigali'),
(4, 7, 'David Nkurunziza', '0788456789', 'KG 7 Ave 30, Remera, Kigali');

INSERT INTO orders (order_id, customer_id, assigned_staff_id, pickup_date, delivery_date, total, status, updated_at) VALUES
(1, 1, 2, '2026-11-10', '2026-11-13', 3900.00, 'delivered', '2026-11-13 15:30:00'),
(2, 1, 3, '2026-11-18', '2026-11-22', 4400.00, 'processing', '2026-11-19 09:15:00'),
(3, 2, 2, '2026-11-20', '2026-11-24', 4500.00, 'picked_up', '2026-11-20 10:05:00'),
(4, 2, NULL, '2026-11-25', '2026-11-28', 4200.00, 'pending', NULL),
(5, 3, 3, '2026-11-19', '2026-11-23', 6200.00, 'out_for_delivery', '2026-11-21 08:40:00'),
(6, 3, NULL, '2026-11-15', '2026-11-18', 2100.00, 'cancelled', '2026-11-15 12:00:00'),
(7, 4, 2, '2026-11-22', '2026-11-25', 6800.00, 'confirmed', '2026-11-21 07:50:00'),
(8, 4, 3, '2026-11-21', '2026-11-24', 6600.00, 'ready_for_delivery', '2026-11-21 09:30:00'),
(9, 4, 2, '2026-11-05', '2026-11-08', 3000.00, 'delivered', '2026-11-08 14:10:00');

INSERT INTO order_items (order_id, item_type, quantity, unit_price) VALUES
(1, 'Shirt', 5, 500.00),
(1, 'Trousers', 2, 700.00),
(2, 'Bedsheet', 2, 1000.00),
(2, 'Towel', 4, 600.00),
(3, 'Suit', 1, 3000.00),
(3, 'Shirt', 3, 500.00),
(4, 'Dress', 2, 1200.00),
(4, 'Jacket', 1, 1800.00),
(5, 'Blanket', 2, 2500.00),
(5, 'Towel', 2, 600.00),
(6, 'Trousers', 3, 700.00),
(7, 'Shirt', 8, 500.00),
(7, 'Trousers', 4, 700.00),
(8, 'Dress', 1, 1200.00),
(8, 'Jacket', 2, 1800.00),
(8, 'Towel', 3, 600.00),
(9, 'Bedsheet', 3, 1000.00);
