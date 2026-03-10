# PHP-MyGigGuide

A Laravel-based event management platform for discovering and managing gigs, events, venues, and artists.

## Features

- 🎵 **Event Management** - Create and manage events
- 🎤 **Artist Profiles** - Artist profiles and information
- 🏟️ **Venue Management** - Venue listings and details
- 📅 **Organiser Dashboard** - Event organiser tools
- ⭐ **Rating System** - Rate events, venues, and artists
- 👥 **User Management** - User accounts and roles
- 🔐 **Authentication** - Secure login and registration
- 📱 **Responsive Design** - Mobile-friendly interface

## Technology Stack

- **Backend**: Laravel 11.x
- **Database**: MySQL
- **Frontend**: Blade templates with TailwindCSS
- **Authentication**: Laravel Sanctum
- **Permissions**: Laravel Trust (Roles & Permissions)
- **Maps**: Google Maps integration

## Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/yourusername/PHP-MyGigGuide.git
   cd PHP-MyGigGuide
   ```

2. **Install dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**:
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

5. **Build assets**:
   ```bash
   npm run build
   ```

6. **Start development server**:
   ```bash
   php artisan serve
   ```

## Database Structure

### Core Tables
- `users` - User accounts and authentication
- `venues` - Venue information and details
- `artists` - Artist profiles and information
- `events` - Event listings and details
- `organisers` - Event organiser profiles
- `ratings` - Rating system for events/venues/artists

### Permission System
- `permissions` - System permissions
- `roles` - User roles
- `permission_role` - Permission-Role relationships
- `role_user` - User-Role assignments

### Relationship Tables
- `user_artist_favorites` - User favorite artists
- `user_event_favorites` - User favorite events
- `user_organiser_favorites` - User favorite organisers
- `user_venue_favorites` - User favorite venues
- `event_artist` - Events linked to artists

## API Endpoints

### Authentication
- `POST /login` - User login
- `POST /register` - User registration
- `POST /logout` - User logout

### Events
- `GET /events` - List all events
- `POST /events` - Create new event
- `GET /events/{id}` - Get specific event
- `PUT /events/{id}` - Update event
- `DELETE /events/{id}` - Delete event

### Artists
- `GET /artists` - List all artists
- `POST /artists` - Create new artist
- `GET /artists/{id}` - Get specific artist

### Venues
- `GET /venues` - List all venues
- `POST /venues` - Create new venue
- `GET /venues/{id}` - Get specific venue

## Configuration

### Environment Variables

```env
APP_NAME=MyGigGuide
APP_ENV=production
APP_KEY=your_app_key_here
APP_URL=https://mygigguide.co.za

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=ecotribe_mygigguide
DB_USERNAME=ecotribe_08600
DB_PASSWORD=your_password

GOOGLE_MAPS_API_KEY=your_google_maps_api_key
```

## Deployment

### Production Deployment

1. **Upload files** to your server
2. **Set up environment** variables
3. **Run migrations**:
   ```bash
   php artisan migrate --force
   ```
4. **Clear caches**:
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### SSL Configuration

The project includes SSL configuration files:
- `.htaccess` - Apache HTTPS redirect
- `ForceHttps.php` - Laravel HTTPS middleware
- `TrustProxies.php` - Proxy configuration

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License.

## Support

For support, email support@mygigguide.co.za or create an issue in this repository.

## Changelog

### Version 1.0.0
- Initial release
- Event management system
- Artist and venue profiles
- User authentication
- Rating system
- Admin dashboard