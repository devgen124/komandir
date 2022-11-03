
<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>
<div class="woocommerce" style="margin: 10px 20px 0 2px;">
	<div class="wt-wishlist-webtoffee-wrapper" id="icon-wishlist-webtoffee"><br></div>
    <h2 class="nav-tab-wrapper wt-nav-tab-wrapper">
        <a href="<?php echo admin_url('admin.php?page=wishlist-webtoffee') ?>" class="nav-tab wt-nav-tab <?php echo ($tab == 'general_settings') ? 'wt-nav-tab-active' : ''; ?>"><?php _e('General settings', 'wt-woocommerce-wishlist'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wishlist-webtoffee&tab=wishlist_page') ?>" class="nav-tab wt-nav-tab <?php echo ($tab == 'wishlist_page') ? 'wt-nav-tab-active' : ''; ?>"><?php _e('Wishlist page', 'wt-woocommerce-wishlist'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=wishlist-webtoffee&tab=button_style') ?>" class="nav-tab wt-nav-tab <?php echo ($tab == 'button_style') ? 'wt-nav-tab-active' : ''; ?>"><?php _e("'Add to wishlist' options", 'wt-woocommerce-wishlist'); ?></a>
		<a href="<?php echo admin_url('admin.php?page=wishlist-webtoffee&tab=popular_products') ?>" class="nav-tab wt-nav-tab <?php echo ($tab == 'popular_products') ? 'wt-nav-tab-active' : ''; ?>"><?php _e("Popular products", 'wt-woocommerce-wishlist'); ?></a>
    </h2>

	<?php
		switch ($tab) {
			case "general_settings" :
				$this->admin_general_settings_page();
			break;
                case "wishlist_page" :
				$this->admin_wishlist_table_settings_page();
			break;
                case "button_style" :
				$this->admin_wishlist_button_style_settings_page();
			break;
			    case "popular_products" :
				$this->admin_wishlist_popular_products_page();
			break;
			default :
				$this->admin_general_settings_page();
			break;
		}
	?>
</div>