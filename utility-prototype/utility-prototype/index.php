<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<div class="wrap prose">
  <h1><?php bloginfo('name'); ?></h1>
  <p class="muted">This theme is intended to be used with the <strong>Product Prototype</strong> page template.</p>

  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class('card'); ?>>
      <h2 class="h3"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
      <div class="muted small"><?php echo esc_html(get_the_date()); ?></div>
      <div class="prose"><?php the_excerpt(); ?></div>
    </article>
  <?php endwhile; endif; ?>
</div>
<?php get_footer(); ?>
