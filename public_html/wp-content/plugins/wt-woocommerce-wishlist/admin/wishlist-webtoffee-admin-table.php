<?php
// If this file is called directly, abort.
if (!defined('WPINC')) {
	die;
}

if (!class_exists('WP_List_Table')) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class WT_Wishlist extends WP_List_Table {

	function __construct() {
		global $status, $page, $wpdb;

		parent::__construct(array(
			'singular' => __('product', 'wt-woocommerce-wishlist'), //singular name of the listed records
			'plural' => __('products', 'wt-woocommerce-wishlist'), //plural name of the listed records
			'ajax' => true        //does this table support ajax?
		));
		if (isset($_GET['action']) && $_GET['action'] == 'delete') {
			$user_id = absint($_GET['product']);
			$table_name = $wpdb->prefix . 'wt_wishlists';
			$wpdb->get_results("DELETE FROM `$table_name` WHERE `$table_name`.`user_id` = $user_id");
			//TODO Notifications
			wp_redirect(wp_get_referer());
		}
	}

	function column_default($item, $column_name) {
		switch ($column_name) {
			case 'user':
			case 'count':
			case 'date':
				return $item[$column_name];
			default:
				return print_r($item, true); //Show the whole array for troubleshooting purposes
		}
	}

	function get_columns() {
		$columns = array(
			'cb' => '<input type="checkbox" />',
			'user' => __('User', 'wt-woocommerce-wishlist'),
			'count' => __('No of Items', 'wt-woocommerce-wishlist'),
			'date' => __('Last Updated', 'wt-woocommerce-wishlist'),
		);

		return $columns;
	}

	function prepare_items() {
            
		global $wpdb;
		$table_name = $wpdb->prefix . 'wt_wishlists';

		$all_users = $wpdb->get_results("SELECT DISTINCT `user_id` FROM `$table_name`", ARRAY_A);

		$i = 0;
		foreach ($all_users as $user) {
                    
			$product[$i]['id'] = $user_id = $user['user_id'];
                        
                        $umeta = get_user_meta($user_id);
                        
			$result = $wpdb->get_results("SELECT count(`product_id`) as `count`, (SELECT date FROM `$table_name` where `user_id` = '$user_id' ORDER BY `$table_name`.`date` DESC LIMIT 1) as `date` FROM `$table_name` where `user_id` = '$user_id'", ARRAY_A);
			$product[$i]['count'] = $result[0]['count'];
			$product[$i]['date'] = isset($result[0]['date']) ? date('F j, Y', strtotime($result[0]['date'])) : '';
			$product[$i]['user'] = '<a href="'. get_edit_user_link($user_id).'">'.$umeta['first_name'][0] . " " . $umeta['last_name'][0].'</a>';
			if (empty($umeta['first_name'][0] || $umeta['last_name'][0])) {
				$product[$i]['user'] = $umeta['nickname'][0];
			}
			$i++;
		}


		if (isset($product)) {
			$data = $product;
			$columns = $this->get_columns();
			$hidden = array();
			$sortable = $this->get_sortable_columns();
			$this->_column_headers = array($columns, $hidden, $sortable);
			usort($data, array(&$this, 'usort_reorder'));
			$this->items = $data;
		}
		$this->process_bulk_action();
	}

	function get_sortable_columns() {
            
		$sortable_columns = array(
			'user' => array('user', false),
			'count' => array('count', false),
			'date' => array('date', false),
		);

		return $sortable_columns;
	}

	function usort_reorder($a, $b) {
		// If no sort, default to title
		$orderby = (!empty($_GET['orderby']) ) ? $_GET['orderby'] : 'date';
		// If no order, default to asc
		$order = (!empty($_GET['date']) ) ? $_GET['date'] : 'asc';
		// Determine sort order
		$result = strcmp($a[$orderby], $b[$orderby]);

		// Send final sort direction to usort
		return ( $order === 'asc' ) ? $result : - $result;
	}

	function column_user($item) {
		$actions = array(
			'delete' => sprintf('<a href="?page=%s&action=%s&product=%s">Delete</a>', $_REQUEST['page'], 'delete', $item['id']),
		);

		return sprintf('%1$s %2$s', $item['user'], $this->row_actions($actions));
	}

	function get_bulk_actions() {
		$actions = array(
			'delete' => 'Delete'
		);

		return $actions;
	}

	public function process_bulk_action() {

		$action = $this->current_action();

		switch ($action) {

			case 'delete':
				if (current_user_can('manage_woocommerce')) {

					global $wpdb;
					$table_name = $wpdb->prefix . 'wt_wishlists';

					if (isset($_POST['product']) && !empty($_POST['product'])) {

                                                $users = isset($_POST['product']) ? array_filter(array_map('intval', $_POST['product'])) : array();

						foreach ($users as $user) {
							$query_wp = "DELETE FROM `$table_name` WHERE `user_id` = '$user'";
							$wpdb->query($query_wp);
						}
					}
					wp_redirect(wp_get_referer());
				}
				break;
			default:
				return;
				break;
		}
	}

	function column_cb($item) {
		return sprintf(
			'<input type="checkbox" name="product[]" value="%s" />', $item['id']
		);
	}

}

$tt = $_REQUEST['page'];
$myListTable = new WT_Wishlist();
echo '<div class="wrap"><form id="posts-filter" method="post"><h2>'.__('Manage Wishlist', 'wt-woocommerce-wishlist').' </h2>';
echo "<input type=\"hidden\" name=\"page\" value=\"$tt\" />";
$myListTable->prepare_items();
$myListTable->display();
echo '</form></div>';