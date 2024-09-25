<?php
// Fichier où le hash du mot de passe est stocké
$fichierHash = './mdp.txt';

// Fonction pour lire le hash du fichier texte
function lireHashMotDePasse($fichierHash) {
    $hash = trim(file_get_contents($fichierHash)) ; // On récupèrd le hash, sans espace initial ou final
    return $hash ;
}

// Si le formulaire a été soumis
/* if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     $mot_de_passe = $_POST['motDePasse'] ?? '';
 * 
 *     if (empty($mot_de_passe)) {
 *         echo "Veuillez entrer un mot de passe.";
 *     } else {
 *         // Lire le hash du fichier
 *         $hash_enregistre = lireHashMotDePasse($fichierHash);
 * 
 *         // Vérifier le mot de passe saisi par l'utilisateur avec le hash
 *         if (password_verify($mot_de_passe, $hash_enregistre)) {
 *             echo "Mot de passe correct !";
 *         } else {
 *             echo "Mot de passe incorrect.";
 *         }
 *     }
 * } */

$mdp = $_POST['motDePasse'] ?? '';

if (! isset($mdp)) {
    echo "Veuillez entrer un mot de passe.";
} else {
    // Lire le hash du fichier
    $hash = lireHashMotDePasse($fichierHash);

    // Vérifier le mot de passe saisi par l'utilisateur avec le hash
    if (password_verify($mdp, $hash)) {
        echo "Mot de passe correct !";
    } else {
        echo "Mot de passe incorrect.";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Vérification du mot de passe</title>
    </head>
    <body>
        <h2>Veuillez entrer votre mot de passe :</h2>
        <form method="POST" action="">
            <label for="motDePasse">Mot de passe :</label>
            <input type="password" id="motDePasse" name="motDePasse" required>
            <button type="submit">Vérifier</button>
        </form>
    </body>
</html>
