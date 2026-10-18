<?php
declare(strict_types=1);

/*
 * Définition des contenus gérés depuis l'administration.
 * L'écran de liste, le formulaire et l'enregistrement sont générés à partir de ces définitions.
 *
 * Types de champ : text, textarea, markup, url, email, number, date, datetime, select,
 * relation, checkbox, image, pdf.
 * 'publish' => true marque le champ qui contrôle la mise en ligne (réservé aux rôles autorisés).
 */

function zones(): array
{
    $list = ['Kinshasa', 'Kongo-Central', 'Kwango', 'Kwilu', 'Mai-Ndombe', 'Kasaï', 'Kasaï-Central',
        'Kasaï-Oriental', 'Lomami', 'Sankuru', 'Maniema', 'Sud-Kivu', 'Nord-Kivu', 'Ituri', 'Haut-Uélé',
        'Bas-Uélé', 'Tshopo', 'Mongala', 'Nord-Ubangi', 'Sud-Ubangi', 'Équateur', 'Tshuapa', 'Tanganyika',
        'Haut-Lomami', 'Lualaba', 'Haut-Katanga', 'National / Multi-provinces'];
    return array_combine($list, $list);
}

const PROJECT_STATUSES = ['en_cours' => 'En cours', 'termine' => 'Réalisé', 'planifie' => 'En préparation'];
const ARTICLE_TYPES = ['actualite' => 'Actualité', 'communique' => 'Communiqué officiel', 'breve' => 'Brève humanitaire'];
const PUBLICATION_CATEGORIES = ['rapport_annuel' => 'Rapport annuel', 'rapport' => 'Rapport de projet', 'note' => 'Note / Policy brief', 'etude' => 'Étude', 'autre' => 'Autre document'];
const PARTNER_TYPES = ['financier' => 'Partenaire financier', 'technique' => 'Partenaire technique', 'institutionnel' => 'Partenaire institutionnel'];

function entity_defs(): array
{
    $markupHelp = 'Mise en forme : ## Titre, - liste, **gras**, *italique*, [lien](https://...). Ligne vide = nouveau paragraphe.';
    return [
        'projects' => [
            'label' => 'Projets', 'singular' => 'un projet', 'table' => 'projects',
            'order' => 'created_at DESC', 'search' => ['title', 'zone', 'donors'], 'slug' => 'title', 'timestamps' => true,
            'list' => ['title' => 'Titre', 'zone' => 'Zone', 'status' => 'Statut', 'published' => 'En ligne'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre du projet', 'required' => true, 'max' => 200],
                'summary' => ['type' => 'textarea', 'label' => 'Résumé (affiché dans les listes)', 'required' => true, 'max' => 500, 'rows' => 3],
                'zone' => ['type' => 'select', 'label' => 'Zone d\'intervention', 'options' => zones(), 'required' => true],
                'status' => ['type' => 'select', 'label' => 'Statut', 'options' => PROJECT_STATUSES, 'required' => true],
                'domain' => ['type' => 'text', 'label' => 'Domaine d\'intervention', 'max' => 120, 'help' => 'Ex. Santé, Protection, Autonomisation économique'],
                'start_date' => ['type' => 'date', 'label' => 'Date de début'],
                'end_date' => ['type' => 'date', 'label' => 'Date de fin'],
                'donors' => ['type' => 'text', 'label' => 'Bailleurs de fonds', 'max' => 300, 'help' => 'Séparés par des virgules'],
                'beneficiaries' => ['type' => 'text', 'label' => 'Bénéficiaires', 'max' => 200, 'help' => 'Ex. 4 500 femmes et filles'],
                'body' => ['type' => 'markup', 'label' => 'Description détaillée', 'help' => $markupHelp, 'rows' => 12],
                'results' => ['type' => 'markup', 'label' => 'Résultats obtenus', 'help' => $markupHelp, 'rows' => 6],
                'image' => ['type' => 'image', 'label' => 'Photo principale (terrain)'],
                'published' => ['type' => 'checkbox', 'label' => 'Publié sur le site', 'publish' => true],
            ],
        ],
        'articles' => [
            'label' => 'Actualités', 'singular' => 'une actualité', 'table' => 'articles',
            'order' => 'published_at DESC', 'search' => ['title'], 'slug' => 'title', 'timestamps' => true, 'author' => true,
            'list' => ['title' => 'Titre', 'type' => 'Type', 'published_at' => 'Date', 'published' => 'En ligne'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true, 'max' => 200],
                'type' => ['type' => 'select', 'label' => 'Type', 'options' => ARTICLE_TYPES, 'required' => true],
                'excerpt' => ['type' => 'textarea', 'label' => 'Chapeau (résumé court)', 'max' => 400, 'rows' => 3],
                'body' => ['type' => 'markup', 'label' => 'Texte', 'required' => true, 'help' => $markupHelp, 'rows' => 16],
                'image' => ['type' => 'image', 'label' => 'Image d\'illustration'],
                'published_at' => ['type' => 'datetime', 'label' => 'Date de publication', 'required' => true, 'default' => 'now'],
                'published' => ['type' => 'checkbox', 'label' => 'Publié sur le site', 'publish' => true],
            ],
        ],
        'albums' => [
            'label' => 'Galerie', 'singular' => 'un album', 'table' => 'albums',
            'order' => 'created_at DESC', 'search' => ['title'], 'slug' => 'title', 'timestamps' => true,
            'list' => ['title' => 'Album', 'project_id' => 'Projet', 'published' => 'En ligne'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre de l\'album', 'required' => true, 'max' => 200],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'max' => 1000, 'rows' => 3],
                'project_id' => ['type' => 'relation', 'label' => 'Projet associé', 'table' => 'projects', 'display' => 'title'],
                'cover' => ['type' => 'image', 'label' => 'Image de couverture', 'help' => 'Facultatif : la première photo de l\'album est utilisée sinon.'],
                'published' => ['type' => 'checkbox', 'label' => 'Publié sur le site', 'publish' => true],
            ],
            'after_form' => 'admin/album_media',
        ],
        'publications' => [
            'label' => 'Publications', 'singular' => 'une publication', 'table' => 'publications',
            'order' => 'year DESC, created_at DESC', 'search' => ['title'], 'timestamps' => true,
            'list' => ['title' => 'Titre', 'category' => 'Catégorie', 'year' => 'Année', 'downloads' => 'Téléch.', 'published' => 'En ligne'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre du document', 'required' => true, 'max' => 250],
                'category' => ['type' => 'select', 'label' => 'Catégorie', 'options' => PUBLICATION_CATEGORIES, 'required' => true],
                'year' => ['type' => 'number', 'label' => 'Année', 'required' => true, 'min' => 1990, 'max' => 2100, 'default' => 'year'],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'max' => 1000, 'rows' => 3],
                'file' => ['type' => 'pdf', 'label' => 'Fichier PDF (25 Mo max.)', 'required' => true],
                'cover' => ['type' => 'image', 'label' => 'Couverture (facultatif)', 'max_width' => 800],
                'published' => ['type' => 'checkbox', 'label' => 'Publié sur le site', 'publish' => true],
            ],
        ],
        'partners' => [
            'label' => 'Partenaires', 'singular' => 'un partenaire', 'table' => 'partners',
            'order' => 'position ASC, name ASC', 'search' => ['name'],
            'list' => ['name' => 'Nom', 'type' => 'Type', 'position' => 'Ordre', 'active' => 'Affiché'],
            'fields' => [
                'name' => ['type' => 'text', 'label' => 'Nom', 'required' => true, 'max' => 200],
                'type' => ['type' => 'select', 'label' => 'Type', 'options' => PARTNER_TYPES, 'required' => true],
                'logo' => ['type' => 'image', 'label' => 'Logo (PNG transparent conseillé)', 'max_width' => 600],
                'url' => ['type' => 'url', 'label' => 'Site web'],
                'description' => ['type' => 'textarea', 'label' => 'Description du partenariat', 'max' => 800, 'rows' => 3],
                'position' => ['type' => 'number', 'label' => 'Ordre d\'affichage', 'min' => 0, 'max' => 999, 'default' => '0'],
                'active' => ['type' => 'checkbox', 'label' => 'Afficher sur le site', 'publish' => true],
            ],
        ],
        'slides' => [
            'label' => 'Bannière d\'accueil', 'singular' => 'une diapositive', 'table' => 'slides',
            'order' => 'position ASC, id ASC',
            'list' => ['title' => 'Titre', 'position' => 'Ordre', 'active' => 'Affichée'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Titre', 'required' => true, 'max' => 140],
                'subtitle' => ['type' => 'textarea', 'label' => 'Sous-titre', 'max' => 300, 'rows' => 2],
                'image' => ['type' => 'image', 'label' => 'Photo (format paysage, 1920 px de large conseillé)', 'required' => true],
                'cta_label' => ['type' => 'text', 'label' => 'Texte du bouton', 'max' => 60],
                'cta_url' => ['type' => 'text', 'label' => 'Lien du bouton', 'max' => 300, 'help' => 'Ex. /projets ou https://...'],
                'position' => ['type' => 'number', 'label' => 'Ordre', 'min' => 0, 'max' => 99, 'default' => '0'],
                'active' => ['type' => 'checkbox', 'label' => 'Afficher', 'publish' => true],
            ],
        ],
        'stats' => [
            'label' => 'Chiffres clés', 'singular' => 'un chiffre clé', 'table' => 'stats',
            'order' => 'position ASC, id ASC',
            'list' => ['value' => 'Valeur', 'label' => 'Libellé', 'position' => 'Ordre', 'active' => 'Affiché'],
            'fields' => [
                'value' => ['type' => 'text', 'label' => 'Valeur', 'required' => true, 'max' => 30, 'help' => 'Ex. 12 500+'],
                'label' => ['type' => 'text', 'label' => 'Libellé', 'required' => true, 'max' => 120],
                'position' => ['type' => 'number', 'label' => 'Ordre', 'min' => 0, 'max' => 99, 'default' => '0'],
                'active' => ['type' => 'checkbox', 'label' => 'Afficher', 'publish' => true],
            ],
        ],
        'domains' => [
            'label' => 'Domaines d\'intervention', 'singular' => 'un domaine', 'table' => 'domains',
            'order' => 'position ASC, id ASC',
            'list' => ['title' => 'Domaine', 'position' => 'Ordre'],
            'fields' => [
                'title' => ['type' => 'text', 'label' => 'Domaine', 'required' => true, 'max' => 120],
                'description' => ['type' => 'textarea', 'label' => 'Description', 'max' => 500, 'rows' => 3],
                'position' => ['type' => 'number', 'label' => 'Ordre', 'min' => 0, 'max' => 99, 'default' => '0'],
            ],
        ],
    ];
}

function entity_def(string $name): ?array
{
    return entity_defs()[$name] ?? null;
}

function entity_publish_field(array $def): ?string
{
    foreach ($def['fields'] as $name => $field) {
        if (!empty($field['publish'])) {
            return $name;
        }
    }
    return null;
}

/** Champs du menu Paramètres (stockés dans la table settings). */
function settings_fields(): array
{
    return [
        'Identité' => [
            'org_name' => ['type' => 'text', 'label' => 'Sigle', 'default' => 'RAPDFEM'],
            'org_full_name' => ['type' => 'text', 'label' => 'Nom complet de l\'organisation'],
            'tagline' => ['type' => 'text', 'label' => 'Slogan'],
            'logo' => ['type' => 'image', 'label' => 'Logo (PNG transparent)', 'max_width' => 600],
            'meta_description' => ['type' => 'textarea', 'label' => 'Description pour les moteurs de recherche (160 caractères)', 'max' => 300],
        ],
        'Qui sommes-nous' => [
            'about_intro' => ['type' => 'textarea', 'label' => 'Présentation courte (accueil)'],
            'history' => ['type' => 'markup', 'label' => 'Historique en RDC'],
            'vision' => ['type' => 'textarea', 'label' => 'Vision'],
            'mission' => ['type' => 'textarea', 'label' => 'Mission'],
            'values' => ['type' => 'textarea', 'label' => 'Valeurs', 'help' => 'Une valeur par ligne, au format « Valeur : explication ».'],
            'governance' => ['type' => 'markup', 'label' => 'Gouvernance'],
        ],
        'Contact' => [
            'address' => ['type' => 'textarea', 'label' => 'Adresse du siège'],
            'offices' => ['type' => 'textarea', 'label' => 'Bureaux de terrain', 'help' => 'Un bureau par ligne, au format « Ville : adresse ».'],
            'phone' => ['type' => 'text', 'label' => 'Téléphone'],
            'whatsapp' => ['type' => 'text', 'label' => 'WhatsApp (numéro international, ex. 243810000000)'],
            'email' => ['type' => 'email', 'label' => 'E-mail public'],
            'hours' => ['type' => 'text', 'label' => 'Horaires'],
        ],
        'Réseaux sociaux' => [
            'facebook' => ['type' => 'url', 'label' => 'Facebook'],
            'x' => ['type' => 'url', 'label' => 'X (Twitter)'],
            'linkedin' => ['type' => 'url', 'label' => 'LinkedIn'],
            'youtube' => ['type' => 'url', 'label' => 'YouTube'],
            'instagram' => ['type' => 'url', 'label' => 'Instagram'],
        ],
        'Appel à l\'action' => [
            'cta_title' => ['type' => 'text', 'label' => 'Titre du bandeau partenaires'],
            'cta_text' => ['type' => 'textarea', 'label' => 'Texte du bandeau'],
        ],
    ];
}
