<?php
/*
Template Name: Plagiarism Check Template
*/
get_header();

// Display the form
echo do_shortcode('[plagiarism_check_form]');

// Display the API response
echo '<h2>API Response:</h2>';
echo '<div id="api-response"></div>';

get_footer();
?>
