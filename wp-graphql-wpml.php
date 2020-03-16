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

  // foreach (pll_languages_list() as $lang) {
  //   $language_codes[strtoupper($lang)] = $lang;
  // }

  register_graphql_enum_type('LanguageCodeEnum', [
      'description' => __('Enum of all available language codes', 'wp-graphql-wpml' ),
      'values' => ['hr'] //$language_codes,
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
        // $fields = $info->getFieldSelection();

        $languages = array_map(
          function ($lang) {
            return [
              'id' => Relay::toGlobalId('Language', rand()),
              // 'code' => $lang['language_code'],
              // 'slug' => $lang['language_code'],
              'code' => 'hr',
              'name' => 'tralala',
              'slug' => $lang,
            ];
          },
          // apply_filters( 'wpml_active_languages', NULL )
          [1,2,3]
        );

        // if (isset($fields['name'])) {
        //     foreach (
        //         pll_languages_list(['fields' => 'name'])
        //         as $index => $name
        //     ) {
        //         $languages[$index]['name'] = $name;
        //     }
        // }

        // if (isset($fields['locale'])) {
        //     foreach (
        //         pll_languages_list(['fields' => 'locale'])
        //         as $index => $locale
        //     ) {
        //         $languages[$index]['locale'] = $locale;
        //     }
        // }

        return $languages;
    },
  ]);

  register_graphql_field('RootQuery', 'defaultLanguage', [
    'type' => 'Language',
    'description' => __('Default site language', 'wp-graphql-wpml'),
    'resolve' => function ($source, $args, $context, $info) {
        $fields = $info->getFieldSelection();
        $language = [];

        $language['code'] = 'hr';
        $language['name'] = 'Hrvatski';
        $language['locale'] = 'hrhr';
        $language['id'] = 'id';
        $language['slug'] = 'slug';

        return $language;


    },
  ]);


}

