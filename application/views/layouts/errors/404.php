<!DOCTYPE html>
<!--[if lt IE 7]>  <html class="lt-ie7"> <![endif]-->
<!--[if IE 7]>     <html class="lt-ie8"> <![endif]-->
<!--[if IE 8]>     <html class="lt-ie9"> <![endif]-->
<!--[if gt IE 8]><!-->
<html>
<!--<![endif]-->

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>Error 404</title>

  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href='http://fonts.googleapis.com/css?family=Roboto:400,100,300,500,700,900' rel='stylesheet' type='text/css'>

  <!-- Main -->
  <link rel="stylesheet" type="text/css" href="<?php echo CDN; ?>plugins/_con/css/_con.min.css" />

  <!--[if lt IE 9]>
    <script src="<?php echo CDN; ?>plugins/html5shiv/html5shiv.min.js"></script>
  <![endif]-->
</head>

<body>
  <!-- Main Content -->
  <section class="">

    <div id="page-message">
      <h2>404</h2>
      <h3>Page not Found ;(</h3>
    </div>

  </section>
  <!-- /Main Content -->
<?php if (DEBUG): ?>
    <pre><?php print_r($e); ?></pre>
<?php endif; ?>
</body>

</html>