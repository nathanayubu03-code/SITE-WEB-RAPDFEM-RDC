# Proposition technique : site web RAPDFEM RDC

## 1. Choix d'architecture

Le cahier des charges recommandait WordPress. Le site livré utilise un CMS PHP léger écrit sur mesure. Raisons :

- Surface d'attaque réduite. WordPress est la cible n°1 des attaques automatisées, surtout via ses extensions. Ici, aucune extension tierce, aucun code non audité.
- Aucune mise à jour hebdomadaire de cœur, de thème et d'extensions à surveiller. Pour une petite équipe, c'est la première cause de piratage des sites d'ONG.
- Rapide sur des connexions lentes (RDC) : pages de 10 à 30 Ko de HTML, aucune bibliothèque JavaScript lourde.
- Fonctionne sur tout hébergement PHP standard, sans base MySQL obligatoire.

Contreparties assumées : pas d'écosystème d'extensions (boutique de dons en ligne, newsletter, traduction automatique). Toute nouvelle fonction demande un développeur PHP. Si RAPDFEM prévoit un site multilingue ou des dons en ligne à court terme, WordPress redevient un choix défendable, à condition de financer une maintenance mensuelle.

## 2. Plan du site

| Rubrique | URL | Contenu | Géré depuis |
|---|---|---|---|
| Accueil | `/` | Bannière défilante, présentation, mission et vision, chiffres clés, domaines, 3 projets, 3 actualités, logos partenaires, appel à l'action | Bannière, Chiffres clés, Domaines, Paramètres |
| Qui sommes-nous ? | `/qui-sommes-nous` | Historique, vision, mission, valeurs, gouvernance, domaines d'intervention | Paramètres > Qui sommes-nous |
| Nos projets | `/projets`, `/projets/{slug}` | Catalogue filtrable par province (26 provinces + national) et par statut. Fiche : zone, période, bailleurs, bénéficiaires, description, résultats, photos liées | Projets |
| Actualités | `/actualites`, `/actualites/{slug}` | Articles, communiqués officiels, brèves. Publication programmable par date. Partage Facebook, LinkedIn, WhatsApp | Actualités |
| Galerie | `/galerie`, `/galerie/{slug}` | Albums photo et vidéos YouTube, liés ou non à un projet, visionneuse plein écran | Galerie |
| Publications | `/publications` | PDF filtrables par catégorie et année, taille affichée, compteur de téléchargements | Publications |
| Nos partenaires | `/partenaires` | Partenaires financiers, techniques et institutionnels avec logo et description | Partenaires |
| Contact | `/contact` | Coordonnées du siège et des bureaux, WhatsApp, réseaux sociaux, formulaire sécurisé | Paramètres > Contact, Messages |

## 3. Spécifications du back-office

Accès : `/admin`, non indexé par les moteurs de recherche.

Authentification : e-mail et mot de passe (12 caractères minimum), double authentification par application (Google Authenticator, Microsoft Authenticator, Aegis), obligatoire pour les administrateurs, optionnelle pour les autres. Blocage 15 minutes après 5 échecs. Déconnexion après 30 minutes d'inactivité. Changer son mot de passe déconnecte ses autres sessions.

Rôles : administrateur, éditeur, contributeur (voir README). Un contributeur ne peut ni publier, ni supprimer, ni modifier un contenu déjà en ligne.

Contenus : formulaires simples, sans HTML. Mise en forme par conventions (`## Titre`, `- liste`, `**gras**`, `[lien](https://...)`). Adresse de page générée automatiquement. Photos redimensionnées à 1920 px avec miniature, métadonnées supprimées. PDF jusqu'à 25 Mo, refusés s'ils contiennent du code.

Suivi : tableau de bord (compteurs, contenus hors ligne, derniers messages, téléchargements), journal d'activité (qui a fait quoi, quand, depuis quelle IP).

Sauvegardes : archive ZIP (base + photos + PDF), automatique chaque nuit par cron, manuelle depuis l'administration, 14 dernières conservées, téléchargeables.

## 4. Hébergement recommandé

- Hébergement mutualisé PHP 8.1+ avec cPanel, SSL gratuit (AutoSSL / Let's Encrypt), tâches cron et accès Terminal. Budget indicatif : 40 à 120 USD par an. Privilégier un datacenter en Europe ou en Afrique du Sud pour la latence depuis la RDC.
- Cloudflare gratuit devant le site : CDN, certificat, pare-feu applicatif, limitation de débit sur `/admin/login`.
- Nom de domaine : `.org` ou `.cd`, enregistré au nom de l'organisation (pas d'un salarié ou prestataire).
- Boîte e-mail professionnelle sur le même domaine pour recevoir les notifications du formulaire.

## 5. Calendrier de déploiement (6 semaines)

| Semaine | Étape | Livrable | Responsable |
|---|---|---|---|
| 1 | Collecte des contenus | Textes « Qui sommes-nous », logo vectoriel, 30 à 60 photos de terrain avec consentement, liste des projets, rapports PDF, logos partenaires | RAPDFEM |
| 1 | Hébergement | Domaine, hébergement, e-mail, compte Cloudflare | RAPDFEM + technicien |
| 2 | Installation | Site installé sur un sous-domaine de test, SSL, sauvegarde nocturne planifiée | Technicien |
| 2-3 | Saisie des contenus | Paramètres, 5 diapositives, chiffres clés, projets, 3 actualités, albums, publications, partenaires | Équipe communication |
| 3 | Formation | Session de 2 h : publier une actualité, un album, un PDF ; activer la 2FA | Technicien |
| 4 | Recette | Test sur téléphone Android d'entrée de gamme en 3G, relecture de tous les textes, test du formulaire, vérification des droits par rôle | RAPDFEM |
| 5 | Mise en ligne | Bascule DNS, Cloudflare, Google Search Console, envoi du sitemap | Technicien |
| 6 | Lancement | Annonce sur les réseaux sociaux et auprès des bailleurs | RAPDFEM |
| Ensuite | Exploitation | 2 actualités par mois minimum, téléchargement mensuel d'une sauvegarde hors serveur, mise à jour de PHP par l'hébergeur | RAPDFEM |

## 6. Comment savoir que c'est réussi

- Chaque page s'affiche en moins de 3 secondes sur un téléphone en 3G (test : PageSpeed Insights, mode mobile, score supérieur à 85).
- Aucun texte « [À compléter] » ni contenu d'exemple ne reste en ligne (recherche « À compléter » sur le site).
- Une personne de l'équipe sans formation technique publie une actualité avec photo en moins de 10 minutes.
- Le site apparaît sur Google pour la recherche « RAPDFEM » dans les 4 semaines suivant la mise en ligne (Search Console).
- Une sauvegarde de moins de 24 h existe toujours dans Administration > Sauvegardes.

## 7. Photos : point de vigilance

Le site n'utilise aucune photo de banque d'images ; il affiche des visuels neutres tant que l'équipe n'a pas téléversé ses propres images. Pour les photos de bénéficiaires, en particulier de survivantes de violences et de mineurs : consentement écrit, aucun visage identifiable sans accord explicite, aucun nom ni lieu précis pouvant exposer une personne. Les coordonnées GPS sont retirées automatiquement des fichiers, mais le contenu de l'image reste la responsabilité de l'organisation.
