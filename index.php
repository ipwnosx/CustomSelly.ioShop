<?php
require 'vendor/autoload.php';
require 'admin/bootstrap.php';

use \Selly as Selly;
use Tuna\CloudflareMiddleware;

$parsedown = new Parsedown();
$cache = new Gilbitron\Util\SimpleCache();

$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Static Site Data
$data = cockpit('singletons')->getData('siteDetails');

if($data['playformApiKey'] == 'example_api_key') {
    header("Location: error.php?error=api");
}

if($products = $cache->get_cache('products')){
	$platform = $cache->get_cache('platform');
	$products = json_decode($products);
} else {
	if($data['platform'] == "Shoppy.GG") {
		// Shoppy
		try {
			\Shoppy\Shoppy::setApiKey($data['playformApiKey']);
			$productsData = \Shoppy\Models\Product::all()->apiResponse->headers;
			$pages = $productsData['x-total-pages'];

			$products = [];

			for ($i=1; $i <= $pages; $i++) {
				$temp = \Shoppy\Models\Product::all($i)->apiResponse->json;

				foreach($temp as $p) {
					$products[] = $p;
				}
			}

			$platform = "shoppy";
		} catch(Exception $e) {
			header("Location: error.php?error=api");
		}
	} else if($data['platform'] == 'Selly.GG' ){
		// Selly
		try {
			$auth = Selly\Client::authenticate($data['platformEmail'], $data['playformApiKey']);
			
			$products = new \Selly\Products;

			$products = $products->list();
			$products = (array) $products;
			$platform = "selly";
		} catch(Exception $e) {
			header("Location: error.php?error=api");

			die();
		}

	} else if($data['platform'] == 'Sellix.IO') {
	    try {
    		$sellix = new \Sellix\sellix($data['playformApiKey']);
    		$products = $sellix->getProducts();
    		$products = json_decode($products)->data->products;
    
    		$platform = "sellix";
	    } catch(Exception $e) {
	        header("Location: error.php?error=api");

			die();
	    }
	}

	if(!file_exists('cache')) {
		mkdir('cache', 0770, true);
	}

	if(!file_exists('cache/products.cache')) {
		$productsCache = fopen('cache/products.cache', 'w');
		$platformCache = fopen('cache/platform.cache', 'w');

		chmod('cache/products.cache', 0770);
		chmod('cache/platform.cache', 0770);
	}

	try {
		$cache->set_cache('platform', $platform);
		$cache->set_cache('products', json_encode($products));
	} catch(Exception $e) {
		die($e . "<br> It's likely you deleted the cache folder or the htaccess within it.");
	}
}

if(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
	$url = "https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
} else {
	$url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Designed and Customly Developed by Tom Croft - https://tom-croft.com -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?=$data['title']?> | Home</title>

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
    <link href="css/style.css" rel="stylesheet">

	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.7.2/animate.min.css">
	<link href="https://fonts.googleapis.com/css?family=DM+Sans:400,500,700&display=swap" rel="stylesheet">

	<!-- Primary Meta Tags -->
	<title><?=$data['seoTitle'] ?? $data['title']?></title>
	<meta name="title" content="<?=$data['seoTitle'] ?? $data['title']?>">
	<meta name="description" content="<?=$data['seoDesc'] ?? $data['tagline']?>">

	<!-- Open Graph / Facebook -->
	<meta property="og:type" content="website">
	<meta property="og:url" content="<?=$url?>">
	<meta property="og:title" content="<?=$data['seoTitle'] ?? $data['title']?>">
	<meta property="og:description" content="<?=$data['seoDesc'] ?? $data['tagline']?>">
	<?php if($data['siteImage'] == null) { ?>
		<meta property="og:image" content="<?=$url?><?=$data['siteImage']['path']?>">
	<?php } ?>

	<!-- Twitter -->
	<meta property="twitter:card" content="summary">
	<meta property="twitter:url" content="<?=$url?>">
	<meta property="twitter:title" content="<?=$data['seoTitle'] ?? $data['title']?>">
	<meta property="twitter:description" content="<?=$data['seoDesc'] ?? $data['tagline']?>">
	<?php if($data['siteImage'] == null) { ?>
		<meta property="twitter:image" content="<?=$url?><?=$data['siteImage']['path']?>">
	<?php } ?>

	<style>
		<?php if($data['backgroundImage'] !== null && !empty($data['backgroundImage'])) { ?>
			body {
				background-image: url('<?=$url . $data['backgroundImage']['path']?>');
				background-size: cover;
				background-repeat: no-repeat;
				background-attachment: fixed;
			}
		<?php } else { ?>
			body {
				<?=$data['bodyStyle'] ?? "" ?>
			}
		<?php } ?>

		.card-img-top {
			background-color: <?=$data['themeColour']?>
		}
	</style>
</head>

<body >
    <!-- Header -->
    <div class="header text-white wow zoomInDown" data-wow-delay="1s">
        <div class="flex-center flex-column">
			<?php if($data['siteImage'] == null) { ?>
            	<h1 class="animated fadeInUp mb-0 display-2 font-weight-bold text-center"><?=$data['title']?></h1>
			<?php } else { ?>
				<img src="<?=$url?><?=$data['siteImage']['path'] ?? ""?>" alt="">
			<?php } ?>

            <h5 class="animated fadeInUp delay-200 mb-4 leads text-center"><?=$data['tagline'] ?? ""?></h5>

            <div>
                <a class="btn btn-light btn-center animated fadeInUp delay-500" href="#products">Prices</a>
				<?php if(isset($data['socialName'])) { ?>
                	<a class="btn btn-white btn-lg btn-center animated fadeInUp delay-500" href="<?=$data['socialLink'] ?? ""?>" target="_blank"><?=$data['socialName'] ?? ""?></a>
				<?php } ?>
            </div>
        </div>
    </div>

    <div class="container content animated fadeInUp delay-700 pb-5">
        <div class="row row-eq-height products justify-content-center wow fadeInUp" data-wow-delay="1s" id="products">
            <?php
                foreach($products as $product) {
            ?>
            <div class="col-xs-12 col-sm-6 col-md-4">
                <div class="card hoverable">
					<?php if($platform !== 'sellix') { ?>
						<?php if($product->image !== null) { ?>
							<img class="card-img-top" src="<?=$product->image->url?>" alt="Card image cap">
						<?php } else { ?>
							<div class="card-img-top py-3">
								<h1 class="h4 mb-0 text-center text-white p-4"><?=$product->title?></h1>
							</div>
						<?php } ?>
					<?php } else { ?>
						<?php if($product->image_name !== null) { ?>
							<img class="card-img-top" src="https://cdn.sellix.io/static/images/products/<?=$product->image_name?>" alt="Card image cap">
						<?php } else { ?>
							<div class="card-img-top py-3">
								<h1 class="h4 mb-0 text-center text-white p-4"><?=$product->title?></h1>
							</div>
						<?php } ?>
					<?php } ?>
                    <div class="card-body text-center">
                        <p class="card-text"><?=$parsedown->line($product->description) ?? ""?></p>
                        <div class="bottom">
                            <div class="d-block w-100 px-3" style="margin-bottom: 25px;">
                                <h6 class="float-left card-text price text-muted">$<?=number_format($product->price, 2)?></h6>
								<?php if($platform == "shoppy") { ?>
                                	<?php if($product->stock == 0) { ?>
										<h6 class="float-right card-text price text-muted">Out of Stock</h6>
									<?php } else { ?>
										<h6 class="float-right card-text price text-muted">In Stock</h6>
									<?php }?>
								<?php } else { ?>
									<h6 class="float-right card-text price text-muted"><?=$product->stock?> In Stock</h6>
								<?php } ?>
                            </div>
								<button type="button" class="btn btn-dark btn-block mt-3 " <?php if($platform == "selly") { ?>data-selly-product="<?=$product->id?>" <?php } else if($platform == "shoppy") { ?>data-shoppy-product="<?=$product->id?>"<?php } else if($platform == "sellix") { ?> data-sellix-product="<?=$product->uniqid?>" <?php } ?> style="background-color: <?=$data['themeColour'] ?? '#fff'?>">Buy Now</button>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script type="text/javascript" src="js/particles.min.js"></script>
    <script type="text/javascript" src="js/app.js"></script>

	<?php if($platform == "selly") { ?>
    	<script src="https://embed.selly.gg/"></script>
	<?php } else if($platform == 'shoppy') { ?>
		<script src="https://shoppy.gg/api/embed.js"></script>
	<?php } else if($platform == 'sellix') { ?>
		<script src="https://cdn.sellix.io/static/js/embed.js" ></script>
	<?php } ?>

    <script src="https://joaopereirawd.github.io/fakeLoader.js/demo/js/fakeLoader.min.js"></script>
    <script src="https://cferdinandi.github.io/smooth-scroll/dist/smooth-scroll.polyfills.min.js"></script>
	<script>
		console.log('Hash: 765e0b64268331ab449d7d2b66affe4ce4a57ef7');
		console.log('Timestamp: 1611760796');
		console.log('Username: ipwnosx');
	</script>

    <script>
        var scroll = new SmoothScroll('a[href*="#"]:not([data-easing])');
        var linear = new SmoothScroll('[data-easing="linear"]', {easing: 'linear'});
    </script>

    <!-- Designed and Customly Developed by Tom Croft - https://tom-croft.com -->
</body>

</html>
