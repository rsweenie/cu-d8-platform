<?php 

namespace Drupal\cu_hub_consumer\Plugin\cu_hub_consumer\ResourceFieldType;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Datetime\DateFormatterInterface;

/**
 * Generic datetime resource field.
 * 
 * @HubResourceFieldType(
 *   id = "daterange",
 *   label = @Translation("Date Range"),
 *   description = @Translation("Date and time range."),
 * )
 */
class DateRangeFieldItem extends ArrayFieldItemBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * Cached computed start date.
   *
   * @var \DateTime|null
   */
  protected $dateStart = NULL;

  /**
   * Cached computed end date.
   *
   * @var \DateTime|null
   */
  protected $dateEnd = NULL;

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

  /**
   * {@inheritdoc}
   */
  public function mainPropertyName() {
    return 'value';
  }

  /**
   * Get the start date as a datetime object.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getStartDateTime() {
    if ($this->dateStart !== NULL) {
      return $this->dateStart;
    }

    // 2019-08-13T03:48:53+00:00
    $format = 'Y-m-d\TH:i:sP';

    try {
      // Assumes ISO 8601 date/time format.
      $date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:sP', $this->value['value'], 'UTC');
      if ($date instanceof DrupalDateTime && !$date->hasErrors()) {
        $this->dateStart = $date;
      }
    }
    catch (\Exception $e) {
      // @todo Handle this.
    }
    return $this->dateStart;
  }

  /**
   * Get the start date as a datetime object.
   *
   * @return \Drupal\Core\Datetime\DrupalDateTime
   */
  public function getEndDateTime() {
    if ($this->dateEnd !== NULL) {
      return $this->dateEnd;
    }

    // 2019-08-13T03:48:53+00:00
    $format = 'Y-m-d\TH:i:sP';

    try {
      // Assumes ISO 8601 date/time format.
      $date = DrupalDateTime::createFromFormat('Y-m-d\TH:i:sP', $this->value['end_value'], 'UTC');
      if ($date instanceof DrupalDateTime && !$date->hasErrors()) {
        $this->dateEnd = $date;
      }
    }
    catch (\Exception $e) {
      // @todo Handle this.
    }
    return $this->dateEnd;
  }

  /**
   * Formats a datetime object.
   *
   * @param \Drupal\Core\Datetime\DrupalDateTime $date
   * @param string $format_type
   * @return string
   */
  protected function formatDate($date, $format_type = 'medium') {
    return $this->dateFormatter
      ->format($date->getTimestamp(), $format_type);
  }

  /**
   * {@inheritdoc}
   */
  public function view() {
    $elements = [];

    if ($start_date = $this->getStartDateTime()) {
      // Create the ISO date in Universal Time.
      $iso_date = $start_date->format("Y-m-d\\TH:i:s") . 'Z';
      $output = $this->formatDate($start_date);

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

      if ($end_date = $this->getEndDateTime()) {
        if ($start_date->getTimestamp() !== $end_date->getTimestamp()) {
          // Create the ISO date in Universal Time.
          $iso_date = $start_date->format("Y-m-d\\TH:i:s") . 'Z';
          $output = $this->formatDate($start_date);

          // Display the date using theme datetime.
          $end_elements = [
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

          $elements = [
            'start_date' => $elements,
            'separator' => ['#plain_text' => ' - '],
            'end_date' => $end_elements,
          ];
        }
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldFriendlyValue() {
    if ($start_datetime = $this->getStartDateTime()) {
      $value = ['value' => $start_datetime->format('Y-m-d\TH:i:s')];
      if ($end_datetime = $this->getEndDateTime()) {
        $value['end_value'] = $end_datetime->format('Y-m-d\TH:i:s');
      }
      return $value;
    }
  }

}
