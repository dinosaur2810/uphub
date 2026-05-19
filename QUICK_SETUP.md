# Quick Database Setup Guide

## Problem
Database 'uphub' doesn't exist, so SQL uploads fail.

## Solution (5 minutes)

### 1. Create Database
1. Open: http://localhost/phpmyadmin
2. Click "New" (left sidebar)
3. Name: `uphub`
4. Click "Create"

### 2. Import Tables
1. Click `uphub` database (left sidebar)
2. Click "Import" tab
3. Choose file: `sql/install.sql`
4. Character set: `utf8mb4`
5. Click "Go"

### 3. Test Connection
Visit: http://localhost/UpHub/verify_setup.php

Should show:
✓ Database connected
✓ users table exists
✓ jobs table exists
✓ financial_aid_programs table exists
✓ social services table exists
✓ jobs has exact_address

### 4. Test Application
Visit: http://localhost/UpHub/jobs.php
Should show job listings without errors.

## Common Issues
- If import fails: Check file permissions
- If connection fails: Restart XAMPP MySQL service
- If tables missing: Run migration again

Once complete, the exact address feature will work fully!
