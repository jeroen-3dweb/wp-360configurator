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

    public function searchProducts()
    {
        check_ajax_referer('jsv_save_setting');
        if (!current_user_can('edit_products')) {
            wp_send_json_error(['error' => 'Permission denied'], 403);
        }
        $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
        $api = new DWeb_PS_API();
        $result = $api->searchProducts($search);

        if (isset($result['error'])) {
            wp_send_json_error($result);
        }

        wp_send_json_success($result);
    }

    static function DWeb_PS_woo_product_360_view_callback($post)
    {
        wp_nonce_field(basename(__FILE__), self::FIELD_NONCE);
        $productID = get_post_meta($post->ID, self::FIELD_PRODUCT_ID, true);

        $token = get_option(DWeb_PS_ADMIN_API::TOKEN, '');
        $host  = get_option(DWeb_PS_ADMIN_API::CONFIGURATOR_HOST, DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST);
        if (empty($host)) {
            $host = DWeb_PS_API::DEFAULT_CONFIGURATOR_HOST;
        }
        $hasCredentials = !empty($token) && !empty($host);
        ?>
        <table class="form-table">
            <tr>
                <td>
                    <div>
                        <?php if (!$hasCredentials): ?>
                            <p style="color: #d63638;">
                                <?php esc_html_e('Please configure your API credentials first.', '3dweb-ps'); ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=3dweb-ps-api-settings')); ?>"><?php esc_html_e('Go to API Settings', '3dweb-ps'); ?></a>
                            </p>
                        <?php else: ?>
                            <p><?php esc_html_e('Search and select the product for the model you want to configure.', '3dweb-ps'); ?></p>
                        <?php endif; ?>

                        <div style="position: relative; width: 80%;">
                            <div id="dweb_ps_select_wrapper" style="position:relative; border:1px solid #8c8f94; border-radius:4px; background:#fff; cursor:pointer; <?php echo !$hasCredentials ? 'opacity:0.6; pointer-events:none;' : ''; ?>">
                                <div id="dweb_ps_select_display" style="padding:6px 30px 6px 8px; min-height:20px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                    <?php if ($productID): ?>
                                        <?php echo esc_html($productID); ?>
                                    <?php else: ?>
                                        <span style="color:#999;"><?php esc_html_e('Select a product...', '3dweb-ps'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <span style="position:absolute; right:8px; top:50%; transform:translateY(-50%); pointer-events:none;">&#9662;</span>
                                <input
                                    type="text"
                                    id="dweb_ps_product_search"
                                    placeholder="<?php esc_attr_e('Search...', '3dweb-ps'); ?>"
                                    style="display:none; width:100%; border:none; border-top:1px solid #ccc; padding:6px 8px; outline:none; box-sizing:border-box;"
                                    autocomplete="off"
                                />
                                <div id="dweb_ps_product_dropdown" style="display:none; border-top:1px solid #ccc; max-height:200px; overflow-y:auto;"></div>
                            </div>
                            <input
                                type="hidden"
                                name="<?php echo esc_attr(self::FIELD_PRODUCT_ID); ?>"
                                id="dweb_ps_product_id"
                                value="<?php echo esc_attr($productID); ?>"
                            />
                        </div>

                        <script>
                        jQuery(function($) {
                            var $search = $('#dweb_ps_product_search');
                            var $dropdown = $('#dweb_ps_product_dropdown');
                            var $hidden = $('#dweb_ps_product_id');
                            var searchTimer = null;

                            function renderProducts(products) {
                                $dropdown.empty();
                                if (!products || !Array.isArray(products) || products.length === 0) {
                                    var items = (products && products.data) ? products.data : products;
                                    if (!items || !Array.isArray(items) || items.length === 0) {
                                        $dropdown.html('<div style="padding:8px; color:#999;">No products found</div>').show();
                                        return;
                                    }
                                    products = items;
                                }
                                $.each(products, function(i, product) {
                                    var label = product.name || product.title || product.id;
                                    var sku = product.sku || '';
                                    $dropdown.append(
                                        $('<div>')
                                            .text(label + ' (' + sku + ')')
                                            .css({ padding: '8px', cursor: 'pointer' })
                                            .hover(
                                                function() { $(this).css('background', '#f0f0f0'); },
                                                function() { $(this).css('background', '#fff'); }
                                            )
                                            .on('click', function() {
                                                $hidden.val(sku);
                                                $display.html('<strong>' + $('<span>').text(label + ' (' + sku + ')').html() + '</strong>');
                                                closeDropdown();
                                            })
                                    );
                                });
                                $dropdown.show();
                            }

                            var $wrapper = $('#dweb_ps_select_wrapper');
                            var $display = $('#dweb_ps_select_display');
                            var isOpen = false;

                            function openDropdown() {
                                if (isOpen) return;
                                isOpen = true;
                                $search.show().val('').focus();
                                loadProducts('');
                            }

                            function closeDropdown() {
                                isOpen = false;
                                $search.hide().val('');
                                $dropdown.hide().empty();
                            }

                            function loadProducts(query) {
                                var params = query ? { search: query } : {};
                                window.DWEB_PS_ADMIN.sync('dweb_ps_search_products', params, 'get')
                                    .then(function(response) {
                                        var products = response.data;
                                        if (!products || !Array.isArray(products)) {
                                            products = (products && products.data) ? products.data : [];
                                        }
                                        renderProducts(query ? products : products.slice(0, 5));
                                    })
                                    .catch(function(err) {
                                        $dropdown.html('<div style="padding:8px; color:red;">Error searching products</div>').show();
                                        console.warn(err);
                                    });
                            }

                            $display.on('click', function() {
                                if (isOpen) { closeDropdown(); } else { openDropdown(); }
                            });

                            $search.on('input', function() {
                                clearTimeout(searchTimer);
                                var query = $(this).val();
                                searchTimer = setTimeout(function() {
                                    loadProducts(query);
                                }, 300);
                            });

                            $(document).on('click', function(e) {
                                if (!$(e.target).closest('#dweb_ps_select_wrapper').length) {
                                    closeDropdown();
                                }
                            });
                        });
                        </script>
                    </div>
                </td>
            </tr>
        </table>
        <?php
    }
}
