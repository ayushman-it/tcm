# TCM Recommended Store - Implementation Guide 🛍️

## Overview
E-commerce section for recommending products to students (laptops, books, gadgets, courses).

## Status: Database Schema Created ✅
Location: `database/store_schema.sql`

## Next Steps Required:

### 1. Run Database Migration
```sql
-- Run this file to create tables:
source database/store_schema.sql;
```

**Tables Created:**
- `store_categories` - Product categories
- `store_products` - Products with images, pricing, affiliate links
- `store_product_clicks` - Click tracking

**Default Categories Added:**
- Laptops & Computers
- Programming Books  
- Tech Gadgets
- Online Courses
- Developer Tools
- Accessories

### 2. Add Store Section to index.html

**Location:** After `.courses-section` (around line 1077)

**Section Code:**
```html
<!-- TCM Recommended Store Section -->
<section class="store-section" style="padding: 80px 0; background: #fff;">
    <div class="container">
        <!-- Section Header -->
        <div style="text-align: center; margin-bottom: 50px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: #f5f5f5; border: 1px solid #e0e0e0; border-radius: 30px; padding: 6px 16px; margin-bottom: 16px;">
                <i class="bi bi-shop" style="font-size: 0.9rem; color: #111;"></i>
                <span style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #666;">TCM Recommends</span>
            </div>
            <h2 style="font-size: 2.2rem; font-weight: 900; color: #111; margin-bottom: 12px; letter-spacing: -0.5px;">
                Gear Up for Success
            </h2>
            <p style="font-size: 1rem; color: #666; max-width: 600px; margin: 0 auto; line-height: 1.6;">
                Handpicked tools, gadgets, and resources to supercharge your coding journey
            </p>
        </div>

        <!-- Category Carousel Navigation -->
        <div style="display: flex; gap: 8px; overflow-x: auto; margin-bottom: 30px; padding-bottom: 10px; -webkit-overflow-scrolling: touch;">
            <button class="store-cat-btn active" onclick="filterStoreCategory('all')" 
                    style="padding: 10px 20px; background: #111; color: #fff; border: none; border-radius: 25px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                <i class="bi bi-grid-3x3"></i> All Products
            </button>
            <button class="store-cat-btn" onclick="filterStoreCategory('laptops')"
                    style="padding: 10px 20px; background: #f5f5f5; color: #666; border: 1px solid #e0e0e0; border-radius: 25px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                <i class="bi bi-laptop"></i> Laptops
            </button>
            <button class="store-cat-btn" onclick="filterStoreCategory('books')"
                    style="padding: 10px 20px; background: #f5f5f5; color: #666; border: 1px solid #e0e0e0; border-radius: 25px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                <i class="bi bi-book"></i> Books
            </button>
            <button class="store-cat-btn" onclick="filterStoreCategory('gadgets')"
                    style="padding: 10px 20px; background: #f5f5f5; color: #666; border: 1px solid #e0e0e0; border-radius: 25px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                <i class="bi bi-cpu"></i> Gadgets
            </button>
            <button class="store-cat-btn" onclick="filterStoreCategory('tools')"
                    style="padding: 10px 20px; background: #f5f5f5; color: #666; border: 1px solid #e0e0e0; border-radius: 25px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; transition: all 0.2s;">
                <i class="bi bi-tools"></i> Dev Tools
            </button>
        </div>

        <!-- Products Grid -->
        <div id="storeProductsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; margin-bottom: 30px;">
            
            <!-- Sample Product Card (Myntra Style - Black & White) -->
            <div class="store-product-card" data-category="laptops" 
                 style="background: #fff; border: 1px solid #ececec; border-radius: 12px; overflow: hidden; transition: all 0.3s; cursor: pointer;"
                 onmouseover="this.style.borderColor='#111'; this.style.transform='translateY(-5px)'; this.style.boxShadow='0 10px 30px rgba(0,0,0,0.1)'"
                 onmouseout="this.style.borderColor='#ececec'; this.style.transform='translateY(0)'; this.style.boxShadow=''">
                
                <!-- Product Image -->
                <div style="position: relative; padding-top: 100%; background: #f9f9f9; overflow: hidden;">
                    <img src="https://via.placeholder.com/300x300/111111/FFFFFF?text=Laptop" 
                         style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; object-fit: cover;">
                    
                    <!-- Discount Badge -->
                    <div style="position: absolute; top: 10px; left: 10px; background: #111; color: #fff; padding: 4px 10px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">
                        20% OFF
                    </div>
                    
                    <!-- Wishlist/Favorite -->
                    <button style="position: absolute; top: 10px; right: 10px; background: #fff; border: none; width: 32px; height: 32px; border-radius: 50%; display: grid; place-items: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <i class="bi bi-heart" style="font-size: 0.9rem; color: #111;"></i>
                    </button>
                </div>
                
                <!-- Product Info -->
                <div style="padding: 14px;">
                    <!-- Brand -->
                    <div style="font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px;">
                        Apple
                    </div>
                    
                    <!-- Product Name -->
                    <h3 style="font-size: 0.9rem; font-weight: 600; color: #111; margin-bottom: 8px; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        MacBook Air M2 Chip 13.6" Laptop
                    </h3>
                    
                    <!-- Price -->
                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                        <span style="font-size: 1.1rem; font-weight: 800; color: #111;">₹99,990</span>
                        <span style="font-size: 0.85rem; color: #999; text-decoration: line-through;">₹1,24,990</span>
                    </div>
                    
                    <!-- Rating -->
                    <div style="display: flex; align-items: center; gap: 4px; margin-bottom: 12px;">
                        <div style="display: flex; gap: 2px;">
                            <i class="bi bi-star-fill" style="font-size: 0.75rem; color: #111;"></i>
                            <i class="bi bi-star-fill" style="font-size: 0.75rem; color: #111;"></i>
                            <i class="bi bi-star-fill" style="font-size: 0.75rem; color: #111;"></i>
                            <i class="bi bi-star-fill" style="font-size: 0.75rem; color: #111;"></i>
                            <i class="bi bi-star-half" style="font-size: 0.75rem; color: #111;"></i>
                        </div>
                        <span style="font-size: 0.75rem; color: #666; font-weight: 600;">4.5</span>
                        <span style="font-size: 0.7rem; color: #999;">| 234 reviews</span>
                    </div>
                    
                    <!-- CTA Button -->
                    <button style="width: 100%; padding: 10px; background: #111; color: #fff; border: none; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 6px;"
                            onmouseover="this.style.background='#333'"
                            onmouseout="this.style.background='#111'">
                        <i class="bi bi-cart-plus"></i> View Product
                    </button>
                </div>
            </div>

            <!-- Add more product cards here (repeat above structure) -->
            
        </div>

        <!-- View All Button -->
        <div style="text-align: center;">
            <a href="/student/store" 
               style="display: inline-flex; align-items: center; gap: 8px; padding: 14px 32px; background: #111; color: #fff; text-decoration: none; border-radius: 30px; font-size: 0.95rem; font-weight: 700; transition: all 0.2s;"
               onmouseover="this.style.background='#333'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='#111'; this.style.transform='translateY(0)'">
                <i class="bi bi-shop"></i> Explore All Products
            </a>
        </div>
    </div>
</section>

<script>
// Category Filter Function
function filterStoreCategory(category) {
    const products = document.querySelectorAll('.store-product-card');
    const buttons = document.querySelectorAll('.store-cat-btn');
    
    // Update button states
    buttons.forEach(btn => {
        btn.style.background = '#f5f5f5';
        btn.style.color = '#666';
        btn.style.border = '1px solid #e0e0e0';
        btn.classList.remove('active');
    });
    event.target.style.background = '#111';
    event.target.style.color = '#fff';
    event.target.style.border = 'none';
    event.target.classList.add('active');
    
    // Filter products
    products.forEach(product => {
        if (category === 'all') {
            product.style.display = 'block';
        } else {
            if (product.dataset.category === category) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        }
    });
}
</script>
```

### 3. Myntra-Style Features Included:

✅ **Product Card Design:**
- Square image (1:1 ratio)
- Brand name at top
- Product name (2 lines max)
- Original + discounted price
- Star rating with count
- Discount badge
- Wishlist button
- Clean black & white theme

✅ **Responsive:**
- Grid adapts: 4 cols (desktop) → 2 cols (tablet) → 1 col (mobile)
- Horizontal scroll for categories on mobile
- Touch-friendly buttons

✅ **Interactions:**
- Hover effects on cards
- Category filtering
- Smooth transitions

### 4. Required Backend Files (To Be Created):

**Models:**
- `src/Models/StoreProduct.php`
- `src/Models/StoreCategory.php`

**Controllers:**
- `src/Controllers/Student/StoreController.php` (browse, product detail)
- `src/Controllers/Admin/StoreProductController.php` (CRUD)

**Views:**
- `views/student/store/index.php` (full store page)
- `views/student/store/product.php` (product details)
- `views/admin/store/products/index.php` (admin list)
- `views/admin/store/products/form.php` (admin add/edit)

### 5. Sample Product Data:

```sql
INSERT INTO store_products (category_id, name, slug, brand, price, original_price, discount_percent, images, primary_image, short_description, affiliate_url, is_in_stock, status, featured) VALUES
(1, 'MacBook Air M2 13.6" Laptop', 'macbook-air-m2', 'Apple', 99990, 124990, 20, '["https://example.com/macbook1.jpg"]', 'https://example.com/macbook1.jpg', 'Lightweight and powerful laptop for coding', 'https://amazon.in/...', 1, 'active', 1),
(2, 'Clean Code by Robert Martin', 'clean-code-book', 'Pearson', 599, 899, 33, '["https://example.com/book1.jpg"]', 'https://example.com/book1.jpg', 'Essential book for every developer', 'https://amazon.in/...', 1, 'active', 1);
```

### 6. Admin Panel Integration:

Add menu item in admin sidebar:
```php
<li><a href="/admin/store/products"><i class="bi bi-shop"></i> Store Products</a></li>
<li><a href="/admin/store/categories"><i class="bi bi-tags"></i> Store Categories</a></li>
```

### 7. Image Upload:

Products need image upload functionality:
- Primary image
- Multiple images (gallery)
- Store in: `uploads/store/products/`

---

## Mobile Responsive CSS:

```css
@media (max-width: 768px) {
    #storeProductsGrid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 12px !important;
    }
    
    .store-section h2 {
        font-size: 1.6rem !important;
    }
}

@media (max-width: 480px) {
    #storeProductsGrid {
        grid-template-columns: 1fr !important;
    }
}
```

---

**Next Steps:**
1. ✅ Database schema created
2. ⏳ Add store section to index.html
3. ⏳ Create backend controllers & models
4. ⏳ Create admin interface
5. ⏳ Add sample products
6. ⏳ Create student store page

**Theme:** Black & White ✅
**Style:** Myntra-inspired ✅
**Responsive:** Yes ✅

Let me know when you want me to proceed with the next steps!
