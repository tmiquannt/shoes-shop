<?php
/**
 * PAXG'Shop storefront home matching the approved brand direction.
 */
get_header();

$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/shop/');
$theme_uri = get_template_directory_uri();
$is_vi = function_exists('paxig_get_current_language') && paxig_get_current_language() === 'vi';
$hero_slides = [
    ['image' => 'vyre-slide-night-v2.webp', 'name' => $is_vi ? 'CHINH PHỤC MÀN ĐÊM' : 'RUN THE NIGHT'],
    ['image' => 'vyre-slide-court-v2.webp', 'name' => $is_vi ? 'LÀM CHỦ SÂN ĐẤU' : 'OWN THE COURT'],
    ['image' => 'vyre-slide-city-v2.webp', 'name' => $is_vi ? 'SINH RA ĐỂ CHUYỂN ĐỘNG' : 'MADE TO MOVE'],
];

// Retrieve products for Curated / 01 New Arrivals
$new_products = function_exists('paxig_home_products') ? paxig_home_products(['limit' => 4]) : [];
if (empty($new_products) && function_exists('wc_get_products')) {
    $new_products = wc_get_products(['status' => 'publish', 'visibility' => 'visible', 'limit' => 4]);
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
                    <span class="vyre-arrow" aria-hidden="true">→</span>
                </a>
                <button
                    type="button"
                    class="vyre-hero-video-status"
                    data-vyre-toggle
                    data-label-play="<?php echo esc_attr($is_vi ? 'Phát trình chiếu' : 'Play slideshow'); ?>"
                    data-label-pause="<?php echo esc_attr($is_vi ? 'Tạm dừng trình chiếu' : 'Pause slideshow'); ?>"
                    data-text-playing="<?php echo esc_attr($is_vi ? 'ĐANG PHÁT TRÌNH CHIẾU' : 'SLIDESHOW PLAYING'); ?>"
                    data-text-paused="<?php echo esc_attr($is_vi ? 'TẠM DỪNG TRÌNH CHIẾU' : 'SLIDESHOW PAUSED'); ?>"
                    hidden
                    aria-label="<?php echo esc_attr($is_vi ? 'Tạm dừng trình chiếu' : 'Pause slideshow'); ?>"
                >
                    <span class="vyre-video-pause-icon" data-vyre-toggle-icon aria-hidden="true">Ⅱ</span>
                    <span data-vyre-toggle-label><?php echo esc_html($is_vi ? 'ĐANG PHÁT TRÌNH CHIẾU' : 'SLIDESHOW PLAYING'); ?></span>
                </button>
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
                    <span><?php echo esc_html__( 'DRAG TO EXPLORE', 'paxig-shoes' ); ?></span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- TICKER MARQUEE STRIP MATCHING 01_Homepage_Video_Hero.png -->
    <div class="vyre-ticker" aria-hidden="true">
        <div class="paxig-container vyre-ticker-row">
            <span class="vyre-ticker-col vyre-ticker-col--left"><?php echo esc_html__( "PAXG'Shop / WORLDWIDE", 'paxig-shoes' ); ?></span>
            <span class="vyre-ticker-col vyre-ticker-col--center"><?php echo esc_html__( 'BETTER MATERIALS. BOLDER IDEAS.', 'paxig-shoes' ); ?></span>
            <a href="<?php echo esc_url($shop_url); ?>" class="vyre-ticker-col vyre-ticker-col--right">
                <span><?php echo esc_html__( 'THE NEXT MOVE STARTS HERE', 'paxig-shoes' ); ?></span>
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>

    <!-- CURATED / 01 - NEW ARRIVALS SECTION -->
    <section class="vyre-curated paxig-container" id="new-arrivals" aria-labelledby="new-arrivals-title">
        <div class="vyre-section-head">
            <div class="vyre-section-title-wrap">
                <span class="vyre-section-eyebrow"><?php echo esc_html__( 'CURATED / 01', 'paxig-shoes' ); ?></span>
                <h2 id="new-arrivals-title" class="vyre-section-title"><?php echo esc_html__( 'NEW ARRIVALS.', 'paxig-shoes' ); ?></h2>
            </div>
            <a href="<?php echo esc_url($shop_url); ?>" class="vyre-section-link">
                <?php echo esc_html__( 'VIEW ALL', 'paxig-shoes' ); ?> <span aria-hidden="true">→</span>
            </a>
        </div>

        <ul class="vyre-products-grid">
            <?php
            $badges = ['', $is_vi ? 'BÁN CHẠY' : 'BESTSELLER', '', ''];
            $subtitles = $is_vi
                ? ['Cloud · Hàng ngày', 'Sandstone · Hàng ngày', 'Olive · Thể thao', 'Onyx · Hàng ngày']
                : ['Cloud · Everyday', 'Sandstone · Everyday', 'Olive · Performance', 'Onyx · Everyday'];
            $swatch_palettes = [
                ['#d9d6d0', '#b39b7d', '#4b5320', '#151514'],
                ['#d2c6b4', '#9e8c78', '#4b5320', '#151514'],
                ['#808674', '#4b5320', '#d2c6b4', '#151514'],
                ['#151514', '#3d3d3d', '#9e8c78', '#d2c6b4'],
            ];

            if (!empty($new_products)) :
                foreach (array_values($new_products) as $i => $item) :
                    $id = $item->get_id();
                    $name = $item->get_name();
                    $permalink = $item->get_permalink();
                    $price_html = $item->get_price_html();
                    $badge = $badges[$i % 4];
                    $sub = $subtitles[$i % 4];
                    $swatches = $swatch_palettes[$i % 4];
            ?>
                    <li class="vyre-product-card" data-product-id="<?php echo esc_attr($id); ?>">
                        <div class="vyre-card-media">
                            <?php if ($badge) : ?>
                                <span class="vyre-card-badge"><?php echo esc_html($badge); ?></span>
                            <?php endif; ?>
                            <button type="button" class="vyre-card-wishlist paxig-wishlist-card-btn" data-product-id="<?php echo esc_attr($id); ?>" aria-label="<?php echo esc_attr__( 'Add to wishlist', 'paxig-shoes' ); ?>">
                                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path d="M20.8 8.7c0 5.2-8.8 11-8.8 11s-8.8-5.8-8.8-11A4.7 4.7 0 0 1 12 6.1a4.7 4.7 0 0 1 8.8 2.6Z"/>
                                </svg>
                            </button>
                            <a href="<?php echo esc_url($permalink); ?>" class="vyre-card-img-link">
                                <?php echo $item->get_image('woocommerce_thumbnail', ['loading'=>'lazy']); ?>
                            </a>
                        </div>
                        <div class="vyre-card-info">
                            <div class="vyre-card-row">
                                <h3 class="vyre-card-title">
                                    <a href="<?php echo esc_url($permalink); ?>"><?php echo esc_html($name); ?></a>
                                </h3>
                                <div class="vyre-card-swatches" aria-hidden="true">
                                    <?php foreach ($swatches as $s_idx => $hex) : ?>
                                        <span class="vyre-swatch <?php echo $s_idx === 0 ? 'is-active' : ''; ?>" style="background-color: <?php echo esc_attr($hex); ?>;"></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="vyre-card-subtitle"><?php echo esc_html($sub); ?></div>
                            <div class="vyre-card-price price"><?php echo wp_kses_post($price_html); ?></div>
                        </div>
                    </li>
            <?php
                endforeach;
            endif;
            ?>
        </ul>
    </section>

    <!-- WHO WE ARE SECTION MATCHING 01_Homepage_Video_Hero.png -->
    <section class="vyre-brand-story" aria-labelledby="brand-story-title">
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
                <a href="<?php echo esc_url(home_url('/ve-paxig/')); ?>" class="vyre-btn-story">
                    <span><?php echo esc_html__( 'OUR STORY', 'paxig-shoes' ); ?></span>
                    <span aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
