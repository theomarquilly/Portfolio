<?php
require_once __DIR__ . '/data/projects.php';

$pageTitle = 'Theo Marquilly - Portfolio';
$currentPage = 'portfolio';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<header style="text-align: center; padding: 4rem 1rem;">
    <h1 class="hero-title" style="font-size: 8vw; margin-bottom: 1rem;">Réalisations</h1>
    <p class="tagline" style="background: none; padding: 0;">Audiovisuel & Communication</p>
</header>

<div class="stripes-container">
    <div class="stripe s3"></div>
    <div class="stripe s4"></div>
</div>

<!-- Main Projects Section -->
<section class="projects">
    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <button class="filter-btn active" data-filter="all">Tout</button>
        <button class="filter-btn" data-filter="audiovisuel">Audiovisuel</button>
        <button class="filter-btn" data-filter="graphisme">Graphisme</button>
    </div>

    <div class="project-grid">
        <?php foreach (get_all_projects() as $slug => $project): ?>
            <a href="project.php?id=<?= urlencode($slug) ?>" class="project-card" data-category="<?= htmlspecialchars($project['category']) ?>"
                style="text-decoration: none; color: inherit; display: block;">
                <div class="p-img" style="background-color: var(--c-red);">
                    <img src="<?= htmlspecialchars($project['thumbnail']) ?>" alt="<?= htmlspecialchars($project['title']) ?>">
                </div>
                <div class="p-info">
                    <h3 class="p-title"><?= htmlspecialchars($project['title']) ?></h3>
                    <p><?= htmlspecialchars($project['category_label']) ?></p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
