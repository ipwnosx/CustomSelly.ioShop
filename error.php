<?php
require 'vendor/autoload.php';
require 'admin/bootstrap.php';

use \Selly as Selly;
use Tuna\CloudflareMiddleware;
use GuzzleHttp\Cookie\FileCookieJar;

$parsedown = new Parsedown();
$cache = new Gilbitron\Util\SimpleCache();

$client = new \GuzzleHttp\Client(['cookies' => new FileCookieJar('cookies.txt')]);
$client->getConfig('handler')->push(CloudflareMiddleware::create());

$url = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Static Site Data
$data = cockpit('singletons')->getData('siteDetails');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Designed and Customly Developed by Tom Croft - https://tom-croft.com -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>Error</title>

	<meta name="robots" content="noindex" />

	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
	<link href="css/style.css" rel="stylesheet">

	<link hef="https://fonts.googleapis.com/css?family=DM+Sans:400,500,700&display=swap" rel="stylesheet">


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
    <div class="container vh-100 d-flex justify-content-center flex-column">
		<div class="text-white">
			<h1 class="display-2 font-weight-bold mb-0">Error</h1>

			<?php if($_GET['error'] == 'api') { ?>
				<p class="lead">
					Your API key for Shoppy / Selly is incorrect. Please update it in the admin panel.
				</p>
				<a class="btn btn-light mt-2" href="admin">Admin Panel</a>
			<?php }?>
		</div>
    </div>
    <!-- Designed and Customly Developed by Tom Croft - https://tom-croft.com -->
</body>

</html>
