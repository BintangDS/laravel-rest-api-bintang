# E-Commerce Simple REST API - Laravel 12

This is a simple E-Commerce REST API built using **Laravel 12** and **Laravel Sanctum** for authentication. This repository is part of a Backend Intern technical assignment.

## Features

- **Authentication System:** Secure API login and logout using Laravel Sanctum tokens.
- **Role-based Authorization:** Simple role structure (`admin` and `user`) saved in the database.
- **Product Module:**
  - Public endpoints to view active products.
  - Admin-only CRUD operations (Create, Update, Delete) secured via custom admin middleware.
- **Order Module:**
  - Public endpoint to submit orders (calculates subtotals and total price automatically, uses DB Transaction for atomicity, and restricts order to active products only).
  - Admin-only order listing and detail lookup endpoints.
- **Response Standardization:** Consistent API output formats for successful operations and validation errors.
- **Automated Tests:** Comprehensive feature testing for all core functionalities.

---

## Database Structure

### Users
- `id` (Primary Key)
- `name` (String)
- `email` (String, Unique)
- `password` (String)
- `role` (String: `admin` | `user`)
- `timestamps`

### Products
- `id` (Primary Key)
- `name` (String)
- `description` (Text, Nullable)
- `price` (Decimal, 15,2)
- `status` (Enum/String: `active` | `inactive`)
- `timestamps`

### Orders
- `id` (Primary Key)
- `customer_name` (String)
- `customer_email` (String)
- `status` (Enum/String: `pending` | `paid` | `cancelled`)
- `total_price` (Decimal, 15,2)
- `timestamps`

### OrderItems
- `id` (Primary Key)
- `order_id` (Foreign Key to `orders`)
- `product_id` (Foreign Key to `products`)
- `qty` (Integer)
- `price` (Decimal, 15,2 - snapshot of product price at the time of purchase)
- `subtotal` (Decimal, 15,2 - `qty * price`)
- `timestamps`

---

## Installation & Setup Guide

### 1. Prerequisites
Ensure you have the following installed on your machine:
- PHP >= 8.2 (Laravel 12 requires PHP 8.2+)
- Composer
- SQLite or MySQL Database

### 2. Clone the Repository
```bash
git clone https://github.com/BintangDS/laravel-rest-api-bintang.git
cd laravel-bintang
```

### 3. Install Dependencies
```bash
composer install
```

### 4. Configure Environment Variables
Copy `.env.example` to `.env` and set up your database connection:
```bash
cp .env.example .env
```
Ensure your database settings in `.env` are configured correctly (e.g. using `sqlite` or `mysql`).

### 5. Generate Application Key
```bash
php artisan key:generate
```

### 6. Run Migrations and Seeders
Run the database migrations and populate seed data (this creates an Admin account and a User account):
```bash
php artisan migrate:fresh --seed
```

### 7. Run the Application
Start the local development server:
```bash
php artisan serve
```
By default, the API will be available at `http://127.0.0.1:8000`.

---

## Testing

To run the full automated test suite (includes 16 assertions checking Authentication, Product management permissions, and Order transaction/calculation logic):
```bash
php artisan test
```

---

## API Documentation & Endpoints

All request and response payloads are in JSON format. For protected routes, pass the returned token in the `Authorization: Bearer <token>` header.

### Authentication Endpoints
- **POST `/api/login`** (Public) - Login and receive a Sanctum token.
  - Body:
    ```json
    {
      "email": "admin@mail.com",
      "password": "password123"
    }
    ```
- **POST `/api/logout`** (Protected) - Revoke/delete the active access token.

### Product Endpoints
- **GET `/api/products`** (Public) - Get list of all products.
- **GET `/api/products/{id}`** (Public) - Get details of a single product.
- **POST `/api/products`** (Admin Only) - Create a new product.
  - Body:
    ```json
    {
      "name": "Eco-friendly Water Bottle",
      "description": "Stainless steel insulated bottle.",
      "price": 19.99,
      "status": "active"
    }
    ```
- **PUT `/api/products/{id}`** (Admin Only) - Update product details.
- **DELETE `/api/products/{id}`** (Admin Only) - Remove a product.

### Order Endpoints
- **POST `/api/orders`** (Public) - Place a new order.
  - Body:
    ```json
    {
      "customer_name": "Budi",
      "customer_email": "budi@mail.com",
      "items": [
        { "product_id": 1, "qty": 2 }
      ]
    }
    ```
- **GET `/api/orders`** (Admin Only) - Retrieve listing of all orders.
- **GET `/api/orders/{id}`** (Admin Only) - Retrieve detailed order profile.

---

## Postman Collection

A pre-configured Postman Collection is included in the project root:
- [laravel_bintang_postman_collection.json](./laravel_bintang_postman_collection.json)

To use it:
1. Open Postman.
2. Click **Import** and select the JSON file.
3. Use the `base_url` collection variable (defaults to `http://127.0.0.1:8000`).
4. Set the `auth_token` variable after logging in to test protected endpoints.
