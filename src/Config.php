<?php
/**
 * @file
 * Contains \Drupal\cloudflare\Config.
 */

namespace Drupal\cloudflare;

use Drupal\Core\Url;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use CloudFlarePhpSdk\Exceptions\CloudFlareHttpException;
use CloudFlarePhpSdk\Exceptions\CloudFlareApiException;
use CloudFlarePhpSdk\ApiEndpoints\ZoneApi;
use Psr\Log\LoggerInterface;

/**
 * Invalidation methods for CloudFlare.
 */
class Config implements CloudFlareConfigInterface {
  use StringTranslationTrait;

  /*
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config;

  /*
   * @var string
   */
  protected $apiKey;

  /*
   * @var string
   */
  protected $email;

  /*
   * @var string
   */
  protected $zone;

  /*
   * @var \CloudFlarePhpSdk\ApiEndpoints\ZoneApi
   */
  protected $zoneApi;

  /*
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /*
   * @var \Drupal\cloudflare\StateInterface
   */
  protected $state;

  /**
   * CloudFlareInvalidator constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   The config factory.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   * @param \Drupal\cloudflare\CloudFlareStateInterface $state
   *   Tracks rate limits associated with CloudFlare Api.
   */
  public function __construct(ConfigFactoryInterface $config, LoggerInterface $logger, CloudFlareStateInterface $state) {
    $this->config = $config->get('cloudflare.settings');
    $this->logger = $logger;
    $this->state = $state;

    $this->apiKey = $this->config->get('apikey');
    $this->email = $this->config->get('email');
    $this->zoneApi = new ZoneApi($this->apiKey, $this->email);
    if ($this->hasApiCredentials()) {
      $this->zone = $this->getZoneId();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLogger() {
    return $this->logger;
  }

  /**
   * {@inheritdoc}
   */
  public function getZoneApi() {
    return $this->zoneApi;
  }

  /**
   * {@inheritdoc}
   */
  public function getZoneId() {
    $has_zone_in_cmi = !is_null($this->config->get('zone'));

    // If this is a multi-zone cloudflare account and a zone has been set.
    if ($has_zone_in_cmi) {
      return $this->config->get('zone');
    }

    if (!is_null($this->zone)) {
      return $this->zone;
    }

    // If there is no zone set and the account only has a single zone.
    try {
      $zones_from_api = $this->zoneApi->listZones();
      $this->state->incrementApiRateCount();
    }

    catch (CloudFlareException $e) {
      $this->logger->error($e->getMessage());
      return NULL;
    }

    $num_zones_from_api = count($zones_from_api);
    $is_single_zone_cloudflare_account = $num_zones_from_api == 1;
    if ($is_single_zone_cloudflare_account) {

      // If there is a default zone that we can set do so in CMI.
      $zone_id = $zones_from_api[0]->getZoneId();
      $this->config->set('zone', $zone_id);
      return $zone_id;
    }

    // If the zone has multiple accounts and none is specified in CMI we cannot
    // move forward.
    if (!$is_single_zone_cloudflare_account) {
      $link_to_settings = Url::fromRoute('cloudflare.admin_settings_form')->toString();
      $message = $this->t('No default zone has been entered for CloudFlare. Please go <a href="@link_to_settings">here</a> to set.', ['@link_to_settings' => $link_to_settings]);
      $this->logger->error($message);
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function hasApiCredentials() {
    if (!isset($this->apiKey)) {
      $link_to_settings = Url::FromRoute('cloudflare.admin_settings_form')->toString();
      $message = $this->t('No valid credentials have been entered for CloudFlare. Please go <a href="@link_to_settings">here</a> to set them.', ['@link_to_settings' => $link_to_settings]);
      $this->logger->error($message);
      return FALSE;
    }

    return TRUE;
  }

}
