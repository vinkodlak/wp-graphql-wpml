<?php

namespace WPGraphQL\Extensions\WPML;

class Helpers {
  static function force_suppress_filters(array $query_args) {
    if (!isset($query_args['suppress_filters'])) {
      $query_args['suppress_filters'] = true;
    }
    return $query_args;
  }
  
  static function map_language_to_query_args(
    array $query_args,
    array $where_args
  ) {
    $query_args['suppress_filters'] = true;
    
    if (!isset($where_args['language'])) {
        return $query_args;
    }
  
    $lang = $where_args['language'];
    unset($where_args['language']);
  
    if ('all' === $lang || 'ALL' === $lang) {
        // No need to do anything. We show all languages by default
        return $query_args;
    }
  
    if ('default' === $lang || 'DEAFULT' === $lang) {
        $defaultLanguage = apply_filters( 'wpml_default_language', NULL );
        $lang = $defaultLanguage['language_code'];
    }
  
    do_action( 'wpml_switch_language', $lang );
  
    // return only selected language
    $query_args['suppress_filters'] = false;
    $query_args['lang'] = $lang;
  
    return $query_args;
  }
}