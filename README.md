```
STORE: An e-commerce toolkit for Wordpress.
      ___ _   _ _  _ _  __
     | __| | | | \| | |/ /
     | _|| |_| | .` | ' <
     |_| _\___/|_|\_|_|\_\
     | || | /_\| | | / __|
     | __ |/ _ \ |_| \__ \
     |_||_/_/ \_\___/|___/

```

### Summary:

__STORE__ is an e-commerce toolkit for WordPress, designed to make the process of building an online store as intuitive as building a WordPress theme. At the highest level the basic idea of STORE is to expose a “WordPressy” API develop with so that building carts and checkout pages feels the same as building anything else in your theme. Functions like `the_price()` and the `the_quantity()` just work.

This plugin is a product of frustration with other WordPress e-commerce solutions. The goal is to keep the codebase slim and efficient, and not to try and solve every little problem a user might have. Core features are supported out of the box; product and product-variation inventory and tracking. Cart and order management, customer accounts and address data as well. There is also a powerful javascript API that allows for flexible front-end development.

To get started, head over to our github page and/or check out the steps for installation.

####Quick Start:

Once the plugin is installed and activated, you’ll want to add a few files to your theme folder. Make a folder within your theme called ‘store’ and within that folder add a file called `store-product.php`.

You can now add some basic template code to `store-product.php`, something like this:

```php
<?php get_header(); ?>

    <div id="content" class="product">
        <?php if (have_posts()) : ?>
        <?php while (have_posts()) : the_post(); ?>

		<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

			<h3><?php the_title(); ?></h3>
			<h4><?php the_price(); ?></h4>

			<div class="entry">
				<?php the_content(); ?>
			</div>

		</div>

        <?php endwhile; ?>
        <?php endif; ?>
    </div>

<?php get_footer(); ?>
```

Now when you add products to the store, `store-product.php` will be automatically used as the template for all product detail pages. Here are some other template file names you can add into the /store directory:

* __store-front-page.php__: Will be used by default for any product archive pages. Most notably at the /product path.
* __store-page-$pagename.php__:  Will be used for any store-page templates. By default there are 6 store pages created on install: ‘cart’, ‘checkout’, ‘my account’, ‘sign in’, ‘sign up’, and ‘thank you’. These pages are not manageable through the STORE ui, but you can add/manage them by navigating to: `www.yoursite.com/wp-admin/edit.php?post_type=store`
* __store-product.php__ or __store-product-$slug.php__:  Will be used on the detail page of any product.
* __store-order.php__ or __store-order-$ID.php__:  Will be used to display an individual order. By default access to any order page is restricted unless the logged in user is the purchasing customer OR unless the proper token was provided as a query string.

Once you have your template files setup you can begin templating using the WordPress loop and the `$post` object as you normally would. The various functions documented throughout this site will help you access data that is not built into wordpress (such as price or SKU.)

Keep in mind you can also use `store_get_cart();` to get the current customer’s active cart at any time. You can also use `store_get_customer();` to access the current customer or check if the user is logged in.
