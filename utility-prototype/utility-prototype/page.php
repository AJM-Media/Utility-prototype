<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="wrap prose">
<?php
if (have_posts()) : while (have_posts()) : the_post();
  the_content();
endwhile; endif;
?>
</div>
<?php get_footer(); ?>
