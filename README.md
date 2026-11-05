# RM Drink Service

Application web interne de gestion d'une entreprise de distribution de boissons organisée en plusieurs dépôts. Elle centralise les stocks, les approvisionnements, les ventes, les paiements, les promotions et le suivi des livraisons et des casiers.

> **Périmètre actuel :** l'application est centrée sur l'administration et le personnel des dépôts. Le portail client et ses routes restent désactivés/commentés. Les clients sont néanmoins gérés par le personnel pour les ventes, les soldes et les opérations de caisse.

## Sommaire

- [Fonctionnalités](#fonctionnalités)
- [Rôles et accès](#rôles-et-accès)
- [Parcours opérationnels](#parcours-opérationnels)
- [Architecture et données](#architecture-et-données)
- [Technologies](#technologies)
- [Installation locale](#installation-locale)
- [Configuration](#configuration)
- [Tests et qualité](#tests-et-qualité)
- [Mise en production](#mise-en-production)
- [Sécurité et précautions](#sécurité-et-précautions)
- [État et limites connues](#état-et-limites-connues)

## Fonctionnalités

### Direction / administration

- Tableau de bord consolidé de l'activité du réseau et comparaison des dépôts.
- Création et gestion des dépôts : nom, adresse, horaires et état opérationnel.
- Gestion des employés et affectation à un dépôt.
- Vue transversale des références et des stocks de tous les dépôts.
- Gestion des produits : prix d'achat et de vente, format, fournisseur, dépôt et seuil d'alerte.
- Enregistrement d'approvisionnements et ajout des quantités au stock.
- Suivi des alertes de stock faible.
- Gestion des clients, de leur solde et des mouvements de caisse associés.
- Création, activation, désactivation et suppression des promotions.
- Consultation de l'historique, des transactions et des factures PDF.

### Personnel du dépôt

- Tableau de bord local : ventes, stocks, alertes et activité du dépôt.
- Gestion des clients et recharges de leur solde.
- Consultation des produits et du stock du dépôt affecté.
- Caisse : création d'un panier, ajout/suppression de lignes, paiement et génération de facture.
- Traitement des ventes mises en attente.
- Préparation et validation des sorties de livraison.
- Enregistrement des casiers rendus à la livraison ou dans un retour libre.
- Consultation de l'historique et des factures.

L'accès du personnel est lié à son dépôt. Le middleware du secrétariat contrôle les horaires et l'état opérationnel du dépôt.

### Promotions

Les promotions sont configurées par un administrateur et ciblent un dépôt et une liste de produits choisis.

1. **Quantité offerte** : après le seuil d'achat cumulé, une quantité cadeau est ajoutée au panier. Le produit offert est choisi parmi les produits de la sélection disposant du stock requis.
2. **Prix réduit à partir d'un seuil** : lorsque la quantité cumulée atteint le seuil, toutes les unités admissibles prennent le prix promotionnel.

Pour une réduction, les produits sélectionnés doivent avoir le même prix catalogue et le prix promotionnel doit être inférieur au prix courant. Les chevauchements de promotions sont contrôlés par produit et période. Le calcul du prix est effectué côté serveur; les prix envoyés par le navigateur ne font pas autorité. Le stock des cadeaux affiché dans l'administration est une **estimation** : les unités ne sont pas réservées avant la finalisation de la vente.

## Rôles et accès

| Rôle applicatif | Accès principal |
|---|---|
| `admin` | Console de direction, dépôts, personnel, stocks du réseau, approvisionnements, transactions et promotions. |
| `employe` avec le type `personnel` | Opérations du dépôt affecté : caisse, clients, commandes, produits, livraisons et historique. |
| `client` | Compte utilisé par les fonctionnalités internes (identification des ventes, solde, historique comptable). Le portail en ligne est actuellement en pause. |

La connexion professionnelle est disponible par adresse e-mail ou numéro de téléphone. Les contrôles d'accès sont appliqués par middleware et, pour les promotions, également par le contrôleur.

## Parcours opérationnels

### 1. Préparer l'entreprise

1. Créer/configurer chaque dépôt et ses horaires.
2. Créer les comptes employés et les affecter aux dépôts.
3. Enregistrer les produits avec leur fournisseur, leur format, leurs prix et leur seuil.
4. Réapprovisionner les produits du dépôt concerné.
5. Vérifier les stocks et les alertes.
6. Créer les comptes clients utilisés à la caisse, si nécessaire.

Les fournisseurs de base sont chargés par les migrations. Les dépôts de départ sont également créés par la migration initiale; il faut les adapter à l'entreprise avant exploitation.

### 2. Effectuer une vente

1. Le personnel ajoute les articles et quantités au panier.
2. Les prix catalogue et les offres admissibles sont calculés côté serveur.
3. Le personnel choisit le mode de paiement (`Espece`, `OM-MOMO` ou `Epargne`) et finalise la vente.
4. La finalisation contrôle le stock du dépôt, déduit les quantités et enregistre les mouvements nécessaires.
5. La facture peut être consultée/téléchargée au format PDF.

Le paiement par épargne nécessite un client identifié. Les ventes comptoir sans compte client ne peuvent pas créer de dette.

### 3. Traiter une vente en attente et la livraison

- Une vente `attente` conserve l'acompte réellement encaissé et réserve déjà le stock.
- Le personnel peut ensuite valider son encaissement ou refuser la vente. Le refus remet en stock les quantités réservées; la vente clôturée ne peut pas être refusée une seconde fois.
- Une facture payée (`payer`) apparaît dans le flux de livraison du dépôt. Sa validation enregistre les retours de casiers, marque la facture `livrer` et empêche le double enregistrement.
- Un retour de casiers peut aussi être saisi sans facture. L'identification d'un client est facultative.

### 4. Comprendre les statuts de vente

| Statut | Signification |
|---|---|
| `draft` | Panier en cours, non finalisé. |
| `attente` | Vente finalisée avec paiement partiel/en attente; stock réservé. |
| `payer` | Facture encaissée et disponible dans le flux de livraison. |
| `livrer` | Sortie du dépôt confirmée. |
| `annuler` | Vente en attente refusée/annulée. |
| `envoyer` | Statut historique prévu pour les commandes client en ligne; le portail client correspondant est actuellement désactivé. |

## Architecture et données

### Organisation du code

- `app/Http/Controllers/` : contrôleurs des pages, ventes, administration, authentification, promotions et livraisons.
- `app/Http/Middleware/` : autorisations par rôle et contrôle des accès du personnel.
- `app/Models/` : modèles Eloquent et relations.
- `app/Services/PromotionService.php` : calcul central des offres et synchronisation des lignes cadeaux/remisées.
- `routes/web.php` : routes web. Les routes du portail client y restent commentées.
- `resources/views/` : interfaces Blade, dont les espaces d'administration et du personnel.
- `resources/js/`, `resources/css/` : interface JavaScript, composants front-end et styles.
- `database/migrations/` : structure et données initiales de la base.
- `database/seeders/` et `database/factories/` : données de développement/test.
- `tests/` : tests unitaires et tests de parcours.

Le projet combine des pages Laravel Blade avec des ressources JavaScript/Vue et Inertia générées par Vite.

### Principales entités

| Entité/table | Rôle |
|---|---|
| `depots` | Sites de l'entreprise, horaires et état. |
| `users` | Comptes administrateur, employés et clients, affectation au dépôt et solde client. |
| `fournisseurs` | Brasseries/fournisseurs et compteur historique global de casiers vides. |
| `produits` | Références, format, prix, stock et seuil, rattachés à un fournisseur et à un dépôt. |
| `approvisionnements`, `detail_approvisionnements` | Historique des entrées de stock et lignes détaillées. |
| `ventes`, `detail_ventes` | Factures, lignes vendues, articles offerts et remises appliquées. |
| `promotions`, `promotion_produit` | Règles promotionnelles et produits ciblés. |
| `recharges` | Recharges et mouvements du solde client liés aux opérations de caisse. |
| `retours_casiers` | Retours associés à une facture ou retours libres. |
| `fournisseur_depot_stocks` | Stock de casiers vides ventilé par dépôt et fournisseur. |
| `alertes_stocks`, `notifications_systeme` | Alertes de stock et notifications internes. |

## Technologies

- Laravel 12, PHP `^8.4`.
- MySQL/MariaDB recommandé en production; SQLite est configuré pour le développement et les tests.
- Composer pour les dépendances PHP.
- Node.js et npm, Vite pour compiler les ressources.
- Blade, Bootstrap, jQuery/SweetAlert2; composants Vue/Inertia également présents.
- Dompdf pour les factures PDF.
- Pest/PHPUnit pour les tests et Laravel Pint pour le style PHP.

## Installation locale

### Prérequis

- PHP 8.4 ou 8.5 avec les extensions utilisées par Laravel et PDO SQLite.
- Composer 2.
- Node.js 22 et npm.
- Git.

### Installation rapide

```bash
git clone <URL_DU_DEPOT>
cd RM-Drink-Service
composer run setup
```

Le script `setup` installe les dépendances, crée `.env` depuis `.env.example` s'il n'existe pas, génère la clé, applique les migrations et construit les ressources front-end. Vérifier que la configuration locale utilise une base SQLite avant de lancer ce script.

Pour lancer les serveurs de développement (Laravel, Vite et écouteur de queue) :

```bash
composer run dev
```

L'application est généralement accessible à l'adresse indiquée par `php artisan serve` (souvent `http://127.0.0.1:8000`). Les commandes peuvent aussi être lancées séparément :

```bash
php artisan serve
npm run dev
```

### Installation manuelle

```bash
composer install
```

Copier `.env.example` en `.env`, vérifier le pilote SQLite, puis :

```bash
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan serve
```

Dans PowerShell, la copie de l'environnement peut se faire ainsi :

```powershell
Copy-Item .env.example .env
```

Ne pas exécuter `php artisan migrate:fresh` sur une base contenant des données à conserver : cette commande supprime et recrée les tables.

## Configuration

Les paramètres sont définis dans `.env`, qui ne doit pas être ajouté à Git. Les variables principales sont :

| Variable | Utilité |
|---|---|
| `APP_ENV`, `APP_DEBUG`, `APP_URL` | Environnement, niveau de détail des erreurs, URL publique. |
| `APP_KEY` | Clé de chiffrement Laravel; la conserver entre les déploiements. |
| `APP_TIMEZONE`, `APP_LOCALE`, `APP_FALLBACK_LOCALE` | Fuseau horaire et langue. |
| `DB_CONNECTION` et `DB_*` | Pilote et accès à la base de données. |
| `SESSION_DRIVER` | Stockage des sessions. |
| `CACHE_STORE` | Stockage du cache. |
| `QUEUE_CONNECTION` | Traitement des tâches en file d'attente. |
| `FILESYSTEM_DISK` | Stockage des fichiers et factures. |
| `MAIL_*` | Configuration de l'envoi de courriels si activé/configuré. |

Après une modification d'environnement en local, utiliser au besoin :

```bash
php artisan config:clear
```

## Tests et qualité

Lancer toute la suite de tests :

```bash
php artisan test
```

Vérifier le style PHP sans modifier les fichiers :

```bash
vendor/bin/pint --test
```

Corriger le style PHP avec Pint :

```bash
vendor/bin/pint
```

Construire les ressources destinées à la production :

```bash
npm run build
```

La suite utilise SQLite en mémoire. Avant un déploiement réel, tester les migrations et les parcours métier sur une base du même moteur/version que la production.

## Mise en production

Le guide détaillé de déploiement, des variables d'environnement, des prérequis serveur et des précautions d'hébergement se trouve dans [DEPLOYMENT.md](./DEPLOYMENT.md).

Principes à respecter :

- Configurer `APP_ENV=production` et `APP_DEBUG=false`.
- Servir uniquement le dossier `public/` et activer HTTPS.
- Utiliser une base MySQL/MariaDB persistante et sauvegardée.
- Préserver `APP_KEY` et ne jamais versionner `.env`.
- Conserver `storage/app/public` sur un stockage persistant; les factures et images locales ne doivent pas disparaître au redémarrage du serveur.
- Exécuter explicitement les migrations après sauvegarde, avec `php artisan migrate --force`.
- Ne pas lancer le seeder de démonstration sur une base de production.
- La configuration Vercel présente dans le dépôt n'est pas adaptée au stockage local persistant; vérifier les limitations d'un hébergeur avant d'y conserver les données métier.

## Sécurité et précautions

- **Comptes initiaux :** la migration initiale crée un administrateur, un employé et un client de démonstration avec des identifiants prédéfinis dans le code. Remplacer/supprimer ces comptes et leurs mots de passe avant toute exposition Internet. Ne pas utiliser les comptes de développement en production.
- **Données de démonstration :** `DatabaseSeeder` crée des produits de démonstration (150 références). Ne pas lancer `php artisan db:seed` sur les données réelles sans adapter le seeder.
- **Sauvegardes :** sauvegarder la base ainsi que les fichiers sous `storage/app/public`, hors du serveur, puis tester périodiquement une restauration.
- **Migrations :** plusieurs migrations créent des données initiales. Ne pas supprimer l'historique des migrations ni réinitialiser la base de production.
- **Stock de casiers :** l'ancien total global des casiers vides ne peut pas être ventilé rétroactivement par dépôt de façon fiable. Le nouveau suivi par dépôt s'alimente à partir des retours enregistrés après l'application de la migration correspondante.
- **Données sensibles :** ne pas copier les données, journaux ou fichiers contenant des renseignements clients vers un service tiers non approuvé.
- **Paiements :** les valeurs de caisse et de solde doivent être rapprochées avec les pièces comptables de l'entreprise avant exploitation.

## État et limites connues

- Le portail et les routes client sont volontairement en pause. Les commandes client en ligne ne constituent pas un parcours actif à ce stade.
- Les comptes clients restent disponibles dans l'espace interne pour les ventes, les soldes et le suivi.
- Le stock cadeau d'une promotion est une estimation, pas une réservation.
- Les données existantes de casiers n'ont pas de répartition par dépôt avant le nouveau suivi.
- Les mises à jour de Composer ne lancent plus les migrations automatiquement; cette opération est volontairement séparée pour éviter une modification implicite de la base.

