<?php
/**
 * @file
 * Contains \Drupal\cloudflare\CloudflareConfigInterface.
 */

namespace Drupal\cloudflare;

/**
 * Config methods for CloudFlare.
 */
interface CloudFlareConfigInterface {
  /**
   * Determines the Cloudflare ZoneId for the site.
   *
   * Most CloudFlare accounts have a single zone. It can be assumed as the
   * default. If there is more than one the user needs to specify the zone.
   * on the cloudflare config page.
   *
   * @return string
   *   The id of the zone.
   */
  public function getZoneId();

  /**
   * Gets ZoneApi object for the domain.
   *
   * @return \CloudFlarePhpSdk\ApiEndpoints\ZoneApi
   *   Instance of ZoneApi for the domain.
   */
  public function getZoneApi();

  /**
   * Gets logger.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger.
   */
  public function getLogger();

  /**
   * Checks if the site has valid CloudFlare Api credentials.
   *
   * @return bool|NULL
   *   TRUE if there are valid credentials.  FALSE otherwise.
   */
  public function hasApiCredentials();

}
