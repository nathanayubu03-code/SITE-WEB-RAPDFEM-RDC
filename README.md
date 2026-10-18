# Site web RAPDFEM RDC

Site institutionnel de l'ONG RAPDFEM avec son espace d'administration. PHP 8.1+ sans framework ni dépendance externe, base SQLite (par défaut) ou MySQL/MariaDB. Il fonctionne sur n'importe quel hébergement mutualisé cPanel.

La proposition complète (plan du site, spécifications du back-office, calendrier de déploiement) se trouve dans [docs/PROPOSITION.md](docs/PROPOSITION.md).

## Contenu

Site public : Accueil (bannière défilante, mission, chiffres clés, domaines, projets, actualités, partenaires), Qui sommes-nous, Nos projets (filtres par province et statut), Actualités (articles, communiqués, brèves), Galerie (albums photo et vidéos YouTube avec visionneuse), Publications (PDF filtrables par catégorie et année, compteur de téléchargements), Nos partenaires, Contact (formulaire protégé). Plan du site `sitemap.xml`, `robots.txt`, balises Open Graph et données structurées schema.org.

Administration (`/admin`) : tableau de bord, gestion de tous les contenus sans code, téléversement de photos (redimensionnées, métadonnées GPS supprimées) et de PDF, boîte de réception des messages, utilisateurs et rôles, paramètres du site (textes, coordonnées, réseaux sociaux, logo), sauvegardes téléchargeables, journal d'activité.

## Sécurité

Mots de passe hachés (bcrypt), double authentification TOTP (obligatoire pour les administrateurs), blocage après 5 échecs de connexion, protection CSRF sur tous les formulaires, sessions avec expiration après 30 minutes d'inactivité, en-têtes de sécurité (CSP stricte, HSTS, X-Frame-Options), requêtes SQL préparées, échappement systématique à l'affichage, aucune saisie HTML acceptée des rédacteurs. Les images sont ré-encodées, les PDF contenant du JavaScript sont refusés et stockés hors de la racine web. Le dossier `uploads` interdit l'exécution de scripts. Formulaire de contact : pot de miel, délai minimal, limitation à 5 messages par heure et reCAPTCHA v2 optionnel.

## Rôles

| Rôle | Droits |
|---|---|
| Administrateur | Tout, y compris utilisateurs, paramètres, sauvegardes et journal |
| Éditeur | Créer, modifier, publier et supprimer les contenus, lire les messages |
| Contributeur | Préparer des contenus, qu'un éditeur valide et met en ligne |

## Installation locale

```bash
php bin/admin.php install          # crée app/config.php, la base et le premier administrateur
php -S localhost:8000 -t public public/index.php
```

Ouvrir http://localhost:8000 puis http://localhost:8000/admin.

## Tester gratuitement en ligne (InfinityFree)

Pour une démonstration avant d'acheter un hébergement. Limites de l'offre gratuite : pas d'envoi d'e-mail (les messages du formulaire restent consultables dans l'administration), fichiers de 10 Mo maximum, pas de tâches cron, adresse en `.infinityfreeapp.com`. À ne pas utiliser comme site officiel.

1. Créer un compte sur infinityfree.com, puis « Create Account » en choisissant un sous-domaine gratuit (ex. `rapdfem.infinityfreeapp.com`).
2. Ouvrir le « File Manager », entrer dans `htdocs`, supprimer le fichier `index2.html` présent par défaut.
3. Envoyer le contenu de l'archive `rapdfem-site.zip` dans `htdocs` (envoyer le ZIP puis « Extract », ou utiliser FileZilla avec les identifiants FTP du panneau). Le fichier `.htaccess` et les dossiers `app`, `public`, `storage` doivent se trouver directement dans `htdocs`.
4. Ouvrir tout de suite l'adresse du site : l'assistant d'installation s'affiche. Créer le compte administrateur. Le premier qui ouvre cette page crée le compte, d'où l'intérêt de le faire immédiatement.
5. Se connecter sur `/admin` et activer la double authentification.

Si l'assistant affiche « pdo_sqlite » en rouge, utiliser la base MySQL fournie par InfinityFree : copier `app/config.sample.php` en `app/config.php`, mettre `'driver' => 'mysql'` et les informations de la rubrique « MySQL Databases » du panneau, puis recharger la page.

Créer l'archive à partir du dépôt : `git archive --format=zip HEAD -o rapdfem-site.zip`

## Mise en ligne sur un hébergement cPanel

1. Vérifier que l'hébergement fournit PHP 8.1 ou plus avec les extensions `pdo_sqlite` (ou `pdo_mysql`), `gd`, `fileinfo`, `mbstring` et `zip`.
2. Envoyer tous les fichiers du dépôt sur le serveur (Gestionnaire de fichiers, FTP ou « Git Version Control » de cPanel).
3. Faire pointer la racine du domaine sur le dossier `public/` (Domaines > Gérer > Racine du document). Si ce n'est pas possible, le fichier `.htaccess` à la racine redirige automatiquement vers `public/` et bloque l'accès aux dossiers sensibles.
4. Ouvrir le Terminal de cPanel dans le dossier du site et lancer `php bin/admin.php install` (sans Terminal : ouvrir simplement le site, un assistant d'installation s'affiche). Pour MySQL : créer la base et l'utilisateur dans cPanel, renseigner `app/config.php` (`driver => 'mysql'`), puis relancer la commande.
5. Dans `app/config.php` : `site_url` (ex. `https://www.rapdfem.org`), `force_https => true`, adresse de notification dans `mail.to`, clés reCAPTCHA si souhaité.
6. Activer le certificat SSL gratuit (cPanel > SSL/TLS Status > AutoSSL).
7. Planifier la sauvegarde nocturne (cPanel > Tâches cron) : `0 2 * * * cd /home/COMPTE/chemin-du-site && php bin/admin.php backup`
8. Se connecter sur `/admin`, activer la double authentification, puis remplacer tous les textes marqués « [À compléter] » dans Paramètres, Chiffres clés et Domaines, et supprimer les contenus d'exemple.

Droits d'écriture nécessaires pour PHP : `storage/` et `public/uploads/`.

### Cloudflare (CDN et pare-feu)

Ajouter le domaine sur Cloudflare (offre gratuite), changer les serveurs DNS chez le registraire, puis : SSL/TLS en mode « Full (strict) », « Always Use HTTPS » activé, règle de cache contournant `/admin*`, et règle WAF de limitation de débit sur `/admin/login`. Le site lit l'IP réelle des visiteurs via l'en-tête `CF-Connecting-IP`.

## Commandes utiles

```bash
php bin/admin.php create-user              # ajouter un utilisateur
php bin/admin.php reset-password EMAIL     # mot de passe oublié
php bin/admin.php reset-2fa EMAIL          # téléphone perdu
php bin/admin.php backup                   # sauvegarde manuelle
```

Restauration : décompresser l'archive, replacer `database/rapdfem.sqlite` dans `storage/database/` (ou importer `rapdfem.sql` dans MySQL via phpMyAdmin), `uploads/` dans `public/uploads/` et `docs/` dans `storage/docs/`.

## Structure

```
app/            code (contrôleurs, bibliothèques, vues), inaccessible depuis le web
  config.php    configuration locale, jamais versionnée
bin/admin.php   outil en ligne de commande
public/         racine web : index.php, assets, uploads
storage/        base SQLite, PDF, sauvegardes (inaccessible depuis le web)
docs/           proposition et calendrier
```
