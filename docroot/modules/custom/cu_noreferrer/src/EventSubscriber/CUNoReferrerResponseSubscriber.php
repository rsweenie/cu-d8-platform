<?php

namespace Drupal\cu_noreferrer\EventSubscriber;

use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\AttachmentsResponseProcessorInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Response subscriber to handle HTML responses.
 */
class CUNoReferrerResponseSubscriber implements EventSubscriberInterface {

  /**
   * The HTML response attachments processor service.
   *
   * @var \Drupal\Core\Render\AttachmentsResponseProcessorInterface
   */
  protected $htmlResponseAttachmentsProcessor;

  /**
   */
  public function __construct() {
  }

  /**
   * Processes attachments for HtmlResponse responses.
   *
   * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
   *   The event to process.
   */
  public function onRespond(FilterResponseEvent $event) {
    $response = $event->getResponse();
    if (!$response instanceof HtmlResponse) {
      return;
    }
    kint($response);
    //$event->setResponse($this->htmlResponseAttachmentsProcessor->processAttachments($response));
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

}
