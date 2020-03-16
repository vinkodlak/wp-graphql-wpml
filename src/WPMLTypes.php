<?php

namespace WPGraphQL\Extensions\WPML;

class WPMLTypes {
  function init() {
    add_action(
      'graphql_register_types',
      [$this, '__action_graphql_register_types'],
      9,
      0
    );
  }

  function __action_graphql_register_types() {
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
  }
}