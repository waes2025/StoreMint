# StoreMint - eCommerce Website Templates

StoreMint is a modern, high-performance eCommerce website templates system. Built on top of the Laravel framework and powered by Vue 3, Inertia.js, and Tailwind CSS v4, StoreMint provides a modular-first structure for building and customizing online stores with ease.

---

## 🚀 Key Features

- **Modern Tech Stack:** Laravel 13, Vue 3, Inertia.js, and Vite.
- **Tailwind CSS v4:** Beautiful, utility-first styling with modern animations and fluid layouts.
- **TypeScript Support:** Fully type-safe frontend development with TypeScript and `vue-tsc`.
- **Modular Architecture:** Structured using `laravel-modules` for modular, clean, and extensible development.
- **Out-of-the-Box SQLite Support:** Start developing immediately without setting up a standalone database server.
- **Demo Data Seeders:** Ready-to-use seeders to quickly populate the database with default store settings, permissions, currencies, and demo products.

---

## 📋 Prerequisites

Before you begin, ensure you have the following installed on your local machine:

- **PHP:** `^8.3` (with standard extensions including `sqlite3`, `pdo_sqlite`, `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `mbstring`, `openssl`, `xml`, etc.)
- **Composer:** Dependency manager for PHP
- **Node.js:** `v18` or higher, along with **npm** (or pnpm / yarn)
- **Database:** SQLite (default/recommended for development) or MySQL/PostgreSQL

---

## 🛠️ Step-by-Step Installation Guide

Follow these steps to set up StoreMint locally:

### 1. Download & Extract
Download the StoreMint ZIP file and extract it to your desired directory on your local machine, or clone the repository directly.

### 2. Configure Environment Variables
Copy the `.env.example` file to create your `.env` configuration file:

```bash
cp .env.example .env
```
*(On Windows Command Prompt or PowerShell, you can use `copy .env.example .env`)*

By default, the application is configured to use a local **SQLite** database. You do not need to configure any username or password for this. If you wish to use MySQL instead, uncomment the database configuration block in your `.env` and fill in your database credentials:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=storemint_db
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Install PHP Dependencies
Run Composer to install all the backend packages:

```bash
composer install
```

### 4. Generate the Application Key
Generate a secure encryption key for your application:

```bash
php artisan key:generate
```

### 5. Install Frontend Dependencies
Install the required Node.js packages:

```bash
npm install
```

### 6. Run Database Migrations & Seeders
Create the database tables and populate them with default/demo data. Run the migrations and seed command:

```bash
php artisan migrate --seed
```
*(If using SQLite, Laravel will prompt you to create the `database.sqlite` file if it does not already exist. Select **Yes** when prompted).*

Alternatively, you can run migrations and seeders separately:
```bash
# Run migrations
php artisan migrate

# Seed default & demo data
php artisan db:seed
```

---

## ⚡ Quick One-Step Setup (Alternative)
For an automated setup, you can run the pre-configured Composer script which installs dependencies, creates the `.env` file, generates keys, runs migrations, and builds frontend assets automatically:

```bash
composer run setup
```
*(Note: Ensure your database server is running and configured in `.env` beforehand if you are not using the default SQLite database).*

---

## 🏃 Running the Application Locally

You can run the application using one of the following methods:

### Method A: Combined Development Server (Recommended)
Run the unified development command to start the Laravel backend and Vite asset bundler concurrently:

```bash
composer dev
```
or:
```bash
npm run dev
```

### Method B: Separate Terminals

1. **Start the Laravel backend server:**
   ```bash
   php artisan serve
   ```
2. **Start the Vite development server (for Hot Module Replacement):**
   ```bash
   npm run dev
   ```

Once started, open your browser and navigate to `http://127.0.0.1:8000`.

---

## 📦 Production Build

When deploying to production, compile and minify the frontend assets using:

```bash
npm run build
```

---

## 🧹 Code Quality, Testing & Linting

StoreMint comes with built-in tools to keep code clean and tested:

- **Run Test Suite:** `php artisan test`
- **Lint Code (PHP & JS):** `npm run lint` or `composer run lint`
- **Format Code (Prettier):** `npm run format`
- **TypeScript Check:** `npm run types:check`

