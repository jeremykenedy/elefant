<?php

/**
 * Embeds a YouTube video into the current page. Used by
 * the WYSIWYG editor's dynamic objects menu.
 */

$query = parse_url ($data['url'], PHP_URL_QUERY);
parse_str ($query, $params);
if (isset ($params['v'])) {
	$data['video'] = $params['v'];
} else {
	$data['video'] = substr (parse_url ($data['url'], PHP_URL_PATH), 1);
}

$data['width'] = isset ($data['width']) ? $data['width'] : 480;
$data['height'] = isset ($data['height']) ? $data['height'] : 303;

echo $tpl->render ('social/video/youtube', $data);

?>