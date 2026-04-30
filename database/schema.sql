DROP DATABASE IF EXISTS restaurant_db;
CREATE DATABASE restaurant_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurant_db;

CREATE TABLE users (
    id             INT                  NOT NULL AUTO_INCREMENT,
    name           VARCHAR(100)         NOT NULL,
    email          VARCHAR(150)         NOT NULL,
    password       VARCHAR(255)         NOT NULL,
    role           ENUM('admin','user') NOT NULL DEFAULT 'user',
    remember_token VARCHAR(64)          NULL,
    created_at     TIMESTAMP            NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_remember_token (remember_token)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE menu_items (
    id           INT           NOT NULL AUTO_INCREMENT,
    name         VARCHAR(150)  NOT NULL,
    description  TEXT          NULL,
    price        DECIMAL(10,2) NOT NULL,
    category     VARCHAR(100)  NOT NULL,
    image_path   VARCHAR(255)  NULL,
    is_available TINYINT(1)    NOT NULL DEFAULT 1,
    created_at   TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_menu_items_category     (category),
    KEY idx_menu_items_is_available (is_available)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE orders (
    id           INT                                      NOT NULL AUTO_INCREMENT,
    user_id      INT                                      NOT NULL,
    menu_item_id INT                                      NOT NULL,
    quantity     INT                                      NOT NULL DEFAULT 1,
    total_price  DECIMAL(10,2)                            NOT NULL,
    status       ENUM('pending','confirmed','cancelled')  NOT NULL DEFAULT 'pending',
    notes        TEXT                                     NULL,
    created_at   TIMESTAMP                                NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_orders_user_id      (user_id),
    KEY idx_orders_menu_item_id (menu_item_id),
    KEY idx_orders_status       (status),
    CONSTRAINT fk_orders_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_orders_menu_item
        FOREIGN KEY (menu_item_id) REFERENCES menu_items (id)
        ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (name, email, password, role) VALUES (
    'Admin User',
    'admin@restaurant.com',
    '$2y$12$IoRPTmpkPuVpVJNRbCngN.Soil3pN4WPKL43RUwHQhGgkBEVhBqw6',
    'admin'
);

INSERT INTO users (name, email, password, role) VALUES (
    'Test User',
    'user@restaurant.com',
    '$2y$12$IoRPTmpkPuVpVJNRbCngN.Soil3pN4WPKL43RUwHQhGgkBEVhBqw6',
    'user'
);


INSERT INTO menu_items (name, description, price, category, is_available) VALUES
    ('Bruschetta',          'Toasted bread topped with fresh tomatoes, garlic, and basil.',                      6.50,  'Starters', 1),
    ('Soup of the Day',     'Ask your server for today''s freshly made soup.',                                   5.00,  'Starters', 1),
    ('Calamari Fritti',     'Lightly battered squid rings served with aioli dipping sauce.',                     9.00,  'Starters', 1),
    ('Grilled Salmon',      'Atlantic salmon fillet with lemon butter sauce and seasonal vegetables.',           22.00, 'Mains',    1),
    ('Beef Tenderloin',     '200g prime beef tenderloin, served with truffle mashed potato.',                    32.00, 'Mains',    1),
    ('Mushroom Risotto',    'Creamy Arborio rice with wild mushrooms, parmesan, and fresh thyme.',               18.00, 'Mains',    1),
    ('Margherita Pizza',    'Classic tomato base, fresh mozzarella, and basil leaves.',                          14.00, 'Mains',    1),
    ('Chocolate Fondant',   'Warm dark chocolate cake with a molten centre, served with vanilla ice cream.',     8.50,  'Desserts', 1),
    ('Crème Brûlée',        'Classic French vanilla custard with a caramelised sugar crust.',                    7.50,  'Desserts', 1),
    ('Seasonal Fruit Tart', 'Buttery pastry shell filled with crème pâtissière and fresh fruit.',                7.00,  'Desserts', 1),
    ('Still Water',         '500ml bottle of still mineral water.',                                              2.50,  'Drinks',   1),
    ('Sparkling Water',     '500ml bottle of sparkling mineral water.',                                          2.50,  'Drinks',   1),
    ('Fresh Orange Juice',  'Freshly squeezed orange juice.',                                                    4.50,  'Drinks',   1),
    ('House Red Wine',      'Glass of the chef''s selected house red wine.',                                     7.00,  'Drinks',   1),
    ('Chef''s Special',     'Ask your server — changes daily based on seasonal ingredients.',                    0.00, 'Mains',     0);