<?php
require_once __DIR__ . '/data/projects.php';

$id = isset($_GET['id']) ? trim($_GET['id']) : '';
$project = get_project($id);

if (!$project) {
    http_response_code(404);
    $pageTitle = 'Projet introuvable - Theo Marquilly';
    $currentPage = 'project';
    include __DIR__ . '/includes/header.php';
    ?>
    <header style="text-align: center; padding: 4rem 1rem;">
        <h1 class="hero-title" style="font-size: 6vw; margin-bottom: 1rem;">Projet introuvable</h1>
        <p class="tagline" style="background: none; padding: 0;">Ce projet n'existe pas ou a été déplacé.</p>
    </header>

    <div class="stripes-container">
        <div class="stripe s3"></div>
        <div class="stripe s4"></div>
    </div>

    <div style="text-align: center; padding: 4rem; background-color: var(--bg-cream);">
        <a href="portfolio.php" class="btn-submit" style="text-decoration: none; display: inline-block;">Retour au Portfolio</a>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = 'Theo Marquilly - ' . $project['title'];
$currentPage = 'project';

include __DIR__ . '/includes/header.php';
?>

<!-- Project Hero -->
<header style="text-align: center; padding: 4rem 1rem;">
    <h1 class="hero-title" style="font-size: 6vw; margin-bottom: 1rem;"><?= htmlspecialchars($project['title']) ?></h1>
    <p class="tagline" style="background: none; padding: 0;"><?= htmlspecialchars($project['category_label']) ?></p>
</header>

<div class="stripes-container">
    <div class="stripe s3"></div>
    <div class="stripe s4"></div>
</div>

<section class="bio-container" style="border: none;">
    <div class="bio-header">
        <h2>Détails<br>Projet</h2>
        <span class="bio-ref"><?= htmlspecialchars($project['ref']) ?></span>
    </div>
    <div class="bio-content">
        <p><?= $project['description'] ?></p>

        <p><strong>Rôle :</strong> <?= htmlspecialchars($project['role']) ?><br>
            <strong>Logiciels :</strong> <?= htmlspecialchars($project['software']) ?><br>
            <strong>Année :</strong> <?= htmlspecialchars($project['year']) ?>
        </p>

        <?php if ($project['media_type'] === 'video'): ?>
            <div class="p-img" style="background-color: var(--c-red); height: 100%; margin-top: 2rem;">
                <iframe width="560" height="315" src="<?= htmlspecialchars($project['media_url']) ?>"
                    title="YouTube video player" frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            </div>
        <?php else: ?>
            <div class="p-img" style="background-color: var(--c-red); height: 100%; margin-top: 2rem;">
                <img src="<?= htmlspecialchars($project['media_url']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
            </div>
        <?php endif; ?>
        <p style="text-align: center; margin-top: 1rem; font-style: italic; opacity: 0.7;">(Capture du projet)</p>
    </div>
</section>

<!-- Back to Portfolio button -->
<div style="text-align: center; padding: 4rem; background-color: var(--bg-cream); border-top: 4px solid var(--text-black);">
    <a href="portfolio.php" class="btn-submit" style="text-decoration: none; display: inline-block;">Retour au Portfolio</a>
</div>

<?php
include __DIR__ . '/includes/footer.php';
?>
