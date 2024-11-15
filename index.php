<?php 
  get_header();
  pageBanner(array(
    'title' => 'Welcome to our blog!',
    'subtitle' => 'Keep Up With Our Latest News...'
  ));
  ?>
  <div class="container container--narrow page-section">
    <?php 
      while(have_posts()) {
        the_post(); ?>

          <div class="post-item">
            <h2><a class="headline headline--medium headline--post-title" href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
            <div class="metabox">
              <p>Posted by 
                <?php the_author_posts_link() ?> on 
                <?php the_time('M j, Y, g:i A') ?> in 
                <?php echo get_the_category_list(', ') ?>
              </p>
            </div>
            <div class="generic-content">
              <?php the_excerpt() ?>
              <p><a class="btn btn--blue" href="<?php echo the_permalink() ?>">Continue reading &raquo</a></p>
            </div>
          </div>

      <?php }
      //Pagination
      echo paginate_links();
    ?>
  </div>

  <?php
  get_footer();
?>