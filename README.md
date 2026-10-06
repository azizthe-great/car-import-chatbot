# Customer Care Chatbot for Car Importation

This project is a simple customer support chatbot for a car import business. It uses:

- JavaScript for frontend interactions
- PHP for backend logic
- MySQL for storing FAQ and chat logs
- XAMPP for local server environment
- Browser for testing
- Visual Studio Code for development

## Project structure

- `index.html` – browser chat interface
- `assets/styles.css` – page styling
- `assets/app.js` – frontend chat logic
- `api/chat.php` – backend chatbot API
- `config/db.php` – database connection settings
- `database/schema.sql` – MySQL database schema and sample FAQs

## Local setup with XAMPP

1. Install XAMPP and start Apache and MySQL.
2. Copy this project folder into `C:/xampp/htdocs/car-import-chatbot`.
3. Open phpMyAdmin at `http://localhost/phpmyadmin`.
4. Create a database called `car_import_chatbot`.
5. Import the SQL file from `database/schema.sql`.
6. Open `http://localhost/car-import-chatbot/` in your browser.

## Default database settings

- Host: `127.0.0.1`
- Database: `car_import_chatbot`
- User: `root`
- Password: empty

## Features

- Chat UI for customers
- Keyword-based FAQ responses
- MySQL storage for FAQs and inquiries
- Fallback replies if the database is unavailable

## Notes

This is a starter project designed for learning and prototype use. You can extend it later with:

- real customer authentication
- WhatsApp integration
- email notifications
- admin dashboard
- AI-powered responses
