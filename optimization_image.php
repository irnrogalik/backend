<?
/*image optimmization from the directory*/
$result = get_files("../bitrix/templates/steelline/img/not_optimiz");

if (!empty($result)) {
    $images = preg_grep("/\.(?:png|jpe?g)$/i", $result);
}

if (is_array($images)) {
    foreach ($images as $key => $image) {
        $name = explode("/", $image);
        $img_name = $name[count($name) - 1];
		
        if (strpos($img_name, "png")) {
            $qp = 'optipng -o7 -preserve -strip all ' . $image;
            exec($qp . ' 2>&1', $output);

            $optimmized = false;
            foreach ($output as $out) {
                if (strpos($out, "is already optimized")) {
                    $optimmized = true;
                }
            }
            if (!$optimmized) {
                exec('convert ' . $image . ' -strip ' . $image);
            }
        } else {
            $q = 'convert ' . $image . ' -sampling-factor 4:2:0 -strip -quality 81 -interlace JPEG ' . $image;
            exec($q);
        }
		
    }
}

function get_files($dir = ".") {
    $files = array();
    if ($handle = opendir($dir)) {
        while (false !== ($item = readdir($handle))) {
            if (is_file("$dir/$item")) {
                $files[] = "$dir/$item";
            } elseif (is_dir("$dir/$item") && ($item != ".") && ($item != "..")) {
                $files = array_merge($files, get_files("$dir/$item"));
            }
        }
        closedir($handle);
    }
    return $files;
}
