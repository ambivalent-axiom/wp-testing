<?php

add_action('rest_api_init', 'universityRegisterSearch');

function universityRegisterSearch() {
    register_rest_route('university/v1', 'search', array(
        'methods' => WP_REST_SERVER::READABLE,
        'callback' => 'universitySearchResults'
    ));
}

function universitySearchResults($data) {
    $results = array(
        'generalInfo' => array(),
        'professor' => array(),
        'program' => array(),
        'event' => array(),
        'campus' => array()
    );

    $mainQuery = new WP_Query(array(
        'post_type' => array('post', 'page', 'professor', 'program', 'campus', 'event'),
        's' => sanitize_text_field($data['term'])
    ));

    while($mainQuery->have_posts()) {
        $mainQuery->the_post();
        get_post_type() == 'event' ? $eventDate = new DateTime(get_field('eventDate')) : $eventDate = false;
        
        foreach (array_keys($results) as $postType) {
            if (get_post_type() == $postType) {
                array_push($results[$postType], array(
                    'title' => get_the_title(),
                    'url' => get_the_permalink(),
                    'postType' => get_post_type(),
                    'authorName' => get_the_author(),
                    'image' => get_the_post_thumbnail_url(0, 'professorLandscape') ?? '',
                    'month' => $eventDate ? $eventDate->format('M') : '',
                    'day' => $eventDate ? $eventDate->format('d') : '',
                    'description' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18),
                    'id' => get_the_id()
                ));
            }
        }
        if (get_post_type() == 'program') {
            $relatedCampuses = get_field('related_campus') ?? false;
            foreach ($relatedCampuses as $campus) {
                array_push($results['campus'], array(
                    'title' => get_the_title($campus),
                    'url' => get_the_permalink($campus)
                ));
            }
        }
        if (get_post_type() == 'post' || get_post_type() == 'page') {
            array_push($results['generalInfo'], array(
                'title' => get_the_title(),
                'url' => get_the_permalink(),
                'postType' => get_post_type(),
                'authorName' => get_the_author()
            ));
        }
    }


    //must be after $mainQuery loop so $results is defined
    if ($results['program']) {
        $programsMetaQuery = array('relation' => 'OR');

        foreach($results['program'] as $item) {
            array_push($programsMetaQuery, array(
                'key' => 'related_program',
                'compare' => 'LIKE',
                'value' => '"' . $item['id'] . '"',
            ));
        }
    
        $programRelationshipQuery = new WP_Query(array(
            'post_type' => array('professor', 'event'),
            'meta_query' => $programsMetaQuery
        ));

        while($programRelationshipQuery->have_posts()) {
            $programRelationshipQuery->the_post();
            get_post_type() == 'event' ? $eventDate = new DateTime(get_field('eventDate')) : $eventDate = false;

            foreach ($programRelationshipQuery->query['post_type'] as $postType) {
                if (get_post_type() == $postType) {
                    array_push($results[$postType], array(
                        'title' => get_the_title(),
                        'url' => get_the_permalink(),
                        'image' => get_the_post_thumbnail_url(0, 'professorLandscape') ?? '',
                        'month' => $eventDate ? $eventDate->format('M') : '',
                        'day' => $eventDate ? $eventDate->format('d') : '',
                        'description' => has_excerpt() ? get_the_excerpt() : wp_trim_words(get_the_content(), 18),
                        'id' => get_the_id()
                    ));
                }
            }
        }
        foreach ($programRelationshipQuery->query['post_type'] as $postType) {
            $results[$postType] = array_values(array_unique($results[$postType], SORT_REGULAR));
        }
    }

    return $results;
}