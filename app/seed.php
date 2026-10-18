<?php
declare(strict_types=1);

/*
 * Contenu de démarrage. Tous les textes marqués [À compléter] sont des exemples
 * à remplacer par les informations réelles de RAPDFEM depuis l'administration.
 * Aucune photo n'est fournie : le site affiche des visuels neutres tant que
 * l'équipe n'a pas téléversé ses propres photos de terrain.
 */
function seed_database(): void
{
    if ((int) db_val('SELECT COUNT(*) FROM settings') > 0) {
        return;
    }
    $now = now();

    $settings = [
        'org_name' => 'RAPDFEM',
        'org_full_name' => '[À compléter : nom complet de l\'organisation]',
        'tagline' => 'Agir sur le terrain, en République Démocratique du Congo',
        'meta_description' => 'RAPDFEM, organisation non gouvernementale congolaise : projets, actualités, publications et partenaires en République Démocratique du Congo.',
        'about_intro' => '[À compléter] RAPDFEM est une organisation non gouvernementale congolaise qui intervient auprès des communautés vulnérables en République Démocratique du Congo. Présentez ici en deux ou trois phrases qui vous êtes, où vous travaillez et pour qui.',
        'history' => "[À compléter] Racontez la création de l'organisation : l'année, les fondatrices et fondateurs, le contexte et les premières actions.\n\n## Étapes clés\n- Année : création de l'organisation\n- Année : premier projet financé\n- Année : ouverture d'un bureau de terrain",
        'vision' => '[À compléter] Une société congolaise où chaque personne vit dans la dignité et la sécurité.',
        'mission' => '[À compléter] Protéger, accompagner et renforcer les communautés vulnérables en RDC à travers des programmes de terrain conçus avec elles.',
        'values' => "Intégrité : nous rendons compte de chaque ressource confiée.\nProximité : nous travaillons avec les communautés, pas à leur place.\nNeutralité : nous aidons selon les besoins, sans distinction.\nRedevabilité : nous mesurons et publions nos résultats.",
        'governance' => "[À compléter] Décrivez les organes de gouvernance.\n\n## Assemblée générale\nOrgane souverain, elle se réunit une fois par an.\n\n## Conseil d'administration\nIl définit les orientations stratégiques et contrôle la gestion.\n\n## Coordination nationale\nElle met en œuvre les programmes et dirige les équipes de terrain.",
        'address' => "[À compléter] Avenue ..., Commune de ...\nKinshasa, République Démocratique du Congo",
        'offices' => "Goma : [À compléter]\nBukavu : [À compléter]",
        'phone' => '+243 000 000 000',
        'email' => 'contact@exemple.org',
        'hours' => 'Lundi au vendredi, 8 h 00 à 16 h 30',
        'cta_title' => 'Devenez partenaire de notre action',
        'cta_text' => 'Bailleurs, institutions, entreprises : construisons ensemble des réponses durables pour les communautés de RDC.',
    ];
    foreach ($settings as $k => $v) {
        db_insert('settings', ['skey' => $k, 'svalue' => $v]);
    }

    $stats = [['[000]', 'Bénéficiaires accompagnés'], ['[00]', 'Projets réalisés'], ['[0]', 'Provinces d\'intervention'], ['[00]', 'Partenaires']];
    foreach ($stats as $i => [$value, $label]) {
        db_insert('stats', ['value' => $value, 'label' => $label, 'position' => $i, 'active' => 1]);
    }

    $domains = [
        ['Protection', 'Prévention des violences, prise en charge et accompagnement des survivantes et survivants.'],
        ['Santé', 'Accès aux soins de santé primaires et santé communautaire.'],
        ['Autonomisation économique', 'Formation, activités génératrices de revenus et épargne communautaire.'],
        ['Éducation', 'Scolarisation, alphabétisation et formation professionnelle.'],
    ];
    foreach ($domains as $i => [$title, $desc]) {
        db_insert('domains', ['title' => $title . ' [exemple]', 'description' => $desc, 'position' => $i]);
    }

    db_insert('projects', [
        'title' => 'Projet exemple au Nord-Kivu', 'slug' => 'projet-exemple-nord-kivu',
        'summary' => '[Exemple à remplacer] Résumé du projet en deux phrases : le problème traité et la réponse apportée.',
        'body' => "## Contexte\nDécrivez la situation qui justifie le projet.\n\n## Activités\n- Activité 1\n- Activité 2\n- Activité 3",
        'results' => "- Résultat chiffré 1\n- Résultat chiffré 2",
        'zone' => 'Nord-Kivu', 'status' => 'en_cours', 'domain' => 'Protection', 'start_date' => date('Y') . '-01-01',
        'end_date' => null, 'donors' => 'Bailleur A, Bailleur B', 'beneficiaries' => '[nombre] bénéficiaires',
        'image' => null, 'published' => 1, 'created_at' => $now, 'updated_at' => $now,
    ]);
    db_insert('projects', [
        'title' => 'Projet exemple à Kinshasa', 'slug' => 'projet-exemple-kinshasa',
        'summary' => '[Exemple à remplacer] Projet terminé, avec ses résultats et ses bailleurs.',
        'body' => 'Description du projet.', 'results' => '- Résultat 1',
        'zone' => 'Kinshasa', 'status' => 'termine', 'domain' => 'Autonomisation économique',
        'start_date' => (date('Y') - 2) . '-03-01', 'end_date' => (date('Y') - 1) . '-02-28',
        'donors' => 'Bailleur C', 'beneficiaries' => '[nombre] bénéficiaires',
        'image' => null, 'published' => 1, 'created_at' => $now, 'updated_at' => $now,
    ]);

    db_insert('articles', [
        'title' => 'Bienvenue sur le nouveau site de RAPDFEM', 'slug' => 'bienvenue-sur-le-nouveau-site',
        'type' => 'communique',
        'excerpt' => 'Notre nouveau site présente nos projets, nos résultats et nos publications en toute transparence.',
        'body' => "[Exemple à remplacer] Ce premier article montre la mise en forme disponible.\n\n## Un intertitre\nUn paragraphe avec du **gras**, de l'*italique* et un [lien](/projets).\n\n- Un point de liste\n- Un autre point",
        'image' => null, 'published_at' => $now, 'published' => 1, 'author_id' => null,
        'created_at' => $now, 'updated_at' => $now,
    ]);
}
