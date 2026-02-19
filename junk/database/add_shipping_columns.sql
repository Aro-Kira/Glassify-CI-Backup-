-- Add shipping columns to payment table
ALTER TABLE payment 
ADD COLUMN shipping_name VARCHAR(255) NULL AFTER billing_country,
ADD COLUMN shipping_email VARCHAR(255) NULL AFTER shipping_name,
ADD COLUMN shipping_phone VARCHAR(20) NULL AFTER shipping_email,
ADD COLUMN shipping_unit VARCHAR(100) NULL AFTER shipping_phone,
ADD COLUMN shipping_street VARCHAR(255) NULL AFTER shipping_unit,
ADD COLUMN shipping_subdivision VARCHAR(255) NULL AFTER shipping_street,
ADD COLUMN shipping_barangay VARCHAR(255) NULL AFTER shipping_subdivision,
ADD COLUMN shipping_city VARCHAR(100) NULL AFTER shipping_barangay,
ADD COLUMN shipping_province VARCHAR(100) NULL AFTER shipping_city,
ADD COLUMN shipping_region VARCHAR(100) NULL AFTER shipping_province,
ADD COLUMN shipping_postal_code VARCHAR(20) NULL AFTER shipping_region,
ADD COLUMN shipping_country VARCHAR(100) NULL AFTER shipping_postal_code;
