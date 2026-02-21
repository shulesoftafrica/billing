-- SQL Script to Verify and Fix Database Structure
-- Database: billing (or shulesoft2024 with billing schema)
-- PostgreSQL

-- ============================================
-- SECTION 1: Database and Schema Verification
-- ============================================

-- List all databases
SELECT datname FROM pg_database WHERE datname LIKE '%billing%' OR datname LIKE '%shulesof%';

-- List all schemas in current database
SELECT schema_name FROM information_schema.schemata;

-- Check if 'billing' schema exists
SELECT EXISTS (
    SELECT 1 FROM information_schema.schemata WHERE schema_name = 'billing'
) AS billing_schema_exists;

-- Check if 'constant' schema exists
SELECT EXISTS (
    SELECT 1 FROM information_schema.schemata WHERE schema_name = 'constant'
) AS constant_schema_exists;

-- ============================================
-- SECTION 2: Table Verification
-- ============================================

-- List all tables in billing schema (if using schema approach)
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'billing' 
ORDER BY tablename;

-- List all tables in public schema (if using database approach)
SELECT tablename 
FROM pg_tables 
WHERE schemaname = 'public' 
ORDER BY tablename;

-- Check specific tables exist
SELECT 
    EXISTS (SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'billing' AND table_name = 'organizations') AS organizations_exists,
    EXISTS (SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'billing' AND table_name = 'cache') AS cache_exists,
    EXISTS (SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'billing' AND table_name = 'bank_accounts') AS bank_accounts_exists,
    EXISTS (SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'billing' AND table_name = 'users') AS users_exists,
    EXISTS (SELECT 1 FROM information_schema.tables 
            WHERE table_schema = 'billing' AND table_name = 'migrations') AS migrations_exists;

-- ============================================
-- SECTION 3: Create Missing Schema (if needed)
-- ============================================

-- Create billing schema if it doesn't exist
CREATE SCHEMA IF NOT EXISTS billing;

-- Set search path to include billing schema
SET search_path TO billing, public;

-- Grant permissions to postgres user
GRANT ALL PRIVILEGES ON SCHEMA billing TO postgres;
GRANT ALL PRIVILEGES ON ALL TABLES IN SCHEMA billing TO postgres;
GRANT ALL PRIVILEGES ON ALL SEQUENCES IN SCHEMA billing TO postgres;

-- ============================================
-- SECTION 4: Create Cache Table (if missing)
-- ============================================

-- Check if cache table exists
DO $$
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.tables 
        WHERE table_schema = 'billing' AND table_name = 'cache'
    ) THEN
        CREATE TABLE billing.cache (
            key VARCHAR(255) PRIMARY KEY,
            value TEXT NOT NULL,
            expiration INTEGER NOT NULL
        );
        
        CREATE INDEX cache_expiration_index ON billing.cache (expiration);
        
        RAISE NOTICE 'Cache table created successfully';
    ELSE
        RAISE NOTICE 'Cache table already exists';
    END IF;
END $$;

-- ============================================
-- SECTION 5: Verify Organizations Table
-- ============================================

-- Check organizations table structure
SELECT 
    column_name,
    data_type,
    is_nullable,
    column_default
FROM information_schema.columns
WHERE table_schema = 'billing' 
  AND table_name = 'organizations'
ORDER BY ordinal_position;

-- Count records in organizations table
-- SELECT COUNT(*) AS organization_count FROM billing.organizations;

-- ============================================
-- SECTION 6: Check Foreign Key Constraints
-- ============================================

-- List all foreign keys referencing "constant" schema
SELECT 
    tc.table_schema,
    tc.table_name,
    kcu.column_name,
    ccu.table_schema AS foreign_table_schema,
    ccu.table_name AS foreign_table_name,
    ccu.column_name AS foreign_column_name,
    tc.constraint_name
FROM information_schema.table_constraints AS tc
JOIN information_schema.key_column_usage AS kcu
    ON tc.constraint_name = kcu.constraint_name
    AND tc.table_schema = kcu.table_schema
JOIN information_schema.constraint_column_usage AS ccu
    ON ccu.constraint_name = tc.constraint_name
    AND ccu.table_schema = tc.table_schema
WHERE tc.constraint_type = 'FOREIGN KEY'
  AND ccu.table_schema = 'constant';

-- ============================================
-- SECTION 7: Fix Bank Accounts Table Constraint (if exists)
-- ============================================

-- Drop problematic foreign key constraint if it exists
DO $$
BEGIN
    IF EXISTS (
        SELECT 1 FROM information_schema.table_constraints 
        WHERE constraint_name = 'bank_accounts_refer_bank_id_foreign'
          AND table_schema = 'billing'
          AND table_name = 'bank_accounts'
    ) THEN
        ALTER TABLE billing.bank_accounts 
        DROP CONSTRAINT bank_accounts_refer_bank_id_foreign;
        RAISE NOTICE 'Dropped foreign key constraint: bank_accounts_refer_bank_id_foreign';
    ELSE
        RAISE NOTICE 'Constraint bank_accounts_refer_bank_id_foreign does not exist';
    END IF;
END $$;

-- ============================================
-- SECTION 8: Database Statistics
-- ============================================

-- Show table sizes
SELECT 
    schemaname,
    tablename,
    pg_size_pretty(pg_total_relation_size(schemaname||'.'||tablename)) AS size
FROM pg_tables
WHERE schemaname IN ('billing', 'public')
ORDER BY pg_total_relation_size(schemaname||'.'||tablename) DESC;

-- Show row counts for main tables
SELECT 
    'organizations' AS table_name,
    (SELECT COUNT(*) FROM billing.organizations) AS row_count
UNION ALL
SELECT 
    'users' AS table_name,
    (SELECT COUNT(*) FROM billing.users) AS row_count
UNION ALL
SELECT 
    'customers' AS table_name,
    (SELECT COUNT(*) FROM billing.customers) AS row_count
UNION ALL
SELECT 
    'cache' AS table_name,
    (SELECT COUNT(*) FROM billing.cache) AS row_count;

-- ============================================
-- SECTION 9: Connection Information
-- ============================================

-- Show current database and user
SELECT 
    current_database() AS current_db,
    current_schema() AS current_schema,
    current_user AS current_user,
    version() AS pg_version;

-- Show active connections
SELECT 
    datname,
    usename,
    application_name,
    client_addr,
    state,
    query_start
FROM pg_stat_activity
WHERE datname = current_database()
ORDER BY query_start DESC;

-- ============================================
-- NOTES:
-- ============================================
-- Run this script as postgres superuser:
-- sudo -u postgres psql -d billing -f verify_database.sql
--
-- Or if using different database:
-- sudo -u postgres psql -d shulesoft2024 -f verify_database.sql
