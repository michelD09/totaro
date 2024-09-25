<?php
include "outils_md.php" ;
session_start() ;
$listePhotos = listeFichiers("Photos et Videos par année", Array()) ;
if ($_SESSION["photos"] === null) {
    $_SESSION["photos"] = $listePhotos ;
}
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
    <body id="Accueil_md">
        <header>
            <img src="Icone photo d'une famille.png">
            <div>
                <h1>Bonjour sur le site de notre petite (enfin grande...) famille !</h1> 
                <p>
                    Sur ce site vous trouverez nos photos classées par année ou par destination / thème.<br>
                    Si besoin vous pouvez utiliser le Moteur de Recherche pour aller droit au but. Tapez une année ou un mot clé !<br>
                    Bonne navigation !
                </p>
            </div>
        </header>
        <main>
            <!-- Recherche par mot-clé -->
            <div id="moteurDeRecherche">
                <h2>Moteur de Recherche</h2>
                <form method='post' action='Traitement_md.php'>
                    <label>Tapez un mot clé</label>
                    <input type='text' name='clé'>
                </form>
            </div>

            <!-- Menu déroulant pour choisir une Année -->
            <div id="menuChoixAnnée">
                <h2> Cliquez sur l'année dans le menu déroulant<br>pour afficher les photos de l'année.</h2>
                <?php FormulaireMenuListeDesAnnées("Photos et Videos par année") ; ?>
            </div>
        </main>
    </body>
</html>
