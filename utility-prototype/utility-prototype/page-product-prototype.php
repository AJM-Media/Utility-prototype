<?php
/**
 * Template Name: Product Prototype
 */
if (!defined('ABSPATH')) { exit; }

$pid = (int) get_post_meta(get_the_ID(), '_utility_product_id', true);
$config_raw = $pid ? (string) get_post_meta($pid, '_utility_product_config', true) : '';
$data = null;

if ($config_raw !== '') {
  $decoded = json_decode($config_raw, true);
  if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
    $errors = [];
    if (utility_validate_product_data($decoded, $errors)) {
      $data = $decoded;
    }
  }
}

if (!$data) {
  $data = [
    'title' => 'Hay About A Lounge Chair - Low',
    'sku' => 'AAL82',
    'leadTime' => 'Made To Order: 6 - 8 weeks',
    'basePrice' => 779.00,
    'images' => [
      [ 'file' => 'chair_grey.jpg', 'alt' => 'Front' ],
      [ 'file' => 'chair_pink.jpg', 'alt' => 'Pink' ],
    ],
    'options' => [
      'fabric' => [
        'label' => 'Fabric',
        'type' => 'select',
        'default' => '',
        'placeholder' => 'Select your fabric',
        'options' => [
          [ 'id' => 'steelcut-trio', 'label' => 'Steelcut Trio', 'price' => 30.00 ],
          [ 'id' => 'canvas', 'label' => 'Canvas', 'price' => 0.00 ],
        ],
      ],
      'colour' => [
        'label' => 'Colour',
        'type' => 'select',
        'default' => '',
        'placeholder' => 'Select your colour',
        'options' => [
          [ 'id' => 'sct526', 'label' => 'SCT526', 'price' => 0.00 ],
          [ 'id' => 'sct124', 'label' => 'SCT124', 'price' => 0.00 ],
        ],
      ],
      'legFinish' => [
        'label' => 'Leg Finish',
        'type' => 'select',
        'default' => '',
        'placeholder' => 'Select your leg finish',
        'options' => [
          [ 'id' => 'matt-lacquered-oak', 'label' => 'Matt Lacquered Oak', 'price' => 0.00 ],
          [ 'id' => 'black-stained-oak', 'label' => 'Black stained oak', 'price' => 0.00 ],
        ],
      ],
      'seatCushion' => [
        'label' => 'Optional Seat Cushion',
        'type' => 'select',
        'default' => '',
        'placeholder' => 'Select your seat cushion',
        'options' => [
          [ 'id' => 'none', 'label' => 'No cushion', 'price' => 0.00 ],
          [ 'id' => 'same-fabric-g4', 'label' => 'In same fabric, Group 4', 'price' => 109.00 ],
        ],
      ],
    ],
  ];
}

$theme_uri = get_template_directory_uri();
$asset_base = trailingslashit($theme_uri) . 'assets/img/';

function utility_asset_url($asset_base, $value) {
  $value = (string) $value;
  if ($value === '') return '';
  if (preg_match('#^https?://#i', $value) || str_starts_with($value, '/')) return $value;
  return $asset_base . ltrim($value, '/');
}

function utility_resolve_product_assets(&$data, $asset_base) {
  if (isset($data['images']) && is_array($data['images'])) {
    foreach ($data['images'] as &$img) {
      if (!is_array($img)) continue;

      if (empty($img['src']) && !empty($img['file'])) {
        $img['src'] = utility_asset_url($asset_base, $img['file']);
      }
    }
    unset($img);
  }

  if (isset($data['options']) && is_array($data['options'])) {
    foreach ($data['options'] as &$group) {
      if (!is_array($group) || empty($group['options']) || !is_array($group['options'])) continue;

      foreach ($group['options'] as &$opt) {
        if (!is_array($opt)) continue;

        if (!empty($opt['swatchFile']) && empty($opt['swatchSrc'])) {
          $opt['swatchSrc'] = utility_asset_url($asset_base, $opt['swatchFile']);
        }

        if (!empty($opt['image']) && empty($opt['imageSrc'])) {
          $opt['imageSrc'] = utility_asset_url($asset_base, $opt['image']);
        }
      }
      unset($opt);
    }
    unset($group);
  }
}

utility_resolve_product_assets($data, $asset_base);

$title = isset($data['title']) ? (string) $data['title'] : get_the_title();
$sku = isset($data['sku']) ? (string) $data['sku'] : '';
$lead_time = isset($data['leadTime']) ? (string) $data['leadTime'] : '';
$base_price = isset($data['basePrice']) ? (float) $data['basePrice'] : 0.0;
$images = (isset($data['images']) && is_array($data['images'])) ? $data['images'] : [];
$options = (isset($data['options']) && is_array($data['options'])) ? $data['options'] : [];

function utility_money($n) {
  return '£' . number_format((float) $n, 2);
}

$first_src = '';
$first_alt = $title;
foreach ($images as $img) {
  if (is_array($img) && !empty($img['src'])) {
    $first_src = (string) $img['src'];
    $first_alt = !empty($img['alt']) ? (string) $img['alt'] : $title;
    break;
  }
}

get_header();
?>

<script id="utility-product-data" type="application/json"><?php echo wp_json_encode($data); ?></script>


<div class="wrap product">
  <header class="product__header">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
      <ol>
        <li><a href="<?php echo esc_url(home_url('/')); ?>">Home</a></li>
        <li><a href="#" aria-current="false">Lounge Chairs</a></li>
        <li aria-current="page"><?php echo esc_html($title); ?></li>
      </ol>
    </nav>

    <h1 class="product__title"><?php echo esc_html($title); ?></h1>
    <?php if ($sku) : ?>
      <p class="product__subtitle muted"><?php echo esc_html($sku); ?></p>
    <?php endif; ?>
  </header>

  <div class="product__grid">
    <section class="gallery card" aria-label="Product images">
      <div class="gallery__stage" tabindex="0" aria-label="Selected product image">
        <?php if ($first_src) : ?>
          <img id="galleryImage" src="<?php echo esc_url($first_src); ?>" alt="<?php echo esc_attr($first_alt); ?>">
        <?php else : ?>
          <span class="muted small">No images configured</span>
        <?php endif; ?>
      </div>

      <?php if (count($images) > 1) : ?>
        <div class="gallery__thumbs" role="list" aria-label="Choose an image">
          <?php foreach ($images as $i => $img) :
            if (!is_array($img) || empty($img['src'])) continue;
            $src = (string) $img['src'];
            $alt = !empty($img['alt']) ? (string) $img['alt'] : $title;
          ?>
            <button
              class="thumb<?php echo $i === 0 ? ' is-active' : ''; ?>"
              type="button"
              data-src="<?php echo esc_attr($src); ?>"
              data-alt="<?php echo esc_attr($alt); ?>"
              aria-label="Select image <?php echo (int) ($i + 1); ?>"
            >
              <img src="<?php echo esc_url($src); ?>" alt="" aria-hidden="true">
            </button>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </section>

    <aside class="panel card" aria-label="Configuration">
      <form id="configForm" novalidate>
        <div class="panel__top">
          <div>
            <div class="panel__from">From</div>
            <div class="price" id="priceFrom"><?php echo esc_html(utility_money($base_price)); ?></div>
          </div>
          <?php if ($lead_time) : ?>
            <div class="badge"><?php echo esc_html($lead_time); ?></div>
          <?php endif; ?>
        </div>

        <?php
  $select_groups = ['fabric', 'colour', 'legFinish', 'seatCushion'];
  foreach ($select_groups as $key) :
    if (empty($options[$key]) || !is_array($options[$key])) continue;
    $group = $options[$key];
    $label = !empty($group['label']) ? (string) $group['label'] : $key;
    $placeholder = !empty($group['placeholder']) ? (string) $group['placeholder'] : ('Select your ' . strtolower($label));
    $default = isset($group['default']) ? (string) $group['default'] : '';
    $opts = (!empty($group['options']) && is_array($group['options'])) ? $group['options'] : [];
?>
  <fieldset class="field">
    <label class="field__label" for="<?php echo esc_attr('opt_' . $key); ?>"><?php echo esc_html($label); ?>:</label>
    <select id="<?php echo esc_attr('opt_' . $key); ?>" name="<?php echo esc_attr($key); ?>" data-group="<?php echo esc_attr($key); ?>">
      <option value="" disabled <?php echo $default === '' ? 'selected' : ''; ?>>
        <?php echo esc_html($placeholder); ?>
      </option>

      <?php foreach ($opts as $opt) :
        if (!is_array($opt)) continue;
        $id = isset($opt['id']) ? (string) $opt['id'] : '';
        if ($id === '') continue;

        $opt_label = isset($opt['label']) ? (string) $opt['label'] : $id;
        $price = isset($opt['price']) ? (float) $opt['price'] : 0.0;
        $swatch = !empty($opt['swatchSrc']) ? (string) $opt['swatchSrc'] : '';
        $img_src = !empty($opt['imageSrc']) ? (string) $opt['imageSrc'] : '';
      ?>
        <option
          value="<?php echo esc_attr($id); ?>"
          <?php echo $default === $id ? 'selected' : ''; ?>
          data-label="<?php echo esc_attr($opt_label); ?>"
          data-price="<?php echo esc_attr($price); ?>"
          <?php echo $swatch ? 'data-swatch="' . esc_attr($swatch) . '"' : ''; ?>
          <?php echo $img_src ? 'data-image="' . esc_attr($img_src) . '"' : ''; ?>
        >
          <?php echo esc_html($opt_label); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </fieldset>
<?php endforeach; ?>


        <div class="summary">
                      
          <div class="summary__row">
            <div class="field__label">Option Summary:</div>
          </div>
          <ul class="summary__list" id="selectionSummary" hidden></ul>

          <div class="muted small" id="unitRow" hidden>Unit Price: <span id="priceUnit"></span></div>

        <fieldset class="field">
          <label class="field__label" for="qty">Quantity:</label>
          <div class="qty__controls">
            <button class="btn-icon" id="qtyMinus" type="button" aria-label="Decrease quantity">−</button>
            <input id="qty" name="qty" type="number" min="1" value="1" inputmode="numeric">
            <button class="btn-icon" id="qtyPlus" type="button" aria-label="Increase quantity">+</button>
          </div>
        </fieldset>

<div class="summary__row summary__row--total" aria-live="polite">
  <div class="field__label">Total cost</div>
  <div class="summary__value" id="priceTotal">£0.00</div>
</div>


          <div class="actions">
          <button class="btn-primary" id="addToBasket" type="submit" disabled>
            <span>Add to Basket</span>
          </button>


            <button class="btn-like" id="likeBtn" type="button" aria-pressed="false" aria-label="Save to wishlist">
              <?php
$like_svg = get_template_directory() . '/assets/img/LikeButton.svg';
if (file_exists($like_svg)) {
  echo file_get_contents($like_svg);
}
?>
            </button>
          </div>

          <div class="perks" aria-label="Delivery and finance info">
            <ul>
              <li>0% finance available, find out more</li>
              <li>Free UK mainland delivery</li>
              <li>100% Original designs only</li>
              <li>Price match. Found it cheaper? contact us</li>
              <li>Trade Discount Available</li>
            </ul>
          </div>

<div class="share" aria-label="Share options">
  <span class="share__label">Share online:</span>

  <a class="share__icon" id="shareFacebook" href="#" target="_blank" rel="noopener" aria-label="Share on Facebook">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
      <path d="M16 8.049C16 3.603 12.418 0 8 0S0 3.603 0 8.049C0 12.067 2.925 15.396 6.75 16v-5.625H4.719V8.05H6.75V6.275c0-2.017 1.195-3.131 3.022-3.131.876 0 1.791.157 1.791.157v1.98h-1.009c-.993 0-1.303.621-1.303 1.258V8.05h2.218l-.354 2.325h-1.864V16C13.075 15.396 16 12.067 16 8.049z"/>
    </svg>
  </a>

  <a class="share__icon" id="shareTwitter" href="#" target="_blank" rel="noopener" aria-label="Share on X">
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false">
      <path d="M5.026 15c6.038 0 9.341-5.003 9.341-9.334 0-.14 0-.282-.006-.422A6.685 6.685 0 0 0 16 3.542a6.658 6.658 0 0 1-1.889.518 3.301 3.301 0 0 0 1.447-1.817 6.533 6.533 0 0 1-2.087.793A3.286 3.286 0 0 0 7.875 6.03a9.325 9.325 0 0 1-6.767-3.429 3.289 3.289 0 0 0 1.018 4.381A3.323 3.323 0 0 1 .64 6.575v.045a3.288 3.288 0 0 0 2.632 3.218 3.203 3.203 0 0 1-.865.115 3.23 3.23 0 0 1-.614-.057 3.283 3.283 0 0 0 3.067 2.277A6.588 6.588 0 0 1 .78 13.58a6.32 6.32 0 0 1-.78-.045A9.344 9.344 0 0 0 5.026 15z"/>
    </svg>
  </a>

  <a class="share__icon" id="sharePinterest" href="#" target="_blank" rel="noopener" aria-label="Share on Pinterest">
<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pinterest" viewBox="0 0 16 16">
  <path d="M8 0a8 8 0 0 0-2.915 15.452c-.07-.633-.134-1.606.027-2.297.146-.625.938-3.977.938-3.977s-.239-.479-.239-1.187c0-1.113.645-1.943 1.448-1.943.682 0 1.012.512 1.012 1.127 0 .686-.437 1.712-.663 2.663-.188.796.4 1.446 1.185 1.446 1.422 0 2.515-1.5 2.515-3.664 0-1.915-1.377-3.254-3.342-3.254-2.276 0-3.612 1.707-3.612 3.471 0 .688.265 1.425.595 1.826a.24.24 0 0 1 .056.23c-.061.252-.196.796-.222.907-.035.146-.116.177-.268.107-1-.465-1.624-1.926-1.624-3.1 0-2.523 1.834-4.84 5.286-4.84 2.775 0 4.932 1.977 4.932 4.62 0 2.757-1.739 4.976-4.151 4.976-.811 0-1.573-.421-1.834-.919l-.498 1.902c-.181.695-.669 1.566-.995 2.097A8 8 0 1 0 8 0"/>
</svg>
  </a>
</div>


          </div>
        </div>
      </form>
    </aside>
  </div>
</div>

<div
  id="mini-toast"
  class="mini-toast"
  role="dialog"
  aria-modal="false"
  aria-live="polite"
  aria-hidden="true"
  tabindex="-1"
>
  <div class="mini-toast__accent" aria-hidden="true"></div>

  <button type="button" class="mini-toast__close" aria-label="Close" data-close-mini>
    <span aria-hidden="true">×</span>
  </button>

  <div class="mini-toast__content">
    <p class="mini-toast__title" id="miniToastTitle">Keyboard user?</p>
    <p class="mini-toast__text">
      Tab through options, use arrow keys within selects, and press Escape to close this tip.
    </p>
  </div>
</div>



<?php get_footer(); ?>
