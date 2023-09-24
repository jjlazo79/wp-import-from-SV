<?php




/**
 * Read CSV file to array
 *
 * @param string $csvFile
 * @return array|false
 */
function csvToArray($csvFile)
{
	$file_to_read = fopen($csvFile, 'r');

	while (!feof($file_to_read)) {
		$lines[] = fgetcsv($file_to_read, 1000, ',');
	}

	fclose($file_to_read);
	array_pop($lines);
	return $lines;
}
/**
 * Save the image on the server.
 *
 * @param string $base64_img
 * @param string $title
 * @return int|WP_Error The attachment ID on success. The value 0 or WP_Error on failure.
 */
function save_image($base64_img, $title)
{
	// Upload dir.
	$upload_dir  = wp_upload_dir();
	$upload_path = str_replace('/', DIRECTORY_SEPARATOR, $upload_dir['path']) . DIRECTORY_SEPARATOR;

	$img             = str_replace('data:image/jpeg;base64,', '', $base64_img);
	$img             = str_replace(' ', '+', $img);
	$decoded         = base64_decode($img);
	$filename        = $title . '.jpeg';
	$file_type       = 'image/jpeg';
	$hashed_filename = md5($filename . microtime()) . '_' . $filename;

	// Save the image in the uploads directory.
	$upload_file = file_put_contents($upload_path . $hashed_filename, $decoded);
	if (false == $upload_file) {
		return false;
	}

	$attachment = array(
		'post_mime_type' => $file_type,
		'post_title'     => preg_replace('/\.[^.]+$/', '', basename($hashed_filename)),
		'post_content'   => '',
		'post_status'    => 'inherit',
		'guid'           => $upload_dir['url'] . '/' . basename($hashed_filename)
	);

	$attach_id = wp_insert_attachment($attachment, $upload_dir['path'] . '/' . $hashed_filename);

	return $attach_id;
}
function import_csv($csvFile)
{
	// 0 => string 'nombre' (length=6)
	// 1 => string 'questions' (length=9)
	// 2 => string 'id' (length=2)
	// 3 => string 'entidad' (length=7)
	// 4 => string 'title' (length=5)
	// 5 => string 'image' (length=5)
	// 6 => string 'images' (length=6)
	// 7 => string 'description' (length=11)
	// 8 => string 'wikipedia' (length=9)
	// 9 => string 'lugar_nacimiento' (length=16)
	// 10 => string 'hijos' (length=5)
	// 11 => string 'hermanos' (length=8)
	// 12 => string 'conyugues' (length=9)
	// 13 => string 'padres' (length=6)
	// 14 => string 'altura' (length=6)
	// 15 => string 'instagram' (length=9)
	// 16 => string 'twitter' (length=7)
	// 17 => string 'youtube' (length=7)
	// 18 => string 'facebook' (length=8)
	// 19 => string 'tiktok' (length=6)
	// 20 => string 'relacionados' (length=12)
	// 21 => string 'bio' (length=3)

	// $csvFile = __DIR__  . '/data/cotilleo_501_1000.csv';

	//read the csv file into an array
	$csv = csvToArray($csvFile);
	// //render the array with print_r
	// echo '<pre>';
	// print_r($csv);
	// echo '</pre>';
	// die();


	$count = 0;
	foreach ($csv as $item) {

		$count++;
		if ($count == 1) continue;
		$title     = (!empty($item[4])) ? wp_strip_all_tags($item[4]) : wp_strip_all_tags($item[0]);
		$content   = (!empty($item[21])) ? $item[21] : $item[4];
		$excerpt   = wp_strip_all_tags($item[7]);
		// $faqsa     = explode(',', $item[1]);
		// $faqsa1     = (!empty($faqsa[0])) ? serialize($faqsa[] = array('question' => $faqsa[0], 'answer' => '')) : '';
		// $faqsa2     = (!empty($faqsa[1])) ? serialize($faqsa[] = array('question' => $faqsa[1], 'answer' => '')) : '';
		// $faqsa3     = (!empty($faqsa[2])) ? serialize($faqsa[] = array('question' => $faqsa[2], 'answer' => '')) : '';
		// $faqsa4     = (!empty($faqsa[3])) ? serialize($faqsa[] = array('question' => $faqsa[3], 'answer' => '')) : '';
		// $faqsa5     = (!empty($faqsa[4])) ? serialize($faqsa[] = array('question' => $faqsa[4], 'answer' => '')) : '';


		// Create post object
		$personas = array(
			'post_title'    => ucwords($title),
			'post_content'  => (!empty($content)) ? $content : $excerpt,
			'post_author'   => 1,
			'post_excerpt'  => $excerpt,
			'post_status'   => 'publish', //'draft',
			'post_type'     => 'personas',
			'meta_input'    => array(
				'_person_entity'         => $item[3],
				'_person_wikipedia'      => $item[8],
				'_person_birthday'       => $item[9],
				'_person_children'       => $item[10],
				'_person_brother_sister' => $item[11],
				'_person_partner'        => $item[12],
				'_person_parents'        => $item[13],
				'_person_height'         => $item[14],
				'_person_instagram'      => $item[15],
				'_person_twitter'        => $item[16],
				'_person_youtube'        => $item[17],
				'_person_facebook'       => $item[18],
				'_person_tiktok'         => $item[19],
				'_person_related'        => $item[20],
				// '_person_faqs1'          => $faqsa1,
				// '_person_faqs2'          => $faqsa2,
				// '_person_faqs3'          => $faqsa3,
				// '_person_faqs4'          => $faqsa4,
				// '_person_faqs5'          => $faqsa5,
			),
		);

		// Insert the post into the database
		$person_id = wp_insert_post($personas);

		// var_dump($personas);
		// var_dump($person_id);
		// Image
		if (false != $person_id && isset($item[5]) && !empty($item[5])) {
			$featured_image_id = save_image($item[5], sanitize_title($title));

			set_post_thumbnail($person_id, $featured_image_id);
		}
	}
}
