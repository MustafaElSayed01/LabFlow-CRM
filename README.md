# LabFlow public site template

Arabic-first public website starter for PHP 8.5, procedural PHP, MySQLi, Bootstrap 5.3, and vanilla JavaScript with optional jQuery enhancement.

## Setup

1. Copy `.env.example` to `.env` and enter the database connection values.
2. Run `composer install` to install `vlucas/phpdotenv` from `composer.lock`.
3. Import the existing SQL files in dependency order, then import `sql/theme_settings.sql` once the database has been created. The theme file inserts the starter palette row.
4. Configure the web server to use `index.php` as its directory index. For a subdirectory install, set `APP_URL` in `.env` to that URL path.
5. Populate `lab_settings`, campaigns, branches, and campaign-to-branch links in the database.

## Per-site branding

- `lab_settings.logo_path` and `lab_settings.favicon_path` override the `.env` fallback paths. The supplied logo and generated SVG favicon are the starter defaults.
- Set the site's name and fallback image paths with `SITE_NAME`, `SITE_LOGO`, and `SITE_FAVICON` in `.env`.
- Import `sql/theme_settings.sql`, then change its six light and six dark color columns for the installation. Its navy and red starter palette matches the supplied logo. `assets/css/main.css` also contains matching `:root` fallback values for development and pre-database rendering.
- Visitors can switch light and dark appearances; their choice is stored in their browser.

## Booking behavior

The home page contains the campaign and branch sections and the reservation form. Campaign tags send the selected campaign ID. If that campaign has several linked branches, the visitor must select one; a single linked branch is assigned automatically. Bookings create or reuse a patient by phone number, create a pending reservation without a date/time, and add a reservation-history entry in one transaction. Unique schema constraints prevent a patient from having more than one active reservation for the same campaign.
