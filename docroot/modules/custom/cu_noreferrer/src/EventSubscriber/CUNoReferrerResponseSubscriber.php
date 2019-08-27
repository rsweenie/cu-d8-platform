<?php

namespace Drupal\cu_noreferrer\EventSubscriber;

use Drupal\cu_noreferrer\Component\Utility\CUNoReferrer;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to handle HTML responses.
 */
class CUNoReferrerResponseSubscriber implements EventSubscriberInterface {

  /**
   * \Drupal::config object needed by filter
   */
  protected $config;

  /**
   * 
   */
  public function __construct($config) {
    $this->config = $config;
  }

  /**
   */
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }
    /**
     * if either apply, apply it.
     */
    if($this->config->get('cu_noreferrer.settings')->get('noreferrer') || $this->config->get('cu_noreferrer.settings')->get('noopener'))
      $response->setContent(NoReferrer::filter($response->getContent()
                                                        ,$this->config)->getProcessedText());
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
