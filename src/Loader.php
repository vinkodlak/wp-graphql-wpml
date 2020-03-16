<?php

namespace WPGraphQL\Extensions\WPML;

class Loader {
  static function init() {
    define ('WPGRAPHQL_WPML', true);
    (new Loader())->bind_hooks();
  }

  function bind_hooks() {
    add_action('graphql_init', [$this, '__action_graphql_init']);
  }

  function __action_graphql_init() {
    if (!$this->is_graphql_request()) {
        return;
    }

    (new WPMLTypes())->init();
    (new PostObject())->init();
    // (new TermObject())->init();
    (new LanguageRootQueries())->init();
    // (new MenuItem())->init();
    // (new StringsTranslations())->init();
  }

  function is_graphql_request() {
      // Detect WPGraphQL activation by checking if the main class is defined
      if (!class_exists('WPGraphQL')) {
          return false;
      }

      // Copied from https://github.com/wp-graphql/wp-graphql/pull/1067
      // For now as the existing version is buggy.
      if (isset($_GET[\WPGraphQL\Router::$route])) {
          return true;
      }

      // If before 'init' check $_SERVER.
      if (isset($_SERVER['HTTP_HOST']) && isset($_SERVER['REQUEST_URI'])) {
          $haystack =
              wp_unslash($_SERVER['HTTP_HOST']) .
              wp_unslash($_SERVER['REQUEST_URI']);
          $needle = site_url(\WPGraphQL\Router::$route);
          // Strip protocol.
          $haystack = preg_replace('#^(http(s)?://)#', '', $haystack);
          $needle = preg_replace('#^(http(s)?://)#', '', $needle);
          $len = strlen($needle);
          return substr($haystack, 0, $len) === $needle;
      }

      return false;
  }
}