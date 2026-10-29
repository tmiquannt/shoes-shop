<?php
/**
 * PAXG'Shop storefront home matching the approved brand direction.
 */
get_header();

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$catalog_url = function_exists('get_post_type_archive_link') ? get_post_type_archive_link('product') : $shop_url;
if (!$catalog_url) $catalog_url = $shop_url;
$best_sellers_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url(['orderby' => 'popularity']) : add_query_arg('orderby', 'popularity', $catalog_url);
$new_arrivals_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url(['orderby' => 'date']) : add_query_arg('orderby', 'date', $catalog_url);
$popular_url = function_exists('paxig_plp_catalog_url') ? paxig_plp_catalog_url(['orderby' => 'rating']) : $shop_url;
$about_url = function_exists('paxig_storefront_page_url') ? paxig_storefront_page_url('about') : '';
$theme_uri = get_template_directory_uri();
$is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
$hero_slides = [
    ['image' => 'vyre-slide-night-v2.webp', 'name' => $is_vi ? 'CHINH PHỤC MÀN ĐÊM' : 'RUN THE NIGHT'],
    ['image' => 'vyre-slide-court-v2.webp', 'name' => $is_vi ? 'LÀM CHỦ SÂN ĐẤU' : 'OWN THE COURT'],
    ['image' => 'vyre-slide-city-v2.webp', 'name' => $is_vi ? 'SINH RA ĐỂ CHUYỂN ĐỘNG' : 'MADE TO MOVE'],
];

// Retrieve products for Best Sellers (top total sales)
$best_seller_products = [];
if (function_exists('wc_get_products')) {
    $best_seller_products = wc_get_products([
        'status'     => 'publish',
        'visibility' => 'visible',
        'limit'      => 4,
        'orderby'    => 'meta_value_num',
        'meta_key'   => 'total_sales',
        'order'      => 'DESC',
    ]);
}
if (empty($best_seller_products) && function_exists('wc_get_products')) {
    $best_seller_products = wc_get_products([
        'status'     => 'publish',
        'visibility' => 'visible',
        'limit'      => 4,
        'orderby'    => 'date',
        'order'      => 'DESC',
    ]);
}

// Retrieve products for New Arrivals (latest published, excluding best sellers)
$best_seller_ids = !empty($best_seller_products) ? wp_list_pluck($best_seller_products, 'id') : [];
$new_products = [];
if (function_exists('wc_get_products')) {
    $new_products = wc_get_products([
        'status'     => 'publish',
        'visibility' => 'visible',
        'limit'      => 4,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'exclude'    => $best_seller_ids,
    ]);
}
if (empty($new_products) && function_exists('paxig_home_products')) {
    $new_products = paxig_home_products(['limit' => 4]);
}

// Retrieve products for Popular Products (popular items with high sales/engagement, excluding already shown)
$shown_ids = array_merge($best_seller_ids, !empty($new_products) ? wp_list_pluck($new_products, 'id') : []);
$popular_products = [];
if (function_exists('wc_get_products')) {
    $popular_products = wc_get_products([
        'status'     => 'publish',
        'visibility' => 'visible',
        'limit'      => 4,
        'orderby'    => 'meta_value_num',
        'meta_key'   => 'total_sales',
        'order'      => 'DESC',
        'exclude'    => $shown_ids,
    ]);
}
if (count($popular_products) < 4 && function_exists('wc_get_products')) {
    $needed = 4 - count($popular_products);
    $exclude_all = array_merge($shown_ids, !empty($popular_products) ? wp_list_pluck($popular_products, 'id') : []);
    $fallback_popular = wc_get_products([
        'status'     => 'publish',
        'visibility' => 'visible',
        'limit'      => $needed,
        'orderby'    => 'date',
        'order'      => 'DESC',
        'exclude'    => $exclude_all,
    ]);
    $popular_products = array_merge($popular_products, $fallback_popular);
}
?>

<main class="paxig-home-main vyre-home-main" id="main">
    <!-- HERO SECTION MATCHING 01_Homepage_Video_Hero.png -->
    <section class="vyre-hero" aria-labelledby="vyre-hero-title">
        <div class="vyre-hero-media">
            <?php foreach ($hero_slides as $slide_index => $slide) : ?>
                <picture
                    class="vyre-hero-slide <?php echo 0 === $slide_index ? 'is-active' : ''; ?>"
                    data-slide-name="<?php echo esc_attr($slide['name']); ?>"
                >
                    <img
                        src="<?php echo esc_url($theme_uri . '/assets/images/' . $slide['image']); ?>"
                        alt=""
                        class="vyre-hero-shoe-img"
                        decoding="async"
                        <?php if (0 === $slide_index) : ?>fetchpriority="high"<?php endif; ?>
                    >
                </picture>
            <?php endforeach; ?>
            <div class="vyre-hero-vignette" aria-hidden="true"></div>
        </div>

        <div class="vyre-hero-content paxig-container">
            <span class="vyre-hero-eyebrow">
                <?php echo $is_vi ? 'MÙA MỚI — THU / ĐÔNG 2026' : 'NEW SEASON — AUTUMN / WINTER 2026'; ?>
            </span>
            <h1 id="vyre-hero-title" class="vyre-hero-title">
                <span>BUILT</span>
                <span>DIFFERENT.</span>
            </h1>
            <p class="vyre-hero-desc">
                <?php if ($is_vi) : ?>
                    GIÀY THỂ THAO CHO MỘT TƯƠNG LAI RỰC RỠ HƠN.<br>
                    VĂN HOÁ THÚC ĐẨY CON NGƯỜI TIẾN BƯỚC.
                <?php else : ?>
                    SNEAKERS FOR A BRIGHTER TOMORROW.<br>
                    CULTURE MOVES PEOPLE FORWARD.
                <?php endif; ?>
            </p>
            <div class="vyre-hero-ctas">
                <a href="<?php echo esc_url($shop_url); ?>" class="vyre-hero-pill-btn">
                    <span><?php echo esc_html__( 'SHOP THE COLLECTION', 'paxig-shoes' ); ?></span>
                    <span class="vyre-arrow" aria-hidden="true"><?php echo paxig_icon('arrow-right', 18); ?></span>
                </a>
            </div>
        </div>

        <div class="vyre-hero-footer paxig-container">
            <div class="vyre-hero-footer-left">
                <span class="vyre-slide-indicator" data-vyre-indicator>01 / <?php echo esc_html(str_pad((string) count($hero_slides), 2, '0', STR_PAD_LEFT)); ?></span>
                <span class="vyre-slide-name" data-vyre-caption><?php echo esc_html($hero_slides[0]['name']); ?></span>
            </div>
            <div class="vyre-hero-progress" data-vyre-progress role="group" aria-label="<?php echo esc_attr($is_vi ? 'Ảnh trình chiếu' : 'Hero slides'); ?>" hidden>
                <?php foreach ($hero_slides as $slide_index => $slide) : ?>
                    <button
                        type="button"
                        class="vyre-prog-bar <?php echo 0 === $slide_index ? 'is-active' : ''; ?>"
                        data-vyre-slide-to="<?php echo esc_attr((string) $slide_index); ?>"
                        aria-label="<?php echo esc_attr($is_vi ? sprintf('Chuyển đến ảnh %d', $slide_index + 1) : sprintf('Show slide %d', $slide_index + 1)); ?>"
                        <?php if (0 === $slide_index) : ?>aria-current="true"<?php endif; ?>
                    ></button>
                <?php endforeach; ?>
            </div>
            <div class="vyre-hero-footer-right">
                <a href="<?php echo esc_url($shop_url); ?>">
                    <span><?php echo esc_html($is_vi ? 'KHÁM PHÁ BỘ SƯU TẬP' : 'EXPLORE THE COLLECTION'); ?></span>
                    <?php echo paxig_icon('arrow-right', 18); ?>
                </a>
            </div>
        </div>
    </section>



    <!-- 01 - BEST SELLERS SECTION -->
    <section class="vyre-curated paxig-container" id="best-sellers" aria-labelledby="best-sellers-title">
        <div class="vyre-section-head">
            <div class="vyre-section-title-wrap">
                <span class="vyre-section-eyebrow"><?php echo $is_vi ? 'TUYỂN CHỌN / 01' : esc_html__( 'CURATED / 01', 'paxig-shoes' ); ?></span>
                <h2 id="best-sellers-title" class="vyre-section-title"><?php echo $is_vi ? 'BÁN CHẠY NHẤT' : esc_html__( 'BEST SELLERS', 'paxig-shoes' ); ?></h2>
            </div>
            <a href="<?php echo esc_url($best_sellers_url); ?>" class="vyre-section-link">
                <?php echo $is_vi ? 'XEM TẤT CẢ' : esc_html__( 'VIEW ALL', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-right', 18); ?>
            </a>
        </div>

        <ul class="vyre-products-grid">
            <?php
            if (!empty($best_seller_products)) :
                foreach (array_values($best_seller_products) as $item) :
                    get_template_part('template-parts/product-card', null, ['product' => $item]);
                endforeach;
            endif;
            ?>
        </ul>
    </section>

    <!-- 02 - NEW ARRIVALS SECTION -->
    <section class="vyre-curated paxig-container" id="new-arrivals" aria-labelledby="new-arrivals-title">
        <div class="vyre-section-head">
            <div class="vyre-section-title-wrap">
                <span class="vyre-section-eyebrow"><?php echo $is_vi ? 'TUYỂN CHỌN / 02' : esc_html__( 'CURATED / 02', 'paxig-shoes' ); ?></span>
                <h2 id="new-arrivals-title" class="vyre-section-title"><?php echo $is_vi ? 'SẢN PHẨM MỚI' : esc_html__( 'NEW ARRIVALS', 'paxig-shoes' ); ?></h2>
            </div>
            <a href="<?php echo esc_url($new_arrivals_url); ?>" class="vyre-section-link">
                <?php echo $is_vi ? 'XEM TẤT CẢ' : esc_html__( 'VIEW ALL', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-right', 18); ?>
            </a>
        </div>

        <ul class="vyre-products-grid">
            <?php
            if (!empty($new_products)) :
                foreach (array_values($new_products) as $item) :
                    get_template_part('template-parts/product-card', null, ['product' => $item]);
                endforeach;
            endif;
            ?>
        </ul>
    </section>

    <!-- 03 - POPULAR PRODUCTS SECTION -->
    <section class="vyre-curated paxig-container" id="popular-products" aria-labelledby="popular-products-title">
        <div class="vyre-section-head">
            <div class="vyre-section-title-wrap">
                <span class="vyre-section-eyebrow"><?php echo $is_vi ? 'TUYỂN CHỌN / 03' : esc_html__( 'CURATED / 03', 'paxig-shoes' ); ?></span>
                <h2 id="popular-products-title" class="vyre-section-title"><?php echo $is_vi ? 'SẢN PHẨM PHỔ BIẾN' : esc_html__( 'POPULAR PRODUCTS', 'paxig-shoes' ); ?></h2>
            </div>
            <a href="<?php echo esc_url($popular_url); ?>" class="vyre-section-link">
                <?php echo $is_vi ? 'XEM TẤT CẢ' : esc_html__( 'VIEW ALL', 'paxig-shoes' ); ?> <?php echo paxig_icon('arrow-right', 18); ?>
            </a>
        </div>

        <ul class="vyre-products-grid">
            <?php
            if (!empty($popular_products)) :
                foreach (array_values($popular_products) as $item) :
                    get_template_part('template-parts/product-card', null, ['product' => $item]);
                endforeach;
            endif;
            ?>
        </ul>
    </section>

    <!-- WHO WE ARE SECTION MATCHING 01_Homepage_Video_Hero.png -->
    <section class="vyre-brand-story" id="brand-story" aria-labelledby="brand-story-title">
        <div class="paxig-container vyre-brand-inner">
            <div class="vyre-brand-left">
                <span class="vyre-section-eyebrow"><?php echo esc_html__( 'WHO WE ARE', 'paxig-shoes' ); ?></span>
                <h2 id="brand-story-title" class="vyre-brand-title">
                    <span><?php echo $is_vi ? 'HƠN CẢ' : 'MORE THAN'; ?></span>
                    <span><?php echo $is_vi ? 'GIÀY THỂ THAO.' : 'SNEAKERS.'; ?></span>
                </h2>
            </div>
            <div class="vyre-brand-right">
                <p class="vyre-brand-desc">
                    <?php if ($is_vi) : ?>
                        Một thế hệ giày mới được định hình bởi văn hóa, tay nghề thủ công và lòng dũng cảm để bước đi khác biệt.
                    <?php else : ?>
                        A new generation of footwear shaped by culture, craftsmanship, and the courage to move differently.
                    <?php endif; ?>
                </p>
                <?php if ($about_url) : ?>
                    <a href="<?php echo esc_url($about_url); ?>" class="vyre-btn-story">
                        <span><?php echo esc_html__( 'OUR STORY', 'paxig-shoes' ); ?></span>
                        <?php echo paxig_icon('arrow-right', 18); ?>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
