CREATE DATABASE IF NOT EXISTS webapp_db CHARACTER
SET
    utf8mb4 COLLATE utf8mb4_unicode_ci;

USE webapp_db;

-- Таблица администраторов
CREATE TABLE
    IF NOT EXISTS administrators (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password_hash VARCHAR(255) NOT NULL,
        salt VARCHAR(64) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_username (username),
        INDEX idx_email (email)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Таблица продуктов (для публичного контента)
CREATE TABLE
    IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10, 2) NOT NULL,
        category VARCHAR(50) NOT NULL,
        image_url VARCHAR(255),
        stock INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        is_active BOOLEAN DEFAULT TRUE,
        INDEX idx_category (category),
        INDEX idx_name (name),
        INDEX idx_is_active (is_active)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Таблица отзывов (форма сбора данных)
CREATE TABLE
    IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        product_id INT NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        rating INT NOT NULL CHECK (
            rating >= 1
            AND rating <= 5
        ),
        comment TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_approved BOOLEAN DEFAULT FALSE,
        FOREIGN KEY (fk_product) REFERENCES products (id) ON DELETE CASCADE,
        INDEX idx_product_id (product_id),
        INDEX idx_is_approved (is_approved),
        INDEX idx_created_at (created_at)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Таблица сессий
CREATE TABLE
    IF NOT EXISTS sessions (
        id VARCHAR(128) PRIMARY KEY,
        admin_id INT NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NOT NULL,
        FOREIGN KEY (fk_admin) REFERENCES administrators (id) ON DELETE CASCADE,
        INDEX idx_admin_id (admin_id),
        INDEX idx_expires_at (expires_at)
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Вставка тестовых данных
INSERT INTO
    administrators (username, email, password_hash, salt)
VALUES
    ('admin', 'admin@example.com', '', '');

-- Тестовые продукты
INSERT INTO
    products (
        name,
        description,
        price,
        category,
        image_url,
        stock,
        is_active
    )
VALUES
    (
        'Ноутбук Dell XPS 13',
        'Мощный ультрабук с процессором Intel Core i7',
        89999.00,
        'Электроника',
        'https://via.placeholder.com/300x200?text=Dell+XPS+13',
        15,
        TRUE
    ),
    (
        'iPhone 14 Pro',
        'Смартфон Apple с камерой 48MP',
        119999.00,
        'Электроника',
        'https://via.placeholder.com/300x200?text=iPhone+14+Pro',
        25,
        TRUE
    ),
    (
        'Книга "Clean Code"',
        'Роберт Мартин - Чистый код',
        2500.00,
        'Книги',
        'https://via.placeholder.com/300x200?text=Clean+Code',
        50,
        TRUE
    ),
    (
        'Механическая клавиатура',
        'RGB подсветка, Cherry MX switches',
        7999.00,
        'Периферия',
        'https://via.placeholder.com/300x200?text=Keyboard',
        30,
        TRUE
    ),
    (
        'Игровая мышь Logitech',
        'Беспроводная мышь с DPI до 16000',
        4500.00,
        'Периферия',
        'https://via.placeholder.com/300x200?text=Mouse',
        40,
        TRUE
    ),
    (
        'Монитор LG 27"',
        '4K UHD, IPS панель, 144Hz',
        35999.00,
        'Электроника',
        'https://via.placeholder.com/300x200?text=Monitor',
        12,
        TRUE
    ),
    (
        'SSD Samsung 1TB',
        'Твердотельный накопитель NVMe',
        8999.00,
        'Комплектующие',
        'https://via.placeholder.com/300x200?text=SSD',
        60,
        TRUE
    ),
    (
        'Наушники Sony WH-1000XM5',
        'Беспроводные с шумоподавлением',
        28999.00,
        'Аудио',
        'https://via.placeholder.com/300x200?text=Headphones',
        20,
        TRUE
    );

-- Тестовые отзывы
INSERT INTO
    reviews (
        product_id,
        name,
        email,
        rating,
        comment,
        is_approved
    )
VALUES
    (
        1,
        'Иван Петров',
        'ivan@example.com',
        5,
        'Отличный ноутбук! Быстрый и легкий.',
        TRUE
    ),
    (
        1,
        'Мария Сидорова',
        'maria@example.com',
        4,
        'Хороший, но дорогой.',
        TRUE
    ),
    (
        2,
        'Алексей Смирнов',
        'alex@example.com',
        5,
        'Лучший смартфон на рынке!',
        TRUE
    ),
    (
        3,
        'Ольга Иванова',
        'olga@example.com',
        5,
        'Очень полезная книга для программистов.',
        TRUE
    );