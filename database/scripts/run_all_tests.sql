-- =====================================================
-- Complete Test Setup Script
-- Run this after add_customization_fields_tables.sql
-- =====================================================

-- First, ensure all tables and columns exist
-- (Run add_customization_fields_tables.sql first)

-- Then insert test product
SOURCE database/scripts/insert_test_product.sql;

-- Or if SOURCE doesn't work, copy the content from insert_test_product.sql here
