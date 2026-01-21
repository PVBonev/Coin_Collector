INSERT INTO users (id, username, email, password, is_verified, role) VALUES
(1, 'Admin', 'admin@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'admin'),-- password is 'password123' for all
(2, 'Test1', 'ivan@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(3, 'Test2', 'maria@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(4,'Test3', 'gosho@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 0, 'user'),
(5, 'Test4', 'todor4@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(6, 'Test5', 'todor@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(7, 'Test6', 'todor1@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(8, 'Test7', 'todor2@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(9, 'Test8', 'todor3@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user'),
(10, 'Test10', 'bg_fan@coinapp.com', '$2y$10$ZWsgb9YIvjGHmU0..Qd4oeB75SFVclx8eInDJe9SLBbHjVRawk1aC', 1, 'user')

ON DUPLICATE KEY UPDATE username=username;