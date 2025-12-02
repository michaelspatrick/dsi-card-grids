<?php
/**
 * Plugin Name: DSI Card Grids & Sliders
 * Description: Card-based product and post grids/sliders for consistent visual layouts. WooCommerce products + blog posts.
 * Author: Michael Patrick
 * Version: 1.0.2
 * Text Domain: dsi-card-grids
 * Requires Woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class DSI_Card_Grids {

    public function __construct() {
        add_action( 'init', array( $this, 'register_shortcodes' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
    }

    public function register_assets() {
        wp_register_style(
            'dsi-card-grids',
            plugins_url( 'assets/css/dsi-card-grids.css', __FILE__ ),
            array(),
            '1.0.1'
        );

        wp_register_script(
            'dsi-card-grids',
            plugins_url( 'assets/js/dsi-card-grids.js', __FILE__ ),
            array(),
            '1.0.1',
            true
        );
    }

    public function register_shortcodes() {
        add_shortcode( 'dsi_products', array( $this, 'shortcode_products' ) );
        add_shortcode( 'dsi_posts', array( $this, 'shortcode_posts' ) );
    }

    /**
     * Enqueue assets only when needed.
     */
    protected function enqueue_assets() {
        wp_enqueue_style( 'dsi-card-grids' );
        wp_enqueue_script( 'dsi-card-grids' );
    }

    /**
     * [dsi_products] shortcode
     */
    public function shortcode_products( $atts ) {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return '<!-- DSI Card Grids: WooCommerce not active -->';
        }

        $atts = shortcode_atts(
            array(
                'ids'        => '',
                'category'   => '',
                'tag'        => '',
                'limit'      => 12,
                'orderby'    => 'date',
                'order'      => 'DESC',
                'on_sale'    => 'false',
                'featured'   => 'false',
                'slider'     => 'false',
                'items'      => 3,
                'interval'   => 5000,
                'button'     => 'view',
                'class'      => '',
            ),
            $atts,
            'dsi_products'
        );

        $this->enqueue_assets();

        $query_args = array(
            'limit'   => intval( $atts['limit'] ),
            'orderby' => sanitize_text_field( $atts['orderby'] ),
            'order'   => sanitize_text_field( $atts['order'] ),
            'return'  => 'ids',
            'status'  => array( 'publish' ),
        );

        if ( ! empty( $atts['ids'] ) ) {
            $ids = array_map( 'absint', explode( ',', $atts['ids'] ) );
            $query_args['include'] = $ids;
        }

        if ( ! empty( $atts['category'] ) ) {
            $query_args['category'] = array_map( 'sanitize_title', explode( ',', $atts['category'] ) );
        }

        if ( ! empty( $atts['tag'] ) ) {
            $query_args['tag'] = array_map( 'sanitize_title', explode( ',', $atts['tag'] ) );
        }

        if ( 'true' === strtolower( $atts['on_sale'] ) ) {
            $query_args['on_sale'] = true;
        }

        if ( 'true' === strtolower( $atts['featured'] ) ) {
            $query_args['featured'] = true;
        }

        $products = wc_get_products( $query_args );

        if ( empty( $products ) ) {
            return '<div class="dsi-cards dsi-cards-empty">' . esc_html__( 'No products found.', 'dsi-card-grids' ) . '</div>';
        }

        $is_slider   = ( 'true' === strtolower( $atts['slider'] ) );
        $items       = max( 1, intval( $atts['items'] ) );
        $interval    = max( 1000, intval( $atts['interval'] ) );
        $button_type = ( 'add_to_cart' === $atts['button'] ) ? 'add_to_cart' : 'view';

        $outer_classes   = array( 'dsi-cards', 'dsi-products-cards' );
        if ( $is_slider ) {
            $outer_classes[] = 'dsi-card-slider';
        }
        if ( ! empty( $atts['class'] ) ) {
            $outer_classes[] = sanitize_html_class( $atts['class'] );
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $outer_classes ) ); ?>"
             <?php if ( $is_slider ) : ?>
                data-dsi-slider="true"
                data-dsi-items="<?php echo esc_attr( $items ); ?>"
                data-dsi-interval="<?php echo esc_attr( $interval ); ?>"
             <?php endif; ?>
        >
            <?php if ( $is_slider ) : ?>
                <div class="dsi-card-slider-track">
            <?php endif; ?>

            <?php foreach ( $products as $product_id ) :
                $product = wc_get_product( $product_id );
                if ( ! $product ) {
                    continue;
                }

                $link   = get_permalink( $product_id );
                $title  = $product->get_name();
                $short  = apply_filters( 'woocommerce_short_description', $product->get_short_description() );
                $price  = $product->get_price_html();
                $img    = get_the_post_thumbnail( $product_id, 'woocommerce_thumbnail', array( 'class' => 'dsi-card-image' ) );
                if ( ! $img ) {
                    $img = wc_placeholder_img( 'woocommerce_thumbnail', array( 'class' => 'dsi-card-image dsi-card-image-placeholder' ) );
                }

                $badges = array();
                if ( $product->is_on_sale() ) {
                    $badges[] = esc_html__( 'Sale', 'dsi-card-grids' );
                }
                if ( $product->get_featured() ) {
                    $badges[] = esc_html__( 'Featured', 'dsi-card-grids' );
                }

                $button_url  = $link;
                $button_text = esc_html__( 'View Product', 'dsi-card-grids' );

                if ( 'add_to_cart' === $button_type ) {
                    $button_url  = esc_url( $product->add_to_cart_url() );
                    $button_text = esc_html( $product->add_to_cart_text() );
                }
                ?>
                <article <?php post_class( 'dsi-card dsi-card-product', $product_id ); ?>>
                    <div class="dsi-card-media">
                        <a href="<?php echo esc_url( $link ); ?>">
                            <?php echo $img; ?>
                        </a>
                        <?php if ( ! empty( $badges ) ) : ?>
                            <div class="dsi-card-badges">
                                <?php foreach ( $badges as $badge ) : ?>
                                    <span class="dsi-card-badge badge"><?php echo esc_html( $badge ); ?></span>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dsi-card-body entry-summary">
                        <h3 class="dsi-card-title entry-title">
                            <a href="<?php echo esc_url( $link ); ?>">
                                <?php echo esc_html( $title ); ?>
                            </a>
                        </h3>
                        <?php if ( ! empty( $short ) ) : ?>
                            <div class="dsi-card-excerpt">
                                <?php echo wp_kses_post( wp_trim_words( $short, 25 ) ); ?>
                            </div>
                        <?php endif; ?>
                        <div class="dsi-card-meta dsi-card-meta-product price">
                            <?php if ( $price ) : ?>
                                <span class="dsi-card-price"><?php echo wp_kses_post( $price ); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="dsi-card-footer">
                        <a class="dsi-card-button button" href="<?php echo esc_url( $button_url ); ?>">
                            <?php echo esc_html( $button_text ); ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>

            <?php if ( $is_slider ) : ?>
                </div><!-- .dsi-card-slider-track -->
                <button type="button" class="dsi-card-slider-nav dsi-card-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'dsi-card-grids' ); ?>">&lsaquo;</button>
                <button type="button" class="dsi-card-slider-nav dsi-card-slider-next" aria-label="<?php esc_attr_e( 'Next', 'dsi-card-grids' ); ?>">&rsaquo;</button>
            <?php endif; ?>
        </section>
        <?php

        return ob_get_clean();
    }

    /**
     * [dsi_posts] shortcode
     */
    public function shortcode_posts( $atts ) {
        $atts = shortcode_atts(
            array(
                'post_type' => 'post',
                'category'  => '',
                'tag'       => '',
                'ids'       => '',
                'exclude'   => '',
                'limit'     => 6,
                'orderby'   => 'date',
                'order'     => 'DESC',
                'slider'    => 'false',
                'items'     => 3,
                'interval'  => 5000,
                'class'     => '',
            ),
            $atts,
            'dsi_posts'
        );

        $this->enqueue_assets();

        $query_args = array(
            'post_type'           => sanitize_key( $atts['post_type'] ),
            'posts_per_page'      => intval( $atts['limit'] ),
            'orderby'             => sanitize_text_field( $atts['orderby'] ),
            'order'               => sanitize_text_field( $atts['order'] ),
            'ignore_sticky_posts' => true,
        );

        if ( ! empty( $atts['ids'] ) ) {
            $query_args['post__in'] = array_map( 'absint', explode( ',', $atts['ids'] ) );
        }

        if ( ! empty( $atts['exclude'] ) ) {
            $query_args['post__not_in'] = array_map( 'absint', explode( ',', $atts['exclude'] ) );
        }

        if ( ! empty( $atts['category'] ) && 'post' === $atts['post_type'] ) {
            $query_args['category_name'] = implode( ',', array_map( 'sanitize_title', explode( ',', $atts['category'] ) ) );
        }

        if ( ! empty( $atts['tag'] ) && 'post' === $atts['post_type'] ) {
            $query_args['tag'] = implode( ',', array_map( 'sanitize_title', explode( ',', $atts['tag'] ) ) );
        }

        $q = new WP_Query( $query_args );

        if ( ! $q->have_posts() ) {
            return '<div class="dsi-cards dsi-cards-empty">' . esc_html__( 'No posts found.', 'dsi-card-grids' ) . '</div>';
        }

        $is_slider = ( 'true' === strtolower( $atts['slider'] ) );
        $items     = max( 1, intval( $atts['items'] ) );
        $interval  = max( 1000, intval( $atts['interval'] ) );

        $outer_classes = array( 'dsi-cards', 'dsi-posts-cards' );
        if ( $is_slider ) {
            $outer_classes[] = 'dsi-card-slider';
        }
        if ( ! empty( $atts['class'] ) ) {
            $outer_classes[] = sanitize_html_class( $atts['class'] );
        }

        ob_start();
        ?>
        <section class="<?php echo esc_attr( implode( ' ', $outer_classes ) ); ?>"
             <?php if ( $is_slider ) : ?>
                data-dsi-slider="true"
                data-dsi-items="<?php echo esc_attr( $items ); ?>"
                data-dsi-interval="<?php echo esc_attr( $interval ); ?>"
             <?php endif; ?>
        >
            <?php if ( $is_slider ) : ?>
                <div class="dsi-card-slider-track">
            <?php endif; ?>

            <?php
            while ( $q->have_posts() ) :
                $q->the_post();
                $post_id = get_the_ID();
                $link    = get_permalink();
                $title   = get_the_title();
                $excerpt = get_the_excerpt();
                $date    = get_the_date();
                $thumb   = get_the_post_thumbnail( $post_id, 'medium', array( 'class' => 'dsi-card-image' ) );

                if ( ! $thumb ) {
                    $thumb = '<div class="dsi-card-image dsi-card-image-placeholder"></div>';
                }
                ?>
                <article <?php post_class( 'dsi-card dsi-card-post', $post_id ); ?>>
                    <div class="dsi-card-media">
                        <a href="<?php echo esc_url( $link ); ?>">
                            <?php echo $thumb; ?>
                        </a>
                    </div>
                    <div class="dsi-card-body entry-summary">
                        <h3 class="dsi-card-title entry-title">
                            <a href="<?php echo esc_url( $link ); ?>">
                                <?php echo esc_html( $title ); ?>
                            </a>
                        </h3>
                        <div class="dsi-card-meta dsi-card-meta-post">
                            <span class="dsi-card-date"><?php echo esc_html( $date ); ?></span>
                        </div>
                        <?php if ( ! empty( $excerpt ) ) : ?>
                            <div class="dsi-card-excerpt">
                                <?php echo esc_html( wp_trim_words( $excerpt, 30 ) ); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="dsi-card-footer">
                        <a class="dsi-card-button button" href="<?php echo esc_url( $link ); ?>">
                            <?php esc_html_e( 'Read More', 'dsi-card-grids' ); ?>
                        </a>
                    </div>
                </article>
            <?php endwhile; wp_reset_postdata(); ?>

            <?php if ( $is_slider ) : ?>
                </div><!-- .dsi-card-slider-track -->
                <button type="button" class="dsi-card-slider-nav dsi-card-slider-prev" aria-label="<?php esc_attr_e( 'Previous', 'dsi-card-grids' ); ?>">&lsaquo;</button>
                <button type="button" class="dsi-card-slider-nav dsi-card-slider-next" aria-label="<?php esc_attr_e( 'Next', 'dsi-card-grids' ); ?>">&rsaquo;</button>
            <?php endif; ?>
        </section>
        <?php

        return ob_get_clean();
    }
}

new DSI_Card_Grids();
