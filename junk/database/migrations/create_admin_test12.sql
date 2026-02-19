-- Create admin account: admin.test12@gmail.com
-- Password: admin123

INSERT INTO `user` (
    `First_Name`, 
    `Last_Name`, 
    `Middle_Name`, 
    `Email`, 
    `Password`, 
    `PhoneNum`, 
    `Role`, 
    `Status`
) VALUES (
    'Admin',
    'Test12',
    NULL,
    'admin.test12@gmail.com',
    '$2y$10$oCvYAi1i9jlMOU.27bRjwe.HmQ6vnBvP07l/86LfQqCGtHEyMGmQS',
    '09999999999',
    'Admin',
    'Active'
);
