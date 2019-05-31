<?php

namespace Drupal;

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext,
    Behat\Behat\Tester\Exception\PendingException;

/**
 * FeatureContext class defines custom step definitions for Behat.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {
  //requested page not found
  const RPNF = "The requested page could not be found";
  private $output = '';
  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * @When I fill in the wysiwyg :locator with :text
   */
  public function iFillInTheWysiwygWith($locator, $text)
  {
    $field = $this->getSession()->getPage()->findField($locator);
    if (null === $field) {
      throw new ElementNotFoundException($this->getDriver(), 'form field', 'id|name|label|value|placeholder', $locator);
    }
    $id = $field->getAttribute('id');
    $instance = $this->getWysiwygInstance($id);
    $this->getSession()->executeScript("$instance.setData(\"$text\");");
  }

  /**
   * Get the wysiwyg instance variable to use in Javascript.
   *
   * @param string
   *   The instanceId used by the WYSIWYG module to identify the instance.
   *
   * @throws Exception
   *   Throws an exception if the editor does not exist.
   *
   * @return string
   *   A Javascript expression representing the WYSIWYG instance.
   */
  protected function getWysiwygInstance($instanceId) {
    $instance = "CKEDITOR.instances['$instanceId']";
    if (!$this->getSession()->evaluateScript("return !!$instance")) {
      throw new \Exception(sprintf('The editor "%s" was not found on the page %s', $instanceId, $this->getSession()->getCurrentUrl()));
    }
    return $instance;
  }

  /**
   * @Then I leave the frame
   * @Then I switch to the window
   */
  public function iSwitchToTheWindow()
  {
    $this->getSession()->getDriver()->switchToIFrame(null);
  }

  /**
   * @When I wait for :arg1 seconds
   */
  public function iWaitForSeconds($arg1)
  {
    $this->getSession()->wait($arg1);
  }

  /**
   * @Then all anchor elements are valid
   */
  public function allAnchorElementsAreValid()
  {
      $this->allElementsShouldHaveAValidAttribute("a","href");
      $this->iClickAnAnchorElementIShouldNotSee(SELF::RPNF);
  }

  /**
   * @Then all :arg1 elements should have a valid attribute :arg2
   */
  public function allElementsShouldHaveAValidAttribute($arg1, $arg2)
  {
    $elements = $this->getSession()->getPage()->findAll('xpath','//'.$arg1.'[@'.$arg2.' = ""]');
    if(!empty($elements)){
      $this->output = '"'.$arg1.'" elements missing "'.$arg2.'"';
      foreach($elements as $element)
        $this->output .= PHP_EOL.'-'.$element->getText();
      throw new PendingException(
        $this->output
      );
    }
  }

  /**
   * @When I click an anchor element I should not see :arg1
   */
  public function iClickAnAnchorElementIShouldNotSee($arg1)
  {
    //return internal links only
    $elements = $this->getSession()->getPage()->findAll('xpath','//a[not(contains(@href,"http")) and not(contains(@href,"mailto")) and not(contains(@href,"#")) and not(@href = "")]//@href');
    $hrefs = [];
    foreach($elements as $key => $element){
      array_push($hrefs,$element->getText());
    }
    foreach($hrefs as $href){
      //the page changes so the hrefs need to be stored prior to checking
      $this->getSession()->visit($this->locatePath($href));
      if($this->getSession()->getStatusCode() != 200 || $this->assertSession()->pageTextNotContains($arg1)){
        $this->output .= PHP_EOL.$href;
      }
    }
    if(!empty($this->output))
      throw new PendingException(
        'Failed Links: '.$this->output
      );
  }

  /**
   * @Then all the links should be valid
   */
  public function allTheLinksShouldBeValid()
  {
    $elements = $this->getSession()->getPage()->findAll('xpath','//a[@href = ""]');
    if(!empty($elements)){
      throw new PendingException(
        $this->output
      );
    }
  }
}
