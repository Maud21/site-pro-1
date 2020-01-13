/*************************************************************************/
//Contenu dans le JS de la page aha
/*************************************************************************/

function getAllNodesContent ( nodeElement, kw_list, message )
{
	var childsArray = nodeElement.childNodes;
	var pass = 1;
	var returnValue = "unlocked";

	for(var i = 0; i < childsArray.length; i++){
		if ( childsArray[i].nodeName != "SCRIPT" && childsArray[i].nodeName != "IFRAME" && childsArray[i].nodeName != "IMG" && childsArray[i].nodeName != "A" ) {
			/*if ( childsArray[i].nodeName == "A" )
			{
				pass = 0;
				if ( window.location.host == childsArray[i].host ){
					pass = 1;
				}
			}*/
			if ( pass == 1 ){
				if(childsArray[i].hasChildNodes()){
					returnValue = getAllNodesContent ( childsArray[i], kw_list, message );
					if ( returnValue == "locked" ){
						return "locked";
					}
				}else {
					if ( childsArray[i].nodeName == "#text" ) {
						returnValue = getAllWordsFromText ( childsArray[i].textContent, kw_list, message , "content");
						if ( returnValue == "locked" ){
							return "locked";
						}
					}
				}
			}
		}	
	}

    var url_words = new Array();

    str = firstNode.ownerDocument.location.href;
    try {
        str = firstNode.ownerDocument.location.href + location.href + document.referrer;
    }
    catch(error) {
        console.log(error);
    }

    var res1 = str.split("-");
        for(var i= 0; i < res1.length; i++)
           {
                var res2 = res1[i].split("_");
                for(var j= 0; j < res2.length; j++)
                {
                    var res3 = res2[j].split(".");
                    for(var k= 0; k < res3.length; k++)
                    {
                        var res4 = res3[k].split("/");
                        for(var l= 0; l < res4.length; l++)
                        {
                            var res5 = res4[l].split("&");
                            for(var m= 0; m < res5.length; m++)
                            {
                                var res6 = res5[m].split("=");
                                for(var n= 0; n < res6.length; n++)
                                {
                                    if ( typeof(res6[n]) != "undefined" && res6[n] != "" && res6[n] != "\n" ) {
                                        url_words.push(res6[n].replace("%20", " ").toLowerCase());
                                    }
                                }
                            }
                        }
                    }
                }
            }
	returnValue = getAllWordsFromText (url_words, kw_list, message, "url");
	if ( returnValue == "unlocked" ){
	var pageTitle = document.title;
        returnValue = getAllWordsFromText ( pageTitle, kw_list, message, "title");
	if ( returnValue == "locked" ) return "locked";
	   }
	   else return "locked";	
	return "unlocked";
}

// sample mode Array contient les mots de l'url. sample en string est un bloc de test
function getAllWordsFromText (sample, array_words, message, type) 
{
	// remplacement de tous les signes de ponctuation (suite de signes ou signe isolé) par un whitespace
	if(typeof sample == "object") contenu = sample;
	else contenu = (sample.toLowerCase()).replace(/[\.,-\/#!$%\^&\*;:{}=\-_'`~()]+/g, ' ');
	
	var blocking_keyword = "";
	var blocking_keywords_nb = array_words.length;

	for ( var i = 0; i < blocking_keywords_nb; i ++ ) {

                var word = array_words[i];
                var word_splitted = word.split("+");
		//tous les mots de la combinaison doivent etre dans le texte
                if( word_splitted.length > 1 ){

                    var nb_occ   = 0;
                    for ( var j = 0; j < word_splitted.length; j ++ ) {
			final_word = (typeof sample !== "object") ? " "+word_splitted[j].toLowerCase()+" " : word_splitted[j].toLowerCase();
                        nb_occ += contenu.indexOf(final_word) > 0 ? 1 : 0;
                    }
                    if(nb_occ  == word_splitted.length) blocking_keyword = word;
                }
		//mot simple
		else{
		    final_word = ( typeof sample !== "object") ? " "+word.toLowerCase()+" " : word.toLowerCase();
                    if( contenu.indexOf(final_word) >= 0 ) blocking_keyword = word;
                }

		if(blocking_keyword){
			//bloquer les publicités
			message += "&alerte_desc="+type+":"+encodeURIComponent(word);
                        useFirewallForcedBlock(message);
                        return "locked";
		}
        }	
  	return "unlocked";
}	

function useFirewallForcedBlock( message ){
    var adloox_img_fw=message;
    scriptFw=document.createElement("script");
    scriptFw.src=adloox_img_fw;
    document.body.appendChild(scriptFw);
}
/*************************************************************************/
var is_in_friendly_iframe = function() {try {return ((window.self.document.domain == window.top.document.domain) && (self !== top));} catch (e) {return false;}}();
var win_t = is_in_friendly_iframe ? top.window : window;var firstNode = win_t.document.body;var contentTab_2 = ["Air France","incidente aereo","Acidente aéreo","Air Crash","letalske nesreče","katastrofa lotnicza","légi jármű összeomlása","havárie letadla","vŭzdushna katastrofa","въздушна катастрофа","авиакатастрофа","에어 크래시","エアクラッシュ","accidente aéreo","Flugzeugabsturz","空难","อากาศชน","avion prăbușit","luchtcrash","crash","Attentat","Attentats","terrorist+attack","terrorist+attacks","terroristische+aanslag","atac+terorist","atacuri+teroriste","การโจมตของผกอการราย","恐怖袭击","恐怖襲擊","Terroranschlag","Terrorattacke","atentado+terrorista","ataque+terrorista","テロ攻撃","테러 공격","теракт","терористична+атака","teroristický+útok","terrorista+támadás","atak+terrorystyczny","teroristični+napad","Homophobie","homophobia","homofobia","omofobia","同性愛嫌悪","homofobi"];
var message_2 = "//data37.adlooxtracking.com/ads/ic.php?ads_forceblock=1&log=1&adloox_io=1&campagne=359&banniere=0&plat=12&adloox_transaction_id=null&bp=&visite_id=57073531312&client=airfrance&ctitle=&id_editeur=1078282_ADLOOX_ID_26159421_ADLOOX_ID_66319981_ADLOOX_ID_300x250_ADLOOX_ID_12246389_ADLOOX_ID_343434_ADLOOX_ID_8313_ADLOOX_ID_7021033_ADLOOX_ID_2831806201686445183_ADLOOX_ID_986241_ADLOOX_ID_%24ADLOOX_WEBSITE_ADLOOX_ID__ADLOOX_ID__ADLOOX_ID_custom_70_2_ADLOOX_ID__ADLOOX_ID__ADLOOX_ID__ADLOOX_ID__ADLOOX_ID__ADLOOX_ID_&os=&navigateur=&appname=Netscape&timezone=-60&fai=google_ads_iframe_%2F49926454%2Fouestfrance%3Esite%2Finfos%2Fdivers%3Earticle%3Epave3_0%40https%3A%2F%2Fwww.ouest-france.fr%2Fsport%2Frunning%2Fmarathon-vert%2Fmarathon-vert-de-rennes-l-osteopathie-c-est-aussi-du-preventif-6548354&alerte=&alerte_desc=&data=-229162033tttttttttfttttttttftftfftttfttttf&js=https%3A%2F%2Fj.adlooxtracking.com%2Fads%2Fjs%2Ftfav_adl_359.js%23platform%3D12%26scriptname%3Dadl_359%26tagid%3D812%26typejs%3Dtvaf%26fwtype%3D1%26creatype%3D2%26targetelt%3D%26custom2area%3D70%26custom2sec%3D2%26id11%3D%24ADLOOX_WEBSITE%26id14%3Dcustom_70_2%26id1%3D1078282%26id2%3D26159421%26id3%3D66319981%26id4%3D300x250%26id5%3D12246389%26id6%3D343434%26id7%3D8313%26id8%3D7021033%26id9%3D2831806201686445183%26id10%3D986241&commitid=-dirty&fw=1&version=1&iframe=3&hadnxs=&ua=Mozilla%2F5.0%20%28Windows%20NT%2010.0%3B%20Win64%3B%20x64%29%20AppleWebKit%2F537.36%20%28KHTML%2C%20like%20Gecko%29%20Chrome%2F79.0.3945.88%20Safari%2F537.36&url_referrer=https%3A%2F%2Fwww.ouest-france.fr%2Fsport%2Frunning%2Fmarathon-vert%2Fmarathon-vert-de-rennes-l-osteopathie-c-est-aussi-du-preventif-6548354&resolution=1280x720&nb_cpu=4&nav_lang=fr-FR&date_regen=2019-06-13%2011%3A50%3A03&debug=7%3A%20top%20%21%3D%20window%20%26%20friendly%20-%3E%20location.href%20&ao=https%3A%2F%2Fwww.ouest-france.fr&fake=010000&popup_history=9&popup_visible=true&type_crea=2&tagid=812&popup_menubar=true&popup_locationbar=true&popup_personalbar=true&popup_scrollbars=true&popup_statusbar=true&popup_toolbar=true&id11=%24ADLOOX_WEBSITE&id14=custom_70_2&id1=1078282&id2=26159421&id3=66319981&id4=300x250&id5=12246389&id6=343434&id7=8313&id8=7021033&id9=2831806201686445183&id10=986241&version=3";getAllNodesContent ( firstNode, contentTab_2, message_2 );
var adloox_impression=1;
