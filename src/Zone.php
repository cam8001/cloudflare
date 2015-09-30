<?php

/**
 * @file
 * Contains \Drupal\cloudflare\Zone.
 */

namespace Drupal\cloudflare;
use CloudFlarePhpSdk\ApiTypes\Zone\ZoneSettings;
use CloudFlarePhpSdk\Exceptions\CloudFlareException;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Zone methods for CloudFlare.
 */
class Zone implements CloudFlareZoneInterface {
  use StringTranslationTrait;

  /*
   * @var \Drupal\cloudflare\Config
   */
  protected $config;

  /*
   * @var \CloudFlarePhpSdk\ApiEndpoints\ZoneApi
   */
  protected $zoneApi;

  /*
   * @var string
   */
  protected $zone;

  /**
   * Zone constructor.
   *
   * @param \Drupal\cloudflare\Config $config
   *   CloudFlare config object.
   */
  public function __construct(Config $config) {
    $this->config = $config;
    $this->zoneApi = $config->getZoneApi();

    if ($this->config->hasApiCredentials()) {
      $this->zone = $this->config->getZoneId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getZoneSettings() {
    if (!$this->config->hasApiCredentials()) {
      return NULL;
    }

    try {
      return $this->zoneApi->getZoneSettings($this->zone);
    }

    catch (CloudFlareException $e) {
      $this->config->getLogger()->error($e->getMessage());
      throw $e;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function updateZoneSettings(ZoneSettings $zone_settings) {
    if (!$this->config->hasApiCredentials()) {
      return;
    }

    try {
      $this->zoneApi->updateZone($zone_settings);
    }

    catch (CloudFlareException $e) {
      $this->config->getLogger()->error($e->getMessage());
      throw $e;
    }
  }

}
