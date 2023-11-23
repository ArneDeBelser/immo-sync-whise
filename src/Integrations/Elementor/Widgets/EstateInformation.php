<?php

namespace ADB\ImmoSyncWhise\Integrations\Elementor\Widgets;

use ADB\ImmoSyncWhise\Matchers\EstateMatcher;
use ADB\ImmoSyncWhise\Model\Estate;
use Illuminate\Container\Container;

class EstateInformation extends \Elementor\Widget_Base
{
    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);

        add_action('wp_ajax_fetch_estate_information', [$this, 'fetchEstateInformation']);
        add_action('wp_ajax_nopriv_fetch_estate_information', [$this, 'fetchEstateInformation']);
    }

    public function get_name()
    {
        return 'estate-information-widget';
    }

    public function get_title()
    {
        return __('Estate Information', ' immo-sync-whise');
    }

    public function get_icon()
    {
        return 'eicon-wordpress';
    }

    public function get_categories()
    {
        return ['immo-sync-whise'];
    }

    protected function render()
    {
        if (is_singular('estate')) {
            global $post;

            $estate = Container::getInstance()->make(Estate::class);
            $matcher = Container::getInstance()->make(EstateMatcher::class);
            $fields = $estate->getMetaFields($post->ID);

            if (!empty($fields)) {
                echo '<div class="information_wrapper">';
                foreach ($fields as $key => $field) {
                    $showField = get_post_meta($post->ID, '_show' . $key, true);
                    if ($showField == 1) {
                        echo '<div class="information_group">';
                        echo '<p><strong><span>' . esc_html($matcher->getTitle($key)) . ':</span></strong> <span id="' . esc_attr($key) . '">' . esc_html($matcher->getField($key, $field)) . '</span></p>';
                        echo '</div>';
                    }
                }
                echo '</div>';
            }
        }
    }

    protected function content_template()
    {
?>
        <div class="information_wrapper">
            <p>Estate information will be shown here.</p>
        </div>

        <script>
            (function($) {
                $(document).ready(function() {
                    const urlParams = new URLSearchParams(window.location.search);
                    const elementorPreviewID = urlParams.get('elementor-preview');
                    console.log(elementorPreviewID);

                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            action: 'fetch_estate_information',
                            security: '<?php echo wp_create_nonce('fetch_estate_information_nonce'); ?>',
                            elementorPreviewID: elementorPreviewID
                        },
                        success: function(response) {
                            if (response.success) {
                                $('.information_wrapper').html(response.data.html);
                                console.error('Success:', response.data);
                            } else {
                                console.error('Error:', response.data);
                            }
                        },
                        error: function(errorThrown) {
                            console.error('AJAX Error:', errorThrown);
                        }
                    });
                });
            })(jQuery);
        </script>
<?php
    }

    /**
     * This function is in an unhappy place for now. Normally this would live in the widget. But the widget constructor won't allow to me hook into wp_ajax
     * 
     * This function returns the HTML for the EstateInformation widget content_template function
     *
     * @return void
     */
    public function fetchEstateInformation()
    {
        check_ajax_referer('fetch_estate_information_nonce', 'security');

        $estate = new Estate();
        $matcher = new EstateMatcher();

        $elementorWidgetFields = get_post_meta($_POST['elementorPreviewID']);
        $previewObject = unserialize($elementorWidgetFields['_elementor_page_settings'][0]);
        $postPreviewID = $previewObject['preview_id'];

        $fields = $estate->getMetaFields($postPreviewID);

        ob_start();

        if (!empty($fields)) {
            echo '<div class="information_wrapper">';
            foreach ($fields as $key => $field) {
                $showField = get_post_meta($postPreviewID, '_show' . $key, true);
                if ($showField == 1) {
                    echo '<div class="information_group">';
                    echo '<p><strong><span>' . esc_html($matcher->getTitle($key)) . ':</span></strong> <span id="' . esc_attr($key) . '">' . esc_html($matcher->getField($key, $field[0])) . '</span></p>';
                    echo '</div>';
                }
            }
            echo '</div>';
        } else {
            echo '<div class="information_group">';
            echo '<p>Estate information will be shown here.</p>';
            echo '</div>';
        }

        $html = ob_get_clean();

        wp_send_json_success(array('html' => $html));
        wp_die();
    }
}
