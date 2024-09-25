zoom = false ;
/* function PleinEcran(e) {
 *     var elem = e.target ; // l'élément sur lequel on a cliqué
 *     // bascule
 *     if (document.fullscreenElement) { // si on est déjà en plein écran
 *         document.exitFullscreen() ; // on revient à l'affichage normal
 *     } else { // sinon
 *         elem.requestFullscreen() ; // on met l'élément en plein écran
 *     }
 * } */
// function PleinEcran(e) {             
//     if (! document.fullscreenElement &&
//         ! document.mozFullScreenElement &&
//         ! document.webkitFullscreenElement &&
//         ! document.msFullscreenElement) {
//         if (document.documentElement.requestFullscreen) { // javascript standard
//             document.documentElement.requestFullscreen() ;
//         } else if (document.documentElement.mozRequestFullScreen) { // pour firefox et dérivés 
//             document.documentElement.mozRequestFullScreen() ;
//         } else if (document.documentElement.webkitRequestFullscreen) { // pour chrome
//             document.documentElement.webkitRequestFullscreen() ;
//         } else if (document.documentElement.msRequestFullscreen) { // pour edge 
//             document.documentElement.msRequestFullscreen() ;
//         }
//     } else {
//         if (document.exitFullscreen) {
//             document.exitFullscreen() ;
//         } else if (document.mozCancelFullScreen) {
//             document.mozCancelFullScreen() ;
//         } else if (document.webkitExitFullscreen) {
//             document.webkitExitFullscreen() ;
//         } else if (document.msExitFullscreen) {
//             document.msExitFullscreen() ;
//         }
//     }
// }

function PleinEcran(e) {
    var elem = e.target ;
     if (zoom === false) { // on passe en mode zoom
         zoom = true ;
         elem.className = "zoom" ;
     } else { // on annule le zoom
         zoom = false ;
         elem.className = "miniature" ;
     }
}
