<?php
/**
 * Interface d'Administration - Theo Marquilly Portfolio
 * Permet d'ajouter et de supprimer des projets facilement avec gestion de mot de passe et upload d'images.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/data/projects.php';

// =========================================================================
// CONFIGURATION DU MOT DE PASSE (Modifiable ici)
// =========================================================================
$ADMIN_PASSWORD = 'admin'; // <-- Changez ce mot de passe selon votre choix

$errorMsg = '';
$successMsg = '';
$newProjectUrl = '';

// -------------------------------------------------------------------------
// 1. GESTION DE LA DÉCONNEXION
// -------------------------------------------------------------------------
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    $_SESSION['admin_logged_in'] = false;
    unset($_SESSION['admin_logged_in']);
    header('Location: admin.php');
    exit;
}

// -------------------------------------------------------------------------
// 2. GESTION DE LA CONNEXION
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $inputPassword = isset($_POST['password']) ? trim($_POST['password']) : '';
    if ($inputPassword === $ADMIN_PASSWORD) {
        $_SESSION['admin_logged_in'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $errorMsg = 'Mot de passe incorrect. Veuillez réessayer.';
    }
}

$isLoggedIn = !empty($_SESSION['admin_logged_in']);

// -------------------------------------------------------------------------
// 3. ACTIONS ADMINISTRATEUR (Ajout / Suppression)
// -------------------------------------------------------------------------
if ($isLoggedIn && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    // --- SUPPRESSION D'UN PROJET ---
    if ($action === 'delete') {
        $deleteId = isset($_POST['project_id']) ? trim($_POST['project_id']) : '';
        if (!empty($deleteId)) {
            $deletedProject = get_project($deleteId);
            $deletedTitle = $deletedProject ? $deletedProject['title'] : $deleteId;
            if (delete_project($deleteId)) {
                $successMsg = "Le projet \"<strong>" . htmlspecialchars($deletedTitle) . "</strong>\" a été supprimé avec succès.";
            } else {
                $errorMsg = "Impossible de supprimer le projet \"$deleteId\" (introuvable).";
            }
        }
    }

    // --- AJOUT D'UN PROJET ---
    if ($action === 'add') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $category = isset($_POST['category']) ? trim($_POST['category']) : 'audiovisuel';
        $categoryCustom = isset($_POST['category_custom']) ? trim($_POST['category_custom']) : '';
        if ($category === 'custom' && !empty($categoryCustom)) {
            $category = slugify($categoryCustom);
        }

        $categoryLabel = isset($_POST['category_label']) ? trim($_POST['category_label']) : '';
        $ref = isset($_POST['ref']) ? trim($_POST['ref']) : '';
        $year = isset($_POST['year']) ? trim($_POST['year']) : date('Y');
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';
        $software = isset($_POST['software']) ? trim($_POST['software']) : '';
        $mediaType = isset($_POST['media_type']) ? trim($_POST['media_type']) : 'video';
        $rawDescription = isset($_POST['description']) ? trim($_POST['description']) : '';
        $customSlug = isset($_POST['slug']) ? trim($_POST['slug']) : '';

        // Formatage de la description (conserve les balises HTML ou convertit les sauts de ligne)
        if (strpos($rawDescription, '<p>') === false && strpos($rawDescription, '<br') === false) {
            $description = nl2br($rawDescription);
        } else {
            $description = $rawDescription;
        }

        // Slug / identifiant unique
        $slug = !empty($customSlug) ? slugify($customSlug) : slugify($title);
        $existing = get_all_projects();
        if (isset($existing[$slug])) {
            $slug .= '-' . time();
        }

        // Traitement du média principal
        $mediaUrl = '';
        if ($mediaType === 'video') {
            $rawVideoUrl = isset($_POST['video_url']) ? trim($_POST['video_url']) : '';
            $mediaUrl = format_youtube_embed($rawVideoUrl);
            if (empty($mediaUrl)) {
                $errorMsg = "Veuillez renseigner une URL YouTube valide.";
            }
        } else {
            // Téléversement de l'image principale
            if (isset($_FILES['media_image']) && $_FILES['media_image']['error'] === UPLOAD_ERR_OK) {
                $uploadRes = handle_image_upload($_FILES['media_image'], $slug . '-main');
                if ($uploadRes['success']) {
                    $mediaUrl = $uploadRes['path'];
                } else {
                    $errorMsg = "Erreur lors de l'envoi de l'image principale : " . $uploadRes['error'];
                }
            } elseif (!empty($_POST['media_image_url'])) {
                $mediaUrl = trim($_POST['media_image_url']);
            } else {
                $errorMsg = "Veuillez fournir une image principale (upload de fichier ou chemin).";
            }
        }

        // Traitement de la miniature (thumbnail)
        $thumbnailUrl = '';
        if (isset($_FILES['thumbnail_image']) && $_FILES['thumbnail_image']['error'] === UPLOAD_ERR_OK) {
            $thumbRes = handle_image_upload($_FILES['thumbnail_image'], $slug . '-thumb');
            if ($thumbRes['success']) {
                $thumbnailUrl = $thumbRes['path'];
            }
        } elseif (!empty($_POST['thumbnail_url'])) {
            $thumbnailUrl = trim($_POST['thumbnail_url']);
        } elseif ($mediaType === 'image' && !empty($mediaUrl)) {
            // Réutilise l'image principale comme miniature
            $thumbnailUrl = $mediaUrl;
        }

        if (empty($thumbnailUrl)) {
            $thumbnailUrl = 'img/illustration/liminal.jpg'; // Visuel par défaut si non fourni
        }

        // Référence automatique si non renseignée
        if (empty($ref)) {
            $prefix = ($mediaType === 'video') ? 'REF. VIDEO' : 'REF. GRAPHIQUE';
            $ref = $prefix . '-' . sprintf('%03d', count($existing) + 1);
        }

        // Enregistrement si pas d'erreur
        if (empty($errorMsg) && !empty($title)) {
            $newProjectData = [
                'title'          => $title,
                'category'       => $category,
                'category_label' => $categoryLabel ?: ($category === 'audiovisuel' ? 'Réalisation & Montage' : 'Affiche & Graphisme'),
                'ref'            => $ref,
                'description'    => $description,
                'role'           => $role,
                'software'       => $software,
                'year'           => $year,
                'thumbnail'      => $thumbnailUrl,
                'media_type'     => $mediaType,
                'media_url'      => $mediaUrl,
            ];

            if (save_project($slug, $newProjectData)) {
                $successMsg = "Le projet \"<strong>" . htmlspecialchars($title) . "</strong>\" a été publié avec succès !";
                $newProjectUrl = "project.php?id=" . urlencode($slug);
            } else {
                $errorMsg = "Une erreur est survenue lors de l'enregistrement dans projects.json.";
            }
        }
    }
}

/**
 * Fonction utilitaire d'upload sécurisé d'images
 */
function handle_image_upload($file, $baseName) {
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $allowedExts  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $allowedExts)) {
        return ['success' => false, 'error' => "Format .$extension non autorisé (formats acceptés : JPG, PNG, WEBP, GIF)."];
    }

    $uploadDir = __DIR__ . '/img/illustration/';
    if (!is_dir($uploadDir)) {
        @mkdir($uploadDir, 0777, true);
    }

    $fileName = $baseName . '-' . time() . '.' . $extension;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => 'img/illustration/' . $fileName];
    }

    return ['success' => false, 'error' => "Échec du déplacement du fichier téléversé."];
}

$allProjects = get_all_projects();

$pageTitle = 'Administration - Theo Marquilly';
$currentPage = 'admin';
include __DIR__ . '/includes/header.php';
?>

<!-- Header Section -->
<header style="text-align: center; padding: 3rem 1rem 2rem;">
    <h1 class="hero-title" style="font-size: 6vw; margin-bottom: 0.5rem;">Administration</h1>
    <p class="tagline" style="background: none; padding: 0;">Gestion des Projets & Réalisations</p>
</header>

<div class="stripes-container">
    <div class="stripe s1"></div>
    <div class="stripe s2"></div>
    <div class="stripe s3"></div>
    <div class="stripe s4"></div>
    <div class="stripe s5"></div>
</div>

<main style="max-width: 1050px; margin: 0 auto; padding: 2.5rem 1.5rem 5rem;">

    <?php if (!$isLoggedIn): ?>
        <!-- =================================================================== -->
        <!-- FORMULAIRE DE CONNEXION (MOT DE PASSE)                             -->
        <!-- =================================================================== -->
        <div class="admin-login-card">
            <h2 style="font-size: 2rem; font-weight: 900; text-transform: uppercase; margin-bottom: 1rem; border-bottom: 4px solid var(--text-black); padding-bottom: 0.5rem;">
                Connexion requise
            </h2>
            <p style="margin-bottom: 1.5rem; opacity: 0.85;">
                Veuillez saisir votre mot de passe administrateur pour accéder à la gestion des projets.
            </p>

            <?php if (!empty($errorMsg)): ?>
                <div class="admin-alert admin-alert-danger">
                    <?= htmlspecialchars($errorMsg) ?>
                </div>
            <?php endif; ?>

            <form action="admin.php" method="POST" style="display: flex; flex-direction: column; gap: 1.2rem;">
                <input type="hidden" name="action" value="login">
                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required autofocus placeholder="Entrez le mot de passe">
                </div>
                <button type="submit" class="btn-submit" style="cursor: pointer; width: 100%;">
                    Déverrouiller l'accès
                </button>
            </form>
        </div>

    <?php else: ?>
        <!-- =================================================================== -->
        <!-- TABLEAU DE BORD ADMINISTRATEUR CONNECTÉ                            -->
        <!-- =================================================================== -->

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
            <div>
                <span class="bio-ref" style="display: inline-block; background-color: var(--text-black); color: var(--bg-cream); padding: 0.3rem 0.8rem; font-weight: 800; font-size: 0.9rem;">
                    SESSION ADMIN ACTIVE
                </span>
            </div>
            <div style="display: flex; gap: 1rem;">
                <a href="portfolio.php" class="filter-btn" style="text-decoration: none; padding: 0.6rem 1.2rem; font-size: 0.9rem;" target="_blank">
                    Voir Portfolio ↗
                </a>
                <a href="admin.php?action=logout" class="filter-btn" style="text-decoration: none; padding: 0.6rem 1.2rem; font-size: 0.9rem; background: var(--c-red); color: #fff; border-color: var(--c-burgundy);">
                    Déconnexion
                </a>
            </div>
        </div>

        <!-- Messages d'alertes -->
        <?php if (!empty($successMsg)): ?>
            <div class="admin-alert admin-alert-success">
                <?= $successMsg ?>
                <?php if (!empty($newProjectUrl)): ?>
                    <div style="margin-top: 0.8rem;">
                        <a href="<?= htmlspecialchars($newProjectUrl) ?>" target="_blank" style="font-weight: 900; color: #fff; text-decoration: underline; font-size: 1.1rem;">
                            ➔ Voir la page du projet immédiatement ↗
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            <div class="admin-alert admin-alert-danger">
                <?= htmlspecialchars($errorMsg) ?>
            </div>
        <?php endif; ?>

        <!-- SECTION 1 : FORMULAIRE D'AJOUT -->
        <section class="admin-card" style="margin-bottom: 3.5rem;">
            <div class="admin-card-header">
                <h2>Ajouter un Nouveau Projet</h2>
                <span class="bio-ref">NOUVELLE ENTRÉE</span>
            </div>

            <form action="admin.php" method="POST" enctype="multipart/form-data" class="admin-form">
                <input type="hidden" name="action" value="add">

                <div class="form-grid">
                    <!-- Titre -->
                    <div class="form-group full-width">
                        <label for="title">Titre du Projet *</label>
                        <input type="text" id="title" name="title" required placeholder="Ex : The Weeknd - Open Hearts">
                    </div>

                    <!-- Catégorie -->
                    <div class="form-group">
                        <label for="category">Catégorie *</label>
                        <select id="category" name="category" onchange="toggleCategoryCustom(this.value)">
                            <option value="audiovisuel">Audiovisuel (Vidéo, Montage...)</option>
                            <option value="graphisme">Graphisme (Affiche, Logo, DA...)</option>
                            <option value="custom">+ Autre catégorie personnalisée</option>
                        </select>
                    </div>

                    <!-- Sous-titre / Label catégorie -->
                    <div class="form-group" id="cat-label-container">
                        <label for="category_label">Sous-titre / Libellé affiché *</label>
                        <input type="text" id="category_label" name="category_label" placeholder="Ex : Réalisation & Montage">
                    </div>

                    <!-- Catégorie personnalisée (masqué par défaut) -->
                    <div class="form-group full-width" id="cat-custom-container" style="display: none;">
                        <label for="category_custom">Nom de la nouvelle catégorie *</label>
                        <input type="text" id="category_custom" name="category_custom" placeholder="Ex : 3D, Web design, Photographie...">
                    </div>

                    <!-- Rôle & Année -->
                    <div class="form-group">
                        <label for="role">Votre Rôle</label>
                        <input type="text" id="role" name="role" placeholder="Ex : Réalisateur & Monteur, Graphiste">
                    </div>

                    <div class="form-group">
                        <label for="year">Année de réalisation</label>
                        <input type="text" id="year" name="year" value="<?= date('Y') ?>">
                    </div>

                    <!-- Logiciels -->
                    <div class="form-group full-width">
                        <label for="software">Logiciels utilisés</label>
                        <input type="text" id="software" name="software" placeholder="Ex : Premiere Pro, After Effects, Photoshop">
                    </div>

                    <!-- Référence optionnelle -->
                    <div class="form-group">
                        <label for="ref">Référence projet (facultatif)</label>
                        <input type="text" id="ref" name="ref" placeholder="Ex : REF. VIDEO-005 (auto si vide)">
                    </div>

                    <div class="form-group">
                        <label for="slug">Identifiant d'URL (slug, facultatif)</label>
                        <input type="text" id="slug" name="slug" placeholder="Auto-généré depuis le titre">
                    </div>
                </div>

                <!-- TYPE DE MÉDIA -->
                <div style="margin: 2rem 0; padding: 1.5rem; border: 3px solid var(--text-black); background-color: rgba(255,255,255,0.7);">
                    <h3 style="font-size: 1.3rem; font-weight: 900; margin-bottom: 1rem; text-transform: uppercase;">
                        Type de média principal
                    </h3>

                    <div style="display: flex; gap: 2rem; margin-bottom: 1.5rem; flex-wrap: wrap;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 900; cursor: pointer; font-size: 1.1rem;">
                            <input type="radio" name="media_type" value="video" checked onchange="toggleMediaType('video')" style="transform: scale(1.3);">
                            Vidéo YouTube
                        </label>
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-weight: 900; cursor: pointer; font-size: 1.1rem;">
                            <input type="radio" name="media_type" value="image" onchange="toggleMediaType('image')" style="transform: scale(1.3);">
                            Image / Graphisme
                        </label>
                    </div>

                    <!-- Champ Vidéo -->
                    <div id="media-video-group" class="form-group">
                        <label for="video_url">Lien de la vidéo YouTube *</label>
                        <input type="text" id="video_url" name="video_url" placeholder="Ex : https://www.youtube.com/watch?v=... ou https://youtu.be/...">
                        <small style="margin-top: 0.3rem; opacity: 0.8; font-family: sans-serif;">
                            💡 Collez n'importe quel lien YouTube normal, il sera automatiquement transformé en lecteur vidéo intégré.
                        </small>
                    </div>

                    <!-- Champ Image principale -->
                    <div id="media-image-group" class="form-group" style="display: none;">
                        <label for="media_image">Téléverser l'image principale (JPG, PNG, WEBP) *</label>
                        <input type="file" id="media_image" name="media_image" accept="image/*">
                        <div style="margin-top: 0.5rem;">
                            <span style="font-size: 0.85rem; opacity: 0.7;">OU indiquez un chemin existant :</span>
                            <input type="text" name="media_image_url" placeholder="Ex : img/illustration/mon-affiche.jpg" style="margin-top: 0.3rem;">
                        </div>
                    </div>
                </div>

                <!-- MINIATURE (THUMBNAIL) -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="thumbnail_image">Image miniature pour la grille du Portfolio (JPG, PNG, WEBP)</label>
                    <input type="file" id="thumbnail_image" name="thumbnail_image" accept="image/*">
                    <small style="margin-top: 0.3rem; opacity: 0.8; font-family: sans-serif;">
                        Pour un projet de type image, si vous ne téléversez rien ici, l'image principale sera utilisée automatiquement.
                    </small>
                </div>

                <!-- DESCRIPTION -->
                <div class="form-group" style="margin-bottom: 2rem;">
                    <label for="description">Description détaillée du projet *</label>
                    <textarea id="description" name="description" rows="5" required placeholder="Décrivez le projet, le contexte, vos choix créatifs... Les retours à la ligne sont conservés."></textarea>
                </div>

                <button type="submit" class="btn-submit" style="width: 100%; text-align: center; cursor: pointer;">
                    ➕ Publier le projet
                </button>
            </form>
        </section>

        <!-- SECTION 2 : GESTION DES PROJETS EXISTANTS -->
        <section class="admin-card">
            <div class="admin-card-header">
                <h2>Projets Existants (<?= count($allProjects) ?>)</h2>
                <span class="bio-ref">GESTION & SUPPRESSION</span>
            </div>

            <div style="padding: 2rem;">
                <p style="margin-bottom: 2rem; opacity: 0.85;">
                    Retrouvez ci-dessous la liste de tous vos projets en ligne. Vous pouvez les prévisualiser ou les supprimer en un clic.
                </p>

                <div class="admin-projects-grid">
                    <?php foreach ($allProjects as $slug => $proj): ?>
                        <div class="admin-project-item">
                            <div class="admin-project-thumb" style="background-color: var(--c-red);">
                                <img src="<?= htmlspecialchars($proj['thumbnail']) ?>" alt="<?= htmlspecialchars($proj['title']) ?>">
                                <span class="admin-badge <?= $proj['media_type'] === 'video' ? 'badge-video' : 'badge-image' ?>">
                                    <?= $proj['media_type'] === 'video' ? '🎬 Vidéo' : '🖼️ Image' ?>
                                </span>
                            </div>
                            <div class="admin-project-details">
                                <h3 style="font-size: 1.2rem; font-weight: 900; margin-bottom: 0.3rem;">
                                    <?= htmlspecialchars($proj['title']) ?>
                                </h3>
                                <p style="font-size: 0.85rem; opacity: 0.7; margin-bottom: 0.5rem;">
                                    <?= htmlspecialchars($proj['category_label']) ?> • <?= htmlspecialchars($proj['year']) ?>
                                </p>
                                <p style="font-size: 0.75rem; font-family: monospace; opacity: 0.6; margin-bottom: 1rem;">
                                    ID: <?= htmlspecialchars($slug) ?> | <?= htmlspecialchars($proj['ref']) ?>
                                </p>

                                <div style="display: flex; gap: 0.8rem; align-items: center;">
                                    <a href="project.php?id=<?= urlencode($slug) ?>" target="_blank" class="filter-btn" style="text-decoration: none; padding: 0.5rem 1rem; font-size: 0.85rem;">
                                        Voir la page ↗
                                    </a>

                                    <form action="admin.php" method="POST" onsubmit="return confirm('Attention : Êtes-vous sûr de vouloir supprimer définitivement le projet « <?= addslashes(htmlspecialchars($proj['title'])) ?> » ?');" style="margin: 0;">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="project_id" value="<?= htmlspecialchars($slug) ?>">
                                        <button type="submit" class="admin-btn-delete">
                                            🗑️ Supprimer
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php endif; ?>

</main>

<script>
function toggleMediaType(type) {
    const videoGroup = document.getElementById('media-video-group');
    const imageGroup = document.getElementById('media-image-group');
    const videoInput = document.getElementById('video_url');
    const imageInput = document.getElementById('media_image');

    if (type === 'video') {
        videoGroup.style.display = 'flex';
        imageGroup.style.display = 'none';
        videoInput.required = true;
        imageInput.required = false;
    } else {
        videoGroup.style.display = 'none';
        imageGroup.style.display = 'flex';
        videoInput.required = false;
    }
}

function toggleCategoryCustom(val) {
    const customContainer = document.getElementById('cat-custom-container');
    const customInput = document.getElementById('category_custom');
    const labelInput = document.getElementById('category_label');

    if (val === 'custom') {
        customContainer.style.display = 'flex';
        customInput.required = true;
        if (!labelInput.value) {
            labelInput.value = 'Projet Multimédia';
        }
    } else {
        customContainer.style.display = 'none';
        customInput.required = false;
        if (val === 'audiovisuel' && (!labelInput.value || labelInput.value === 'Affiche & Graphisme')) {
            labelInput.value = 'Réalisation & Montage';
        } else if (val === 'graphisme' && (!labelInput.value || labelInput.value === 'Réalisation & Montage')) {
            labelInput.value = 'Affiche & Graphisme';
        }
    }
}
</script>

<?php
include __DIR__ . '/includes/footer.php';
?>
