<?php
/**
 * Fichier de gestion des données des réalisations du portfolio.
 * Les projets sont désormais stockés dans data/projects.json pour permettre
 * une gestion dynamique (ajout, modification, suppression via admin.php).
 */

define('PROJECTS_JSON_PATH', __DIR__ . '/projects.json');

/**
 * Charge tous les projets depuis le fichier JSON.
 */
function get_all_projects() {
    global $projects;

    if (file_exists(PROJECTS_JSON_PATH)) {
        $content = file_get_contents(PROJECTS_JSON_PATH);
        $decoded = json_decode($content, true);
        if (is_array($decoded)) {
            $projects = $decoded;
            return $projects;
        }
    }

    // Données par défaut de secours si le fichier JSON n'existe pas encore
    $defaultProjects = [
        'twd-open-hearts' => [
            'title'          => 'The Weeknd - Open Hearts',
            'category'       => 'audiovisuel',
            'category_label' => 'Réalisation & Montage',
            'ref'            => 'REF. VIDEO-001',
            'description'    => "Ce projet est une exploration visuelle basée sur le titre \"Open Hearts\" de The Weeknd. L'objectif était de capturer l'atmosphère cinématique et mélancolique de l'artiste à travers un montage rythmé et des choix colorimétriques audacieux.",
            'role'           => 'Réalisateur & Monteur',
            'software'       => 'Premiere Pro, After Effects',
            'year'           => '2025',
            'thumbnail'      => 'img/illustration/twd.png',
            'media_type'     => 'video',
            'media_url'      => 'https://www.youtube.com/embed/jzBP2Ycr1Rw',
        ],
        'pupaxorelsan' => [
            'title'          => 'Pupa x Orelsan',
            'category'       => 'graphisme',
            'category_label' => 'Affiche & Graphisme',
            'ref'            => 'REF. GRAPHIQUE-001',
            'description'    => "Création d'une affiche pour une marque fictive de chewing-gum en collaboration avec Orelsan.<br><br><strong>Contexte :</strong> Projet scolaire divisé en deux étapes :<br>1. <strong>Identité de marque :</strong> Conception complète d'une marque de chewing-gum et de son logotype.<br>2. <strong>Campagne publicitaire :</strong> Création d'une affiche promotionnelle mettant en scène une collaboration événementielle entre la marque créée et un artiste tiré au sort (Orelsan).",
            'role'           => 'Graphiste',
            'software'       => 'Photoshop, Illustrator',
            'year'           => '2025',
            'thumbnail'      => 'img/illustration/orelsanXPupa.jpg',
            'media_type'     => 'image',
            'media_url'      => 'img/illustration/orelsanXPupa.jpg',
        ],
        'cvvideo' => [
            'title'          => 'CV Vidéo',
            'category'       => 'audiovisuel',
            'category_label' => 'Réalisation & Montage',
            'ref'            => 'REF. VIDEO-002',
            'description'    => "Projet de CV vidéo demandé pour un cours d'anglais. La vidéo devait me présenter, montrer mes passions et ce dont je suis capable pour la création audiovisuelle.",
            'role'           => 'Réalisateur & Monteur',
            'software'       => 'Premiere Pro, After Effects',
            'year'           => '2026',
            'thumbnail'      => 'img/illustration/cv.png',
            'media_type'     => 'video',
            'media_url'      => 'https://www.youtube.com/embed/mVDiD6Wx4Og?si=aMkv_79nZsnv0UZW',
        ],
        'liminal' => [
            'title'          => 'Pourquoi les liminal spaces nous passionnent ?',
            'category'       => 'audiovisuel',
            'category_label' => 'Réalisation & Montage',
            'ref'            => 'REF. VIDEO-003',
            'description'    => "Projet vidéo personnel sur le phénomène des liminal spaces. Ce phénomène est l'ensemble des lieux étranges, familiers mais inquiétants. Ces lieux sont souvent associés à une sensation de malaise, de nostalgie ou de dérangement.",
            'role'           => 'Réalisateur & Monteur',
            'software'       => 'Premiere Pro, After Effects',
            'year'           => '2026',
            'thumbnail'      => 'img/illustration/liminal.jpg',
            'media_type'     => 'video',
            'media_url'      => 'https://www.youtube.com/embed/PNGEOLOUVMY?si=gEuXt_DVsGXG82xq',
        ],
    ];

    save_all_projects($defaultProjects);
    $projects = $defaultProjects;
    return $projects;
}

/**
 * Enregistre l'ensemble des projets dans le fichier JSON.
 */
function save_all_projects(array $projectsList) {
    $dir = dirname(PROJECTS_JSON_PATH);
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
    $json = json_encode($projectsList, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    return file_put_contents(PROJECTS_JSON_PATH, $json) !== false;
}

/**
 * Ajoute ou met à jour un projet spécifique.
 */
function save_project($id, array $data) {
    $all = get_all_projects();
    $all[$id] = $data;
    return save_all_projects($all);
}

/**
 * Supprime un projet par son identifiant (slug).
 */
function delete_project($id) {
    $all = get_all_projects();
    if (isset($all[$id])) {
        unset($all[$id]);
        return save_all_projects($all);
    }
    return false;
}

/**
 * Récupère un projet spécifique par son identifiant (slug).
 */
function get_project($id) {
    $all = get_all_projects();
    return isset($all[$id]) ? $all[$id] : null;
}

/**
 * Récupère N projets aléatoires différents (en mémorisant la session pour varier à chaque rechargement).
 */
function get_random_projects($count = 2) {
    $all = get_all_projects();
    $keys = array_keys($all);
    $total = count($keys);

    if ($total <= $count) {
        return $all;
    }

    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }

    $lastKeys = isset($_SESSION['last_random_project_keys']) && is_array($_SESSION['last_random_project_keys']) 
        ? $_SESSION['last_random_project_keys'] 
        : [];

    $selectedKeys = [];
    for ($attempt = 0; $attempt < 10; $attempt++) {
        $shuffled = $keys;
        shuffle($shuffled);
        $candidate = array_slice($shuffled, 0, $count);
        sort($candidate);
        $candidateStr = implode(',', $candidate);
        
        $lastSorted = $lastKeys;
        sort($lastSorted);
        $lastStr = implode(',', $lastSorted);

        $selectedKeys = $candidate;
        if ($candidateStr !== $lastStr) {
            break;
        }
    }

    $_SESSION['last_random_project_keys'] = $selectedKeys;

    $result = [];
    foreach ($selectedKeys as $key) {
        $result[$key] = $all[$key];
    }
    return $result;
}

/**
 * Convertit toute URL YouTube standard en URL d'intégration (embed).
 */
function format_youtube_embed($url) {
    $url = trim($url);
    if (empty($url)) {
        return '';
    }

    // Si c'est déjà un lien embed
    if (strpos($url, 'youtube.com/embed/') !== false) {
        return $url;
    }

    $videoId = '';

    // Format: https://www.youtube.com/watch?v=VIDEO_ID
    if (preg_match('/[?&]v=([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    // Format: https://youtu.be/VIDEO_ID
    elseif (preg_match('/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    // Format: https://www.youtube.com/shorts/VIDEO_ID
    elseif (preg_match('/youtube\.com\/shorts\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }

    if (!empty($videoId)) {
        return "https://www.youtube.com/embed/{$videoId}";
    }

    return $url;
}

/**
 * Génère un slug d'URL propre à partir d'un titre (compatible sans mbstring ni intl).
 */
function slugify($text) {
    $text = trim($text);
    // Remplacement des accents en UTF-8
    $text = str_replace(
        ['À','Á','Â','Ã','Ä','Å','à','á','â','ã','ä','å','Ò','Ó','Ô','Õ','Ö','Ø','ò','ó','ô','õ','ö','ø','È','É','Ê','Ë','è','é','ê','ë','Ç','ç','Ì','Í','Î','Ï','ì','í','î','ï','Ù','Ú','Û','Ü','ù','ú','û','ü','ÿ','Ñ','ñ'],
        ['a','a','a','a','a','a','a','a','a','a','a','a','o','o','o','o','o','o','o','o','o','o','o','o','e','e','e','e','e','e','e','e','c','c','i','i','i','i','i','i','i','i','u','u','u','u','u','u','u','u','y','n','n'],
        $text
    );
    // Secours si chaîne encodée en ANSI/ISO-8859-1
    $text = str_replace(["\xe9", "\xe8", "\xea", "\xeb", "\xe0", "\xe2", "\xe4", "\xe7", "\xf4", "\xf6", "\xfb", "\xfc", "\xee", "\xef"], ['e','e','e','e','a','a','a','c','o','o','u','u','i','i'], $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/i', '-', $text);
    $text = trim($text, '-');
    return !empty($text) ? $text : 'projet-' . time();
}

// Initialise la variable globale $projects pour la compatibilité
$projects = get_all_projects();
