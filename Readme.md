# miniFacebook Web Application

miniFacebook is a secure web application that allows users to register, login, create posts, add comments, manage their account, and more. It also includes administrative functionalities for superusers.

## Table of Contents

- [miniFacebook Web Application](#minifacebook-web-application)
  - [Table of Contents](#table-of-contents)
  - [Overview](#overview)
  - [Features](#features)
    - [User Functions (Registered Users)](#user-functions-registered-users)
    - [Superuser Functions](#superuser-functions)
  - [Technologies Used](#technologies-used)
  - [Setup Instructions](#setup-instructions)
    - [1. Database Setup](#1-database-setup)
    - [2. Application Setup](#2-application-setup)
    - [3. Deployment](#3-deployment)
    - [4. Hosting Migration](#4-hosting-migration)
  - [Security Measures](#security-measures)
  - [Usage](#usage)

---

## Overview

This README provides instructions for setting up, deploying, and migrating the miniFacebook web application. The application allows users to perform various tasks like registration, login, creating posts, adding comments, and managing accounts. Superusers have additional administrative functionalities.

## Features

### User Functions (Registered Users)

- **Registration**: Users can register for an account with a unique username and password.
- **Login**: Registered users can securely log in using their credentials.
- **Change Password**: Users can change their password after verifying their current password.
- **Delete Account**: Users can delete their account, which also removes associated posts and comments.
- **Post Management**: Users can create, edit, and delete their posts.
- **Commenting**: Users can add comments to any post on miniFacebook.

### Superuser Functions

- **Superuser Login**: Superusers can directly log in without registration.
- **View Registered Users**: Superusers can view a list of all registered users.
- **Manage Users**: Superusers can disable or enable user accounts.

## Technologies Used

- **Frontend**: HTML, Bootstrap
- **Backend**: PHP
- **Database**: MySQL
- **Security**: Password hashing, Prepared Statements, Input validation, Session management, CSRF protection

## Setup Instructions

Follow these steps to set up, deploy, and migrate the miniFacebook web application.

### 1. Database Setup

1. **Create Database**: Execute the following SQL script to create the required database and tables:
   ```sql
   CREATE DATABASE IF NOT EXISTS mini_facebook_db;
   USE mini_facebook_db;

   -- Create 'users' table
   CREATE TABLE users (
       id INT AUTO_INCREMENT PRIMARY KEY,
       username VARCHAR(255) NOT NULL UNIQUE,
       password_hash VARCHAR(255) NOT NULL,
       is_disabled TINYINT(1) DEFAULT 0,
       role ENUM('regular', 'superuser') DEFAULT 'regular'
   );

   -- Create 'posts' table
   CREATE TABLE posts (
       id INT AUTO_INCREMENT PRIMARY KEY,
       user_id INT NOT NULL,
       content TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );

   -- Create 'comments' table
   CREATE TABLE comments (
       id INT AUTO_INCREMENT PRIMARY KEY,
       post_id INT NOT NULL,
       user_id INT NOT NULL,
       content TEXT NOT NULL,
       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
       FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
       FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```

2. **Database Configuration**: Update the database connection details in `database.php` with your MySQL credentials.

### 2. Application Setup

1. **Upload Files**: Upload all PHP files to your web server directory.

2. **Bootstrap Library**: Ensure the Bootstrap CSS library (from CDN) is included in your HTML files for styling.

### 3. Deployment

1. **Secure Deployment**:
   - Deploy the application on a secure server that supports HTTPS.
   - Ensure the server environment has PHP and MySQL installed.

2. **Access Application**:
   - Navigate to `register.php` to create a new account.
   - Use `login.php` to log in with your registered username and password.
   - After logging in, users are redirected to `dashboard.php` for further actions based on their role.

### 4. Hosting Migration

1. **Transfer Files**: Transfer all application files to the new hosting environment.

2. **Database Migration**: Export the MySQL database and import it into the new hosting environment.

3. **Update Configuration**:
   - Update `database.php` with the new database credentials (if changed).
   - Ensure all file paths and server configurations are updated according to the new hosting environment.

## Security Measures

- Use HTTPS for secure communication.
- Hash passwords using `password_hash()` before storing them in the database.
- Avoid using MySQL root account in PHP code.
- Use Prepared Statements for all SQL queries to prevent SQL injection.
- Implement input validation and sanitization at all layers (HTML, PHP, SQL) to prevent attacks.
- Sanitize HTML outputs to prevent XSS attacks.
- Implement role-based access control (RBAC) to restrict functionalities based on user roles.
- Use session authentication and prevent session hijacking.
- Implement Cross-Site Request Forgery (CSRF) protection for form submissions.

## Usage

- **Regular User**:
  - After logging in, regular users can create posts, add comments, change password, delete account, and manage posts.
  
- **Superuser**:
  - Superusers have additional privileges to view registered users, manage user accounts, and access administrative functionalities.