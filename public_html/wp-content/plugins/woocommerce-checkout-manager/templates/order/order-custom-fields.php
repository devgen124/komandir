<?php
use QuadLayers\WOOCCM\Plugin as Plugin;
$title = get_option( 'wooccm_order_custom_fields_title', esc_html__( 'Order extra', 'woocommerce-checkout-manager' ) );

?>

<h2 class="woocommerce-order-details__title">
	<?php esc_html_e( $title ); ?>
</h2>
<table class="woocommerce-table shop_table order_details">
	<tbody>
		<?php
		$checkout = WC()->checkout->get_checkout_fields();
		if ( count( $checkout ) ) :
			foreach ( $checkout as $field_type => $fields ) :
				if ( isset( Plugin::instance()->$field_type ) ) :
					$defaults = array_column( Plugin::instance()->$field_type->get_defaults(), 'key' );
					foreach ( $fields as $key => $field ) :
						?>
						<?php if ( ! in_array( $key, $defaults ) && empty( $field['hide_order'] ) ) : ?>
							<?php
							$value = get_post_meta( $order_id, sprintf( '_%s', $key ), true );
							if ( $value ) :
								?>
								<tr id="tr-<?php echo esc_attr( $key ); ?>">
									<th><?php echo esc_html( $field['label'] ); ?></th>
									<td><?php echo esc_html( $value ); ?></td>
								</tr>
							<?php endif; ?>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</tbody>
</table>
