CREATE TABLE theme_settings (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    singleton TINYINT GENERATED ALWAYS AS (1) STORED,

    primary_light    VARCHAR(7) NOT NULL DEFAULT '#174f7d',
    secondary_light  VARCHAR(7) NOT NULL DEFAULT '#153754',
    accent_light     VARCHAR(7) NOT NULL DEFAULT '#d52837',
    background_light VARCHAR(7) NOT NULL DEFAULT '#f6f8fb',
    surface_light    VARCHAR(7) NOT NULL DEFAULT '#ffffff',
    text_light       VARCHAR(7) NOT NULL DEFAULT '#1b2c3a',

    primary_dark    VARCHAR(7) NOT NULL DEFAULT '#7db5e1',
    secondary_dark  VARCHAR(7) NOT NULL DEFAULT '#c4d8e8',
    accent_dark     VARCHAR(7) NOT NULL DEFAULT '#ff727e',
    background_dark VARCHAR(7) NOT NULL DEFAULT '#101a26',
    surface_dark    VARCHAR(7) NOT NULL DEFAULT '#192838',
    text_dark       VARCHAR(7) NOT NULL DEFAULT '#eff5f9',

    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,

    PRIMARY KEY (id),
    UNIQUE KEY uq_theme_settings_singleton (singleton),
    CONSTRAINT chk_theme_primary_light CHECK (primary_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_secondary_light CHECK (secondary_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_accent_light CHECK (accent_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_background_light CHECK (background_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_surface_light CHECK (surface_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_text_light CHECK (text_light REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_primary_dark CHECK (primary_dark REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_secondary_dark CHECK (secondary_dark REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_accent_dark CHECK (accent_dark REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_background_dark CHECK (background_dark REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_surface_dark CHECK (surface_dark REGEXP '^#[0-9A-Fa-f]{6}$'),
    CONSTRAINT chk_theme_text_dark CHECK (text_dark REGEXP '^#[0-9A-Fa-f]{6}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO theme_settings (id) VALUES (1);
