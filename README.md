# Installment Management System

A polished PHP installment management system with login, customer/product management, installment and payment tracking, search, and reporting.

## Key Features
- Secure login using username or email
- Dashboard with quick stat cards
- Customer CRUD module with search
- Product CRUD module with search
- Installment creation and management
- Payment entry with remaining balance validation
- Searchable payment history
- Reports for customers, payments, and due installments
- Auto-create MySQL database and schema on first run
- Responsive, modern UI with consistent navigation

## Demo Login
- Username: `admin`
- Email: `admin@example.com`
- Password: `admin123`

## How to Run
1. Install XAMPP and start Apache/MySQL.
2. Place this project folder inside `C:\xampp\htdocs\`.
3. Open `http://localhost/Installment%20Management%20System/` in your browser.
4. The first visit will auto-create the database and tables.
5. Login with the demo credentials.

## Search
- Use the search fields on the Customers, Products, Installments, and Payments pages.
- Clear search results with the `Clear` button.

## Database
The application uses:
- Host: `127.0.0.1`
- Database: `installment_db`
- User: `root`
- Password: empty

## Notes
- Payments automatically update the installment remaining amount.
- The admin user is created automatically if it does not already exist.
