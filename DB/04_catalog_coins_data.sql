-- Auto-generated catalog coins data
-- Generated on: 2026-01-24T21:53:08.229Z

SET @bg_id = (SELECT id FROM countries WHERE name = 'Bulgaria' LIMIT 1);
SET @admin_id = (SELECT id FROM users WHERE username = 'TestUser' LIMIT 1);

INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, weight, diameter, thickness, mintage, catalog_image_front, catalog_image_back, created_by_user_id, is_approved) VALUES 
(@bg_id, '1 Euro (2026)', '1 Euro', 2026, 'European Union (Euro)', 'Aluminium', 17.56, 21.00, 1.45, 8539239, 'uploads/populate_coin_types/bulgaria-1-euro-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-euro-2026.jpg', @admin_id, 1),
(@bg_id, '1 Euro Cent (2026)', '1 Euro Cent', 2026, 'European Union (Euro)', 'Brass', 12.49, 16.47, 2.63, 2804941, 'uploads/populate_coin_types/bulgaria-1-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '1 Lev (1992)', '1 Lev', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Gold', 4.28, 25.73, 2.34, 8714061, 'uploads/populate_coin_types/bulgaria-1-lev-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-lev-1992.jpg', @admin_id, 1),
(@bg_id, '1 Lev (2002)', '1 Lev', 2002, 'Republic of Bulgaria (1992 - 2025)', 'Silver', 19.96, 30.34, 2.01, 3837442, 'uploads/populate_coin_types/bulgaria-1-lev-2002 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-lev-2002.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1951)', '1 Stotinka', 1951, 'People''s Republic (1946 - 1991)', 'Gold', 6.45, 21.23, 1.36, 14007318, 'uploads/populate_coin_types/bulgaria-1-stotinka-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1951.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1962)', '1 Stotinka', 1962, 'People''s Republic (1946 - 1991)', 'Steel', 5.82, 28.04, 2.49, 1514688, 'uploads/populate_coin_types/bulgaria-1-stotinka-1962 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1962.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1990)', '1 Stotinka', 1990, 'People''s Republic (1946 - 1991)', 'Bi-Metallic', 24.22, 33.87, 2.01, 5812809, 'uploads/populate_coin_types/bulgaria-1-stotinka-1990 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1990.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1999)', '1 Stotinka', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Copper', 5.22, 20.73, 2.73, 14938788, 'uploads/populate_coin_types/bulgaria-1-stotinka-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1999.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (2000)', '1 Stotinka', 2000, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 17.61, 17.41, 1.60, 4650765, 'uploads/populate_coin_types/bulgaria-1-stotinka-2000 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-2000.jpg', @admin_id, 1),
(@bg_id, '10 Euro Cent (2026)', '10 Euro Cent', 2026, 'European Union (Euro)', 'Silver', 20.73, 30.06, 1.59, 10543361, 'uploads/populate_coin_types/bulgaria-10-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '10 Leva (1992)', '10 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Gold', 22.22, 34.41, 1.25, 14470275, 'uploads/populate_coin_types/bulgaria-10-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-leva-1992.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1951)', '10 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Aluminium', 3.75, 28.64, 1.33, 367244, 'uploads/populate_coin_types/bulgaria-10-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1992)', '10 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Copper', 10.70, 33.19, 1.55, 4629319, 'uploads/populate_coin_types/bulgaria-10-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1999)', '10 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Aluminium', 17.88, 18.71, 1.25, 12802363, 'uploads/populate_coin_types/bulgaria-10-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '2 Euro (2026)', '2 Euro', 2026, 'European Union (Euro)', 'Copper-Nickel', 8.74, 17.80, 2.61, 315409, 'uploads/populate_coin_types/bulgaria-2-euro-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-euro-2026.jpg', @admin_id, 1),
(@bg_id, '2 Euro Cent (2026)', '2 Euro Cent', 2026, 'European Union (Euro)', 'Silver', 20.46, 26.08, 1.90, 109760, 'uploads/populate_coin_types/bulgaria-2-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '2 Leva (1992)', '2 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 24.21, 24.11, 1.35, 3644417, 'uploads/populate_coin_types/bulgaria-2-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-leva-1992.jpg', @admin_id, 1),
(@bg_id, '2 Leva (2015)', '2 Leva', 2015, 'Republic of Bulgaria (1992 - 2025)', 'Copper', 5.53, 35.53, 2.61, 625872, 'uploads/populate_coin_types/bulgaria-2-leva-2015 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-leva-2015.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1962)', '2 Stotinki', 1962, 'People''s Republic (1946 - 1991)', 'Aluminium', 20.85, 32.09, 1.22, 8976237, 'uploads/populate_coin_types/bulgaria-2-stotinki-1962 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1962.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1989)', '2 Stotinki', 1989, 'People''s Republic (1946 - 1991)', 'Zinc', 10.79, 31.75, 2.35, 11216728, 'uploads/populate_coin_types/bulgaria-2-stotinki-1989 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1989.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1999)', '2 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Aluminium', 19.70, 36.96, 1.87, 13820757, 'uploads/populate_coin_types/bulgaria-2-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '20 Euro Cent (2026)', '20 Euro Cent', 2026, 'European Union (Euro)', 'Bronze', 10.73, 17.62, 2.05, 4958190, 'uploads/populate_coin_types/bulgaria-20-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '20 Leva (1997)', '20 Leva', 1997, 'Republic of Bulgaria (1992 - 2025)', 'Copper-Nickel', 10.47, 32.66, 1.79, 4438503, 'uploads/populate_coin_types/bulgaria-20-leva-1997 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-leva-1997.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1954)', '20 Stotinki', 1954, 'People''s Republic (1946 - 1991)', 'Brass', 3.59, 18.17, 2.20, 1972314, 'uploads/populate_coin_types/bulgaria-20-stotinki-1954 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1954.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1992)', '20 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 6.84, 34.49, 2.56, 13432861, 'uploads/populate_coin_types/bulgaria-20-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1999)', '20 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 12.83, 35.91, 2.01, 14746676, 'uploads/populate_coin_types/bulgaria-20-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '3 Stotinki (1951)', '3 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Gold', 13.05, 35.28, 1.82, 11327810, 'uploads/populate_coin_types/bulgaria-3-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-3-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '5 Euro Cent (2026)', '5 Euro Cent', 2026, 'European Union (Euro)', 'Gold', 6.93, 34.83, 1.61, 9080276, 'uploads/populate_coin_types/bulgaria-5-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '5 Leva (1992)', '5 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Zinc', 9.61, 36.71, 2.73, 2372049, 'uploads/populate_coin_types/bulgaria-5-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-leva-1992.jpg', @admin_id, 1),
(@bg_id, '5 Stotinki (1951)', '5 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Aluminium', 15.04, 31.19, 1.47, 11861211, 'uploads/populate_coin_types/bulgaria-5-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '5 Stotinki (1999)', '5 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Aluminium', 23.80, 21.22, 1.47, 3259718, 'uploads/populate_coin_types/bulgaria-5-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '50 Euro Cent (2026)', '50 Euro Cent', 2026, 'European Union (Euro)', 'Bronze', 8.12, 24.07, 1.73, 5300765, 'uploads/populate_coin_types/bulgaria-50-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '50 Leva (1997)', '50 Leva', 1997, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 13.34, 33.91, 2.68, 2726551, 'uploads/populate_coin_types/bulgaria-50-leva-1997 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-leva-1997.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1988)', '50 Stotinki', 1988, 'People''s Republic (1946 - 1991)', 'Bi-Metallic', 9.78, 28.84, 1.30, 514018, 'uploads/populate_coin_types/bulgaria-50-stotinki-1988 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1988.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1992)', '50 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Brass', 14.55, 29.41, 1.74, 11027180, 'uploads/populate_coin_types/bulgaria-50-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1999)', '50 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Silver', 13.01, 27.46, 2.77, 12238941, 'uploads/populate_coin_types/bulgaria-50-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1999.jpg', @admin_id, 1);

INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, weight, diameter, thickness, mintage, description, created_by_user_id, is_approved)
SELECT 
    c.id,
    CONCAT(c.name, ' Coin #', numbers.n), 
    ELT(FLOOR(1 + (RAND() * 6)), '1 Cent', '5 Cents', '10 Cents', '25 Cents', '1 Dollar', '5 Dollars'),
    FLOOR(1950 + (RAND() * 70)), 
    'Republic Era',
    ELT(FLOOR(1 + (RAND() * 6)), 'Copper', 'Nickel', 'Steel', 'Silver', 'Gold', 'Bronze'),
    ROUND(2 + (RAND() * 28), 2),  -- Weight
    ROUND(15 + (RAND() * 25), 2), -- Diameter
    ROUND(1 + (RAND() * 2), 2),   -- Thickness
    FLOOR(10000 + (RAND() * 10000000)), -- Mintage
    'Auto-generated coin for demo.',
    @admin_id,
    1
FROM countries c
CROSS JOIN (
    SELECT 1 AS n UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5 
    UNION SELECT 6 UNION SELECT 7 UNION SELECT 8 UNION SELECT 9 UNION SELECT 10 
    UNION SELECT 11 UNION SELECT 12 UNION SELECT 13 UNION SELECT 14 UNION SELECT 15 
) AS numbers
WHERE c.name != 'Bulgaria'
  AND numbers.n <= FLOOR(5 + (RAND(c.id * 333) * 11)) 
ORDER BY c.name, numbers.n;
