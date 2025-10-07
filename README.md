# 🧙‍♂️ Yu-Gi-Oh! – Collection & Deck Builder (Laravel)

Application Laravel pour gérer une collection de cartes Yu-Gi-Oh! et construire des decks
(quantités disponibles par utilisateur, filtres, recherche, pagination dynamique, authentification).

---

## 1) Prérequis (avec commandes d’installation)

### Windows (Chocolatey)

```powershell
choco install php composer nodejs-lts git sqlite -y
```

### macOS (Homebrew)

```bash
brew install php composer node git sqlite
```

### Ubuntu / Debian

```bash
sudo apt update
sudo apt install -y php php-cli php-mbstring php-xml php-sqlite3 php-curl unzip \
                    composer nodejs npm git sqlite3
```

**Versions requises** : PHP ≥ 8.2, Composer, Node.js ≥ 18, NPM, SQLite ou MySQL.

---

## 2) Installation des dépendances du projet

```bash
composer install
npm install
```

---

## 3) Fichier d’environnement `.env`

Créez votre fichier :

```bash
cp .env.example .env
```

Puis générez la clé de l’application :

```bash
php artisan key:generate
```

---

## 4) Base de données

### Option A – SQLite (recommandée)

Modifiez `.env` :

```env
DB_CONNECTION=sqlite
DB_DATABASE=${APP_PATH}/database/database.sqlite
```

Créez le fichier :

```bash
touch database/database.sqlite
```

### Option B – MySQL

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=yugioh_collection
DB_USERNAME=root
DB_PASSWORD=
```

Créez la base :

```sql
CREATE DATABASE yugioh_collection CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

---

## 5) Migration & liens de stockage

```bash
php artisan migrate
php artisan storage:link
```

---

## 6) Lancer les serveurs

### Terminal 1 (Laravel)

```bash
php artisan serve
```

👉 [http://localhost:8000](http://localhost:8000)

### Terminal 2 (Vite / Front-end)

```bash
npm run dev
```

> Pour un build de production :
>
> ```bash
> npm run build
> ```

---

## 7) Créer un compte utilisateur

Ouvrez le site et cliquez sur **S’inscrire**.

Sinon, en CLI :

```bash
php artisan tinker
```

```php
\App\Models\User::create([
  'name' => 'Admin',
  'email' => 'admin@example.com',
  'password' => bcrypt('password'),
]);
```

---

## 8) Commandes utiles

```bash
php artisan serve                # Lance le serveur Laravel
npm run dev                      # Compile les assets (dev)
npm run build                    # Compile les assets (prod)
php artisan route:list           # Liste les routes
php artisan optimize:clear       # Vide les caches
php artisan migrate:fresh        # Réinitialise la base
```

---

## 9) Fonctionnalités

✅ Authentification complète (Breeze)
✅ Gestion de collection par utilisateur
✅ Filtres et recherche (type, niveau, ATK/DEF, rareté)
✅ Création / édition de decks avec :

* Quantité disponible = collection – cartes déjà utilisées
* Pagination dynamique (10 cartes/page)
* Conservation des quantités saisies entre filtres/pages
  ✅ Validation 40–60 cartes / deck
  ✅ Interface responsive (Tailwind + Vite)

---

## 10) Dépannage rapide

| Problème                    | Cause               | Solution                                        |
| --------------------------- | ------------------- | ----------------------------------------------- |
| `419 Page Expired`          | Token CSRF invalide | Recharger la page, relancer `php artisan serve` |
| `SQLSTATE[HY000]`           | Mauvaise config DB  | Vérifier `.env`, exécuter `php artisan migrate` |
| Assets non chargés          | Vite non lancé      | `npm run dev`                                   |
| Modifs non prises en compte | Cache Laravel       | `php artisan optimize:clear`                    |

---

## 11) Déploiement en production

```bash
npm run build
php artisan migrate --force
php artisan optimize
```

Configurer le serveur web pour pointer vers `public/`.

---

## 12) Licence

Projet open source sous licence [MIT](https://opensource.org/licenses/MIT).

---

🎴 *Projet Laravel Yu-Gi-Oh! développé pour la gestion complète des cartes et decks, avec expérience util
