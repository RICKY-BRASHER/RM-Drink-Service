# Mise en production

## Hébergement recommandé

Pour héberger gratuitement une première version, Oracle Cloud Free Tier propose des machines virtuelles « Always Free » sur lesquelles Laravel, PHP et MySQL/MariaDB peuvent être installés. C'est une VM à administrer soi-même : il faut configurer le pare-feu, HTTPS, les mises à jour et les sauvegardes. La disponibilité des ressources gratuites dépend de la région et Oracle demande une carte pour vérifier l'identité. Le gratuit n'offre pas de garantie de disponibilité; pour les données réelles de stock et de caisse, prévoir un plan d'hébergement payant et des sauvegardes externes.

Render est pratique pour une démonstration, mais son offre gratuite n'est pas destinée à la production : le système de fichiers du service est éphémère, et les bases Postgres gratuites expirent après 30 jours. Ne pas y conserver la base SQLite, les images ou les factures de l'entreprise.

Liens officiels :

- [Oracle Cloud Free Tier](https://www.oracle.com/cloud/free/)
- [Limites des services gratuits Render](https://render.com/docs/free)

## Prérequis serveur

- PHP 8.4 ou 8.5 avec `pdo_mysql`, `mbstring`, `openssl`, `xml`, `curl`, `fileinfo`, `ctype`, `tokenizer` et `zip`.
- Composer 2, Node.js 22 et npm pour compiler les ressources.
- MySQL/MariaDB avec une base et un utilisateur dédiés à l'application.
- Nginx ou Apache configuré avec `public/` comme unique racine Web.
- HTTPS obligatoire. Ne jamais exposer le dossier racine du dépôt comme racine Web.
- Un disque persistant pour `storage/app/public` (avatars, images et factures) et des sauvegardes régulières de la base et de ces fichiers.

## Variables d'environnement

Créer le fichier `.env` directement sur le serveur (ne pas le committer) et renseigner au minimum :

```dotenv
APP_NAME="RM Drink Service"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://votre-domaine
APP_TIMEZONE=Africa/Douala
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr

LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rm_drink
DB_USERNAME=rm_drink_user
DB_PASSWORD=un-mot-de-passe-long-et-unique

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
CACHE_STORE=file
QUEUE_CONNECTION=sync
FILESYSTEM_DISK=public
```

Adapter le fuseau horaire au pays réel du dépôt. Générer `APP_KEY` une seule fois avec `php artisan key:generate`, puis conserver exactement cette clé lors des déploiements suivants. Une nouvelle clé invaliderait les cookies et les données chiffrées. Ne pas publier de secrets dans Git ou dans les journaux de déploiement.

## Déploiement d'une version

À exécuter dans le répertoire de l'application, une fois la base créée et le `.env` configuré :

```bash
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Les migrations doivent être exécutées explicitement après une sauvegarde et pendant une fenêtre de déploiement contrôlée. Les commandes Composer de mise à jour ne les lancent plus automatiquement. Ne pas utiliser `composer update` en production : déployer le `composer.lock` testé en intégration continue.

Configurer le serveur Web pour envoyer les requêtes vers `public/index.php`, et accorder au compte PHP l'écriture sur `storage/` et `bootstrap/cache/`. Après déploiement, vérifier `https://votre-domaine/up`, la connexion administrateur, la création d'une vente de test, son annulation, et la génération/lecture d'une facture. Supprimer ensuite toutes les données de test.

## Avant d'y mettre les données réelles

- Faire un essai complet sur une copie de la base avec le même moteur MySQL/MariaDB que la production; les tests locaux utilisent SQLite et ne remplacent pas cette vérification.
- Sauvegarder la base et les fichiers uploadés hors de la VM, puis tester une restauration.
- Vérifier les règles réseau : seuls HTTP/HTTPS sont publics; SSH doit être limité à l'administrateur.
- Créer des comptes administrateurs avec des mots de passe uniques et vérifier les rôles et dépôts.
- Le dépôt contient des données de développement/test et des fichiers stockés localement. Ne pas les déployer tels quels comme données réelles; préparer et vérifier séparément l'import des données utiles.

La configuration Vercel existante n'est pas recommandée pour cette application de gestion : SQLite et le stockage local des factures/images ne sont pas persistants sur une fonction sans disque durable.
