<?php

/*
Plugin Name: WP GRAPHQL WPML
Version: 0.1
Description: Adds language meta to posts as result of GraphQL query.
Author: Vinko Vacek
*/

// Use the local autoload if not using project wide autoload
if (!\class_exists('\WPGraphQL\Extensions\WPML\Loader')) {
  require_once __DIR__ . '/vendor/autoload.php';
}

\WPGraphQL\Extensions\WPML\Loader::init();







