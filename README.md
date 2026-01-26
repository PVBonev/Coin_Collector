# 🪙 CoinCollector - Numismatic Collection & Trading Platform

**CoinCollector** is a comprehensive web-based application designed for numismatists to manage their personal coin collections, track statistics, and interact with other collectors through a trading system.



---

## 🚀 Key Features

### 📚 Collection Management
* **Inventory Tracking:** Add coins with detailed specifications (Year, Country, Grade, Mintage, Images).
* **Dynamic Dashboard:** Real-time statistics, estimated value calculation, and visual charts (Pie Chart for collection status).
* **Search & Filter:** Instant client-side filtering and sorting by Year, Date Added, Name, or Country.
* **Data Portability:** Full CSV Import and Export functionality to backup your data.

### 🤝 Trading System (Marketplace)
* **Coin Statuses:** Mark coins as *Collection*, *Swap*, *Sell*.
* **Trade Requests:** Users can initiate **Swap** or **Buy** requests with other users.
* **Counter-Offers:** Advanced negotiation logic allowing users to decline an offer and propose a counter-swap immediately.
* **Transaction Flow:** Full lifecycle management (Pending → Accepted → Confirmed → Completed).

### 🛠️ Administrative Control
* **Admin Dashboard:** Specialized view for administrators to monitor platform activity.
* **Catalog Management:** Ability to edit global coin data and approve user-submitted coin types.
* **User Management:** Review and moderate user content.

### 💻 Technical Highlights
* **Responsive Design:** Custom CSS Grid and Flexbox layout (mobile-friendly), no external frameworks used.
* **Security:** Password hashing, PDO prepared statements for SQL injection prevention, Session management.
* **Architecture:** Dockerized environment for easy deployment.

---

## 🛠️ Tech Stack

* **Frontend:** HTML5, CSS3 (Custom Variables, Grid/Flexbox), JavaScript (DOM Manipulation, AJAX-free filtering).
* **Backend:** PHP 8.2 (Native, Object-Oriented Database interactions).
* **Database:** MySQL 8.0 (Relational Schema with Foreign Keys).
* **DevOps:** Docker & Docker Compose for containerization.

---

## 📂 Project Structure

```text
coin_collector_project
│
├── actions/                    # Backend logic scripts (Form handlers)
│   ├── add_coin_process.php    # Handles adding a coin to user collection
│   ├── create_trade.php        # Handles logic for creating swap/sell requests
│   ├── edit_coin_process.php   # Handles editing/deleting user coins
│   ├── export_csv.php          # Generates CSV export of collection
│   ├── import_csv_process.php  # Parses and imports CSV data
│   ├── process_trade.php       # Handles accept/decline/cancel/confirm trade logic
│   ├── review_coin_process.php # Admin logic for approving/rejecting coins
│   └── update_settings.php     # Handles user profile updates
│
├── admin/                      # Admin-only pages
│   ├── dashboard.php           # Admin dashboard (Pending coins, etc.)
│   ├── edit_catalog_coin.php   # Form to edit global catalog coin data
│   └── review_coin.php         # Page to review specific user-submitted coins
│
├── api/                        # AJAX endpoints
│   └── get_catalog_data.php    # Returns JSON data for dynamic dropdowns
│
├── assets/                     # Static frontend assets
│   ├── css/
│   │   ├── styles.css          # Main project stylesheet
│   │   └── styles_new.css      # (Alternative/Backup styles)
│   └── images/
│       ├── csv_example.png     # Example image for import instructions
│       └── no-coin.png         # Fallback placeholder image
│
├── auth/                       # Authentication specific logic
│   ├── login_process.php       # Handles user login verification
│   ├── logout_process.php      # Destroys session and redirects
│   └── register_process.php    # Handles new user registration
│
├── config/                     # Configuration files
│   └── db.php                  # Database connection setup (PDO)
│
├── DB/                         # Database Schemas and Seed Data
│   ├── 01_schema.sql           # Main table structure
│   ├── 02_countries_data.sql   # Seed data for countries
│   ├── 03_users_data.sql       # Seed data for dummy users
│   ├── 04_catalog_coins_data.sql # Seed data for catalog coins
│   ├── 05_user_coins_data.sql  # Seed data for user collections
│   ├── generate_catalog_coins.js # Helper script for data generation
│   ├── generate_countries.js   # Helper script for data generation
│   └── generate_user_coins.js  # Helper script for data generation
│
├── includes/                   # Reusable HTML snippets
│   ├── footer.php              # Sticky footer with credits
│   └── navbar.php              # Responsive navigation bar
│
├── Logo/                       # Branding assets
│   ├── logo_login.svg          # Logo variant for login page
│   └── logo.svg                # Main site logo
│
├── uploads/                    # User uploaded content (Git ignored usually)
│   ├── coins/                  # Images of specific user coins
│   ├── populate_coin_types/    # Images for catalog coin types
│   └── user_prof_pics/         # User profile avatars
│
├── .gitignore                  # Git configuration
├── docker-compose.yml          # Docker environment setup
├── README.md                   # Project documentation
├── StartupAndOtherTips.txt     # Helper notes
├── Structure.txt               # Project map
├── ToDo.md                     # Task list
│
└── # PUBLIC PAGES (Root)
    ├── index.php               # Homepage / User Dashboard (Collection & Stats)
    ├── login.php               # Login page
    ├── register.php            # Registration page
    ├── countries.php           # List of all countries with flags/search
    ├── country.php             # Specific country page (Lists catalog coins)
    ├── catalog_coin.php        # Global catalog details for a specific coin type
    ├── user_coin_details.php   # Details of a specific coin owned by a user
    ├── add_coin.php            # Form to add coin to collection
    ├── edit_coin.php           # Form to edit existing coin in collection
    ├── create_catalog_coin.php # Form to submit a new coin type to catalog
    ├── view_profile.php        # Public profile of other users
    ├── my_trades.php           # Trade Center (Incoming/Sent offers)
    ├── trade_details.php       # Specific trade interaction page
    ├── swap_request.php        # Form to initiate a swap
    ├── buy_request.php         # Form to initiate a purchase
    ├── search_results.php      # Global search results page
    ├── settings.php            # User account settings
    ├── data_management.php     # Import/Export interface
    ├── grading_guide.php       # Informational page about coin grades
    ├── country_details.php     # (Alternative/Legacy country view)
    └── generate_hash.php       # Utility to generate password hashes manually
```

---

## ⚙️ Installation & Setup

This project is fully containerized using Docker. You do not need to install PHP or MySQL locally.

### Prerequisites
* Docker Desktop installed and running.

### Quick Start

1. Clone/Download the project repository.

2. Open a terminal in the project folder.

3. Run the application:

```bash
docker-compose up -d --build
```

4. Open your browser and navigate to: **http://localhost:8000**

### Stopping the App

To stop the application and clean up containers (data persists):

```bash
docker-compose down
```

> **Note:** To factory reset the database to default seed data, use `docker-compose down -v`

---

## 🌐 Live Demo (Optional)

If presenting remotely, the application can be tunneled using Ngrok:

```bash
ngrok http 8000
```

---

## 👤 Author & Credits

* **Developer:** Petko Bonev
* **Logo Design & Creative Support:** Gabriella Kioseva
* **University Project - 2026**
