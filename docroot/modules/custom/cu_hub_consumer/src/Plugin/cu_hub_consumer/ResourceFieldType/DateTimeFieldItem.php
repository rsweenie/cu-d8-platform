<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Generic string resource field.
 * 
 * @HubResourceFieldType(
 *   id = "datetime",
 *   label = @Translation("Date"),
 *   description = @Translation("Date and time."),
 * )
 */
class DateTimeFieldItem extends ScalarFieldItemBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Cached computed date.
   *
   * @var \DateTime|null
   */
  protected $date = NULL;

  /**
   * Constructs a new class instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger channel for cu_hub_consumer.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigFactoryInterface $config_factory, LoggerInterface $logger,
                              MessengerInterface $messenger, DateFormatterInterface $date_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $config_factory, $logger, $messenger);

    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('logger.factory')->get('cu_hub_consumer'),
      $container->get('messenger'),
      $container->get('date.formatter')
    );
  }

  public function getDateTime() {
    if ($this->date !== NULL) {
      return $this->date;
    }

    // 2019-08-13T03:48:53+00:00
    $format = 'Y-m-d\TH:i:sP';

    try {
      // Assumes ISO 8601 date/time format.
      $date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:sP', $this->value, 'UTC');
      if ($date instanceof DrupalDateTime && !$date
        ->hasErrors()) {
        $this->date = $date;
      }
    }
    catch (\Exception $e) {
      // @todo Handle this.
    }
    return $this->date;
  }

  /**
   * {@inheritdoc}
   */
  protected function formatDate($date) {
    $format_type = 'medium';
    return $this->dateFormatter
      ->format($date->getTimestamp(), $format_type);
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [];

    if ($date = $this->getDateTime()) {
      // Create the ISO date in Universal Time.
      $iso_date = $date->format("Y-m-d\\TH:i:s") . 'Z';
      $output = $this->formatDate($date);

      // Display the date using theme datetime.
      $elements = [
        '#cache' => [
          'contexts' => [
            'timezone',
          ],
        ],
        '#theme' => 'time',
        '#text' => $output,
        '#html' => FALSE,
        '#attributes' => array(
          'datetime' => $iso_date,
        ),
      ];
      /*
      if (!empty($item->_attributes)) {
        $elements[$delta]['#attributes'] += $item->_attributes;

        // Unset field item attributes since they have been included in the
        // formatter output and should not be rendered in the field template.
        unset($item->_attributes);
      }
      */
    }

    return $elements;
  }

}
