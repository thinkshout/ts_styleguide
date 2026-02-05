<?php

namespace Drupal\ts_styleguide\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Utility\Xss;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Error\LoaderError;

/**
 * Creates style guide at /styleguide.
 */
class StyleGuideController extends ControllerBase {

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The twig service.
   *
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twig;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->themeManager = $container->get('theme.manager');
    $instance->twig = $container->get('twig');
    return $instance;
  }

  /**
   * Display TS styleguide for the current theme.
   *
   * @return array
   *   The styleguide markup.
   */
  public function tsStyleGuide() {
    $build = [
      '#theme' => 'styleguide',
    ];

    $build['#theme_name'] = $this->configFactory->get('system.theme')->get('default');

    $styleguide_directory_scans = array(
      '#ts_blocks' => 'organisms/blocks',
      '#ts_molecules' => 'molecules',
    );
    foreach( $styleguide_directory_scans as $key => $template_path ) {
      $build[$key] = [];
      $directory = $this->themeManager->getActiveTheme()->getPath() . '/templates/' . $template_path;
      $element_directories = scandir($directory) ?: array();
      foreach ($element_directories as $id) {
        $file = "$id/$id.twig";
        $styleguide_file = "$id/styleguide/$id--styleguide-layout.twig";
        if ( file_exists( "$directory/$styleguide_file" ) ) {
          $file_headers = $this->get_file_data( "$directory/$file", array(
            'title' => 'Title',
            'description' => 'Description',
          ) );
          if ( ! $file_headers['title'] ) {
            $file_headers['title'] = $id;
          }
          $file_headers['dev_notes'] = "$template_path/$id";
          $file_headers['path'] = "$template_path/$styleguide_file";
          $build[$key][$id] = $file_headers;
        }
      }
    }

    return $build;
  }

  /**
   * Access callback for TS styleguide.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  public function tsStyleGuideAccess() {
    $enabled = $this->configFactory->get('ts_styleguide.settings')->get('enabled');
    return AccessResult::allowedIf($enabled);
  }

  /**
   * Retrieves metadata from a file. (Lovingly stolen from WP core.)
   *
   * @link https://codex.wordpress.org/File_Header
   *
   * @param string $file
   *   Absolute path to the file.
   * @param array $default_headers
   *   List of headers, in the format `array( 'HeaderKey' => 'Header Name' )`.
   *
   * @return string[]
   *   Array of file header values keyed by header name.
   */
  protected function get_file_data($file, $default_headers) {
    // Pull only the first 8 KB of the file in.
    $file_data = file_get_contents($file, FALSE, NULL, 0, 8 * 1024) ?: '';

    // Make sure we catch CR-only line endings.
    $file_data = str_replace("\r", "\n", $file_data);

    $all_headers = $default_headers;

    foreach ($all_headers as $field => $regex) {
      if (preg_match('/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $file_data, $match) && $match[1]) {
        $all_headers[$field] = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $match[1]));
      }
      else {
        $all_headers[$field] = '';
      }
    }

    return $all_headers;
  }

}