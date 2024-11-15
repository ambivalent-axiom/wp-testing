<?php
    get_header();

    while(have_posts()) {
        the_post();
        pageBanner();
        ?>
    <div class="container container--narrow page-section">
      <?php 
        $parentId = wp_get_post_parent_id(get_the_ID());
        if ($parentId) {
          ?>
            <div class="metabox metabox--position-up metabox--with-home-link">
              <p>
                <a class="metabox__blog-home-link" href="<?php echo get_permalink($parentId) ?>">
                  <i class="fa fa-home" aria-hidden="true"></i>
                  <?php echo get_the_title($parentId) //gets the title of the page specified in param ?> 
                </a> 
                <span class="metabox__main">
                  <?php the_title() //gets the title of the current page ?>
                </span>
              </p>
            </div>
          <?php
        }
      ?>

      <?php 
        if ($parentId || get_pages(array('child_of' => get_the_ID()))) { ?>
          <div class="page-links">
            <h2 class="page-links__title">
              <a href="<?php echo get_permalink($parentId) ?>">
                <?php echo get_the_title($parentId) ?>
              </a>
            </h2>
            <ul class="min-list">
              <?php 
                wp_list_pages(array(
                  'title_li' => NULL,
                  'child_of' => $parentId ?: get_the_ID(),
                  'sort_column' => 'menu_order'
                )) //takes associative array 
              ?>
            </ul>
          </div>
        <?php };
      ?>

      <div class="generic-content">
        <?php the_content() ?>
      </div>
    </div>
        
        <?php

    }

    get_footer();
?>

