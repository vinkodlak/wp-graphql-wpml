<?php

namespace WPGraphQL\Extensions\WPML;

use GraphQLRelay\Relay;

class TermObject
{
    function init()
    {
        add_action(
            'graphql_register_types',
            [$this, '__action_graphql_register_types'],
            10,
            0
        );

        add_filter(
          'graphql_term_object_connection_query_args', 
          [__NAMESPACE__ . '\\Helpers', 'force_suppress_filters'],
          9,
          1
        );

        add_filter(
            'graphql_map_input_fields_to_get_terms',
            [__NAMESPACE__ . '\\Helpers', 'map_language_to_query_args'],
            10,
            2
        );

        add_filter(
            'graphql_term_object_insert_term_args',
            [$this, '__filter_graphql_term_object_insert_term_args'],
            10,
            2
        );
    }

    function __filter_graphql_term_object_insert_term_args($insert_args, $input)
    {
        if (isset($input['language'])) {
            $insert_args['language'] = $input['language'];
        }

        return $insert_args;
    }

    function __action_graphql_register_types()
    {
        foreach (\WPGraphQL::get_allowed_taxonomies() as $taxonomy) {
            $this->add_taxonomy_fields(get_taxonomy($taxonomy));
        }
    }

    function add_taxonomy_fields(\WP_Taxonomy $taxonomy)
    {
        // if (!pll_is_translated_taxonomy($taxonomy->name)) {
        //     return;
        // }

        $type = ucfirst($taxonomy->graphql_single_name);

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


        register_graphql_field($type, 'language', [
            'type' => 'Language',
            'description' => __(
                'List available translations for this term',
                'wpnext'
            ),
            'resolve' => function (
                \WPGraphQL\Model\Term $term,
                $args,
                $context,
                $info
            ) {
                $fields = $info->getFieldSelection();
                $language = [];

                $term_language_code = apply_filters(
                  'wpml_element_language_code', 
                  null, 
                  array( 
                    'element_id'=> (int)$term->term_id, 
                    'element_type'=> $type
                  ) 
                );

                return [
                  'id' => Relay::toGlobalId('Language', 'ttt'),
                  'code' => $term_language_code['language_code'],
                  'slug' => $term_language_code['language_code'],
                  'name' => $term->term_id,
                  'locale' => $term_language_code['locale'],
                ];

                if (!$term_language_code) {
                    return null;
                }

                return [
                  'id' => Relay::toGlobalId('Language', $term_language_code['language_code']),
                  'code' => $term_language_code['language_code'],
                  'slug' => $term_language_code['language_code'],
                  'name' => $term_language_code['native_name'],
                  'locale' => $term_language_code['locale'],
                ];
            },
        ]);

        // register_graphql_field($type, 'translations', [
        //     'type' => [
        //         'list_of' => $type,
        //     ],
        //     'description' => __(
        //         'List all translated versions of this term',
        //         'wp-graphql-polylang'
        //     ),
        //     'resolve' => function (\WPGraphQL\Model\Term $term) {
        //         $terms = [];

        //         foreach (
        //             pll_get_term_translations($term->term_id)
        //             as $lang => $term_id
        //         ) {
        //             if ($term_id === $term->term_id) {
        //                 continue;
        //             }

        //             $translation = get_term($term_id);

        //             if (!$translation) {
        //                 continue;
        //             }

        //             if (is_wp_error($translation)) {
        //                 continue;
        //             }

        //             $terms[] = new \WPGraphQL\Model\Term($translation);
        //         }

        //         return $terms;
        //     },
        // ]);

        // register_graphql_field($type, 'translation', [
        //     'type' => $type,
        //     'description' => __(
        //         'Get specific translation version of this object',
        //         'wp-graphql-polylang'
        //     ),
        //     'args' => [
        //         'language' => [
        //             'type' => [
        //                 'non_null' => 'LanguageCodeEnum',
        //             ],
        //         ],
        //     ],
        //     'resolve' => function (\WPGraphQL\Model\Term $term, array $args) {
        //         $translations = pll_get_term_translations($term->term_id);
        //         $term_id = $translations[$args['language']] ?? null;

        //         if (!$term_id) {
        //             return null;
        //         }

        //         return new \WPGraphQL\Model\Term(get_term($term_id));
        //     },
        // ]);
    }
}