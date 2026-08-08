SCHOOL DATA MANAGEMENT SYSTEM
Native PHP + MySQL + Bootstrap

REQUIREMENTS
- XAMPP (Apache + MySQL)
- PHP 8.1+ recommended
- A browser

INSTALLATION
1. Copy the entire "School_Data_Management_System" folder into:
   C:\xampp\htdocs\

2. Start Apache and MySQL from XAMPP.

3. Open phpMyAdmin:
   http://localhost/phpmyadmin

4. Import:
   database.sql

5. If your MySQL username/password is different from XAMPP defaults,
   edit config.php.

6. Open:
   http://localhost/School_Data_Management_System/login.php

DEFAULT LOGIN
Username: admin
Password: admin123

FUNCTIONS INCLUDED
- Login/logout
- Dashboard counts
- Student CRUD
- Teacher CRUD
- Staff CRUD
- Search records
- Profile photo upload
- Document upload
- Document view/download
- Authorized document deletion
- MySQL database

NOTES
- This is intentionally simple and follows the supplied system plan.
- Documents are limited to PDF, DOC, DOCX, JPG, JPEG, PNG, WEBP and 10 MB.
- Profile photos are limited to JPG, JPEG, PNG, WEBP and 5 MB.
- For production deployment, add stronger authorization, CSRF protection,
  audit logs, server-side MIME validation, and protected upload directories.
