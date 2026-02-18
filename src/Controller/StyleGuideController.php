<?php

namespace Drupal\ts_styleguide\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates style guide at /styleguide.
 */
class StyleGuideController extends ControllerBase {

  /**
   * The Library discovery service.
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryCollector
   */
  protected $libraries;

  /**
   * The theme manager.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->configFactory = $container->get('config.factory');
    $instance->libraries = $container->get('library.discovery');
    $instance->themeManager = $container->get('theme.manager');
    return $instance;
  }

  /**
   * Display TS styleguide for the current theme.
   *
   * @return array
   *   The styleguide markup.
   */
  public function tsStyleGuide() {
    $themename = $this->configFactory->get('system.theme')->get('default');
    $content = [
      '#theme' => 'styleguide',
      '#theme_name' => $themename,
    ];
    if ($this->libraries->getLibraryByName($themename, 'ts_styleguide')) {
      $content['#attached']['library'][] = "$themename/ts_styleguide";
    }
    return $content;
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

}