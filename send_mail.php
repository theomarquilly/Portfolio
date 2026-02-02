<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Récupération et nettoyage des données
    $name = strip_tags(trim($_POST["name"]));
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = strip_tags(trim($_POST["message"]));

    // 2. Validation
    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Redirection avec erreur
        header("Location: contact.html?status=error&msg=invalid_input");
        exit;
    }

    // 3. Configuration de l'email
    $recipient = "theomarquilly19@gmail.com";
    $subject = "Nouveau message de $name via le Portfolio";
    
    $email_content = "Nom: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    $email_headers = "From: $name <$email>";

    // 4. Envoi
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Succès
        header("Location: contact.html?status=success");
    } else {
        // Échec serveur (ex: pas de serveur mail configuré en local)
        header("Location: contact.html?status=error&msg=server_error");
    }
} else {
    // Si quelqu'un essaie d'ouvrir le fichier directement sans POST
    header("Location: contact.html");
}
?>
