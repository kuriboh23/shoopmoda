shoopmoda/
├── public/                          # ← Document Root (point domain here)
│   ├── index.php
│   ├── .htaccess
│   ├── assets/
│   │   ├── css/
│   │   │   ├── app.css              # Your main styles
│   │   │   └── admin.css
│   │   ├── js/
│   │   │   ├── app.js               # Main JS (cart, wishlist, etc.)
│   │   │   ├── cart.js
│   │   │   └── admin.js
│   │   ├── images/
│   │   │   ├── products/            # Uploaded product images
│   │   │   ├── icons/
│   │   │   └── placeholders/
│   │   └── fonts/
│   ├── uploads/                     # Secure uploads (outside public if possible)
│   └── favicon.ico
│
├── app/                             # Application logic
│   ├── config/
│   │   ├── database.php
│   │   ├── app.php                  # Site settings, WhatsApp number, etc.
│   │   └── constants.php
│   ├── controllers/
│   │   ├── HomeController.php
│   │   ├── ProductController.php
│   │   ├── CartController.php
│   │   ├── WishlistController.php
│   │   ├── SearchController.php
│   │   ├── CheckoutController.php
│   │   └── AdminController.php
│   ├── models/
│   │   ├── Product.php
│   │   ├── Category.php
│   │   ├── Setting.php
│   │   └── Order.php
│   ├── helpers/
│   │   ├── functions.php            # Global helpers
│   │   ├── cart.php                 # Cookie cart helpers
│   │   ├── wishlist.php
│   │   └── whatsapp.php             # Message generator
│   └── views/
│       ├── layouts/
│       │   ├── main.php
│       │   └── admin.php
│       ├── partials/
│       │   ├── header.php
│       │   ├── footer.php
│       │   ├── bottom-nav.php
│       │   ├── product-card.php
│       │   └── flash-sale.php
│       ├── home.php
│       ├── product.php
│       ├── category.php
│       ├── search.php
│       ├── wishlist.php
│       ├── cart.php
│       ├── checkout.php
│       └── admin/
│           ├── dashboard.php
│           ├── products.php
│           ├── product-form.php
│           ├── categories.php
│           └── settings.php
│
├── storage/                         # Not publicly accessible
│   ├── cache/
│   └── logs/
│
├── config.php                       # ⚙️ ALL settings (shop + database)
├── .gitignore
├── composer.json                    # Optional
└── README.md