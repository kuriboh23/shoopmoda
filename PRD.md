**PRD: Shoop Moda Style – Mobile-First E-Commerce Website**

**Document Version:** 1.0  
**Date:** 5 November 2026  
**Status:** Ready for Development  
**Prepared for:** shoop.moda_style  
**Target Platform:** Namescheap Shared Hosting (PHP + MySQL)  
**Version Control:** GitHub  

---

### 1. Executive Summary

Shoop Moda Style is a Moroccan-based Instagram store specializing in Turkish luxury clothing for women and children, with a strong focus on trendy pajamas and fashion pieces. The goal of this project is to create a fast, elegant, mobile-first e-commerce website that converts Instagram traffic into WhatsApp orders.

The website will feature a modern, clean design inspired by the provided UI screenshots. It will avoid user accounts and payment gateways entirely. Cart, wishlist, and recent search data will be stored primarily in cookies/localStorage to minimize database load and ensure excellent performance on shared hosting. Checkout will generate a pre-filled WhatsApp message sent to **0725605520**.

The final product must be beautiful, extremely fast, secure, and easy for the store owner to manage.

---

### 2. Goals & Objectives

**Primary Goals**
- Deliver a premium shopping experience that matches the visual quality of the provided screenshots.
- Maximize conversion from product view → WhatsApp order.
- Keep the website extremely lightweight and lag-free on shared hosting.
- Minimize database usage through heavy reliance on cookies and localStorage.
- Enable the owner to manage products easily without technical knowledge.

**Success Criteria**
- Lighthouse Mobile Performance ≥ 90
- Average page load < 2 seconds on 4G
- High WhatsApp order conversion rate
- Zero critical security vulnerabilities
- Fully responsive from 320px width upward

---

### 3. Target Audience & User Personas

**Primary Users**
- Moroccan women aged 18–40 interested in Turkish fashion
- Mothers buying for themselves and children
- Instagram users discovering the brand via @shoop.moda_style

**Secondary Users**
- Store owner / admin (managing products and viewing basic order history)

**Key User Needs**
- Fast browsing on mobile data
- Easy product discovery and filtering
- Simple and trustworthy checkout via WhatsApp
- Ability to save favorites (wishlist) without creating an account

---

### 4. Scope

**In Scope (Version 1.0)**
- Fully responsive mobile-first storefront
- Home, Category, Product Detail, Wishlist, Cart, Checkout, Search pages
- Cookie/localStorage-based cart, wishlist, and recent searches
- WhatsApp order generation
- Simple admin panel for product & category management
- Flash Sale with countdown
- Basic promo code support
- SEO fundamentals and Open Graph tags

**Out of Scope (Version 1.0)**
- User registration / login system
- Online payment gateways (Stripe, PayPal, CMI, etc.)
- Advanced filtering (price range, multiple attributes)
- Multi-language system (French primary)
- Real-time inventory synchronization
- Email notifications
- Complex promotion engine
- Customer reviews system (static ratings only)

---

### 5. Functional Requirements

#### 5.1 Home Page
- Hero banner carousel with promotional messages (e.g. “New Collection – Discount 50% for the first transaction”) and “Shop Now” CTA
- Horizontal scrollable category icons (T-Shirt, Pant, Dress, Jacket, etc.)
- Flash Sale section with live countdown timer and filter chips (All, Newest, Popular, Man, Woman)
- Product grid (2 columns on mobile)
- Sticky bottom navigation bar

#### 5.2 Product Listing & Search
- Category pages and search results pages
- Product cards showing: image, title, price, star rating, wishlist heart
- Filter chips and basic sorting
- “X found” result count
- Recent searches list with Clear All option (stored in cookies)

#### 5.3 Product Detail Page
- Swipeable image gallery with thumbnails
- Product title, rating, price
- Short description with “Read more” expand
- Size selector (S, M, L, XL, XXL, XXXL)
- Color selector
- Quantity selector
- Sticky bottom bar with total price and “Add to Cart” button
- Wishlist toggle in header

#### 5.4 Wishlist
- Filterable by category chips
- Same product card layout as listings
- Fully managed via cookies/localStorage

#### 5.5 Cart
- List of items with image, name, selected size, price, quantity controls (±), and remove button
- Promo code input field
- Price summary: Sub-total, Delivery Fee, Discount, Total Cost
- “Proceed to Checkout” button

#### 5.6 Checkout
- Shipping address form (Name, Phone, City, Full Address) – pre-filled from cookie if available
- Shipping type selection (Economy / Express) with estimated arrival date
- Order summary
- Final button generates WhatsApp message and redirects to `wa.me/212725605520`

#### 5.7 WhatsApp Order Message Format
The generated message must be clean and professional, including:
- Customer details
- Full address
- Shipping type
- Itemized list (product name, size, quantity, line total)
- Sub-total, delivery fee, discount, grand total
- Closing message

#### 5.8 Admin Panel (Simple)
- Password-protected access
- CRUD for products (title, description, price, sale price, category, sizes, colors, multiple images, stock, rating, flash sale flag)
- Category management
- Global settings: delivery fee, flash sale end datetime, promo codes
- Optional basic order log (if WhatsApp orders are manually recorded)

---

### 6. Non-Functional Requirements

**Performance**
- Mobile-first architecture
- Lazy loading of images
- WebP format preferred
- Minimal JavaScript
- Aggressive browser caching
- Database queries kept to absolute minimum

**Security**
- PDO prepared statements only
- XSS protection on all outputs
- CSRF protection on forms
- Secure cookie flags (HttpOnly, Secure, SameSite=Lax)
- Input validation and sanitization
- Rate limiting on sensitive actions
- Security headers via .htaccess
- Admin area protected and not publicly discoverable

**Usability & Accessibility**
- Excellent contrast ratios
- Semantic HTML
- Touch-friendly targets (minimum 44px)
- Keyboard navigable
- Clear visual feedback on all interactions

**Compatibility**
- Modern browsers (Chrome, Safari, Firefox, Edge – last 2 versions)
- Perfect rendering from 320px to large desktops

---

### 7. Design & UI Guidelines

**Color Palette (Mandatory)**
- Primary Brown: `#704F38`
- Dark Text / Primary Dark: `#1F2029`
- Medium Gray: `#797979`
- Light Background / Borders: `#EDEDED`
- White: `#FFFFFF`

**Typography**
- Clean modern sans-serif (Inter, Poppins, or system font stack)
- Strong visual hierarchy
- Excellent readability on mobile

**Design Principles**
- Soft shadows, rounded corners, generous white space
- Instagram-inspired product cards
- Consistent component library (buttons, chips, cards, inputs)
- Match the provided screenshots as closely as possible for layout and component style

**Key Screens to Match**
- Home (banner + categories + flash sale)
- Wishlist
- Cart
- Product Details
- Checkout
- Search (empty + results)

---

### 8. Technical Architecture

**Stack**
- Backend: PHP 8.x (vanilla, no heavy frameworks)
- Frontend: HTML5, modern CSS (Flexbox + Grid), Vanilla JavaScript
- Database: MySQL
- Hosting: Namescheap Shared Hosting
- Assets: Optimized images, minimal external dependencies

**Data Storage Strategy**
- **Cookies / localStorage**: Cart, Wishlist, Recent Searches, Last Shipping Address, Applied Promo Code
- **Database only**: Products, Categories, Global Settings, optional Order Log

**Key Technical Decisions**
- No session-based cart (cookie-based only)
- No user authentication system
- All dynamic data fetched via simple PHP includes or lightweight AJAX where necessary
- Clean URL structure via .htaccess

---

### 9. Data Model (High-Level)

**Core Tables**
- `categories` (id, name, slug, icon, sort_order)
- `products` (id, category_id, title, slug, description, price, sale_price, rating, stock, is_flash, created_at, updated_at)
- `product_images` (id, product_id, image_path, sort_order)
- `product_variants` or simple JSON fields for sizes/colors
- `settings` (key-value for delivery_fee, flash_end_time, etc.)
- `promo_codes` (optional)
- `orders` (optional – for admin history)

---

### 10. Assumptions & Constraints

- Hosting is Namescheap shared (limited resources → keep everything light)
- Owner is comfortable using a simple admin panel
- All product images will be optimized before upload
- Primary language is French
- WhatsApp number 0725605520 remains constant
- No legal requirement for online invoices or automated receipts in v1

---

### 11. Risks & Mitigations

| Risk | Impact | Mitigation |
|------|--------|----------|
| Shared hosting performance | High | Cookie-first architecture + aggressive optimization |
| Image weight | Medium | Enforce WebP + lazy loading + size limits |
| Cookie size limits | Medium | Keep cart/wishlist data compact |
| Admin usability | Medium | Extremely simple and clean admin UI |
| WhatsApp message formatting | Low | Thorough testing of URL encoding |

---

### 12. Success Metrics & KPIs

- Page Load Time (mobile) < 2s
- Lighthouse Performance ≥ 90
- WhatsApp order initiation rate
- Average products viewed per session
- Cart abandonment rate (before WhatsApp)
- Admin task completion time (add/edit product)

---

### 13. Future Considerations (Post v1)

- Full Arabic language support
- Customer accounts (optional)
- Advanced filters and search
- Inventory alerts
- Simple analytics dashboard
- Instagram Shop integration

---

### 14. Approval

This PRD defines the complete scope for Version 1.0 of the Shoop Moda Style e-commerce website.  

Any changes to scope after development begins must be formally reviewed.

---