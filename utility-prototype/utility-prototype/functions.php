<?php
if (!defined('ABSPATH')) { exit; }

function utility_theme_setup() {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('html5', [
    'search-form',
    'comment-form',
    'comment-list',
    'gallery',
    'caption',
    'style',
    'script',
  ]);

  register_nav_menus([
    'primary' => __('Primary Menu', 'utility'),
  ]);
}
add_action('after_setup_theme', 'utility_theme_setup');


/* Assets */
function utility_enqueue_assets() {
  wp_enqueue_style(
    'utility-style',
    get_stylesheet_uri(),
    [],
    wp_get_theme()->get('Version')
  );

  wp_enqueue_script(
    'utility-main',
    get_template_directory_uri() . '/assets/js/main.js',
    [],
    wp_get_theme()->get('Version'),
    true
  );

  wp_localize_script('utility-main', 'UtilityPrototype', [
    'currencySymbol' => '£',
    'assetBase' => trailingslashit(get_template_directory_uri()) . 'assets/img/',
  ]);
}
add_action('wp_enqueue_scripts', 'utility_enqueue_assets');


/* Products (CPT) */
function utility_register_product_cpt() {
  register_post_type('utility_product', [
    'labels' => [
      'name' => __('Products', 'utility'),
      'singular_name' => __('Product', 'utility'),
    ],
    'public' => true,
    'show_in_rest' => true,
    'menu_icon' => 'dashicons-cart',
    'supports' => ['title'],
    'has_archive' => false,
    'rewrite' => ['slug' => 'products'],
  ]);
}
add_action('init', 'utility_register_product_cpt');


/* Meta boxes */
function utility_add_metaboxes() {
  add_meta_box(
    'utility_product_config',
    __('Product Config (JSON)', 'utility'),
    'utility_render_product_config_metabox',
    'utility_product',
    'normal',
    'high'
  );

  add_meta_box(
    'utility_product_picker',
    __('Prototype Product', 'utility'),
    'utility_render_product_picker_metabox',
    'page',
    'side',
    'default'
  );
}
add_action('add_meta_boxes', 'utility_add_metaboxes');


function utility_render_product_config_metabox($post) {
  $value = (string) get_post_meta($post->ID, '_utility_product_config', true);
  wp_nonce_field('utility_save_product_config', 'utility_product_config_nonce');

  echo '<p class="description">Schema uses an array of options with explicit <code>id</code> fields.</p>';
  echo '<textarea id="utilityProductConfig" name="utility_product_config" style="width:100%;min-height:280px;font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, \"Liberation Mono\", \"Courier New\", monospace;">' . esc_textarea($value) . '</textarea>';
  echo '<p id="utilityProductConfigStatus" class="description" style="margin-top:8px;"></p>';
}


function utility_render_product_picker_metabox($post) {
  wp_nonce_field('utility_save_product_picker', 'utility_product_picker_nonce');
  $selected = (int) get_post_meta($post->ID, '_utility_product_id', true);

  $products = get_posts([
    'post_type' => 'utility_product',
    'numberposts' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
  ]);

  echo '<p class="description">Select which Product this page should render.</p>';
  echo '<select name="utility_product_id" style="width:100%;">';
  echo '<option value="0">— None (fallback demo data) —</option>';

  foreach ($products as $p) {
    printf(
      '<option value="%d" %s>%s</option>',
      (int) $p->ID,
      selected($selected, $p->ID, false),
      esc_html($p->post_title)
    );
  }

  echo '</select>';
}


/* Validation */
function utility_validate_product_data($data, &$errors = []) {
  $errors = [];

  if (!is_array($data)) {
    $errors[] = 'Root must be an object.';
    return false;
  }

  $title = isset($data['title']) ? trim((string) $data['title']) : '';
  if ($title === '') $errors[] = 'Missing "title".';

  if (!isset($data['basePrice']) || !is_numeric($data['basePrice'])) {
    $errors[] = 'Missing or invalid "basePrice".';
  }

  if (isset($data['sku']) && !is_string($data['sku'])) {
    $errors[] = '"sku" must be a string.';
  }

  if (isset($data['leadTime']) && !is_string($data['leadTime'])) {
    $errors[] = '"leadTime" must be a string.';
  }

  if (isset($data['images'])) {
    if (!is_array($data['images'])) {
      $errors[] = '"images" must be an array.';
    } else {
      foreach ($data['images'] as $i => $img) {
        if (!is_array($img)) {
          $errors[] = 'Image #' . ($i + 1) . ' must be an object.';
          continue;
        }
        $src = isset($img['src']) ? trim((string) $img['src']) : '';
        $file = isset($img['file']) ? trim((string) $img['file']) : '';
        if ($src === '' && $file === '') {
          $errors[] = 'Image #' . ($i + 1) . ' requires "src" or "file".';
        }
        if (isset($img['alt']) && !is_string($img['alt'])) {
          $errors[] = 'Image #' . ($i + 1) . ' has invalid "alt".';
        }
      }
    }
  }

  if (!isset($data['options']) || !is_array($data['options']) || empty($data['options'])) {
    $errors[] = 'Missing or invalid "options".';
    return empty($errors);
  }

  foreach ($data['options'] as $group_key => $group) {
    if (!is_array($group)) {
      $errors[] = 'Option group "' . $group_key . '" must be an object.';
      continue;
    }

    $label = isset($group['label']) ? trim((string) $group['label']) : '';
    $type = isset($group['type']) ? (string) $group['type'] : '';
    $default = isset($group['default']) ? (string) $group['default'] : '';

    if ($label === '') $errors[] = 'Option group "' . $group_key . '" missing "label".';
    if (!in_array($type, ['select', 'radio', 'swatch'], true)) $errors[] = 'Option group "' . $group_key . '" has invalid "type".';

    if ($type !== 'select' && $default === '') {
      $errors[] = 'Option group "' . $group_key . '" missing "default".';
    }

    if (isset($group['placeholder']) && !is_string($group['placeholder'])) {
      $errors[] = 'Option group "' . $group_key . '" has invalid "placeholder".';
    }

    if (!isset($group['options']) || !is_array($group['options']) || empty($group['options'])) {
      $errors[] = 'Option group "' . $group_key . '" missing "options" array.';
      continue;
    }

    $ids = [];
    foreach ($group['options'] as $opt) {
      if (!is_array($opt)) {
        $errors[] = 'Option in "' . $group_key . '" must be an object.';
        continue;
      }

      $id = isset($opt['id']) ? trim((string) $opt['id']) : '';
      $opt_label = isset($opt['label']) ? trim((string) $opt['label']) : '';

      if ($id === '') $errors[] = 'Option in "' . $group_key . '" missing "id".';
      if ($opt_label === '') $errors[] = 'Option "' . ($id ?: 'unknown') . '" in "' . $group_key . '" missing "label".';

      if ($id !== '') {
        if (isset($ids[$id])) $errors[] = 'Duplicate option id "' . $id . '" in "' . $group_key . '".';
        $ids[$id] = true;
      }

      if (isset($opt['price']) && !is_numeric($opt['price'])) {
        $errors[] = 'Option "' . ($id ?: 'unknown') . '" in "' . $group_key . '" has invalid "price".';
      }

      foreach (['swatchFile', 'image', 'file'] as $maybe_file) {
        if (isset($opt[$maybe_file]) && !is_string($opt[$maybe_file])) {
          $errors[] = 'Option "' . ($id ?: 'unknown') . '" in "' . $group_key . '" has invalid "' . $maybe_file . '".';
        }
      }
    }

    if ($type === 'select' && $default !== '' && !isset($ids[$default])) {
      $errors[] = 'Option group "' . $group_key . '" default "' . $default . '" not found in options.';
    }
  }

  return empty($errors);
}
function utility_validate_product_json($raw, &$errors = []) {
  $errors = [];
  $raw = trim((string) $raw);

  if ($raw === '') return true;

  $decoded = json_decode($raw, true);
  if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
    $errors[] = 'Invalid JSON.';
    return false;
  }

  return utility_validate_product_data($decoded, $errors);
}


/* Save handlers */
function utility_admin_notice_key() {
  $user_id = get_current_user_id();
  return 'utility_config_notice_' . (int) $user_id;
}

function utility_set_admin_notice($message, $type = 'error') {
  set_transient(utility_admin_notice_key(), [
    'message' => (string) $message,
    'type' => $type === 'success' ? 'success' : 'error',
  ], 60);
}

add_action('admin_notices', function () {
  $notice = get_transient(utility_admin_notice_key());
  if (!$notice || !is_array($notice)) return;

  delete_transient(utility_admin_notice_key());

  $type = isset($notice['type']) ? (string) $notice['type'] : 'error';
  $msg = isset($notice['message']) ? (string) $notice['message'] : '';

  if ($msg === '') return;

  printf(
    '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
    esc_attr($type),
    esc_html($msg)
  );
});


add_action('save_post_utility_product', function ($post_id) {
  if (!isset($_POST['utility_product_config_nonce']) || !wp_verify_nonce((string) $_POST['utility_product_config_nonce'], 'utility_save_product_config')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $raw = isset($_POST['utility_product_config']) ? (string) wp_unslash($_POST['utility_product_config']) : '';
  $raw = trim($raw);

$errors = [];
if (!utility_validate_product_json($raw, $errors)) {
  utility_set_admin_notice('Product config not saved: ' . implode(' ', $errors), 'error');
  return;
}


  update_post_meta($post_id, '_utility_product_config', $raw);
  if ($raw !== '') utility_set_admin_notice('Product config saved.', 'success');
});


add_action('save_post_page', function ($post_id) {
  if (!isset($_POST['utility_product_picker_nonce']) || !wp_verify_nonce((string) $_POST['utility_product_picker_nonce'], 'utility_save_product_picker')) {
    return;
  }

  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!current_user_can('edit_post', $post_id)) return;

  $pid = isset($_POST['utility_product_id']) ? (int) $_POST['utility_product_id'] : 0;
  update_post_meta($post_id, '_utility_product_id', $pid);
});


/* Admin UX */
add_action('admin_enqueue_scripts', function ($hook) {
  $screen = function_exists('get_current_screen') ? get_current_screen() : null;
  if (!$screen || $screen->post_type !== 'utility_product') return;

  $js = <<<JS
(function(){
  var t = document.getElementById('utilityProductConfig');
  var s = document.getElementById('utilityProductConfigStatus');
  if(!t || !s) return;

  function set(msg, ok){
    s.textContent = msg;
    s.style.color = ok ? '#250858' : '#b91c1c';
  }

  function validate(){
    var v = (t.value || '').trim();
    if(!v){ set('No config set (fallback demo data will be used).', true); return; }
    try{
      var d = JSON.parse(v);
      if(!d || typeof d !== 'object'){ set('Invalid JSON root.', false); return; }
      if(!d.title || typeof d.basePrice === 'undefined' || !d.options){ set('Missing required keys: title, basePrice, options.', false); return; }
      if(typeof d.basePrice !== 'number' && isNaN(Number(d.basePrice))){ set('basePrice must be a number.', false); return; }
      set('JSON looks valid.', true);
    }catch(e){
      set('Invalid JSON.', false);
    }
  }

  t.addEventListener('input', function(){ window.clearTimeout(window.__uCfgT); window.__uCfgT = window.setTimeout(validate, 250); });
  validate();
})();
JS;

  wp_add_inline_script('jquery-core', $js, 'after');
});
