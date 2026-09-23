<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars(isset($pageTitle) ? $pageTitle : 'Theo Marquilly - Portfolio') ?></title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="img/logo/logomini.png">
    <link rel="shortcut icon" type="image/png" href="img/logo/logomini.png">
    <link rel="apple-touch-icon" href="img/logo/logomini.png">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;800;900&family=Playfair+Display:ital@1&display=swap"
        rel="stylesheet">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="style.css?v=100">
</head>

<body>

    <!-- Navigation -->
    <nav>
        <a href="index.php">
            <img src="img/logo/logo.png" alt="Logo" class="nav-logo">
        </a>
        <div class="burger">
            <div class="line1"></div>
            <div class="line2"></div>
            <div class="line3"></div>
        </div>
        <div class="nav-links">
            <a href="index.php" class="<?= (isset($currentPage) && $currentPage === 'home') ? 'active' : '' ?>">Accueil</a>
            <a href="portfolio.php" class="<?= (isset($currentPage) && ($currentPage === 'portfolio' || $currentPage === 'project')) ? 'active' : '' ?>">Portfolio</a>
            <a href="contact.php" class="<?= (isset($currentPage) && $currentPage === 'contact') ? 'active' : '' ?>">Contact</a>
        </div>
    </nav>
