<?php

namespace WPGraphQL\Extensions\WPML;

use GraphQLRelay\Relay;

class LanguageRootQueries {
  function init() {
    add_action(
      'graphql_register_types',
      [$this, '__action_graphql_register_types'],
      10,
      0
    );
  }

  function __action_graphql_register_types() {
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
                // 'locale' => TODO
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
          // 'locale' => TODO 
        ];
  
      },
    ]);
  }
}