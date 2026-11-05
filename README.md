# Shoop Moda Style

Boutique e-commerce **PHP 8 + MySQL** pour une marque marocaine de mode turque
(femme & enfant). Panier, favoris et recherches récentes en cookies/localStorage,
commande finale via **WhatsApp** — sans compte client ni paiement en ligne.

---

## Stack

| Couche | Choix |
|---|---|
| Backend | PHP 8.1+ vanilla, PDO, MVC léger maison |
| Base | MySQL 8 / MariaDB (utf8mb4) |
| Front | HTML5 sémantique, CSS moderne (Flexbox/Grid), JS vanilla |
| Assets | Système de polices natif, icônes SVG inline, aucun CDN |
| Hébergement cible | Apache mutualisé (Namecheap) |

Aucune dépendance Composer. Aucun framework JS. Aucun Tailwind/Swiper/Alpine.

---

## Structure

```
shoopmoda/
├── public/                 ← DocumentRoot du domaine
│   ├── index.php           Front controller (seul point d'entrée)
│   ├── .htaccess           URLs propres, cache, compression, sécurité
│   ├── assets/             css / js / images / fonts
│   ├── uploads/            Images produits (exécution PHP bloquée)
│   └── favicon.svg
├── app/
│   ├── bootstrap.php       Chemins, autoloader, .env, erreurs, en-têtes
│   ├── routes.php          Table des routes (URLs françaises)
│   ├── config/             app.php, database.php
│   ├── core/               Env, Router, View, Database
│   ├── controllers/
│   ├── models/
│   ├── helpers/            functions.php, icons.php
│   └── views/              layouts, partials, pages, errors
├── storage/                cache + logs (jamais accessible publiquement)
├── tools/                  scripts de développement
├── .env                    Secrets locaux (non versionné)
└── .env.example            Modèle à copier
```

---

## Installation locale (XAMPP)

1. Copier `.env.example` vers `.env` et remplir `APP_URL`, `DB_*` et
   `COOKIE_SECRET` (`php -r "echo bin2hex(random_bytes(32));"`).
2. Importer la base (le fichier `database/schema.sql` est livré en phase 2) :
   ```bash
   mysql -u root -p -e "CREATE DATABASE shoopmoda CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   mysql -u root -p shoopmoda < database/schema.sql
   ```
3. Ouvrir `http://localhost/shoopmoda/public/`.
   Le DocumentRoot du site doit être le dossier `public/`, jamais la racine.

### Serveur de développement intégré (sans Apache)

```bash
php -S 127.0.0.1:8080 -t public tools/dev-router.php
```

Avec ce serveur, renseigner `APP_URL=http://127.0.0.1:8080` dans `.env`
(sinon les URLs d'assets pointent vers l'URL Apache).

---

## Conventions

- **Sécurité** : PDO préparé, échappement systématique via `e()`, cookies
  `HttpOnly; Secure; SameSite=Lax` signés HMAC, CSRF sur tout POST, en-têtes
  CSP/HSTS, uploads validés et convertis en WebP.
- **Prix** : jamais stockés dans un cookie. Le cookie ne contient que
  `id / taille / couleur / quantité` ; les prix sont toujours relus en base.
- **Navigation** : header + menu latéral gauche. Pas de barre de navigation basse.
- **Français** : toute l'interface et les messages WhatsApp sont en français.
- **Devise** : DH, formatée par `money()`.

---

## Avancement

| Phase | Contenu | Statut |
|---|---|---|
| 1 | Fondations : bootstrap, routeur, layout, design system, header + drawer, footer, 404 | ✅ |
| 2 | Modèles, helpers cookies, données de démonstration | ⏳ |
| 3 | Accueil, catégories, produit, recherche | ⏳ |
| 4 | Panier et favoris | ⏳ |
| 5 | Checkout + WhatsApp | ⏳ |
| 6 | Administration | ⏳ |
| 7 | Durcissement et performance | ⏳ |
| 8 | Déploiement | ⏳ |
