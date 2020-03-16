<?php

/*
Plugin Name: WP GRAPHQL WPML
Version: 0.1
Description: Adds language meta to posts as result of GraphQL query.
Author: Vinko Vacek
*/

use GraphQLRelay\Relay;

add_action( 'graphql_register_types', 'wp_graphql_wpml_init', 10, 0);

function wp_graphql_wpml_init() {



  register_graphql_field('RootQuery', 'defaultLanguage', [
    'type' => 'Language',
    'description' => __('Default site language', 'wp-graphql-wpml'),
    'resolve' => function ($source, $args, $context, $info) {
        $fields = $info->getFieldSelection();
        $language = 'hrv';

        return $language;

        
    },
  ]);


}

