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
  private $screenshotCount = 0;
  /**
   * Every scenario gets its own context instance.
   *
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct() {

  }

  /**
   * Sets screen height
   */
  public function setScreenHeight($height){
      $this->getSession()->resizeWindow((int) $this->getCurrentScreenWidth(), (int) $height, 'current');
  }

  /**
   * Sets screen width
   */
  public function setScreenWidth($width){
      $this->getSession()->resizeWindow((int) $width, (int) $this->getCurrentScreenHeight(), 'current');
  }

  /**
   * returns the page height
   */
  private function getPageHeight(){
    return (int) $this->getSession()->getDriver()->evaluateScript(
      "function(){ var body = document.body,
        html = document.documentElement;
    
        return Math.max( body.scrollHeight, body.offsetHeight, 
                           html.clientHeight, html.scrollHeight, html.offsetHeight ); }()"
    );
  }

  /**
   * returns the page width
   */
  private function getPageWidth(){
    return (int) $this->getSession()->getDriver()->evaluateScript(
      "function(){ var body = document.body,
        html = document.documentElement;
    
        return Math.max( body.scrollWidth, body.offsetWidth, 
                           html.clientWidth, html.scrollWidth, html.offsetWidth ); }()"
    );
  }

  /**
     * Gets current screen height
     */
    private function getCurrentScreenHeight(){
      return (int) $this->getSession()->getDriver()->evaluateScript(
          "function(){ return screen.height; }()"
      );
  }    

  /**
   * Gets current screen width
   */
  private function getCurrentScreenWidth(){
      return (int) $this->getSession()->getDriver()->evaluateScript(
          "function(){ return screen.width; }()"
      );
  }

  /**
   * @Given /^I set browser window size to "([^"]*)" x "([^"]*)"$/
   * set height to full to match page height
   */
  public function iSetBrowserWindowSizeToX($width, $height) {
    $this->getSession()->resizeWindow((int)$width, (int)$height, 'current');
  }

  /**
   * @Then /^I take a screenshot with size "([^"]*)" x "([^"]*)"$/
   */
  public function iTakeAScreenshotWithSizeX($width, $height)
  {
    //set to page height if height is full
    if($height == "full"){
      $height = $this->getPageHeight();
    }
    $this->iSetBrowserWindowSizeToX($width, $height);
    $filename = sprintf('%s_%d.png', $this->getMinkParameter('browser_name'), ++$this->screenshotCount);
    //save screenshot to reports dir
    $filepath = \Drupal::root() . '/../reports';
    parent::saveScreenshot($filename, $filepath);
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

  /**
   * @Then I move block :label to region :region
   */
  public function iMoveBlockToRegion($label, $region)
  {
    //get the block
    $block = $this->getSession()->getPage()->find("named",["content","Block: ".$label])->getParent();
    //get the move select
    $select = $block->find("named",["option","Move"])->getParent();
    //click it to expose the options
    $select->click();
    //select the region you want to move the block to
    $select->find("named",["option",$region])->click();
    //verify movement
    $region_element = $this->getSession()->getPage()->find("named",["content","Region: ".$region])->getParent();
    if(empty($region_element->find("named",["content","Block: ".$label])))
      throw new \Exception("Block Move Failed");
  }
}
