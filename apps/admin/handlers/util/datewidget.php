<?php

/**
 * Call this to turn text inputs with a class="date" or
 * class="datetime" into date and date/time selectors.
 *
 * In PHP code, call it like this:
 *
 *     $this->run ('admin/util/datewidget');
 *
 * In a view template, call it like this:
 *
 *     {! admin/util/datewidget !}
 */

$page->add_style ('/apps/admin/css/datewidget.css');
$page->add_script ('/apps/blog/js/jquery.timepicker.js');
$page->add_script ('/apps/admin/js/datewidget.js');

?>