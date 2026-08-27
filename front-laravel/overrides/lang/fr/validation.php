<?php

/**
 * Messages de validation en français (Laravel les fournit en anglais par défaut).
 * Seules les règles réellement utilisées par l'application sont traduites ; les
 * autres retombent sur la langue de secours définie dans la configuration.
 */
return [
    'accepted' => 'Le champ :attribute doit être accepté.',
    'after' => 'Le champ :attribute doit être une date postérieure au :date.',
    'array' => 'Le champ :attribute doit être un tableau.',
    'before' => 'Le champ :attribute doit être une date antérieure au :date.',
    'boolean' => 'Le champ :attribute doit être vrai ou faux.',
    'confirmed' => 'La confirmation du champ :attribute ne correspond pas.',
    'current_password' => 'Le mot de passe est incorrect.',
    'date' => 'Le champ :attribute doit être une date valide.',
    'email' => 'Le champ :attribute doit être une adresse email valide.',
    'in' => 'Le champ :attribute sélectionné est invalide.',
    'integer' => 'Le champ :attribute doit être un nombre entier.',
    'numeric' => 'Le champ :attribute doit être un nombre.',
    'prohibited_unless' => 'Le champ :attribute est interdit sauf si :other est :values.',
    'required' => 'Le champ :attribute est obligatoire.',
    'required_if' => 'Le champ :attribute est obligatoire quand :other vaut :value.',
    'required_without' => 'Le champ :attribute est obligatoire quand :values est absent.',
    'string' => 'Le champ :attribute doit être une chaîne de caractères.',
    'unique' => 'Cette valeur de :attribute est déjà utilisée.',

    'min' => [
        'array' => 'Le champ :attribute doit contenir au moins :min éléments.',
        'file' => 'Le fichier :attribute doit faire au moins :min kilo-octets.',
        'numeric' => 'Le champ :attribute doit être au moins :min.',
        'string' => 'Le champ :attribute doit contenir au moins :min caractères.',
    ],
    'max' => [
        'array' => 'Le champ :attribute ne peut pas contenir plus de :max éléments.',
        'file' => 'Le fichier :attribute ne peut pas dépasser :max kilo-octets.',
        'numeric' => 'Le champ :attribute ne peut pas être supérieur à :max.',
        'string' => 'Le champ :attribute ne peut pas dépasser :max caractères.',
    ],

    // Noms lisibles des champs, pour des messages compréhensibles.
    'attributes' => [
        'adresse' => 'adresse',
        'benevole_id' => 'bénévole',
        'capacites' => 'capacités',
        'categorie' => 'catégorie',
        'code_barre' => 'code-barre',
        'code_postal' => 'code postal',
        'commercant_id' => 'commerçant',
        'creer_compte' => 'création de compte',
        'current_password' => 'mot de passe actuel',
        'date_collecte' => 'date de la collecte',
        'date_debut' => 'date et heure',
        'date_limite' => 'date limite',
        'date_tournee' => 'date de la tournée',
        'destinataire' => 'destinataire',
        'disponibilites' => 'disponibilités',
        'email' => 'email',
        'email_connexion' => 'email de connexion',
        'emplacement' => 'emplacement',
        'lieu' => 'lieu',
        'name' => 'nom',
        'nom' => 'nom',
        'notes' => 'notes',
        'password' => 'mot de passe',
        'places_max' => 'nombre de places',
        'produit_id' => 'produit',
        'quantite' => 'quantité',
        'role' => 'rôle',
        'service_id' => 'service',
        'siret' => 'SIRET',
        'source_libre' => 'provenance',
        'statut' => 'statut',
        'telephone' => 'téléphone',
        'ville' => 'ville',
    ],
];
