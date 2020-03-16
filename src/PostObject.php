<?php

namespace WPGraphQL\Extensions\WPML;

use GraphQLRelay\Relay;

class PostObject {

  function init() {
    add_action(
      'graphql_register_types',
      [$this, '__action_graphql_register_types'],
      10,
      0
    );
  
    add_filter(
      'graphql_post_object_connection_query_args', 
      [__NAMESPACE__ . '\\Helpers', 'force_suppress_filters'],
      9,
      1
    );

    add_filter(
      'graphql_map_input_fields_to_wp_query',
      [__NAMESPACE__ . '\\Helpers', 'map_language_to_query_args'],
      10,
      2
    );
  }

  function __action_graphql_register_types() {
    register_graphql_fields('RootQueryToContentNodeConnectionWhereArgs', [
      'language' => [
          'type' => 'LanguageCodeFilterEnum',
          'description' => 'Filter content nodes by language code (WPML)',
      ],
    ]);
  
    foreach (\WPGraphQL::get_allowed_post_types() as $post_type) {
        $this->add_post_type_fields(get_post_type_object($post_type));
    }
  }

  function add_post_type_fields(\WP_Post_Type $post_type_object) {
    // if (!pll_is_translated_post_type($post_type_object->name)) {
    //     return;
    // }

    $type = ucfirst($post_type_object->graphql_single_name);

    register_graphql_fields("RootQueryTo${type}ConnectionWhereArgs", [
        'language' => [
            'type' => 'LanguageCodeFilterEnum',
            'description' => "Filter by ${type}s by language code (WPML)",
        ],
    ]);

    // register_graphql_fields("Create${type}Input", [
    //     'language' => [
    //         'type' => 'LanguageCodeEnum',
    //     ],
    // ]);

    // register_graphql_fields("Update${type}Input", [
    //     'language' => [
    //         'type' => 'LanguageCodeEnum',
    //     ],
    // ]);

    register_graphql_field(
        $post_type_object->graphql_single_name,
        'language',
        [
            'type' => 'Language',
            'description' => __('WPML language', 'wpnext'),
            'resolve' => function (
                \WPGraphQL\Model\Post $post,
                $args,
                $context,
                $info
            ) {
                $post_language_details = apply_filters( 'wpml_post_language_details', NULL, $post->ID );

                if (!$post_language_details) {
                    return null;
                }

                return [
                  'id' => Relay::toGlobalId('Language', $post_language_details['language_code']),
                  'code' => $post_language_details['language_code'],
                  'slug' => $post_language_details['language_code'],
                  'name' => $post_language_details['native_name'],
                  'locale' => $post_language_details['locale'],
                ];
            },
        ]
    );

    // register_graphql_field(
    //     $post_type_object->graphql_single_name,
    //     'translation',
    //     [
    //         'type' => $type,
    //         'description' => __(
    //             'Get specific translation version of this object',
    //             'wp-graphql-polylang'
    //         ),
    //         'args' => [
    //             'language' => [
    //                 'type' => [
    //                     'non_null' => 'LanguageCodeEnum',
    //                 ],
    //             ],
    //         ],
    //         'resolve' => function (
    //             \WPGraphQL\Model\Post $post,
    //             array $args
    //         ) {
    //             $translations = pll_get_post_translations($post->ID);
    //             $post_id = $translations[$args['language']] ?? null;

    //             if (!$post_id) {
    //                 return null;
    //             }

    //             return new \WPGraphQL\Model\Post(
    //                 \WP_Post::get_instance($post_id)
    //             );
    //         },
    //     ]
    // );

    // register_graphql_field(
    //     $post_type_object->graphql_single_name,
    //     'translations',
    //     [
    //         'type' => [
    //             'list_of' => $type,
    //         ],
    //         'description' => __(
    //             'List all translated versions of this post',
    //             'wp-graphql-polylang'
    //         ),
    //         'resolve' => function (\WPGraphQL\Model\Post $post) {
    //             $posts = [];

    //             foreach (
    //                 pll_get_post_translations($post->ID)
    //                 as $lang => $post_id
    //             ) {
    //                 $translation = \WP_Post::get_instance($post_id);

    //                 if (!$translation) {
    //                     continue;
    //                 }

    //                 if (is_wp_error($translation)) {
    //                     continue;
    //                 }

    //                 if ($post->ID === $translation->ID) {
    //                     continue;
    //                 }

    //                 $posts[] = new \WPGraphQL\Model\Post($translation);
    //             }

    //             return $posts;
    //         },
    //     ]
    // );
  }
}