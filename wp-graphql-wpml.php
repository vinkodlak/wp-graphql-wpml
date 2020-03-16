<?php

/*
Plugin Name: WP GRAPHQL WPML
Version: 0.1
Description: Adds language meta to posts as result of GraphQL query.
Author: Vinko Vacek
*/

use GraphQLRelay\Relay;

add_action( 'graphql_register_types', 'wp_graphql_wpml_init', 10, 0);

add_filter( 'graphql_post_object_connection_query_args', 'force_suppress_filters', 10, 1);

add_filter( 'graphql_map_input_fields_to_wp_query', 'map_language_to_query_args', 10, 2);

function wp_graphql_wpml_init() {

  $language_codes = [];

  foreach (apply_filters( 'wpml_active_languages', NULL ) as $lang) {
    $language_codes[$lang['language_code']] = $lang['language_code'];
  }

  register_graphql_enum_type('LanguageCodeEnum', [
      'description' => __('Enum of all available language codes', 'wp-graphql-wpml' ),
      'values' => $language_codes,
  ]);

  register_graphql_enum_type('LanguageCodeFilterEnum', [
    'description' => __(
        'Filter item by specific language, default language or list all languages',
        'wp-graphql-wpml'
    ),
    'values' => array_merge($language_codes, [
        'DEFAULT' => 'default',
        'ALL' => 'all',
    ]),
  ]);

  register_graphql_object_type('Language', [
    'description' => __('Language (WPML)', 'wp-graphql-wpml'),
    'fields' => [
      'id' => [
        'type' => [
          'non_null' => 'ID',
        ],
        'description' => __('Language ID (WPML)', 'wp-graphql-wpml'),
      ],
      'name' => [
        'type' => 'String',
        'description' => __('Human readable language name (WPML)', 'wp-graphql-wpml'),
      ],
      'code' => [
        'type' => 'LanguageCodeEnum',
        'description' => __('Language code (WPML)', 'wp-graphql-wpml'),
      ],
      'locale' => [
        'type' => 'String',
        'description' => __('Language locale (WPML)', 'wp-graphql-wpml'),
      ],
      'slug' => [
        'type' => 'String',
        'description' => __('Language term slug. Prefer the "code" field if possible (WPML)', 'wp-graphql-wpml'),
      ],
    ]
  ]);

  register_graphql_field('RootQuery', 'languages', [
    'type' => ['list_of' => 'Language'],
    'description' => __('List available languages', 'wp-graphql-wpml'),
    'resolve' => function ($source, $args, $context, $info) {
        $fields = $info->getFieldSelection();

        $languages = array_map(
          function ($lang) {
            return [
              'id' => Relay::toGlobalId('Language', $lang['language_code']),
              'code' => $lang['language_code'],
              'slug' => $lang['language_code'],
              'name' => $lang['native_name'],
              // 'locale' =>     
            ];
          },
          apply_filters( 'wpml_active_languages', NULL )
        );

        return $languages;
    },
  ]);

  register_graphql_field('RootQuery', 'defaultLanguage', [
    'type' => 'Language',
    'description' => __('Default site language', 'wp-graphql-wpml'),
    'resolve' => function ($source, $args, $context, $info) {
      $defaultLanguage = apply_filters( 'wpml_default_language', NULL );

      return [
        'id' => Relay::toGlobalId('Language', $defaultLanguage['language_code']),
        'code' => $defaultLanguage['language_code'],
        'slug' => $defaultLanguage['language_code'],
        'name' => $defaultLanguage['native_name'],
        // 'locale' =>     
      ];

    },
  ]);


  register_graphql_fields('RootQueryToContentNodeConnectionWhereArgs', [
    'language' => [
        'type' => 'LanguageCodeFilterEnum',
        'description' => 'Filter content nodes by language code (WPML)',
    ],
  ]);

  foreach (\WPGraphQL::get_allowed_post_types() as $post_type) {
      add_post_type_fields(get_post_type_object($post_type));
  }


}

function force_suppress_filters(array $query_args) {
  $query_args['suppress_filters'] = true;
  return $query_args;
}

function map_language_to_query_args(
  array $query_args,
  array $where_args
) {
  $query_args['suppress_filters'] = true;
  if (!isset($where_args['language'])) {
      return $query_args;
  }

  $lang = $where_args['language'];
  unset($where_args['language']);

  if ('all' === $lang) {
      // No need to do anything. We show all languages by default
      return $query_args;
  }

  if ('default' === $lang) {
      $defaultLanguage = apply_filters( 'wpml_default_language', NULL );
      $lang = $defaultLanguage['language_code'];
  }

  do_action( 'wpml_switch_language', $lang );

  // return only selected language
  $query_args['suppress_filters'] = false;
  $query_args['lang'] = $lang;

  return $query_args;
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

