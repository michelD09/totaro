<?php
$motDePasse = "abcd" ; // Avant l'exécution, remplacer ici par le mot de passe choisi
$fichierMotDePasse = "./mdp.txt" ;

CréationMotDePasse($motDePasse, $fichierMotDePasse) ;

function CréationMotDePasse($motDePasse, $fichierMotDePasse) {
    $hash = password_hash($motDePasse, PASSWORD_DEFAULT) ;  // Générer le hash
    echo $hash . "<br>" ;
    file_put_contents($fichierMotDePasse, $hash) ;  // Stocker le hash dans le fichier mdp.txt
    echo "le mot de passe a été créé" ;
}

?>
