-- Auto-generated catalog coins data
-- Generated on: 2026-01-21T15:57:35.048Z

SET @bg_id = (SELECT id FROM countries WHERE name = 'Bulgaria' LIMIT 1);
SET @admin_id = (SELECT id FROM users WHERE username = 'TestUser' LIMIT 1);

INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, catalog_image_front, catalog_image_back, created_by_user_id, is_approved) VALUES 
(@bg_id, '1 Euro (2026)', '1 Euro', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-euro-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-euro-2026.jpg', @admin_id, 1),
(@bg_id, '1 Euro Cent (2026)', '1 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '1 Lev (1992)', '1 Lev', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-lev-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-lev-1992.jpg', @admin_id, 1),
(@bg_id, '1 Lev (2002)', '1 Lev', 2002, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-lev-2002 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-lev-2002.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1951)', '1 Stotinka', 1951, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-stotinka-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1951.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1962)', '1 Stotinka', 1962, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-stotinka-1962 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1962.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1990)', '1 Stotinka', 1990, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-stotinka-1990 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1990.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (1999)', '1 Stotinka', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-stotinka-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-1999.jpg', @admin_id, 1),
(@bg_id, '1 Stotinka (2000)', '1 Stotinka', 2000, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-1-stotinka-2000 (1).jpg', 'uploads/populate_coin_types/bulgaria-1-stotinka-2000.jpg', @admin_id, 1),
(@bg_id, '10 Euro Cent (2026)', '10 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-10-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '10 Leva (1992)', '10 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-10-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-leva-1992.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1951)', '10 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-10-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1992)', '10 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-10-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '10 Stotinki (1999)', '10 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-10-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-10-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '2 Euro (2026)', '2 Euro', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-euro-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-euro-2026.jpg', @admin_id, 1),
(@bg_id, '2 Euro Cent (2026)', '2 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '2 Leva (1992)', '2 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-leva-1992.jpg', @admin_id, 1),
(@bg_id, '2 Leva (2015)', '2 Leva', 2015, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-leva-2015 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-leva-2015.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1962)', '2 Stotinki', 1962, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-stotinki-1962 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1962.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1989)', '2 Stotinki', 1989, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-stotinki-1989 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1989.jpg', @admin_id, 1),
(@bg_id, '2 Stotinki (1999)', '2 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-2-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-2-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '20 Euro Cent (2026)', '20 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-20-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '20 Leva (1997)', '20 Leva', 1997, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-20-leva-1997 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-leva-1997.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1954)', '20 Stotinki', 1954, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-20-stotinki-1954 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1954.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1992)', '20 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-20-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '20 Stotinki (1999)', '20 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-20-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-20-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '3 Stotinki (1951)', '3 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-3-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-3-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '5 Euro Cent (2026)', '5 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-5-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '5 Leva (1992)', '5 Leva', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-5-leva-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-leva-1992.jpg', @admin_id, 1),
(@bg_id, '5 Stotinki (1951)', '5 Stotinki', 1951, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-5-stotinki-1951 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-stotinki-1951.jpg', @admin_id, 1),
(@bg_id, '5 Stotinki (1999)', '5 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-5-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-5-stotinki-1999.jpg', @admin_id, 1),
(@bg_id, '50 Euro Cent (2026)', '50 Euro Cent', 2026, 'European Union (Euro)', 'Unknown', 'uploads/populate_coin_types/bulgaria-50-euro-cent-2026 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-euro-cent-2026.jpg', @admin_id, 1),
(@bg_id, '50 Leva (1997)', '50 Leva', 1997, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-50-leva-1997 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-leva-1997.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1988)', '50 Stotinki', 1988, 'People''s Republic (1946 - 1991)', 'Unknown', 'uploads/populate_coin_types/bulgaria-50-stotinki-1988 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1988.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1992)', '50 Stotinki', 1992, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-50-stotinki-1992 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1992.jpg', @admin_id, 1),
(@bg_id, '50 Stotinki (1999)', '50 Stotinki', 1999, 'Republic of Bulgaria (1992 - 2025)', 'Unknown', 'uploads/populate_coin_types/bulgaria-50-stotinki-1999 (1).jpg', 'uploads/populate_coin_types/bulgaria-50-stotinki-1999.jpg', @admin_id, 1);

-- 2. REST OF THE WORLD (RANDOMIZER 5-15 COINS)
INSERT INTO catalog_coins (country_id, title, denomination, year, period, material, description, created_by_user_id, is_approved)
SELECT 
    c.id,
    CONCAT(c.name, ' Coin #', numbers.n), 
    ELT(FLOOR(1 + (RAND() * 6)), '1 Cent', '5 Cents', '10 Cents', '25 Cents', '1 Dollar', '5 Dollars'),
    FLOOR(1950 + (RAND() * 70)), 
    'Republic Era',
    ELT(FLOOR(1 + (RAND() * 4)), 'Copper', 'Nickel', 'Steel', 'Silver'),
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
  AND numbers.n <= FLOOR(5 + (RAND(c.id * 333) * 11)) -- Generates between 5 and 15 coins
ORDER BY c.name, numbers.n;
