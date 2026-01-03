<?php if (!defined('ABSPATH')) { exit; } ?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e('Skip to content', 'utility'); ?></a>

<header class="site-header" role="banner">
  <div class="wrap site-header__inner">
    <a class="brand" href="<?php echo esc_url(home_url('/')); ?>">
      <span class="brand__mark" aria-hidden="true"><img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/utility.svg'); ?>" alt="" width="18" height="18"></span>
    </a>

    <nav class="site-nav" aria-label="<?php esc_attr_e('Primary', 'utility'); ?>">
      <?php
        wp_nav_menu([
          'theme_location' => 'primary',
          'container' => false,
          'fallback_cb' => '__return_false',
          'items_wrap' => '<ul class="nav">%3$s</ul>',
          'depth' => 1,
        ]);
      ?>
    </nav>
  </div>
</header>

<main id="main" class="site-main" role="main">
