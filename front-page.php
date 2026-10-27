<?php
/** PAXIG storefront home. Live links and catalogue data use native WooCommerce. */
get_header();
$shop_url = wc_get_page_permalink('shop');
$new_term = paxig_home_term('new-arrivals');
$new_products = paxig_home_products(array_merge(['limit'=>5], $new_term ? ['category'=>['new-arrivals']] : []));
$featured = paxig_home_products(['limit'=>5, 'featured'=>true]);
if (!$featured) $featured = paxig_home_products(['limit'=>5, 'orderby'=>'popularity']);
$collections = [
    'lifestyle'=>['Lifestyle', 'Everyday pairs for life in motion.', 'court.webp'],
    'running'=>['Running', 'Find your pace from the first step.', 'running.webp'],
    'basketball'=>['Basketball', 'Bring your own rhythm to the court.', 'basketball.webp'],
];
$theme_uri = get_template_directory_uri();
?>
<main class="paxig-home-main paxig-poster-home" id="main">
 <section class="paxig-poster-hero" aria-labelledby="paxig-home-title">
  <picture class="paxig-poster-image">
   <source media="(max-width: 600px)" srcset="<?php echo esc_url($theme_uri.'/assets/images/banner-night-court-mobile.png'); ?>">
   <img src="<?php echo esc_url($theme_uri.'/assets/images/banner-night-court.png'); ?>" alt="" width="1038" height="410" fetchpriority="high">
  </picture>
  <div class="paxig-poster-shade" aria-hidden="true"></div>
  <div class="paxig-poster-content">
   <?php $is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi'; ?>
   <h1 id="paxig-home-title">
       <?php if ($is_vi) : ?>
           <span>MỖI BƯỚC</span><span>MỘT CHẤT</span>
       <?php else : ?>
           <span>EVERY STEP</span><span>A STATEMENT</span>
       <?php endif; ?>
   </h1>
   <div class="paxig-poster-bottom">
    <div class="paxig-poster-intro">
     <a class="paxig-poster-cta" href="<?php echo esc_url($new_term ? $new_term['url'] : $shop_url); ?>"><?php echo esc_html__( 'Explore Collection', 'paxig-shoes' ); ?> <span aria-hidden="true">→</span></a>
    </div>
   </div>
  </div>
  <aside class="paxig-poster-rail" aria-label="<?php echo esc_attr__( 'Shop by sport', 'paxig-shoes' ); ?>">
   <nav class="paxig-poster-index" aria-label="<?php echo esc_attr__( 'Collections', 'paxig-shoes' ); ?>">
    <?php
    $rail_categories = [
        'lifestyle'   => __( 'Lifestyle', 'paxig-shoes' ),
        'running'     => __( 'Running', 'paxig-shoes' ),
        'basketball'  => __( 'Basketball', 'paxig-shoes' ),
        'skate'       => __( 'Skate', 'paxig-shoes' ),
        'outdoor'     => __( 'Outdoor', 'paxig-shoes' ),
        'accessories' => __( 'Accessories', 'paxig-shoes' ),
    ];
    foreach ($rail_categories as $slug=>$label) : $term=paxig_home_term($slug); ?>
     <a href="<?php echo esc_url($term ? $term['url'] : $shop_url); ?>"><?php echo esc_html($label); ?></a>
    <?php endforeach; ?>
   </nav>
  </aside>
 </section>

 <div class="paxig-home-container">
  <?php paxig_home_product_section('home-new', __( 'New Arrivals', 'paxig-shoes' ), $new_products, $new_term ? $new_term['url'] : $shop_url, false, __( 'Fresh pairs from the current catalogue.', 'paxig-shoes' )); ?>

  <section class="paxig-home-section paxig-home-collections" id="home-collections" aria-labelledby="collections-title">
   <div class="paxig-home-section-head">
    <div><h2 id="collections-title"><?php echo esc_html__( 'Choose your movement', 'paxig-shoes' ); ?></h2><p><?php echo esc_html__( 'From city streets to the court. Find the collection that moves with you.', 'paxig-shoes' ); ?></p></div>
    <a class="paxig-home-more" href="<?php echo esc_url($shop_url); ?>"><?php echo esc_html__( 'Shop all shoes', 'paxig-shoes' ); ?> <span aria-hidden="true">→</span></a>
   </div>
   <div class="energy-collections">
    <?php
    $collection_labels = [
        'lifestyle'  => [ __( 'Lifestyle', 'paxig-shoes' ), __( 'Everyday pairs for life in motion.', 'paxig-shoes' ), 'court.webp' ],
        'running'    => [ __( 'Running', 'paxig-shoes' ), __( 'Find your pace from the first step.', 'paxig-shoes' ), 'running.webp' ],
        'basketball' => [ __( 'Basketball', 'paxig-shoes' ), __( 'Bring your own rhythm to the court.', 'paxig-shoes' ), 'basketball.webp' ],
    ];
    foreach ($collection_labels as $slug=>$copy) : $term=paxig_home_term($slug); if (!$term) continue; ?>
     <a class="energy-collection energy-collection--<?php echo esc_attr($slug); ?>" href="<?php echo esc_url($term['url']); ?>">
      <img src="<?php echo esc_url(paxig_energy_image($slug,$copy[2])); ?>" alt="" width="900" height="900" loading="lazy">
      <div class="energy-collection-copy"><h3><?php echo esc_html($copy[0]); ?></h3><p><?php echo esc_html($copy[1]); ?></p><span class="energy-collection-link"><?php echo esc_html( sprintf( __( 'Shop %s', 'paxig-shoes' ), $copy[0] ) ); ?> <span aria-hidden="true">→</span></span></div>
     </a>
    <?php endforeach; ?>
   </div>
  </section>

  <?php paxig_home_product_section('home-featured', __( 'Picked for the next move', 'paxig-shoes' ), $featured, $shop_url, false, __( 'Browse more pairs from the live catalogue.', 'paxig-shoes' )); ?>

  <section class="paxig-home-section energy-fit" aria-labelledby="fit-title">
   <div class="energy-fit-intro"><h2 id="fit-title"><?php echo esc_html__( 'Find your fit', 'paxig-shoes' ); ?></h2><p><?php echo esc_html__( 'Use these steps to help choose the right size for your next pair.', 'paxig-shoes' ); ?></p><a class="paxig-poster-cta" href="<?php echo esc_url(home_url('/huong-dan/')); ?>"><?php echo esc_html__( 'Read the size guide', 'paxig-shoes' ); ?> <span aria-hidden="true">→</span></a></div>
   <div class="energy-fit-steps">
    <details open><summary><?php echo esc_html__( 'Measure both feet', 'paxig-shoes' ); ?></summary><p><?php echo esc_html__( 'Stand on a sheet of paper in your usual socks. Mark your heel and longest toe, then measure the distance. Use the longer measurement.', 'paxig-shoes' ); ?></p></details>
    <details><summary><?php echo esc_html__( 'Check the product size guide', 'paxig-shoes' ); ?></summary><p><?php echo esc_html__( 'Sizing varies by brand and model. Compare your measurement with the product information, then select an available size on its product page.', 'paxig-shoes' ); ?></p></details>
    <details><summary><?php echo esc_html__( 'Choose for the way you move', 'paxig-shoes' ); ?></summary><p><?php echo esc_html__( 'Explore Lifestyle for everyday outfits, Running for your next run, or Basketball for the court. Read each product\'s details before choosing.', 'paxig-shoes' ); ?></p></details>
   </div>
  </section>

  <div class="energy-service-strip" aria-label="<?php echo esc_attr__( 'Shopping support', 'paxig-shoes' ); ?>">
   <span><?php echo esc_html(paxig_shipping_summary()); ?></span>
   <a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php echo esc_html__( 'Track your orders →', 'paxig-shoes' ); ?></a>
   <a href="<?php echo esc_url(home_url('/huong-dan/')); ?>"><?php echo esc_html__( 'Shopping help →', 'paxig-shoes' ); ?></a>
  </div>
 </div>
</main>
<?php get_footer(); ?>
