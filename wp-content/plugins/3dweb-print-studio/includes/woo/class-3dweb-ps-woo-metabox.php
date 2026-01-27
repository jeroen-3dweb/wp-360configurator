<?php

class DWeb_PS_WOO_METABOX
{

    const FIELD_PRODUCT_ID = 'DWeb_PS_woo_product_id_';

    const FIELD_NONCE = 'DWeb_PS_woo_nonce_';

    /**
     * @var string $pluginName
     */
    private $pluginName;

    /**
     * @var string $version
     */
    private $version;

    /**
     * JSV_Parser constructor.
     * @param $pluginName
     * @param $version
     */
    public function __construct($pluginName, $version)
    {
        $this->pluginName = $pluginName;
        $this->version    = $version;
    }


    public function addBoxes($post_type)
    {
        if (in_array($post_type, ['product'])) {
            add_meta_box(
                '3dweb-ps-woo-product-sku',
                'Product ID in Configurator',
                array(
                    self::class,
                    'DWeb_PS_woo_product_360_view_callback'
                ),
                $post_type,
                'normal',
                'core'
            );
        }
    }

    public function saveBoxes($post_id)
    {
        if (!isset($_POST[self::FIELD_NONCE]) || (empty($_POST[self::FIELD_NONCE]) || !wp_verify_nonce(
                    $_POST[self::FIELD_NONCE],
                    basename(__FILE__)
                ))) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (isset($_POST[self::FIELD_PRODUCT_ID])) {
            update_post_meta($post_id, self::FIELD_PRODUCT_ID, sanitize_text_field($_POST[self::FIELD_PRODUCT_ID]));
        } else {
            delete_post_meta($post_id, self::FIELD_PRODUCT_ID);
        }
    }

    static function DWeb_PS_woo_product_360_view_callback($post)
    {
        wp_nonce_field(basename(__FILE__), self::FIELD_NONCE);
        $productID = get_post_meta($post->ID, self::FIELD_PRODUCT_ID, true);
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <div>
                        <p><?php esc_html_e('Enter the product ID here for the model you want to configure.', '3dweb-ps'); ?></p>

                        <input placeholder="" value="<?php echo esc_attr($productID); ?>" style="width: 80%"  type="text" name="<?php echo esc_attr(self::FIELD_PRODUCT_ID); ?>" />
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }
}