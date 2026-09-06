-- TCM Recommended Store Database Schema

-- Product Categories
CREATE TABLE IF NOT EXISTS store_categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'bi-tag',
    display_order INT DEFAULT 0,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_order (display_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Products
CREATE TABLE IF NOT EXISTS store_products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    description TEXT,
    short_description VARCHAR(500),
    
    -- Pricing
    price DECIMAL(10,2) NOT NULL,
    original_price DECIMAL(10,2) DEFAULT NULL,
    discount_percent INT DEFAULT 0,
    
    -- Images (JSON array of image URLs)
    images JSON,
    primary_image VARCHAR(500),
    
    -- Product Details
    brand VARCHAR(100),
    tags VARCHAR(500), -- Comma-separated tags
    specifications JSON, -- Key-value pairs
    
    -- Inventory
    stock_quantity INT DEFAULT 0,
    is_in_stock BOOLEAN DEFAULT TRUE,
    
    -- Links
    affiliate_url VARCHAR(1000), -- External purchase link (Amazon, Flipkart, etc.)
    
    -- SEO
    meta_title VARCHAR(255),
    meta_description TEXT,
    
    -- Status
    featured BOOLEAN DEFAULT FALSE,
    status ENUM('active', 'inactive', 'out_of_stock') DEFAULT 'active',
    
    -- Stats
    views_count INT DEFAULT 0,
    clicks_count INT DEFAULT 0,
    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (category_id) REFERENCES store_categories(id) ON DELETE CASCADE,
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_featured (featured),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Product Click Tracking
CREATE TABLE IF NOT EXISTS store_product_clicks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    user_id INT DEFAULT NULL,
    ip_address VARCHAR(45),
    user_agent TEXT,
    clicked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (product_id) REFERENCES store_products(id) ON DELETE CASCADE,
    INDEX idx_product (product_id),
    INDEX idx_user (user_id),
    INDEX idx_date (clicked_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Categories
INSERT INTO store_categories (name, slug, description, icon, display_order) VALUES
('Laptops & Computers', 'laptops-computers', 'Best laptops and computers for coding and development', 'bi-laptop', 1),
('Programming Books', 'programming-books', 'Must-read books for developers', 'bi-book', 2),
('Tech Gadgets', 'tech-gadgets', 'Useful gadgets for programmers', 'bi-cpu', 3),
('Online Courses', 'online-courses', 'Recommended online courses and subscriptions', 'bi-mortarboard', 4),
('Developer Tools', 'developer-tools', 'IDEs, software licenses, and tools', 'bi-tools', 5),
('Accessories', 'accessories', 'Keyboards, mouse, headphones, and more', 'bi-headphones', 6)
ON DUPLICATE KEY UPDATE name=name;
