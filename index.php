<?php
require_once __DIR__ . '/data/projects.php';

$pageTitle = 'Theo Marquilly - Accueil';
$currentPage = 'home';

include __DIR__ . '/includes/header.php';
?>

<!-- Hero Header -->
<header class="hero">
    <h1 class="hero-title">Théo Marquilly</h1>
</header>

<!-- Retro Stripes -->
<div class="stripes-container">
    <div class="stripe s1"></div>
    <div class="stripe s2"></div>
    <div class="stripe s3"></div>
    <div class="stripe s4"></div>
    <div class="stripe s5"></div>
</div>

<!-- Bio Section -->
<section class="bio-container">
    <div class="bio-header">
        <h2>Bio &<br>Infos</h2>
        <span class="bio-ref">REF. MMI-2026</span>
    </div>
    <div class="bio-content">
        <p>Actuellement en 2e année de BUT Métiers du Multimédia et de l’Internet (MMI) à l’IUT
            de Lens.
            Étant passionné de cinéma depuis plus petit, j’adore transformer des idées créatives et innovantes en
            vidéo.</p>

        <p>Curieux et créatif, je m’intéresse à tout ce qui touche à la stratégie de communication, au graphisme et
            à la réalisation audiovisuelle.
            <br><strong>Pour moi, chaque support visuel est une opportunité de raconter une histoire, de transmettre une
                émotion et de donner du sens à un message.</strong>
        </p>

        <p>Au fil de mes expériences et de ma formation, j’ai appris à manier les outils de la Suite Adobe, à gérer
            des projets multimédias et à travailler sur la conception d’identités visuelles ou de contenus vidéo.</p>
    </div>
</section>

<!-- Random Projects Teaser Section -->
<?php
$randomProjects = get_random_projects(2);
?>
<section class="projects reveal">
    <div class="project-grid">
        <?php foreach ($randomProjects as $slug => $project): ?>
            <a href="project.php?id=<?= urlencode($slug) ?>" class="project-card"
                data-category="<?= htmlspecialchars($project['category']) ?>"
                style="text-decoration: none; color: inherit; display: block;">
                <div class="p-img" style="background-color: var(--c-red);">
                    <img src="<?= htmlspecialchars($project['thumbnail']) ?>"
                        alt="<?= htmlspecialchars($project['title']) ?>">
                </div>
                <div class="p-info">
                    <h3 class="p-title"><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['category_label']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
    <div style="display: flex; justify-content: center; margin-top: 3rem;">
        <a href="portfolio.php" class="btn-submit" style="text-decoration: none; display: inline-block;">Voir plus de
            projets</a>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>