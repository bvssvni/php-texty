
<?php ///////////////////////////// SHOTS /////////////////

/* INFO
author: Sven Nilsen
github: https://github.com/bvssvni/php-texty
license: http://www.gnu.org/licenses/gpl-3.0.html
(contact me for propriatory license)
version: 000
*/

/* USAGE
// shots(edit, directory, width);
<?php shots($_SESSION[$login_admin_flag] === TRUE, "pics", 700); ?><br />
<?php login(); ?>
*/

$texty_language = "no";
$texty_interface_text = array(
	"updateText" => array(
		"en" => "Update",
		"no" => "Oppdater",
	),
);

function texty_text($str) {
	global $texty_interface_text;
	global $texty_language;
	if (is_null($texty_interface_text[$str]))
	{
		echo "Can not find " . $str . " in interface dictionary.<br />\n";
		return NULL;
	}

	return $texty_interface_text[$str][$texty_language];
}

function texty_file($dir, $id) {
	return $dir . "/" . $id . ".txt";
}

$texty_updated_done = FALSE;
function texty_update($admin, $dir) {
	// Protect againt non-admins and multiple updates.
	global $texty_updated_done;
	if ($texty_updated_done || !$admin) {
		return;
	}

	$texty_updated_done = TRUE;
	$action = $_POST["action"];
	if ($action !== "texty_update_text") {return;}
	
	$id = $_POST["id"];
	$file = texty_file($dir, $id);
	$content = $_POST["content"];
	$content = str_replace("<br />", "\n", $content);
	file_put_contents($file, $content);
}

function texty_url_email($txt_content, $target="_blank") {
    $regex = "/https?\:\/\/[a-zA-Z0-9\-\.]+/";
    if(preg_match($regex, $txt_content, $matches)) {
       $txt_content = preg_replace($regex, "<a href='".$matches[0]."' target='".$target."'>".$matches[0]."</a> ", $txt_content);
    }
	
	$regex = "/[a-zA-Z0-9\-\.]+@[a-zA-Z0-9\-\.]+/";
    if(preg_match($regex, $txt_content, $matches)) {
		$found = $matches[0];
		$atpos = strpos($found, "@") + 1;
		$part1 = substr($found, 0, $atpos);
		$part2 = substr($found, $atpos);
		$email = "<script language=\"javascript\">document.write('<a href=\"mailto:".$part1."' + '".$part2."\">' + '".$part1."' + '$part2');</script></a>";
		$txt_content = preg_replace($regex, $email, $txt_content);
    }
	
	return $txt_content;
}


function texty($admin, $dir, $id, $cols, $rows) {
	texty_update($admin, $dir);
	
	$file = texty_file($dir, $id);
	$content = file_get_contents($file);
	$content = htmlspecialchars($content);
	
	// Replace new lines with html breaks.
	$contentWithLineBreaks = str_replace("\n", "<br />", texty_url_email($content));
	echo $contentWithLineBreaks;
	
	if ($admin) {
		echo "<br /><form action=\"" . $_SERVER["PATH_INFO"] . "\" method=\"POST\">\n";
		echo "<textarea name=\"content\" cols=\"$cols\" rows=\"$rows\">" . $content . "</textarea><br />";
		echo "<input type=\"hidden\" value=\"texty_update_text\" name=\"action\" />\n";
		echo "<input type=\"hidden\" value=\"$id\" name=\"id\" />\n";
		echo "<input type=\"submit\" value=\"" . texty_text("updateText") . "\" />\n";
		echo "</form>";
	}
}
?>
