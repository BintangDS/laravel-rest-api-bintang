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

---

## Manual Testing Guide (Postman)

To manually test the API endpoints and verify role-based security rules using the included Postman Collection:

### 1. Setup & Import
1. **Import the Collection**: Open Postman, click **Import**, and select the `laravel_bintang_postman_collection.json` file from the project root.
2. **Reset Database**: Run `php artisan migrate:fresh --seed` to start with a fresh database. This seeds the admin account, user account, 6 default products, and 1 default order.

### 2. Standard E-Commerce Workflow (Happy Path)
1. **Login as Admin**:
   - Open **Auth -> Login - Admin** (`admin@mail.com`) and click **Send**.
   - Copy the generated `access_token` from the response.
2. **Set the Collection Variables**:
   - Click on the collection name **Laravel 12 REST API E-commerce** in the left sidebar.
   - Go to the **Variables** tab, paste the token into the **Current Value** of `auth_token`, and click **Save** (`Ctrl + S`).
3. **Create Product (Admin Only)**:
   - Open **Products -> Create Product (Admin Only)** and click **Send** to add **Mangrove Crackers** (gets `id: 7`).
4. **Update Product (Admin Only)**:
   - Open **Products -> Update Product (Admin Only)** and click **Send**.
   - This will update **Mangrove Soap** (ID: 1) and raise its price slightly to **`2.85`** (from `2.79`).
5. **Delete Product (Admin Only)**:
   - Open **Products -> Delete Product (Admin Only)** and click **Send**.
   - This will delete **Mangrove Soap Premium** (ID: 2).
6. **Create Order (Public / Guest)**:
   - Open **Orders -> Create Order (Public)** and click **Send**.
   - This places a guest order for `product_id: 1` with a quantity of `2`.
   - The total price is automatically calculated based on the updated price to **`5.70`** (2 x $2.85).
7. **View Orders (Admin Only)**:
   - Open **Orders -> Get All Orders (Admin Only)** and click **Send** to view all customer orders. You will see the default seeded order (ID 1, containing Mangrove Batik) and the new order (ID 2, containing Mangrove Soap).
8. **View Single Order (Admin Only)**:
   - Open **Orders -> Get Single Order (Admin Only)** and click **Send**.
   - This retrieves the detailed profile of the order with `id: 1` (the default seeded Mangrove Batik order).

### 3. Security & Authorization Checks (Role Protection)
Verify that middleware permissions prevent unauthorized access:

#### Scenario A: Unauthenticated Access (401 Unauthorized)
1. Clear the `auth_token` value in the Postman collection variables and click **Save**.
2. Try sending **Products -> Create Product (Admin Only)** or **Orders -> Get All Orders (Admin Only)**.
3. **Expected Result**: Returns `401 Unauthorized` because no token was provided.

#### Scenario B: Regular User Restriction (403 Forbidden)
1. Open **Auth -> Login - User** (`budi@mail.com`) and click **Send**.
2. Copy the user's token and update the `auth_token` collection variable, then click **Save**.
3. Try sending **Products -> Create Product (Admin Only)** or **Orders -> Get All Orders (Admin Only)**.
4. **Expected Result**: Returns `403 Forbidden` with the message `"Forbidden. Admin access required."` because Budi does not have the admin role.

### 4. Logout & Token Revocation Flow
1. Open **Auth -> Logout** and click **Send** (using Budi's token).
2. **Expected Result**: Returns `200 OK` with the message `"Successfully logged out."`.
3. Try sending any protected request again (e.g. **Logout** or **Get All Orders**).
4. **Expected Result**: Returns `401 Unauthorized` because the token was successfully destroyed in the database.
