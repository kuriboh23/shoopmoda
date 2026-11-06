# Déploiement (hébergement mutualisé / InfinityFree)

Le site s'envoie **tel quel dans `htdocs/`** : `index.php`, `config.php`,
`assets/` et `uploads/` sont côte à côte, à la racine du domaine.

Il n'y a **ni `.env` à créer, ni `APP_URL` à renseigner, ni `.htaccess` à
envoyer**. Tous les réglages sont dans `config.php`, et les liens du site sont
calculés à partir de la requête : ils fonctionnent à la racine du domaine
comme dans un sous-dossier.

---

## Pourquoi la version en ligne n'affichait rien

Trois causes, toutes propres à l'hébergement mutualisé :

1. **Un appel à une méthode inexistante** (`Env::filterBool()`) dans l'ancien
   `app/config/app.php` : l'erreur était fatale dès le chargement de la
   configuration, donc sur *toutes* les pages. La configuration est maintenant
   un simple tableau dans `config.php`, sans dépendance.
2. **`putenv()` est désactivé sur InfinityFree.** L'ancien lecteur de `.env`
   l'appelait au démarrage. Il n'existe plus : plus de `.env` du tout.
3. **Le `.htaccess` contenait des directives que beaucoup d'hébergeurs
   refusent** (`Options`, `Require`, `ExpiresByType`) : Apache répondait une
   erreur 500 sur *chaque* page, ce qui donne une page blanche. Le fichier a
   été supprimé : le site n'en a plus besoin.

---

## InfinityFree, pas à pas

### 1. Créer la base

Panneau InfinityFree → **MySQL Databases** → *Create Database*.

Ouvrez ensuite `config.php` (section **C. DATABASE**) et recopiez les valeurs
affichées dans **Current Databases** :

| Réglage dans `config.php` | Où le trouver dans le panneau | Exemple |
|---|---|---|
| `db_host` | MySQL Hostname | `sql123.infinityfree.com` |
| `db_name` | Nom de la base | `if0_12345678_shoopmoda` |
| `db_user` | Username | `if0_12345678` |
| `db_pass` | mot de passe du compte (vPanel/FTP) | `••••••` |

> Le préfixe est `if0_` avec un **zéro**, pas la lettre `o`.
> `db_port` reste `3306`, `db_charset` reste `utf8mb4`.

### 2. Importer les tables

phpMyAdmin (depuis le panneau) → sélectionner la base → **Importer** →
`database/shoopmoda.sql`. Le fichier ne contient pas de `CREATE DATABASE`, il
s'importe donc directement dans la base choisie.

Contrôle : la base doit contenir `categories`, `products`, `product_images`,
`promo_codes`, `settings`.

> La base est joignable **uniquement depuis votre hébergement**. Depuis votre
> PC, seule phpMyAdmin fonctionne : c'est normal.

### 3. Envoyer les fichiers

Uploadez **le contenu** du projet dans `htdocs/` (FTP ou gestionnaire de
fichiers) : `htdocs/index.php` doit exister, et **pas**
`htdocs/shoopmoda/index.php`.

### 4. Régler `config.php`

`config.php` est un fichier normal (pas de point devant) : il est visible dans
le gestionnaire de fichiers en ligne, on l'ouvre et on l'édite directement sur
le serveur.

Minimum à vérifier :

```php
'app_name'        => 'Shoop Moda Style',
'whatsapp_number' => '212725605520',   // chiffres uniquement, sans +
'admin_path'      => 'gestion',        // changez-le : /gestion
'admin_password'  => '…',              // changez-le aussi

'db_host' => 'sql123.infinityfree.com',
'db_port' => 3306,
'db_name' => 'if0_12345678_shoopmoda',
'db_user' => 'if0_12345678',
'db_pass' => 'votre_mot_de_passe',
```

Les autres lignes de la section A (nom, devise, langue, Instagram) et la
section D (textes de la page d'accueil) se modifient de la même façon : on
change seulement le texte entre les guillemets.

### 5. Ouvrir le site

`https://votre-domaine/` doit afficher la page d'accueil avec les styles et les
images. Les liens ressemblent à `https://votre-domaine/panier` : ce sont les
règles de réécriture du `.htaccess` fourni qui les rendent possibles.

Vérifiez que le `.htaccess` est bien arrivé sur le serveur : c'est le seul
fichier dont le nom commence par un point, donc celui que les clients FTP
cachent. S'il manque, le site fonctionne quand même, avec des liens en
`/?p=/panier` : la bascule est automatique.

Test rapide, dans l'ordre :

1. l'accueil s'affiche **avec** les images et les couleurs ;
2. `/produits` s'ouvre depuis le menu et liste les produits ;
3. un produit s'ajoute au panier, le panier affiche le bon total ;
4. « Commander » ouvre WhatsApp avec le récapitulatif ;
5. `admin_path` + `/connexion` ouvre l'écran de connexion admin.

### 6. Terminer

1. `'app_debug' => false` dans `config.php` (c'est déjà la valeur par défaut).
2. Activer le SSL gratuit dans le panneau, puis mettre en place la redirection
   HTTPS — sur InfinityFree : panneau → *SSL/TLS* → activer, puis
   *Redirect HTTP to HTTPS*.
3. Vérifier que `admin_path` et `admin_password` ont bien été changés.

### 7. Version PHP

Panneau → *PHP Configuration* : choisir **PHP 8.2** (8.1 minimum).
L'application utilise `str_contains`, `mixed` et `match` (PHP 8+).

---

## Permissions

| Dossier | Droit |
|---|---|
| `storage/`, `storage/logs/`, `storage/cache/` | `755` (ou `775`) |
| `uploads/`, `uploads/products/` | `755` (ou `775`) |
| tout le reste | `644` fichiers / `755` dossiers |

L'application crée `storage/logs` et `storage/cache` au besoin ; si
l'hébergeur bloque la création, seuls le cache et les logs fichier sont
perdus, le site continue de fonctionner.

---

## Dépannage

| Symptôme | Cause probable |
|---|---|
| Page blanche / 500 générique | Erreur PHP masquée : `'app_debug' => true` dans `config.php`, recharger, lire le message |
| `Connexion à la base de données impossible` | L'erreur PDO exacte s'affiche sur la page dès que `'app_debug' => true` |
| `[2002] No such file or directory` | `db_host` vaut `localhost` → mettre le MySQL Hostname du panneau |
| `[1045] Access denied` | `db_user` ou `db_pass` incorrect (préfixe `if0_`, zéro et non `o`) |
| `[1044] Access denied ... to database` | `db_name` ne correspond pas exactement au nom affiché |
| `[1049] Unknown database` | La base n'existe pas encore / nom mal recopié |
| Table `... doesn't exist` | Le dump n'a pas été importé dans la bonne base |
| Site sans CSS ni images | Les fichiers ne sont pas tous dans `htdocs/` (dossier `assets/` manquant) |
| L'accueil marche, les autres liens sont en 404 | `'pretty_urls' => true` sans `.htaccess` → repasser à `false` |
| Les liens commencent par `/shoopmoda/` | Le site est encore dans un sous-dossier : déplacez son **contenu** dans `htdocs/` |
| 403 sur tout le site | Pas d'`index.php` à la racine de `htdocs/` |
| 404 sur `/gestion` | `admin_path` a été changé : utilisez la nouvelle valeur |

Le journal applicatif se trouve dans `storage/logs/app-AAAA-MM-JJ.log`.

---

## URLs propres : comment ça marche et quoi faire si ça casse

Les URLs propres (`/panier` au lieu de `/?p=/panier`) viennent du fichier
`.htaccess` livré à la racine du site. Son contenu, en entier :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On

    RewriteRule ^(app|storage|database|tools)(/|$) - [F]
    RewriteRule ^config\.php$ - [F]

    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [L]
</IfModule>
```

- La ligne `[L]` envoie toute adresse inconnue à `index.php`, qui lit l'URL.
- Les deux `[F]` renvoient 403 sur le code, les journaux et le dump SQL :
  sans elles, `config.php` (mot de passe de la base) serait lisible par
  n'importe qui.
- `<IfModule mod_rewrite.c>` évite une 500 si le module n'existe pas.

Trois cas possibles :

| Ce que vous voyez | Cause | Solution |
|---|---|---|
| Liens en `/?p=/panier` | `.htaccess` absent (FTP qui cache les fichiers en point) | L'envoyer, ou garder ce mode : il fonctionne partout |
| 404 sur **toutes** les pages | `.htaccess` présent mais `mod_rewrite` inactif chez l'hébergeur | `'pretty_urls' => false` dans `config.php` |
| 403 sur tout le site | Règles de blocage appliquées trop largement | Supprimer `.htaccess` : le site repart en `/?p=` |

Dans les trois cas, une seule ligne de `config.php` suffit à revenir aux liens
`/?p=/panier`, qui ne demandent **aucune** configuration serveur.
