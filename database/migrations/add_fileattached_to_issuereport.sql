-- Add FileAttached column to issuereport table for issue report attachments
ALTER TABLE `issuereport` 
ADD COLUMN `FileAttached` varchar(255) DEFAULT NULL AFTER `Description`;
