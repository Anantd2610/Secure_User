# SecureUser – Secure Web User Management Application

## Project Title and Overview

**SecureUser** is a PHP/MySQL web application that manages user accounts with basic CRUD operations (Create, Read, Update, Delete) and a login system.  
The project’s main security focus is to demonstrate how an intentionally vulnerable web application can be hardened against common web vulnerabilities such as SQL injection, weak password storage, broken access control, insecure direct object references (IDOR), and cross-site scripting (XSS).

---

## Features and Security Objectives

### Functional Features

- User login and logout
- View user accounts
- Create new user accounts (admin only)
- Edit user details
- Delete user accounts (admin only)
- Simple role model: **normal user** and **admin**

### Security Objectives

- Prevent SQL injection in login and CRUD operations using prepared statements.
- Store passwords securely using bcrypt hashing instead of plaintext.
- Enforce authentication and secure session handling with PHP sessions.
- Enforce authorization rules and prevent IDOR (owner-or-admin rule for editing).
- Prevent information disclosure by never displaying passwords in the UI.
- Reduce XSS risk by encoding all user-controlled output.

---

## Project Structure

SecureUser/
├── db.php # Database connection (MySQLi)
├── index.php # Users list (auth required, RBAC, safe output)
├── login.php # Secure login (prepared statements + password hashing)
├── logout.php # Logout and session destroy
├── user_create.php # Admin-only user creation (hashing for passwords)
├── user_edit.php # Owner-or-admin edit, optional password change
├── user_delete.php # Admin-only delete, prepared statement
└── vuln_crud.sql # Database schema and seed data (3 initial users)


**Key files:**

- `db.php` – Centralised database connection settings.
- `login.php` – Handles authentication, password verification, and migration from plaintext to hashed passwords.
- `index.php` – Displays user list based on the logged-in user’s role (admin vs normal user).
- `user_create.php` – Allows admins to add new users and stores passwords as bcrypt hashes.
- `user_edit.php` – Allows only the account owner or admin to modify user details.
- `user_delete.php` – Allows admins to delete user accounts safely.
- `vuln_crud.sql` – Creates the `vuln_crud` database and `users` table, with three example users.

---

## Setup and Installation Instructions

### Prerequisites

- XAMPP or similar stack with:
  - Apache 2.4+
  - PHP 8.0+
  - MySQL / MariaDB
- phpMyAdmin
- Web browser (Chrome, Firefox, etc.)

### Steps

1. **Copy the project into the web root**

   Place the `Secure_User` folder into your web server’s document root, e.g.:

   C:\xampp\htdocs\Secure_User\


2. **Start Apache and MySQL**

- Open the XAMPP Control Panel.
- Start **Apache**.
- Start **MySQL**.

3. **Create the database and table**

- Go to `http://localhost/phpmyadmin/`.
- Click **Import**.
- Choose `vuln_crud.sql` from the project folder.
- Click **Go**.

This creates the database `vuln_crud` with table `users` and three users:
- `alice / alice123` (normal user)
- `bob / bob123` (normal user)
- `admin / admin123` (admin)

4. **Configure database connection (optional)**

If your MySQL username/password differ from the defaults, edit `db.php`:

$host = 'localhost';
$user = 'root'; // change if needed
$pass = ''; // change if needed
$db = 'vuln_crud';


5. **Access the application**

In your browser, go to:

http://localhost/Secure_User/


You will be redirected to `login.php` the first time.

---

## Usage Guidelines

1. **Login**

- Visit `http://localhost/Secure_User/` → redirected to the login page.
- Use credentials such as:
  - `admin / admin123` for admin operations.
  - `alice / alice123` or `bob / bob123` for normal user behaviour.

2. **Navigating the application**

- After logging in, you see the **Users list** (`index.php`):
  - Admin:
    - Sees all users.
    - Can create, edit and delete users.
  - Normal user:
    - Sees only their own account.
    - Can edit their own profile.

3. **Creating a user**

- As admin, click **“Create new user”**.
- Enter username, password, and optionally tick **“Is admin”**.
- The password is stored as a bcrypt hash in the database.

4. **Editing a user**

- Click **“Edit”** beside a user row.
- Normal users can edit only their own details.
- Admin can edit any user.
- Optionally provide a new password to update the hash.

5. **Deleting a user**

- As admin, click **“Delete”** next to a user (not yourself for demo).
- Confirm the deletion in the browser dialog.
- Normal users and anonymous users cannot delete accounts.

---

## Security Improvements

The application originally contained multiple vulnerabilities. The secure version implements these improvements:

### SQL Injection Prevention

- All database queries use **MySQLi prepared statements** with parameter binding (`mysqli_prepare`, `mysqli_stmt_bind_param`).
- Queries no longer concatenate raw user input into SQL strings.
- SQL injection payloads such as `' OR '1'='1` no longer bypass authentication.

### Secure Password Storage

- Added a `password_hash` column to the `users` table.
- New accounts and password changes use `password_hash()` with `PASSWORD_DEFAULT` (bcrypt).
- Logins use `password_verify()` to validate credentials.
- Legacy plaintext passwords in `password_plain` are migrated to hashes automatically on first successful login.

### Authentication and Session Management

- Application uses PHP sessions to track authenticated users:
- `$_SESSION['user_id']`
- `$_SESSION['username']`
- `$_SESSION['is_admin']`
- Every protected page checks for a valid session before granting access.

### Authorization and IDOR Prevention

- List page (`index.php`) shows:
- All users for admin.
- Only own record for normal users.
- Edit page (`user_edit.php`) enforces **owner-or-admin** rule:
- Normal users can edit only their own ID.
- Admins can edit any user.
- Delete page (`user_delete.php`) is restricted to admin only.

### Output Encoding and Information Disclosure

- All user-controlled output is encoded with `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`.
- The UI never displays plaintext passwords or password hashes.
- XSS attempts are rendered as harmless text.

---

## Testing Process

The secure version was tested using a combination of functional and security-focused tests:

### Functional Testing

- Verified login and logout for all three sample users.
- Verified CRUD operations:
- Admin can create, edit, and delete users.
- Normal users can view and edit only their own accounts.
- Checked session behaviour on logout and page refresh.

### Security Testing

- **SQL Injection**:
- Attempted payload `' OR '1'='1` on the login form.
- Result: login correctly fails; no bypass.
- **Broken Access Control**:
- Tried accessing `user_delete.php?id=...` without login and as a normal user.
- Result: HTTP 403 Forbidden; account not deleted.
- **IDOR**:
- Logged in as a normal user and requested `user_edit.php?id=<other_user_id>`.
- Result: HTTP 403 Forbidden.
- **XSS**:
- Created user with malicious script in username.
- Result: script printed as text; no alert box.
- **Password Storage**:
- Confirmed that `password_hash` column contains bcrypt hashes and plaintext is not used for authentication after migration.

A more detailed test case table is provided in the accompanying report.

---

## Contributions and References

### Contributions

- Design and implementation of the SecureUser application.
- Creation of a vulnerable baseline version and identification of:
- SQL injection points
- Plaintext password storage
- Broken access control and IDOR conditions
- Unsafe output leading to potential XSS
- Implementation of:
- MySQLi prepared statements
- bcrypt password hashing and migration
- Session-based authentication and role-based authorization
- Owner-or-admin rule for editing
- Output encoding and removal of password disclosure
- Development of test cases and validation of all security controls.

### References

- OWASP Top Ten Web Application Security Risks: <https://owasp.org/www-project-top-ten/>
- PHP Manual – MySQLi prepared statements: <https://www.php.net/manual/en/mysqli.quickstart.prepared-statements.php>
- PHP Manual – `password_hash`: <https://www.php.net/manual/en/function.password-hash.php>
- PHP Manual – `password_verify`: <https://www.php.net/manual/en/function.password-verify.php>
- XAMPP Documentation: <https://www.apachefriends.org/>
