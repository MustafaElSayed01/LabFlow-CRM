# LabFlow public site template

Arabic-first public website starter for PHP 8.5, procedural PHP, MySQLi, Bootstrap 5.3, and vanilla JavaScript with optional jQuery enhancement.

## Setup

1. Copy `.env.example` to `.env` and enter the database connection values.
2. Run `composer install` to install `vlucas/phpdotenv` from `composer.lock`.
3. Import the existing SQL files in dependency order, then import `sql/theme_settings.sql` once the database has been created. The theme file inserts the starter palette row.
4. Configure the web server to use `index.php` as its directory index. For a subdirectory install, set `APP_URL` in `.env` to that URL path.
5. Populate `lab_settings`, campaigns, branches, campaign-to-branch links, and branch working hours in the database.

## Per-site branding

- `lab_settings.logo_path` and `lab_settings.favicon_path` override the `.env` fallback paths. The supplied logo and generated SVG favicon are the starter defaults.
- Set the site's name and fallback image paths with `SITE_NAME`, `SITE_LOGO`, and `SITE_FAVICON` in `.env`.
- Import `sql/theme_settings.sql`, then change its six light and six dark color columns for the installation. Its navy and red starter palette matches the supplied logo. `assets/css/main.css` also contains matching `:root` fallback values for development and pre-database rendering.
- Visitors can switch light and dark appearances; their choice is stored in their browser.

## Booking behavior

Bookings create or reuse a patient by phone number, create a pending reservation, and add a reservation-history entry in one transaction. A branch needs a `campaign_branches` link to the chosen campaign. When a branch has a `branch_working_hours` row for the requested weekday, the selected time must fall within those hours. Unique schema constraints prevent duplicate active patient/campaign reservations and occupied branch/date/time slots.

`branch_working_hours.weekday` uses MySQL `WEEKDAY()` values (Monday `0` through Sunday `6`). If a weekday has no hours row, booking time is not restricted by a schedule.
