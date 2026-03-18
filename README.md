# VoteApp - Smart Online Voting System

A secure online voting system built with PHP, MySQL and Tailwind CSS.

## Features
- User registration and login
- Email verification
- Create and manage elections
- One vote per user enforcement
- Live results with Chart.js
- Admin dashboard
- Mobile responsive design

## Tech Stack
- PHP 8
- MySQL
- Tailwind CSS
- Chart.js
- XAMPP

## Setup Instructions
1. Create MySQL database called voting_system
2. Import sql/schema.sql
3. Edit config/db.php with your credentials
4. Open http://localhost/voting_system

## Admin Account
- Email: admin@voteapp.com
- Password: Admin@123

## Security
- Passwords hashed with bcrypt
- PDO prepared statements
- Session based authentication
- Role based access control