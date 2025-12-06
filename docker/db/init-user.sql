INSERT INTO usuario (username, email, password_hash, roles)
VALUES (
    'admin', 
    'admin@booksmart.com', 
    '$2y$13$emJvbwh54t1WK3Oq3diSfuyPnnTQA.qGN3ilvaL9v4oXQn4GOijFe', 
    '["ROLE_ADMIN"]'
) 
ON CONFLICT (username) DO NOTHING; -- Evita errores si el script se ejecuta más de una vez