<div class="wrap">

	<?php if ( isset($_REQUEST['message']) ) : ?>
		<?php if ( $_REQUEST['message'] ) : ?>
			<div id="message" class="updated">
				<p>
					Order successfully updated
				</p>
			</div>
		<?php else : ?>
			<div id="message" class="error">
				<p>
					There was a problem updating the order
				</p>
			</div>
		<?php endif; ?>
	<?php endif; ?>

	<form action="admin-post.php" method="post" id="store-cat-order">
		<?php
			// Get category from request, get full term object
			$term_id = isset( $_REQUEST['category'] ) ? $_REQUEST['category'] : false;
			$term = get_term( $term_id, 'store_category' );

			// Check if there is currently an order set for this category, set into variable
			$current_order = get_term_meta( $term_id, 'store-category-order', true );
			$current_order = !empty( $current_order ) ? implode( ',', $current_order ) : 0;

			// Query for products within this term
		    $args = array(
				'posts_per_page'	=> -1,
				'orderby'			=> 'post__in',
				'meta_key'			=> '',
				'meta_value'		=> '',
				'post_type'			=> 'product',
				'post_parent'		=> 0,
				'post__in'			=> store_get_category_order($term_id),
				'tax_query'			=> array(
					array(
						'taxonomy'		=> 'store_category',
						'terms'			=> $term_id
					),
				)
			);
			$products = get_posts($args); ?>

			<h2>Set Order</h2>
			<p class="description">Category: <?php echo $term->name; ?></p>

			<?php if ( $products ) : ?>

				<ul class="menu ui-sortable" id="order-products">

					<?php foreach ( $products as $product ) : ?>
						<li id="product-<?php echo $product->ID; ?>" data-id="<?php echo $product->ID; ?>" class="<?php foreach ( get_post_class('product', $product->ID) as $class ) echo ' ' . $class; ?>" style="position: relative; top: 0px;">
							<dl class="menu-item-bar">
								<dt class="menu-item-handle">
									<span class="item-title"><span class="menu-item-title"><?php echo get_the_title($product); ?></span></span>
								</dt>
							</dl>
						</li>
					<?php endforeach; ?>

				</ul>

				<input type="hidden" name="action" value="save_order" />
				<input type="hidden" name="category" value="<?php echo $term_id; ?>" />
				<input type="hidden" name="order" value="<?php echo $current_order; ?>" />

			<?php endif; ?>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Save Order">
			</p>
		</form>
</div><!-- END Wrap -->