<?php
function listeFichiers($dossier, $liste) {
    /* renvoie tous les fichiers du $dossier, récursivement */
    $fichiersEtDossiers = scandir($dossier) ;
    foreach ($fichiersEtDossiers as $fd) {
        $chemin = $dossier . DIRECTORY_SEPARATOR . $fd ; // la constante DIRECTORY_SEPARATOR est remplacée par "/" ou par "\" selon que l'on est sous Linux ou sous Windows.
        $chemin = $dossier . "/" . $fd ;
        if (! is_dir($chemin)) {
            array_push($liste, $chemin) ; // on ajoute le $chemin à la $liste
        } else if ($fd != "." && $fd != "..") {
            $liste = listeFichiers($chemin, $liste) ; // appel récursif
        }
    }
    return $liste ;
}

function filtre($type, $liste, $clé) {
    /*
       Fonction destinée à filtrer le tableau (non associatif) $liste selon le mot-clé $clé.
       $type est un mot qui permet de choisir la fonction de rappel servant à filter à l'aide de array_filter (voir la partie switch ci-dessous).
       TODO : pour $clé, peut-on utiliser une regex plutôt qu'un mot-clé ? 
     */ 
    return array_filter(
        $liste,
        function ($élément) use ($type, $clé) { // $élément est un élément de la $liste
            switch ($type) {                  
                case "clé":
                    if (strpos($élément, $clé) !== false) {return $élément ;}
                    break ;
                case "année":
                    // si on veut que l'année soit n'importe où dans le chemin parent
                    $cheminParent = dirname($élément) ;
                    if (strpos($cheminParent, $clé) !== false) {return $cheminParent ;}
                    break ;
                case "année1":
                    // si on veut que l'année soit le dossier parent de premier niveau
                    $composantsChemin = explode(DIRECTORY_SEPARATOR, $élément) ; // On décompose le chemin : si $élément = "/abc/def/ghi.jpg" alors $composantsChemin = Array("abc", "def", "ghi.jpg")
                    $dossierParent = $composantsChemins[count($composantsChemin) - 2] ; // l'avant dernier composant du chemin 
                    if ($dossierParent == $année) {
                        return $élément ;
                    }       
            }
        }
    ) ;
}

function FormulaireMenuListeDesAnnées($dossier) {
    // cette fonction permet de récupérer la liste des années de manière dynamiques
    // ici on veut juste récupérer la liste des années en dynamique pour composer le menu utilisateur, car la liste des années va augmenter tous les ans et il ne faut pas retoucher le programme tous les ans
    // ce sont en fait les noms des sous dossiers du dossier principal "Photos & Videos année par année"
    $ListeDesAnnées = scandir($dossier); 
    echo "<form method='post' action='Traitement_md.php'>" ;
    echo "<select name='année' size='1'>" ;
    foreach ($ListeDesAnnées as $fd) {
        $chemin = $dossier . DIRECTORY_SEPARATOR . $fd ;
        $chemin = $dossier . "/" . $fd ;
        if ($fd != "." && $fd != "..") {
            echo ("<option value='$fd'>Année $fd</option>");
        }
    }
    echo ("</select>") ;
    echo ("<button type='submit'>OK</button>") ;
    echo ("</form>") ;            
}

/***** Bases de données ****/
function connexionBD($host_name, $database, $user_name, $password) {
    /* Renvoie le pointeur de connexion à la base de données ; échoue en cas d'erreur en indiquant le type d'erreur */
    $dbh = null ;
    try {
        $dbh = new PDO("mysql:host=$host_name; dbname=$database;", $user_name, $password) ;
        $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Échec de la connexion, avec le message d'erreur suivant :<br>" ;
        echo $e->getMessage() . "<br/>" ;
        die() ;
    }
    return $dbh ;
}

function tableau_tuple($aTableauPhp) {
    // Convertit un tableau php array("a", "b", "c") en chaine-tuple sql "(a, b, c)"
    return "(" . implode(",", $aTableauPhp) . ")" ;
}

function conversionTableauPhpEnTupleSql($aTableauPhp) {
    /* Exemple : conversionTableauPhpEnTupleSql(array("a", "b", "c")) renvoie la chaine '("a", "b", "c")' */
    $t = "(" ;
    foreach ($aTableauPhp as $k => $v) {
        $t .= $v . ", " ;
    }
    $t = substr($t, 0, -2) ; // on enlève la dernière virgule et le dernier espace
    $t .= ")" ;  // et on ajoute une parenthèse
    echo $t ;
    return $t ;
}

function dbg($val) {
    echo "<br>-----" ;
    print_r($val) ;
    echo "-----<br>" ;
}

function sqlINSERT($table, $aChamps) {
    /* Renvoie le code sql correspondant à
       INSERT INTO $table (champ1, champ2, ...) VALUES (?, ?, ...) ;
       lorsque $aChamps = array("champ1", "champ2", ...)
     */
    $sql = "INSERT INTO $table " ;
    $sql .= tableau_tuple($aChamps) ;
    $marqueurs = array_fill(0, count($aChamps), "?") ; // array("champ1", "champ2", ...)
    $sql .= tableau_tuple($marqueurs) ; // chaine "(champ1, champ2, ...)"
    $sql .= " ;" ;
    return $sql ;
}

function exSql($connexion, $sql, $aEnrs=array()) {
    $req = $connexion->prepare($sql) ;
    if (count($aEnrs) == 0) { // il n'y a pas d'enregistrements
        $res = $req->execute() ;
    } else {
        $index = 1 ;
        foreach($aEnrs[0] as $col) { // premier enregistrement
            ${"col" . $index} = $col ; // on définit une variable pour chaque valeur de colonne
            $req->bindParam($index, ${"col" . $index}, pdo_param($value)) ;
            $index = $index + 1 ;
        }
        $res = $req->execute() ;
        $aEnrs.shift() ; // on enlève le premier enregistrement 
        foreach ($aEnrs as $aEnr) { // pour chaque enregistrement autre que le premier
            $index = 1 ;
            foreach ($aEnr as $col) {
                ${"col" . $i} = $col ; // on utilise les variables 
                $index = $index + 1 ;
            }
            $res = $req->execute() ;
        }
    }
    return $res ;
}

function exSqlINSERT($connexion, $table, $aChamps, $aEnrs) {
    $sql = sqlINSERT($table, $aChamps) ;
    $res = exSql($connexion, $sql, $aEnrs) ;
    return $res ;
 }
function prepSqlINSERT($connexion, $table, $aChamps) {
    $sql = sqlINSERT($table, $aChamps) ;
    $req = $connexion->prepare($sql) ;
    return $req ;
}

function exSqlINSERT($connexion, $table, $aChamps, $aEnregistrements) {
    $req = prepSqlINSERT($connexion, $table, $aChamps) ;
    $res = $req->execute($req, $aEnregistrements) ;
    return $res ;
}

function execSql($connexion, $sql, $aValeursChamps) {
    /* Renvoie un tableau Array($ok, $res) où $ok == true et $res est le résultat de l'exécution de la requête $sql si la requête a réussi, sinon $ok = false et $res est le message d'erreur.
       Pour chaque variable notée par un point d'interrogation dans $sql, une valeur et un type (parmi "INT", "STR", ...) sont précisés dans les tableaux $aValeurs et $aTypesValeurs.
       Exemple : $sql= 'SELECT name, colour, calories FROM fruit  WHERE calories < ? AND colour = ?", $aValeursChamps = Array(150, "red") .
     */
    try {
        $req = $connexion->prepare($sql) ;
        foreach ($aValeursChamps as $k=>&$val) { // Attention au & avant $val
            $req->bindParam($k + 1, $val) ;
        }
        $res = $req->execute() ;
        if (!$res) {
            // Récupérer les informations d'erreur
            $errorInfo = $req->errorInfo();
            echo 'SQLSTATE: ' . $errorInfo[0] . '<br>';
            echo 'Error Code: ' . $errorInfo[1] . '<br>';
            echo 'Error Message: ' . $errorInfo[2] . '<br>';
        } else {
            $res = Array(true, $res) ;
        }
    }
    catch(PDOException $e) {
        $msg = "Erreur dans le traitement de la requêtre sql : " . $sql . "<br>" ;
        if (count($aValeursChamps) > 0) {
            $msg .= "utilisant les valeurs :" . "<br>" ;
            foreach ($aValeursChamps as $i=>$val) {
                $msg .= $val . " de type " . gettype($val) . "<br>" ;
            }
        }
        $msg .= "Avec le message d'erreur suivant :<br>" ;
        $msg .=  $e->getMessage() . "<br>" ;
        $res = Array(false, $msg) ;
    }
    return $res ;
}

function suppressionTable($connexion, $nomTable) {
    $sql = "DROP TABLE " . $nomTable ;
    $res = execSql($connexion, $sql, array()) ;
    return $res ;
}

function créationTable($connexion, $nomTable, $aChamps) {
    /*
       Exemple : pour la commande sql CREATE TABLE IF NOT EXISTS "TableEssai" (id INT AUTO_INCREMENT PRIMARY_KEY, nom VARCHAR(255) NOT NULL, age INT) :
       $ch = Array("id INT AUTO_INCREMENT PRIMARY KEY", "nom VARCHAR(255) NOT NULL", "age INT")
       CréeTable($con, "TableEssai", $ch)
     */
    $sql = "CREATE TABLE IF NOT EXISTS " . $nomTable  ;
    $sql .= conversionTableauPhpEnTupleSql($aChamps) ;
    $res = execSql($connexion, $sql) ;
    return $res ;
}

function insertion($connexion, $table, $aChamps, $aValeurs) {
    $sql = "INSERT INTO " . $table ;
    $sql .= conversionTableauPhpEnTupleSql($aChamps) ;
    $sql .= " VALUES " . conversionTableauPhpEnTupleSql(array_fill(0, count($aValeurs), "?")) ;
    return execSql($connexion, $sql, $aValeurs, $aTypesValeurs) ;
}

/*** alternatives (à tester) à execSql ***/
function pdo_param($value) {
    /* Renvoie la constante PDO::PARAM_ correspondant au type de $value */
    if(is_int($value))
        $param = PDO::PARAM_INT;
    elseif(is_bool($value))
        $param = PDO::PARAM_BOOL;
    elseif(is_null($value))
        $param = PDO::PARAM_NULL;
    elseif(is_string($value))
        $param = PDO::PARAM_STR;
    else
        $param = FALSE;
    return $param ;
}

function execSql1($connexion, $sql, $aValeurs_Types) {
    /* Version alternative 1 */
    /* $sql est une requête avec des paramètres anonymes ? 
       $aValeurs_Types= array("va1"=>TYPE1, "val2"=Type2, ...)
       où les types sont les constantes (sans guillemets) PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_BOOL ou PDO::PARAM_NULL précisant le type de $val1, $val2, ...
     */
    try {
        $req = $connexion->prepare($sql) ;
        $index = 0 ;
        foreach ($aValeurs_Types as $val=>$type) {
            // $req->bindValue($index + 1, $val, "PDO::PARAM_" . $type) ; // ne fonctionne pas
            $req->bindValue($index + 1, $val, $type) ; // l'index des paramètres ? commence à 1
            $index = $index + 1 ;
        }
        $res = $req->execute() ;
        // print_r($req) ;
        $res = Array(true, $res) ;
        print_r("<br>OK1 : " . $res . "<br>") ;
        return $res ;
    }
    catch(PDOException $e) {
        $msg = "Erreur dans le traitement de la requêtre sql : " . $sql . "<br>" ;
        if (count($aValeurs) > 0) {
            $msg .= "utilisant les valeurs :" . "<br>" ;
            foreach ($aValeurs as $i=>$val) {
                $msg .= $val . " de type " . $aTypesValeurs[$i] . "<br>" ;
            }
        }
        $msg .= "Avec le message d'erreur suivant :<br>" ;
        $msg .=  $e->getMessage() . "<br>" ;
        $res = Array(false, $msg) ;
        print_r("<br>OK2 : " . $res . "<br>") ;        
    }
    return $res ;
}    

function execSql2($connexion, $sql, $aValeurs) {
    /* Version alternative 2 */
    /* $sql est une requête avec des paramètres anonymes ? 
       $aValeurs= array("va1", "val2", ...)
       où les types de $val1, $val2, ... sont donnés par la fonction pdo_param
     */
    try {
        $req = $connexion->prepare($sql) ;
        $index = 0 ;
        foreach ($aValeurs as $val) {
            // $req->bindValue($index + 1, $val, "PDO::PARAM_" . $type) ; // ne fonctionne pas
            $req->bindValue($index + 1, $val, pdo_param($val)) ; // l'indes des paramètres ? commence à 1
            $index = $index + 1 ;
        }
        $res = $req->execute() ;
        // print_r($req) ;
        $res = Array(true, $res) ;
        print_r("<br>OK1 : " . $res . "<br>") ;
        return $res ;
    }
    catch(PDOException $e) {
        $msg = "Erreur dans le traitement de la requêtre sql : " . $sql . "<br>" ;
        if (count($aValeurs) > 0) {
            $msg .= "utilisant les valeurs :" . "<br>" ;
            foreach ($aValeurs as $i=>$val) {
                $msg .= $val . " de type " . $aTypesValeurs[$i] . "<br>" ;
            }
        }
        $msg .= "Avec le message d'erreur suivant :<br>" ;
        $msg .=  $e->getMessage() . "<br>" ;
        $res = Array(false, $msg) ;
        print_r("<br>OK2 : " . $res . "<br>") ;        
    }
    return $res ;
}    

/***** Fin Bases de données *****/

/***** Redimensionnement des images *****/
// IMPORTANT : il faut que Imagemagick et Imagick soient installées
?>
<?php
// Chemin du dossier source contenant les images
$dossierSrc = '/chemin/Images/Origine';

// Chemin du dossier de destination pour les images redimensionnées
$dossierSrc = '/chemin/images/Redimensionnées';

// Hauteur fixe des images redimensionnées
$hauteurFixe = 100 ;

// Fonction pour redimensionner et copier les images
function RedimensionnerHauteurImage($cheminImgSrc, $cheminImgDest, $hauteurFixe) {
    try {
        $image = new Imagick($cheminImgSrc->getPathname()) ; // Charger l'image avec Imagick
        $largeur = $image->getImageWidth() ;  // Obtenir les dimensions actuelles de l'image
        $hauteur = $image->getImageHeight() ;
        $nouvelleLargeur = ($hauteurFixe / $hauteur) * $largeur ; // Calculer la nouvelle largeur en maintenant le rapport largeur/hauteur 
        $nouvelleHauteur = $hauteurFixe ;
        $image->resizeImage($nouvelleLargeur, $nouvelleHauteur, Imagick::FILTER_LANCZOS, 1) ;  // Redimensionner l'image
        $dossierDest = dirname($cheminImgDest) ;
        if (!file_exists($dossierDest)) {
            mkdir($dossierDest, 0777, true) ;
        }
        $image->writeImage($cheminImgDest) ; // Sauvegarder l'image redimensionnée dans le dossier de destination
        $image->clear() ; // Libérer la mémoire
        $image->destroy() ;
    } catch (Exception $e) {
        echo "Erreur lors du redimensionnement de l'image : " . $cheminImgSrc->getPathname() . " - " . $e->getMessage() . "\n" ;
    }
}

function RedimensionnerHauteurImages($dossierSrc, $dossierDest, $hauteurFixe) {
    /* Redimensionne les images dans $dossierSrc, récursivement, en fixant leur hauteur à $hauteurFixe (le rapport de forme est conservé) ; les images redimensionnées sont placées dans $dossierDest */ 
    $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dossierSrc)) ;  // Créer un itérateur récursif pour parcourir tous les fichiers dans le dossier et les sous-dossiers
    foreach ($iter as $fichier) {
        if ($fichier->isFile() && in_array(strtolower($fichier->getExtension()), ['jpg', 'jpeg', 'png', 'gif'])) { // Vérifier si le fichier est une image (en fonction de l'extension)
            $fichierSrc = str_replace($dossierSrc, '', $fichier->getPathname()) ; // Construire le chemin de destination
            $cheminDest1 = $dossierDest . $fichierSrc ;                
            $dossierDest1 = dirname($cheminDest1) ; // Créer les sous-dossiers dans le dossier de destination
            RedimensionneHauteurImage($fichier, $cheminDest1, $hauteurFixe) ;
        }
    }
}

// Appeler la fonction pour redimensionner et copier les images dans le dossier de destination
// RedimensionnerHauteurImages($dossierSrc, $dossierDest, $hauteurFixe);

/***** Fin Redimensionnement des images *****/

/***** ANCIENNES VERSIONS *****/
/*** Remplacées par la fonction filtre ***/
function filtreParMotClé($liste, $clé) {
    return array_filter(
        $liste,
        function ($élément) use ($clé) {
            if (strpos($élément, $clé) !== false) {return $élément ;}
        }
    ) ;
}

function filtreParAnnée($liste, $année) {
    return array_filter(
        $liste,
        function($élément) use ($année) {
            // si on veut que l'année soit n'importe où dans le chemin parent
            $cheminParent = dirname($élément) ;
            if (strpos($cheminParent, $année)) {return $cheminParent ;}
            // si on veut que l'année soit le dossier parent de premier niveau
            /*
               $composantsChemin = explode(DIRECTORY_SEPARATOR, $élément) ; // On décompose le chemin : si $élément = "/abc/def/ghi.jpg" alors $composantsChemin = Array("abc", "def", "ghi.jpg")
               $dossierParent = $composantsChemins[count($composantsChemin) - 2] ; // l'avant dernier composant du chemin 
               if ($dossierParent == $année) {
               return $élément ;
               }
             */
        }              
    ) ;
}
?>
