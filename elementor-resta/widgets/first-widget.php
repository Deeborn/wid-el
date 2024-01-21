<?php
/**
 * @package First Widget
 * @author Apple Rinquest
 * @version 1.0.0
 * 
 */
class Elementor_First_Widget extends \Elementor\Widget_Base
{

    public function get_name()
    {
        return 'firstresta';
    }

    public function get_title()
    {
        return __('First Resta', 'quo-elementor-add-on');
    }

    public function get_icon()
    {
        return 'fa fa-wordpress';
    }

    public function get_categories()
    {
        return ['general'];
    }

    /**
     * You must have at least one control in Elementor panel.
     * Otherwise, Advanced settings won't show any settings.
     */
    protected function _register_controls()
    {

        $this->start_controls_section(
            'content_section',
            [
                'label' => __('Content', 'quo-elementor-add-on'),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'quo-note',
            [
                'label' => __('Note', 'quo-elementor-add-on'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __('You can set the number of columns for showing on each row. Also, you can set the number of rows you want "Load More" button showing each click.', 'quo-elementor-add-on'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->add_control(
            'cols',
            [
                'label' => __('Columns per row', 'quo-elementor-add-on'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '2' => __('2', 'quo-elementor-add-on'),
                    '3' => __('3', 'quo-elementor-add-on'),
                    '4' => __('4', 'quo-elementor-add-on'),
                ],
                'default' => '3',
            ]
        );

        $this->add_control(
            'rows',
            [
                'label' => __('Initial number of rows', 'quo-elementor-add-on'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => __('1', 'quo-elementor-add-on'),
                    '2' => __('2', 'quo-elementor-add-on'),
                    '3' => __('3', 'quo-elementor-add-on'),
                ],
                'default' => '2',
            ]
        );

        $this->add_control(
            'load-rows',
            [
                'label' => __('Number of rows on each loading more', 'quo-elementor-add-on'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '1' => __('1', 'quo-elementor-add-on'),
                    '2' => __('2', 'quo-elementor-add-on'),
                    '3' => __('3', 'quo-elementor-add-on'),
                ],
                'default' => '1',
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        // # get the settings from the Elementor controls which we create at _register_controls()
        $settings = $this->get_settings_for_display();
        $posts_per_page = $settings['cols'] * $settings['rows'];


        // # query data 
        // tax_query
        $tax_query[] = array(
            'taxonomy' => 'product_visibility',
            'field'    => 'name',
            'terms'    => 'featured',  // featured product
            'operator' => 'IN', // or 'NOT IN' to exclude feature products
        );

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'offset' => 0,
            'posts_per_page' => (int)$posts_per_page,
            'orderby' => 'name',
            'order' => 'asc',
            'tax_query' => $tax_query
        );
        $query = new WP_Query($args);

        // # display data in HTML template
        if (!empty($query)) {


            // count columns per row
            $count = 1;
            // set unique number for data attribute
            $unique = 1;

            if ($query->have_posts()) {

                // # all classname will use the same as Elementor classname. 
                // Basically I inspect the classname from Product category widget from Elementor Pro
                // then I use those classname in our products widget
                echo '<div class="woocommerce columns-' . $settings['cols'] . '">';
                echo '<ul id="quo-featured-prod" class="products columns-' . $settings['cols'] . '" data-cols="' . $settings['cols'] . '" data-loadRows="' . $settings['load-rows'] . '" data-rows="' . $settings['rows'] . '">';


                // # product loop
                while ($query->have_posts()) :

                    $query->the_post();

                    // # product list template 
                    // note: $count and $unique are used in this template file
                    // we use the template file to make the code shorter
                    include(plugin_dir_path(__FILE__) . '/../' . 'templates/product-template.php');

                endwhile;
                wp_reset_postdata();

                echo '</ul>';
                echo '</div>';

                // # an hidden field which stores "$query->found_posts" for showing or hiding the load more button after the load more button is triggerred.
                // the found_posts is the amount of found posts from the current query.
                echo '<input type="hidden" id="total_featured_posts" name="total_featured_posts" value="' . $query->found_posts . '">';


                // # load more button
                // don't display the load more button if there is not enough post to show.

                // loading icon: 
                // https://fontawesome.com/icons/spinner?style=solid
                // https://fontawesome.com/how-to-use/on-the-web/styling/animating-icons
                if ($query->found_posts > $query->post_count) {
                    echo '<div class="quo_loadMore_btn_wrapper">';
                    echo '<a id="quo-featured-prod_loadmore" class="quo_loadMore_btn button add_to_cart_button">';
                    echo '<span class="loading">' . __('loading..', 'quo-elementor-add-on') . '</span>';
                    echo '<span class="load_more">' . __('Load More', 'quo-elementor-add-on') . '</span>';
                    echo '</a>';
                    echo '</div>';
                }
            }
        }
    }
}