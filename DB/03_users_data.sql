-- password is 'password123' for all
INSERT INTO users (id, username, email, password, is_verified, role, bio, location, profile_image) VALUES
(1, 'Admin', 'admin@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'admin', 'System Administrator', 'Server Room', NULL),
(2, 'Test1', 'ivan@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', 'Enthusiastic collector of European silver coins. Always looking for trades!', 'Sofia, Bulgaria', NULL),
(3, 'Test2', 'maria@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', 'Just starting my collection. I love coins with animals on them.', 'Plovdiv, Bulgaria', NULL),
(4, 'Test3', 'gosho@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 0, 'user', NULL, 'Burgas', NULL),
(5, 'Test4', 'todor4@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', NULL, 'Varna', NULL),
(6, 'Test5', 'todor@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', NULL, 'Love4', NULL),
(7, 'Test6', 'todor1@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', NULL, 'Sliven', NULL),
(8, 'Test7', 'todor2@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', NULL, NULL, NULL),
(9, 'Test8', 'todor3@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', NULL, NULL, NULL),
(10, 'Test10', 'bg_fan@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', 'Dedicated collector of Bulgarian history. Contact me for swaps!', 'Stara Zagora', NULL),
(11, 'Test11', 'bg_fan2@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user', 'Varnete balgarskiq lev', 'Gorno Nanadolnishte', NULL)

ON DUPLICATE KEY UPDATE username=username;