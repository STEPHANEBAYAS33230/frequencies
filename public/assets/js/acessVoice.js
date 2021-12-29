let arrayId=[];
getIds("total");
lecture("bonjour et bienvenue sur le site de","fr");
lecture(" Frequencies.","en")
for (i=0;i<arrayId.length;i++) {
    identificationId(arrayId[i]);

}
function getIds(elt) {
    var node = document.getElementById(elt);
    var children = node.childNodes;
    var k;

    for (k = 0; k < children.length; k++) {

        // On teste si l'id de l'élément est défini
        if (document.getElementById(children[k].id)) {

            //alert(children[k].id);
            //  Ici on fait son traitement.
            arrayId.push((children[k].id));
            if (document.getElementById(children[k].id).hasChildNodes()) {

                getIds(children[k].id);
            }
        }
    }
   //alert(arrayId);
}

function lecture(texte,langue) {
        let parole=new SpeechSynthesisUtterance();
        parole.text=texte;
        parole.lang=langue;
        parole.pitch=1.8; // 0-2 hauteur
        parole.rate=1.2 //0.5-2 vitesse
        parole.volume=1; //0-1 volume
        speechSynthesis.speak(parole);



}

function identificationId(arrayid) {
    var langueId;
    if (arrayid.indexOf("anglais") !== -1) {
        langueId="en";
    } else {langueId="fr";}
    if (arrayid.indexOf("suite")<0) {
        if (arrayid.indexOf("navBar") !== -1) {
            var numeroMenu = arrayid.substring(6);
            lecture("Nous avons un menu de navigation numero : " + numeroMenu+" avec différentes options que nous verrons par la suite",langueId);

        }

        //*titre
        if (arrayid.indexOf("TitreText") !== -1) {
            lecture("Esuite nous avons un titre qui est : ","fr");
            lecture((document.getElementById(arrayid).innerHTML),langueId);

        }

        //*option de menu
        if (arrayid.indexOf("menuOption") !== -1) {
            var numeroOption = arrayid.substring(10);
            lecture("option numero "+numeroOption+"du menu de navigation est : "+(document.getElementById(arrayid).innerHTML),langueId);

        }
        //*paragraphe
        if (arrayid.indexOf("Paragraphe") !== -1) {
            lecture((document.getElementById(arrayid).innerHTML),langueId);

        }










    }
}
