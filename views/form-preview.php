<?php
defined( 'ABSPATH' ) or exit;

// render simple page with form in it.
?><!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <link type="text/css" rel="stylesheet" href="<?php bloginfo( 'stylesheet_url' ); ?>" />
	<?php 
	wp_head(); ?>

    <style type="text/css">
        body{ 
            background: white;
            width: 100%;
	        max-width: 100%;
	        text-align: left;
         }

        /* hide all other elements */
        body::before,
        body::after,
        body > *:not(#form-preview) { 
            display:none !important; 
        }

        #form-preview {
	        display: block !important;
	        width: 100%;
	        height: 100%;
	        padding: 20px;
            border: 0;
        }
    </style>
</head>
<body <?php body_class(); ?>>
    <div id="form-preview" <?php post_class('post-content'); ?>>
    	<?php echo $form; ?>
    </div>
	<?php wp_footer(); ?>
</body>
</html>
