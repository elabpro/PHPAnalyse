<?php
// Setup - START
$elabMaxY = 100;
// It's used for generatin cycle from [-elabMaxX] to [+elabMaxX]
$elabMaxX = 100;
$elabStep = 1;
$elabKoff = 1;
// Setup - END

$elabData = $_POST["data"];
?>
Code for analyse:<br />
<form input="analyse.php" method="post">
    <textarea name="data" rows="5" cols="80"><?php print htmlspecialchars($elabData); ?></textarea>
    <br /><input type="submit" />
</form>
<?php
// Analyse the code
// Main variable for analyse
$mainVar = "";
$elabDataSrc = "<?php " . $elabData . ";";
$tokens = token_get_all($elabDataSrc);
$funcResultlag = false;
$result = array();
foreach ($tokens as $token) {
    if ($token[0] == 320) {
        $result[substr($token[1], 1)] = $token[1];
        if ($mainVar == "") {
            $mainVar = substr($token[1], 1);
        }
    }
}

if ($mainVar != "") {
    $funcResult = array();
    print "<table border='1' style='float:left;'>\n";
    print "<tr>\n";
    foreach ($result as $varName => $varToken) {
        print "<th>" . $varToken . "</th>\n";
    }
    print "<th>Result, $" . $mainVar . "</th></tr>\n";
    // Main cycle for calculation the result for different values for the variables in the code
    for ($elabIdx = -$elabMaxX; $elabIdx <= $elabMaxX; $elabIdx+=$elabStep) {
        print "<tr>\n";
        foreach ($result as $varName => $varToken) {
            $$varName = $elabIdx;
            print "<td>" . $elabIdx . "</td>\n";
        }
        eval($elabData);
        $funcResult[$elabIdx] = $$mainVar;
        print "<th>" . $funcResult[$elabIdx] . "</th>\n";
        if ($funcResult[$elabIdx] > $elabMaxY) {
            $elabMaxY = round($funcResult[$elabIdx]);
        }
        print "</tr>\n";
    }
    print "</table>\n";

    // Make an image for graph
    $elabMaxY = $elabMaxY * $elabKoff;
    $elabImg = @imagecreate($elabMaxX * 2, $elabMaxY * 2);
    $background_color = imagecolorallocate($elabImg, 250, 250, 250);
    $border_color = imagecolorallocate($elabImg, 50, 50, 50);
    imagefill($elabImg, 1, 1, $background_color);
    imageline($elabImg, 0, $elabMaxY, $elabMaxX * 2, $elabMaxY, $border_color);
    imageline($elabImg, $elabMaxX, 0, $elabMaxX, $elabMaxY * 2, $border_color);
    $text_color = imagecolorallocate($elabImg, 233, 14, 91);
    for ($elabIdx = -$elabMaxX; $elabIdx <= $elabMaxX; $elabIdx+=$elabStep) {
        $x = $elabMaxX + $elabIdx;
        $y = $elabMaxY - $funcResult[$elabIdx] * $elabKoff;
        imagesetpixel($elabImg, round($x), round($y), $text_color);
    }
    ob_start();
    imagepng($elabImg);
    $elabImage_data = ob_get_contents();
    ob_end_clean();
    print "<img src='data:image/png;base64," . base64_encode($elabImage_data) . "' style='margin:10px;border:1px solid gray'>";
}
