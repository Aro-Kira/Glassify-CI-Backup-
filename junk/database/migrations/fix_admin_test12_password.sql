-- Fix password hash for admin.test12@gmail.com
-- Password: admin123

UPDATE `user` 
SET `Password` = '$2y$10$nNEsZB40EDE8CxVfodTMpeU81vJv7f64eKE8OvVOr8sqj.HzTDVzC'
WHERE `Email` = 'admin.test12@gmail.com';
