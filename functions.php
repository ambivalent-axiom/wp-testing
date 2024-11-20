<?php 

require get_theme_file_path('/inc/search-route.php');
require get_theme_file_path('env.php');

//You can create and add as many API fields as you like.
function university_custom_rest() {
    register_rest_field('post', 'authorName', array(
        'get_callback' => function() {
            return get_the_author();
        }
    ));
}

add_action('rest_api_init', 'university_custom_rest');

function university_files() {
    wp_enqueue_script('googleMap', '//maps.googleapis.com/maps/api/js?key=AIzaSyBU8XN5-Z6kS1a0iyqdaCW64-9gTwzIY4k', NULL, '1.0', true);
    wp_enqueue_script('main_university_js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style('university_main_style', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('university_extra_style', get_theme_file_uri('/build/index.css'));
    wp_enqueue_style('font_awesome', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('custom_google_fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
    //this will expose a variable for javascript in frontend
    wp_localize_script('main_university_js', 'universityData', array(
        'root_url' => get_site_url()
    ));

}
add_action('wp_enqueue_scripts', 'university_files');


function university_features() {
    // register_nav_menu('headerMenuLocation', 'Header Menu Location');
    // register_nav_menu('footerExplore', 'Explore Menu Location');
    // register_nav_menu('footerLearn', 'Learn Menu Location');

    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('professorLandscape', 400, 260, true);
    add_image_size('professorPortrait', 480, 650, true);
    add_image_size('pageBanner', 1500, 350, true);
}

add_action('after_setup_theme', 'university_features');

function university_adjust_queries($query) {
    if ( ! is_admin() && is_post_type_archive('campus') && $query->is_main_query()) {
        $query->set('posts_per_page', -1);
    }
    if ( ! is_admin() && is_post_type_archive('program') && $query->is_main_query()) {
        $query->set('orerby', 'title');
        $query->set('order', 'ASC');
        $query->set('posts_per_page', -1);
    }

    if ( ! is_admin() && is_post_type_archive('event') && $query->is_main_query()) {
        $today = date('Ymd');
        $query->set('meta_key', 'eventDate');
        $query->set('orderby', 'meta_value_num');
        $query->set('order', 'ASC');
        $query->set('meta_query', array(
                array(
                    'key' => 'eventDate',
                    'compare' => '>=',
                    'value' => $today,
                    'type' => 'numeric'
                )
            )
            
        );
    }
}
add_action('pre_get_posts', 'university_adjust_queries');

function pageBanner($args = NULL) {
    if ( ! isset($args['title'])) {
        $args['title'] = get_the_title();
    }
    if ( ! isset($args['subtitle'])) {
        $args['subtitle'] = get_field('page_banner_subtitle');
    }
    if ( ! isset($args['image'])) {
        if (get_field('page_banner_background_image') AND !is_archive() AND !is_home()) {
            $args['image'] = get_field('page_banner_background_image')['sizes']['pageBanner'];
        } else {
            $args['image'] = get_theme_file_uri('/images/ocean.jpg');
        }
    }
    ?>
        <div class='page-banner'>
            <div 
                class='page-banner__bg-image' 
                style='background-image: url(<?php echo $args['image']; ?>)'>
            </div>
            <div class='page-banner__content container container--narrow'>
                <h1 class='page-banner__title'><?php echo $args['title']; ?></h1>
                <div class='page-banner__intro'>
                <p><?php echo $args['subtitle']; ?></p>
                </div>
            </div>
        </div>
    <?php
}

function universityMapKey($api) {
    $api['key'] = getGoogleApiKey();
    return $api;
}
add_filter('acf/fields/google_map/api', 'universityMapKey');

//redirect subscriber accounts out of admin, onto homepage
add_action('admin_init', 'redirectSubsToFrontEnd');
function redirectSubsToFrontEnd() {
    $currentUser = wp_get_current_user();
    if (count($currentUser->roles) == 1 && $currentUser->roles[0] == 'subscriber') {
        wp_redirect(site_url('/'));
        exit;
    }
}

//remove admin bar
add_action('wp_loaded', 'noSubsAdminBar');
function noSubsAdminBar() {
    $currentUser = wp_get_current_user();
    if (count($currentUser->roles) == 1 && $currentUser->roles[0] == 'subscriber') {
        show_admin_bar(false);
    }
}

//customize login screen
add_filter('login_headerurl', 'ourHeaderUrl'); //hook to logo URL - this is the main logo on login screen
function ourHeaderUrl() {
    return esc_url(site_url('/'));
}
//add custom CSS from cutom theme to login screen
add_action('login_enqueue_scripts', 'ourLoginCSS');
function ourLoginCSS() {
    wp_enqueue_style('university_main_style', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('university_extra_style', get_theme_file_uri('/build/index.css'));
    wp_enqueue_style('font_awesome', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('custom_google_fonts', '//fonts.googleapis.com/css?family=Roboto+Condensed:300,300i,400,400i,700,700i|Roboto:100,300,400,400i,700,700i');
}
add_filter('login_headertext', 'ourLoginTitle');
function ourLoginTitle() {
    return get_bloginfo('name');
}


