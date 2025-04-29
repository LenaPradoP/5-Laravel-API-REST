# **Deck Simulator API**

## **📄 Description**

This project consists of a REST API to simulate a tarot deck for performing and storing readings. The API allows users to register, create their own deck of cards, shuffle, cut the deck, and perform different types of readings (one-card or three-card spreads). The system stores the reading history for each user, allowing them to consult it later. The application has been developed using Laravel 12, with token-based access control (Laravel Passport) and a role system (Spatie Permission) to manage user permissions. This project transforms a standard MVC application into a RESTful API architecture.

## **💻 Technologies Used**

- **Laravel 12**: PHP framework for the backend
- **Laravel Passport**: For OAuth 2.0 authentication
- **Spatie Permission**: For role and permission management
- **MySQL**: As database
- **XAMPP**: Development environment (Apache, MySQL)
- **PHP 8.2**: Base programming language
- **Postman**: For API testing
- **Swagger**: For API documentation

## **📋 Requirements**

- PHP 8.2 or higher
- Composer 2.0 or higher
- MySQL 5.7 or higher
- XAMPP (or equivalent with Apache and MySQL)
- Required PHP extensions: PDO, MySQL, JSON, Fileinfo, OpenSSL

## **🛠️ Installation**

1. **Clone this repository**
   ```bash
   git clone https://github.com/LenaPradoP/5-Laravel-API-REST
   ```

2. **Navigate to the project directory:**
   ```bash
   cd 5-Laravel-API-REST
   ```

3. **Install PHP dependencies:**
   ```bash
   composer install
   ```

4. **Create and configure the .env file** 

5. **Create and configure the .env file:**
   ```
   APP_NAME="Deck Simulator API"
   APP_ENV=local
   APP_KEY=base64:YOUR_GENERATED_KEY
   APP_DEBUG=true
   APP_URL=http://localhost
   
   LOG_CHANNEL=stack
   LOG_LEVEL=debug
   
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=deck_simulator_api
   DB_USERNAME=root
   DB_PASSWORD=
   
   # For running tests with in-memory SQLite
   # DB_CONNECTION=sqlite
   # DB_DATABASE=:memory:
   
   SESSION_DRIVER=file
   SESSION_LIFETIME=120
   
   CACHE_DRIVER=file
   
   # No need to configure email for the API
   MAIL_MAILER=log
   ```

6. **Create the .env.example file:**
   Make a copy of your working .env configuration (after removing any secrets) and save it as .env.example for other developers.

7. **Generate an application key:**
   ```bash
   php artisan key:generate
   ```

8. **Create the database in MySQL:**
   - Open XAMPP and make sure Apache and MySQL services are running
   - Access [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
   - Create a new database called `deck_simulator_api`

9. **Generate Passport keys:**
   ```bash
   php artisan passport:keys
   ```
   This will generate the encryption keys needed for creating secure access tokens.

10. **Run migrations and seeders:**
   ```bash
   php artisan migrate --seed
   ```
   This will create the necessary tables and load the tarot card data, roles, and permissions.

## **▶️ Execution**

1. **Start the development server:**
   ```bash
   php artisan serve
   ```

2. **Access the API:**
   - The API will be available at [http://localhost:8000/api](http://localhost:8000/api)
   - You can use Postman or any REST client to interact with the API

## **🔮 Features**

- **Realistic Deck Simulation**: The deck maintains the order of cards as a physical deck would
  - Each user has their own deck of 78 tarot cards
  - Shuffling randomly reorders all cards in the deck
  - Cutting divides the deck into two halves and swaps their positions

- **Multiple Reading Types**: Support for different tarot spreads
  - One-card reading (spread_type: "first"): Draws a single card for a simple reading
  - Three-card reading (spread_type: "second"): Draws three cards for past-present-future analysis

- **Data Persistence**: Full history of tarot readings
  - Users can view all their past readings
  - Readings include timestamp, type, and cards drawn

- **Role-Based Admin Panel**: Administrators have extended capabilities
  - View and manage all users in the system
  - Access readings from any user
  - Perform administrative operations

## **🔒 Security and Role System**

The API implements a security system using Laravel Passport for OAuth 2.0 authentication and Spatie Permission for role-based access control:

- **User Roles**: The system defines two roles:
  - `user`: Regular users who can only access their own resources
  - `admin`: Administrators who have full access to all resources

- **Permission Control**: Access to endpoints is controlled based on user roles:
  - Regular users can only view, update, and delete their own profiles and resources
  - Administrators can view, update, and delete any user profile and resource
  
- **Token Authentication**: All protected endpoints require a valid OAuth 2.0 token
  - Tokens expire after 15 days by default (configurable in `AppServiceProvider`)
  - Refresh tokens are valid for 30 days
  
- **Token Management**: Users can logout to revoke their current token

## **📚 API Documentation**

### **Swagger Documentation**

The API is documented using Swagger UI, which provides an interactive interface to explore and test the API endpoints.

To access the Swagger documentation:
1. Start the development server
2. Navigate to [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation) in your browser
3. You will see a list of all available endpoints with detailed information about:
   - Required parameters
   - Request bodies
   - Response formats
   - Authentication requirements
   - Available response codes

### **🔐 Authentication**

The API uses OAuth 2.0 with Laravel Passport for authentication. All protected endpoints require an access token in the `Authorization` header as `Bearer {token}`.

#### **Authentication Endpoints**

- **User Registration**
  - `POST /api/users`
  - Body: `{"name": "string", "email": "string", "password": "string", "password_confirmation": "string", "birthdate": "YYYY-MM-DD"}`
  - Response: Returns the created user and an access token

- **Login**
  - `POST /api/tokens`
  - Body: `{"email": "string", "password": "string"}`
  - Response: Returns the user and an access token

- **Logout**
  - `DELETE /api/tokens`
  - Requires authentication
  - Revokes the current access token

### **👥 Users**

#### **User Endpoints**

- **Get User Profile**
  - `GET /api/users`
  - Requires authentication
  - For normal users: Returns their own profile
  - For administrators: Returns a list of all users

- **Get Specific User**
  - `GET /api/users/{id}`
  - Requires authentication
  - For normal users: Can only view their own profile
  - For administrators: Can view any user profile

- **Update User Profile**
  - `PUT /api/users/{id?}`
  - Requires authentication
  - Body: `{"name": "string", "email": "string", "birthdate": "YYYY-MM-DD"}`
  - For normal users: Can only update their own profile
  - For administrators: Can update any user profile

- **Delete User**
  - `DELETE /api/users/{id?}`
  - Requires authentication
  - For normal users: Can only delete their own account
  - For administrators: Can delete any user account

### **🃏 Decks**

Each user has a unique tarot deck with 78 cards. The deck maintains the order of the cards for tarot readings.

#### **Deck Endpoints**

- **Get User's Deck**
  - `GET /api/decks`
  - Requires authentication
  - Returns the user's deck with all 78 cards and their current positions

- **Perform Deck Actions**
  - `PUT /api/decks`
  - Requires authentication
  - Body: `{"action_type": "shuffle"}` or `{"action_type": "cut"}`
  - Shuffle: Randomizes the positions of all cards in the deck
  - Cut: Divides the deck into two halves and swaps their positions

### **🔮 Spreads (Readings)**

Users can perform tarot readings by creating "spreads" which draw cards from their deck.

#### **Spread Endpoints**

- **Create a Spread**
  - `POST /api/spreads`
  - Requires authentication
  - Body: `{"spread_type": "first"}` for one-card reading or `{"spread_type": "second"}` for three-card reading
  - Returns the created spread with the drawn cards

- **Get User's Spreads**
  - `GET /api/spreads`
  - Requires authentication
  - For normal users: Returns their own spreads
  - For administrators: Returns all spreads from all users

- **Get Specific Spread**
  - `GET /api/spreads/{id}`
  - Requires authentication
  - For normal users: Can only view their own spreads
  - For administrators: Can view any spread

- **Delete Spread**
  - `DELETE /api/spreads/{id}`
  - Requires authentication
  - For normal users: Can only delete their own spreads
  - For administrators: Can delete any spread

## **📋 Database Structure**

The application's database schema includes the following main tables:

### **User Management**
- **users**: Stores user profiles (name, email, birthdate, password)
- **oauth_access_tokens**: Access tokens for API authentication
- **oauth_refresh_tokens**: Refresh tokens for authentication renewal
- **permissions**, **roles**, **model_has_roles**, etc.: Role-based access control tables

### **Tarot System**
- **cards**: Stores the 78 tarot cards with their properties
  - Major Arcana (22 cards): The Fool through The World
  - Minor Arcana (56 cards): Four suits (Wands, Cups, Swords, Pentacles) with 14 cards each
  - Properties include: type, number, name, suit, element, meaning

- **decks**: Each user's personal tarot deck
  - Connected to a specific user
  - Tracks when the deck was last used

- **deck_cards**: Junction table linking cards to decks with position
  - The position field (1-78) tracks the current order of cards in the deck
  - Used for simulating shuffling and cutting operations

- **spreads**: Tarot readings performed by users
  - Linked to a specific deck
  - Contains the spread type (one-card or three-card) and creation date

- **spread_cards**: Cards included in a specific spread
  - Links to the card and its position in the reading

### **Entity Relationships**

- Each **User** has one **Deck**
- Each **Deck** contains 78 **Cards** (via deck_cards)
- Each **Deck** can have multiple **Spreads**
- Each **Spread** contains one or three **Cards** (via spread_cards)

## **🌱 Seeding the Database**

The application includes several seeders to initialize the system with essential data:

### **RoleSeeder**
Creates two roles and their associated permissions:
- **admin**: Full access to all features
- **user**: Limited access to own resources

The permissions created include:
- View/create/update/delete users
- View/create/delete spreads

### **TarotCardsSeeder**
Populates the database with all 78 tarot cards:
- 22 Major Arcana cards with their meanings
- 56 Minor Arcana cards (14 cards in each of the four suits)
- Each card includes type, number, name, suit, element, and meaning

### **PassportSeeder**
Initializes the OAuth authentication system:
- Creates a Personal Access Client for token generation

### **DatabaseSeeder**
Orchestrates the seeding process and creates a test user:
- Email: test@example.com
- The password is set in the User factory

When the `php artisan migrate --seed` command runs, it sets up the entire database structure and populates it with the required data to start using the application immediately.

## **🏗️ Project Structure**

The project follows the REST API architecture and is organized as follows:

- **Controllers**: Handle HTTP requests and responses
  - `AuthController`: Manages login and logout operations
  - `UserController`: Handles user registration and profile management
  - `DeckController`: Manages deck operations (retrieve, shuffle, cut)
  - `SpreadController`: Handles tarot reading operations (create, view, delete)

- **Models**: Define database entities and relationships
  - `User`: User information and authentication
  - `Card`: Tarot card data (name, type, meaning, etc.)
  - `Deck`: User's deck of cards
  - `DeckCard`: Pivot model for the relationship between decks and cards with position
  - `Spread`: Tarot reading information (type, date)
  - `SpreadCard`: Pivot model for cards included in a reading with position

- **Services**: Contain business logic
  - `DeckService`: Logic for deck operations (create, shuffle, cut)
  - `SpreadService`: Logic for tarot readings (create, get, delete)
  - `RoleService`: Logic for role-based access control

- **Resources**: Handle API response formatting
  - `UserResource`: Formats user data for API responses
  - `SpreadResource`: Formats spread data for API responses
  - `SpreadCollection`: Formats collections of spreads

- **Observers**: Handle model events
  - `UserObserver`: Creates a deck automatically when a user is registered

- **Providers**: Configure application services
  - `AppServiceProvider`: Configures Passport and registers observers
  - `AuthServiceProvider`: Configures authentication

## **👨‍💻 Development Notes**

### **Authentication Flow**

The API uses Laravel Passport for OAuth 2.0 authentication:

1. **Registration**: When a user registers, the system:
   - Creates a new user account
   - Assigns the 'user' role
   - Creates a new tarot deck with 78 cards
   - Returns an access token

2. **Login**: When a user logs in, the system:
   - Verifies credentials
   - Returns a new access token

3. **Token Usage**: Authenticated requests should include:
   ```
   Authorization: Bearer {token}
   ```

4. **Token Expiration**: 
   - Access tokens expire after 15 days
   - Refresh tokens are valid for 30 days

### **Role System Implementation**

The role system is implemented using Spatie Permission:

1. **Default Roles**:
   - `user`: Default role for all registered users
   - `admin`: For administrators with extended privileges

2. **Permission Checks**:
   - The RoleService class centralizes all permission checks
   - Controllers use the RoleService to determine if a user can perform an action

### **Deck Operations**

The deck implementation simulates a physical tarot deck:

1. **Card Positions**: Each card has a position in the deck (1-78)
2. **Shuffle Algorithm**: Randomizes all positions
3. **Cut Algorithm**: Divides the deck and swaps halves

## **🧪 Testing**

The project includes a test suite covering all API endpoints and business logic.

### **Setting Up the Test Environment**

Tests use an in-memory SQLite database for faster execution:

```php
// phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### **Test Categories**

Tests are organized based on functionality:

1. **Authentication Tests**
   - User registration
   - Login/logout
   - Token validation

2. **User Management Tests**
   - Profile retrieval
   - Profile updates
   - Account deletion
   - Role-based authorization

3. **Deck Operation Tests**
   - Deck creation
   - Shuffling algorithm
   - Deck cutting
   - Card position tracking

4. **Spread (Reading) Tests**
   - Creating single-card spreads
   - Creating three-card spreads
   - Retrieving spread history
   - Spread deletion

### **Running Tests**

To run the entire test suite:
```bash
php artisan test
```

To run a specific test:
```bash
php artisan test --filter=UserTest
```

To run tests with detailed output:
```bash
php artisan test --verbose
```

## **📊 Postman Collection**

A Postman collection is included to help test the API. Import the collection and environment files into Postman:

1. Open Postman
2. Click on "Import" in the top left corner
3. Select the files:
   - `Deck Simulator.postman_collection.json`
   - `Laravel Local.postman_environment.json`
4. The collection includes organized folders for:
   - Authentication (registration, login, logout)
   - User management
   - Deck operations
   - Spread (reading) operations
   - Admin-specific endpoints

Make sure to set the environment variables:
- `base_url`: Should be set to `http://localhost:8000/api` by default

## **🤝 Contributions**

Contributions are welcome! Please follow these steps to contribute:

1. Fork the repository
2. Create a new branch:
   ```bash
   git checkout -b feature/NewFeature
   ```
3. Make your changes and commit them:
   ```bash
   git commit -m "Add New Feature"
   ```
4. Push to your fork:
   ```bash
   git push origin feature/NewFeature
   ```
5. Create a pull request