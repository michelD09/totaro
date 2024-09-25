<?php
include "outils_md.php" ;
session_start() ;

$photos = $_SESSION["photos"] ; // on récupère la variable globale de session

if (isset($_POST['clé'])) { // cas où on a utilisé le champ "clé" du formulaire
    $typeRéponseFormulaire = "clé" ;
    $réponse = $_POST['clé'] ;
    //$photosFiltrées = filtreParMotClé($photos, $réponse) ;
    $message = "<p>Vous avez demandé les images correspondant au mot-clé " . $réponse . "</p>" ;
}

if (isset($_POST['année'])) { // on a utilisé le champ "année" du formulaire
    $typeRéponseFormulaire = "année" ;
    $réponse = $_POST['année'] ;
    //$photosFiltrées = filtreParAnnée($photos, $réponse) ;
    $message = "<p>Vous avez demandé les images de " . $réponse . "</p>" ;
}

$photosFiltrées = filtre($typeRéponseFormulaire, $photos, $réponse) ;
?>

<!doctype html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Site photos de famille</title>
        <link rel="stylesheet" href="sitefamille_md.css" media="screen">
        <script src="sitefamille_md.js" defer></script>
    </head>
    <body id="Traitement_md">
        <main>
            <div>
                <p>
                    <?php
                    echo $message ;
                    ?>
                </p>    
            </div>
            <div id="photos">
                <?php
                $nbImages = 0 ;
                foreach($photosFiltrées as $photo) {
                    $html = '<img onclick="PleinEcran(event)" src="' . $photo . '">' ;
                    echo $html ;
                    $nbImages = $nbImages + 1 ;
                }
                ?>
            </div>
            <div>
                <?php
                if ($nbImages == 0) {
                echo "<p>Votre requête n'a produit aucun résultat</p>" ;
                } else {
                echo "<p>En cliquant sur une image vous pouvez l'afficher en plein écran</p>" ;
                }
                ?>
            </div>
            <div>
                <p>
                    <a href="Accueil_md.php">RETOUR au FORMULAIRE</a> 
                </p>
            </div>
        </main>
    </body>
