<?php
/**
 * @package pragyan
 * @copyright (c) 2010 Pragyan Team
 * @license http://www.gnu.org/licenses/ GNU Public License
 * For more details, see README
 */
if(!defined('__PRAGYAN_CMS'))
{ 
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	echo "<h1>403 Forbidden<h1><h4>You are not authorized to access the page.</h4>";
	echo '<hr/>'.$_SERVER['SERVER_SIGNATURE'];
	exit(1);
}

/**
 * @param $userId The user for whom the list of permitted actions must be computed.
 * @param $pageId The page on which the permissible action for the user is computed
 *
 * @return $searchbar The search bar for tags. 
 */
function getSearchbar($userId, $pageId){
	if(isset($_GET['searchbar']) && isset($_GET['searchContents'])){
		$searchQuery="SELECT DISTINCT `page_id` FROM `". MYSQL_DATABASE_PREFIX ."pagetags` WHERE `tag_text` LIKE '%{$_GET['searchContents']}%';";
		$searchResult=mysql_query($searchQuery);
		$suggestions="";
		while($row=mysql_fetch_assoc($searchResult)){
			$suggestions.="<a href=".hostURL().getPagePath($row['page_id']).">";
			$pageInfo=getPageInfo($row['page_id']);
			$suggestions.=$pageInfo['page_title']."</a><br/>";
		}
		echo $suggestions;
		exit(0);
	}
	$allPageQuery="SELECT `page_id`, `page_module` FROM `". MYSQL_DATABASE_PREFIX ."pages`";
	$allPageResult=mysql_query($allPageQuery);
	$pagesIdList=array();
	while ($row=mysql_fetch_assoc($allPageResult)){
		if(getPermissions($userId, $row['page_id'], $action="view", $module=$row['page_module']))
			array_push($pagesIdList, intval($row['page_id']));
	}
	$tagsWithPermsQuery="SELECT * FROM `". MYSQL_DATABASE_PREFIX ."pagetags` WHERE `page_id` IN (";
	foreach ($pagesIdList as $key => $value) {
		$tagsWithPermsQuery.=$value.",";
	}
	$tagsWithPermsQuery=substr($tagsWithPermsQuery,0,-1).");";
	$tagsWithPermsResult= mysql_query($tagsWithPermsQuery);
	$searchbar=<<<SEARCHSCRIPT
		<script> 
			function showResult(searchstr) {
				if (searchstr.length==0) { 
				    document.getElementById("tagSuggestions").innerHTML="";
				    document.getElementById("tagSuggestions").style.border="0px";
				    return;
				}
				if (window.XMLHttpRequest) {
					// code for IE7+, Firefox, Chrome, Opera, Safari
					xmlhttp=new XMLHttpRequest();
				}else {  // code for IE6, IE5
				    xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
				}
				xmlhttp.onreadystatechange=function() {
					if (xmlhttp.readyState==4 && xmlhttp.status==200) {
				    	if(xmlhttp.responseText != ""){
				    		console.log(xmlhttp.responseText);
				    		document.getElementById("tagSuggestions").innerHTML=xmlhttp.responseText;
				    		document.getElementById("tagSuggestions").style.border="1px solid #A5ACB2";
				    	}
				    	else{
					    	document.getElementById("tagSuggestions").innerHTML="";
					    	document.getElementById("tagSuggestions").style.border="0px";
				    	}
					}
				}
				xmlhttp.open("GET","./&searchbar=1&searchContents="+searchstr,true);
				xmlhttp.send();
			}
		</script>
SEARCHSCRIPT;
	$searchbar.="<div id=\"cms-searchbar\">";
	$searchbar.="<input type=\"text\" size=\"30\" onkeyup=\"showResult(this.value)\">";
	$searchbar.="<div id=\"tagSuggestions\"></div>";
	$searchbar.="</div>";
	return $searchbar;
}
