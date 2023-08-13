<?php
/**
 * Plugin Name: Muuss Plagiarism Checker
 * Plugin URI: https://educationelites.com/
 * Description: A simple plugin to check plagiarism.
 * Version: 1.0.1
 * Author: Musera Isaac
 */

// Add Shortcode for Plagiarism Checker Form
function muuss_plagiarism_check_form_shortcode() {
    ob_start();
    ?>
    <div class="wrap">
        <h1>Plagiarism Checker</h1>
        <form method="post">
            <label for="text_to_check">Enter text to check for plagiarism:</label><br>
            <textarea name="text_to_check" id="text_to_check" rows="10" cols="50"></textarea><br>
            <input type="submit" name="check_plagiarism" value="Check for Plagiarism">
        </form>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('plagiarism_check_form', 'muuss_plagiarism_check_form_shortcode');

// Plagiarism Checker API Function
function muuss_check_plagiarism($text_to_check) {
    // API URL
    $api_url = "https://papersowl.com:443/plagiarism-checker-send-data";

    // API Headers
    $api_headers = array(
        "User-Agent" => "Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0",
        "Accept" => "*/*",
        "Accept-Language" => "ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3",
        "Accept-Encoding" => "gzip, deflate",
        "Referer" => "https://papersowl.com/free-plagiarism-checker",
        "Content-Type" => "application/x-www-form-urlencoded; charset=UTF-8",
        "X-Requested-With" => "XMLHttpRequest",
        "Origin" => "https://papersowl.com",
        "Dnt" => "1",
        "Sec-Fetch-Dest" => "empty",
        "Sec-Fetch-Mode" => "no-cors",
        "Sec-Fetch-Site" => "same-origin",
        "Pragma" => "no-cache",
        "Cache-Control" => "no-cache",
        "Te" => "trailers",
        "Connection" => "close"
    );

    // API Data
    $api_data = array(
        "is_free" => "false",
        "plagchecker_locale" => "en",
        "product_paper_type" => "1",
        "title" => '',
        "text" => $text_to_check
    );

    // Perform API request using wp_remote_post()
    $response = wp_remote_post($api_url, array(
        'method' => 'POST',
        'headers' => $api_headers,
        'body' => $api_data,
        'timeout' => 240
    ));

    if (is_wp_error($response)) {
        return "Error occurred during API request: " . $response->get_error_message();
    } else {
        // Process API response
        $response_body = wp_remote_retrieve_body($response);
        return $response_body;
    }
}

// Check plagiarism on form submission
function muuss_custom_plagiarism_checker_check_submission() {
    if (isset($_POST['check_plagiarism'])) {
        $text_to_check = sanitize_textarea_field($_POST['text_to_check']);
        $api_response = muuss_check_plagiarism($text_to_check);
        $api_data = json_decode($api_response, true);

        if ($api_data && !empty($api_data['text'])) {
            // Start output buffering to capture the HTML content
            ob_start();

            // Include the header
            get_header();

            // Container to center the content
            echo '<div class="container">';
            echo '<div class="main-content">';
            echo '<h2>Plagiarism Check Results:</h2>';
            echo '<p><strong>Checked Text:</strong></p>';
            echo '<p>' . $api_data['text'] . '</p>';

            if (!empty($api_data['matches'])) {
                echo '<p><strong>Matches:</strong></p>';
                $matchCount = 1;
                foreach ($api_data['matches'] as $match) {
                    if (!empty($match['url']) && !empty($match['percent']) && !empty($match['highlight'])) {
                        // Generate a unique bright background color for each match URL
                        $bgColor = get_unique_bright_color($match['url']);
                        echo '<p>';
                        foreach ($match['highlight'] as $highlight) {
                            $startIndex = intval($highlight[0]);
                            $endIndex = intval($highlight[1]);
                            $highlightedText = substr($api_data['text'], $startIndex, $endIndex - $startIndex + 1);
                            echo '<span style="background-color: ' . $bgColor . ';">' . $highlightedText . '</span>';
                        }
                        //echo '</p>';
                        echo '<p>';
                        echo '- <a href="' . $match['url'] . '" target="_blank" style="color: blue;">' . $match['url'] . '</a> - ' . $match['percent'] . '% match';
                        echo '</p>';
                        $matchCount++;
                    }
                }
                echo '<p> ..end.. </p>';
                echo '<div style="background-color: #007bff; padding: 10px; display: inline-block;"><a href="https://educationelites.com/plagiarism-check/" class="button" style="color: #fff; text-decoration: none;">Retry Plagiarism Check</a></div>';
            } else {
                echo '<p>No matches found.</p>';
                echo '<a href="https://educationelites.com/plagiarism-check/" class="button">Retry Plagiarism Check</a>';
            }

            echo '</div>'; // Close main-content

            // Include the theme's sidebar
            echo '<aside class="sidebar">';
            get_sidebar(); // Get the theme's sidebar from sidebar.php
            echo '</aside>';

            echo '</div>'; // Close container

            // Include the footer
            get_footer();

            // Get the captured content and display it
            $content = ob_get_clean();
            echo $content;
        } else {
            echo '<p>No response data found.</p>';
            echo '<p>Please check the text, make sure the text is more than 15 words.</p>';
            echo '<a href="https://educationelites.com/plagiarism-check/" class="button">Retry Plagiarism Check</a>';
        }

        exit;
    }
}
add_action('wp', 'muuss_custom_plagiarism_checker_check_submission');

// Add custom CSS for centering the content and adding space on the sides
function muuss_custom_css() {
    ?>
    <style>
    /* Container styles */
    .container {
        display: flex;
        justify-content: center;
    }
    
    /* Main content styles */
    .main-content {
        justify-content: center;
        max-width: 900px; /* Set the maximum width of the main-content */
        margin: 60px; /* Add some left and right margin to the main-content */
        flex: 1; /* Allow main-content to take remaining width */
    
        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            max-width: 60%; /* Set the maximum width to 100% for smaller screens */
            margin: 0 10px; /* Reduce the left and right margin for smaller screens */
        }
    }
    
    /* Sidebar styles */
    .sidebar {
        max-width: 300px;
        width: 340px; /* Extend the width of the sidebar to make its contents more readable */
        margin-left: 20px;
    
        /* Responsive adjustments for smaller screens */
        @media (max-width: 768px) {
            max-width: 100%; /* Set the maximum width to 100% for smaller screens */
            margin-left: 0; /* Remove the left margin for smaller screens */
        }
    }
    
    /* Add any additional custom styles here */
    <?php
    // Generate unique bright background colors for matches
    $matches_bg_colors = array();
    function get_unique_bright_color($url) {
        global $matches_bg_colors;
        $url_hash = md5($url);
        $color = $matches_bg_colors[$url_hash] ?? null;
        if (!$color) {
            $hue = hexdec(substr($url_hash, 0, 2)) % 360;
            $color = "hsl($hue, 70%, 80%)";
            $matches_bg_colors[$url_hash] = $color;
        }
        return $color;
    }
    ?>
    </style>
    <?php
}
add_action('wp_head', 'muuss_custom_css');

