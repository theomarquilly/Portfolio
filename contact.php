<?php
$pageTitle = 'Theo Marquilly - Contact';
$currentPage = 'contact';

include __DIR__ . '/includes/header.php';
?>

<!-- Page Header -->
<header style="text-align: center; padding: 4rem 1rem;">
    <h1 class="hero-title" style="font-size: 8vw; margin-bottom: 1rem;">Contact</h1>
    <p class="tagline" style="background: none; padding: 0;">Restons en contact !</p>
</header>

<div class="stripes-container">
    <div class="stripe s5"></div>
    <div class="stripe s1"></div>
</div>

<section class="contact-section">
    <div class="bio-content" style="margin-bottom: 2rem;">
        <p><strong>Email :</strong> <br><br> <a href="mailto:theomarquilly19@gmail.com">theomarquilly19@gmail.com</a></p>

        <p>N'hésitez pas à m'envoyer un message pour toute proposition de projet, collaboration ou simple échange
            autour du cinéma et du graphisme.</p>
    </div>

    <form action="https://formspree.io/f/xlglojng" method="POST" class="contact-form">
        <div class="form-group">
            <label for="name">Nom</label>
            <input type="text" id="name" name="name" required>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>

        <div class="form-group">
            <label for="message">Message</label>
            <textarea id="message" name="message" required></textarea>
        </div>

        <button type="submit" class="btn-submit">Envoyer</button>
    </form>
</section>

<?php
include __DIR__ . '/includes/footer.php';
?>
