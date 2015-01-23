<?php
/**
 * LetterPairSimilarity algorithm implementation in PHP
 * @author Igal Alkon
 * @link http://www.catalysoft.com/articles/StrikeAMatch.html
 */
class LetterPairSimilarity
{
    /**
     * @param $str
     * @return mixed
     */
    private function wordLetterPairs($str)
    {
        $allPairs = array();

        // Tokenize the string and put the tokens/words into an array

        $words = explode(' ', $str);

        // For each word
        for ($w = 0; $w < count($words); $w++)
        {
            // Find the pairs of characters
            $pairsInWord = $this->letterPairs($words[$w]);

            for ($p = 0; $p < count($pairsInWord); $p++)
            {
                $allPairs[] = $pairsInWord[$p];
            }
        }

        return $allPairs;
    }

    /**
     * @param $str
     * @return array
     */
    private function letterPairs($str)
    {
        $numPairs = mb_strlen($str)-1;
        $pairs = array();

        for ($i = 0; $i < $numPairs; $i++)
        {
            $pairs[$i] = mb_substr($str,$i,2);
        }

        return $pairs;
    }

    /**
     * @param $str1
     * @param $str2
     * @return float
     */
    public function compareStrings($str1, $str2)
    {
        $pairs1 = $this->wordLetterPairs(strtoupper($str1));
        $pairs2 = $this->wordLetterPairs(strtoupper($str2));

        $intersection = 0;

        $union = count($pairs1) + count($pairs2);

        for ($i=0; $i < count($pairs1); $i++)
        {
            $pair1 = $pairs1[$i];

            $pairs2 = array_values($pairs2);
            for($j = 0; $j < count($pairs2); $j++)
            {
                $pair2 = $pairs2[$j];
                if ($pair1 === $pair2)
                {
                    $intersection++;
                    unset($pairs2[$j]);
                    break;
                }
            }
        }

        return (2.0*$intersection)/$union;
    }
}

function sendJSON($url)
    {
        //  Initiate curl
$ch = curl_init();
// Disable SSL verification
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
// Will return the response, if false it print the response
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Set the url
curl_setopt($ch, CURLOPT_URL,$url);
// Execute
$result=curl_exec($ch);
// Closing
curl_close($ch);

// Will dump a beauty json :3
$values = json_decode($result, true);

return($values);

    }
$obj = new LetterPairSimilarity();

//connect to mySQL database
 //Start session
	session_start();
	
	//Include database connection details
	require_once('config.php');
	
	//Array to store validation errors
	$errmsg_arr = array();
	
	//Validation error flag
	$errflag = false;
	
	//Connect to mysql server
	$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
	if(!$link) {
		die('Failed to connect to server: ' . mysql_error());
	}
	
	//Select database
	$db = mysql_select_db(DB_DATABASE);
	if(!$db) {
		die("Unable to select database");
	}
  
  //Function to sanitize values received from the form. Prevents SQL injection
	function clean($str) {
		$str = @trim($str);
		if(get_magic_quotes_gpc()) {
			$str = stripslashes($str);
		}
		return mysql_real_escape_string($str);
	}

	//echo("script is working");
//check to see if we need to skip the song or build the playlist
$qry="SELECT * FROM `next` WHERE 1";
    $result=mysql_query($qry);	
	
//or see if admin sent next request
$qry="SELECT * FROM `next` WHERE `cell` = 'ADMIN_CELL_NUMBER_HERE'";	
$adminCell = mysql_query($qry);	
//echo(mysql_num_rows($result));
//if the 10 users or more have vetoed //UPDATE TO 4
if(mysql_num_rows($result) >= 4 || mysql_num_rows($adminCell) > 0) {

//skip the song
$url = "http://localhost:8888/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Player.GoTo%22,%20%22params%22:%20{%22playerid%22:0,%22to%22:%22next%22},%20%22id%22:%201}";
//call kodi api
$values = sendJSON($url);
//clear the list
$qry="DELETE FROM `next` WHERE 1";
  $result=mysql_query($qry);	
//echo("next");
}

else{
// get next song to add to queue from database
$qry="SELECT * FROM `requests` ORDER BY `num` ASC LIMIT 1";
 $result=mysql_query($qry);	
//echo("looking for song");
//if there is a song waiting to be retreived...
if(mysql_num_rows($result) >= 1) {



//echo("there's a song!");
$mysqlData= mysql_fetch_row($result );

//take the second column
$requestUrl = $mysqlData[1];


//remove that entry
$qry="DELETE FROM `requests` ORDER BY `num` ASC LIMIT 1";
 $result=mysql_query($qry);



//youtube url

$safeUrl = sprintf('ytdl -i "%s"',$requestUrl);

//extract track title and artist here

$trackInfo = shell_exec($safeUrl);



//output looks like:
/*
Title: Bone Thugs N Harmony - Ghetto Cowboy Lyrics Author: KillFact ID: UDLbWs2EZSE Duration: 00:05:25 Rating: 4.7229154288 Views: 2023417 Thumbnail: http:/i.ytimg.com/vi/UDLbWs2EZSE/default.jpg Keywords: Bone, thugs, ghetto, cowboy
*/

/*
or looks like
Title: Dumb Ways to Die
Author: DumbWays2Die
ID: IJNR2EpS0jw
Duration: 00:03:02
Rating: 4.86546991194
Views: 96086945
Thumbnail: http://i.ytimg.com/vi/IJNR2EpS0jw/default.jpg
Keywords: DWTD, Music, Tangerine Kitty, Metro Trains, Rail Safety, Be Safe Around Trains, Dumb Ways To Die
*/




//echo($trackInfo);
$titleDirty = explode('Author',$trackInfo,2);


//only need to seperate if there is a :


//take out title:
$titleSplit = explode(":",$titleDirty[0],2);

//var_dump($titleSplit);

//now split song name from artist
//only do if in the format title-artist:http
$isHyph = strpos($titleSplit[1],'-');


$artist = '';
if($isHyph !== false){
$splitTitle = explode('-',$titleSplit[1],2);
$artist = $splitTitle[0];
$title = $splitTitle[1];
}

else{
$title = $titleSplit[1];
}



//now delete extra BS like lyrics and anything in ()

$title1=str_replace("Lyrics","",$title);
$title2=str_replace("lyrics","",$title1);
$title3=str_replace("LYRICS","",$title2);

$cleanedTitle = explode('(',$title3,2);

$titleFinal = $cleanedTitle[0];

//echo($titleFinal);

//Check playlist to see if song exists and where it is
$url="http://localhost:8888/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Playlist.GetItems%22,%20%22params%22:%20{%20%22properties%22:%20[%22title%22],%20%22playlistid%22:%200%20},%20%22id%22:%201}";


//call kodi api
$values = sendJSON($url);

//search for title and artist in current playlist

$matchPos = 0;
$pos = 0;


//first check title, then artist
//maybe even better? -> much much better.
foreach ($values['result']['items'] as $key => $value) { 



$matchPercent= $obj->compareStrings($value['label'],$title);


//echo "<p> $matchPercent </p>";
//thresholing for match
if($matchPercent > .4 ){

if($artist === ''){
$matchPos = $pos;
//echo("no Artist");
}

else{
//echo($artist);
$matchPercent= $obj->compareStrings($value['label'],$artist);

if($matchPercent >.1){
$matchPos = $pos;
}
}
}

$pos =$pos+1;

}
//echo ($matchPos);


//first figure out the current song on the playlist

//Poll current playing song
$dirtyUrl = "http://localhost:8888/jsonrpc?request={%22jsonrpc%22:%20%222.0%22,%20%22method%22:%20%22Player.GetProperties%22,%20%22params%22:%20{%22playerid%22:0,%22properties%22:%20[%22playlistid%22,%22position%22]},%20%22id%22:%201}";

//$url = urlencode($dirtyUrl);
$url = str_replace(' ', '%20', $dirtyUrl);

$result = sendJSON($url);

//echo("go look and see...");

//extract the current playlist position from JSON
$currentPos = $result['result']['position'];

//echo($currentPos);

//now we need to either download the song or insert it to playlist.
//if matchPos is 0 then the song wasn't found

if($matchPos == 0){
echo($requestUrl);
$safePython = sprintf('python /home/pi/downloadSong.py "%s" 2>&1',$requestUrl);

//extract track title and artist here

$downloaded_file = shell_exec($safePython);

//trim off new line
$downloaded_file = substr($downloaded_file,0,-1);

echo($downloaded_file);

//$encoded_path = urlencode($downloaded_file);
//echo($encoded_path);



//Insert song to playlist
$dirtyUrl= sprintf('http://localhost:8888/jsonrpc?request={"jsonrpc": "2.0", "method": "Playlist.Insert", "params": { "position": %s, "playlistid": 0, "item": {"file" : "%s"} }, "id": 1}',strval($currentPos+1),$downloaded_file);

//$url = urlencode($dirtyUrl);
$url = str_replace(' ', '%20', $dirtyUrl);

//now add the file to the playlist
$result = sendJSON($url);
var_dump($result);
echo("...in playlist now");

}

//the song is in the playlist
else{
echo ("...exists");
//if song is already in the playlist we need to see if it is before or after the current position

//only if it is after do we want to do anything
if($matchPos > $currentPos){

//swap next song and requested song
$dirtyUrl= sprintf('http://localhost:8888/jsonrpc?request={"jsonrpc": "2.0", "method": "Playlist.Swap", "params": { "position1": %s, "position2" : %s, "playlistid": 0 }, "id": 1}',strval($currentPos+1),strval($matchPos));

//$url = urlencode($dirtyUrl);
$url = str_replace(' ', '%20', $dirtyUrl);

//now add the file to the playlist
$result = sendJSON($url);


var_dump($result);

echo("...iswapped");

}
}
}
}

?>
