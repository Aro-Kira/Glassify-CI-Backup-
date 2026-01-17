-- ============================================================================
-- ADD TAG IMAGE COLUMN TO product_tag_prices TABLE
-- ============================================================================
-- This migration adds an optional ImageUrl column to store tag images
-- Created: 2026-01-15
-- ============================================================================

USE `glassify_db`;

-- Add ImageUrl column to product_tag_prices table
ALTER TABLE `product_tag_prices` 
ADD COLUMN `ImageUrl` varchar(255) DEFAULT NULL COMMENT 'Optional image URL for the tag' 
AFTER `Price`;

-- Add index for better query performance
ALTER TABLE `product_tag_prices` 
ADD INDEX `idx_image_url` (`ImageUrl`);
