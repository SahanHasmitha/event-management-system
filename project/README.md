# **UoM Student Event Hub (IT1208 \- Web Technologies CA-2)**

## **1\. Overview**

This project implements a **Dynamic Web Application for Student Event Management** using a traditional full-stack approach: **HTML, CSS, JavaScript (Client-side), and PHP with MySQL (Server-side)**.

The system allows students to view available events, filter and search the listings, and register securely. An administrative panel is provided for authorized users to perform CRUD (Create, Read, Update, Delete) operations on events and monitor registrations.

## **2\. System Architecture and Technologies**

| Layer | Technologies Used | Description |
| :---- | :---- | :---- |
| **Frontend** | HTML5, CSS3, JavaScript | Provides a responsive layout, aesthetic design, and client-side form validation. |
| **Backend** | PHP 7.4+ | Handles all server-side logic, user authentication, session management, and database queries. |
| **Database** | MySQL (via PDO) | Relational database for persistent storage of users, events, and registrations. |
| **Security** | PDO Prepared Statements | Used exclusively for all database interactions to prevent SQL injection attacks. |
| **Styling** | Custom CSS | Ensures a clean, modern, and responsive interface across all devices. |

## **3\. Setup and Installation Guide**

To run this application locally, you must have a PHP-compatible web server environment (e.g., XAMPP, MAMP, Laragon) installed.

### **A. File Setup**

1. **Place Files:** Copy the entire contents of the /project directory into your local web server's root folder (e.g., htdocs for XAMPP).  
2. **File Structure:** Ensure the following directory structure is maintained:  
   /project  
   |-- index.php  
   |-- login.php  
   |-- ... (other root files)  
   |-- config/  
   |   |-- db\_connect.php  
   |-- assets/  
   |   |-- style.css  
   |   |-- script.js  
   |-- admin/  
   |   |-- dashboard.php  
   |   |-- ... (other admin files)

### **B. Database Setup**

1. **Open MySQL:** Access your MySQL management tool (e.g., phpMyAdmin).  
2. **Create Database:** Create a new database named **event\_management\_db**.  
3. **Import Schema:** Execute the queries from the provided **db\_structure.sql** file to create the users, events, and registrations tables.

### **C. Configuration**

1. **Edit config/db\_connect.php:** Verify and update the database credentials if necessary. The default settings are:  
   define('DB\_HOST', 'localhost');  
   define('DB\_USER', 'root');  
   define('DB\_PASS', ''); // Set to your MySQL password if applicable  
   define('DB\_NAME', 'event\_management\_db');

## **4\. Key Features Implemented**

### **Core Requirements**

* **Responsive UI/UX:** All pages use a responsive design, adapting well to mobile and desktop screens (assets/style.css).  
* **User Authentication:** Secure login (login.php) and sign-up (register\_user.php) with **password hashing** (password\_hash/password\_verify).  
* **Session Management:** Utilized to track logged-in status and user roles (config/db\_connect.php).  
* **Event Listing:** Displays all available events on the home page (index.php).  
* **Event Registration:** Allows non-logged-in students to register, automatically creating a user account if one doesn't exist (registration\_form.php).  
* **Client-side Validation:** Implemented using JavaScript for mandatory fields, email format, etc. (assets/script.js).  
* **Admin CRUD Operations:** Full capability to **Create, Read, Update, and Delete** events via the Admin Dashboard (admin/dashboard.php, admin/event\_form.php, admin/delete\_event.php).  
* **Secure Database Interaction:** All queries use **PDO prepared statements** exclusively.

### **Additional Features (Enhancements)**

* **Search and Filter:** Events can be filtered by date/title and searched by keyword across title, venue, organizer, and description (index.php).  
* **Simple Analytics Dashboard:** Displays key statistics like total users, total events, and total registrations in the admin area (admin/dashboard.php).

## **5\. Test Credentials**

Use these credentials to test the application's Admin and Student functionalities:

| User Type | Student ID / Email | Password |
| :---- | :---- | :---- |
| **Administrator** | admin@uom.lk | admin123 |
| **Student** | IT200100 | admin123 |

