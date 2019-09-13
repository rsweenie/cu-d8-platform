<?php
namespace Drupal\cu_data_transform\Transformation;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\core\Url;
class CUDataTransformation {
  //methods/functions must follow the <transform_type>_transformation
  CONST METHOD_FILTER = '_transformation';

  static function paragraph_links_transformation(){
    //content types to convert links from
    //each contains the old and new field names
    $content = [
        'Node'=>[
        'related_links' => [
          [
            'old' =>'field_related_link',
            'new' =>'field_related_p_link'
          ]
        ],
        'featured_links'=> [
          [
            'old' =>'field_featured_link',
            'new' =>'field_featured_p_link'
          ]
        ],
        'header_alert'=> [
          [
            'old' =>'field_alert_link',
            'new' =>'field_header_alert_p_link'
          ]
        ],
        'news_spotlight'=> [
          [
            'old' =>'field_new',
            'new' =>'field_news_spotlight_p_link'
          ]
        ],
        'profile'=> [
            [
            'old' =>'field_profile_link',
            'new' =>'field_profile_p_link'
          ]
        ],
        'promo_box'=> [
          [
            'old' =>'field_promo_link',
            'new' =>'field_promo_p_link'
          ]
        ],
        'quote_box'=> [
          [
            'old' =>'field_quote_link',
            'new' =>'field_quote_p_link'
          ]
        ],
        'slide'=> [
            [
            'old' =>'field_slide_link',
            'new' =>'field_slide_p_link'
          ]
        ],
      ],
      'Paragraph'=>[
        'featured_content_item'=>[
          [
            'old' =>'field_link',
            'new' =>'field_fc_p_link'
          ],
          [
            'old' =>'field_supporting_links',
            'new' =>'field_fc_supporting_p_links'
          ],
        ]
      ]
    ];

    $log = '';
    $c_cnt = 0;
    $nc_cnt = 0;
    $nl_cnt = 0;
    //iterate over entity types
    foreach($content as $entity_key => $entity_type)
      foreach($entity_type as $type => $fields){
        //get all the node ids for this content type
        $nids = \Drupal::entityQuery(lcfirst($entity_key))->condition('type',$type)->execute();
        //load all of those nodes
        $nodes = ($entity_key == 'Node')?Node::loadMultiple($nids):Paragraph::loadMultiple($nids);
        //log the content type we are converting
        $log .= '<strong>Type: '.$type.'</strong>';
        //iterate over each of these nodes
        foreach($nodes as $entity ){
          //log all the things
          $log .= '<br><strong>Entity: '.$entity->Id();
          //paragraphs dont have these properties
          if($entity_key == 'Node'){
            $log .= ' title: '.(!empty($entity->title->value)?$entity->title->value:'');
            $log .= '<br>'.$entity->toLink()->toString();
          }
          $log .=' </strong>';
          foreach($fields as $field){
            //if the field name exists for this entity
            if(count($entity->{$field['old']})>0){
              $paragraph_array = [];
              //for each link in the entity
              foreach($entity->{$field['old']} as $link){
                //load the link node
                $link_node = Node::load($link->target_id);
                //link node is not null
                if(!is_null($link_node)){
                  //internal links maybe be linkceptioned, so we need to find the base link
                  $base_link_node = Self::getBaseLinkNode($link_node);
                  //logging
                  $log.='<br>Original Link nid: '.$link_node->nid->value;
                  if($link_node->nid->value != $base_link_node->nid->value)
                    $log.='<br>base Link nid: '.$base_link_node->nid->value;

                  //check if link_node has content
                  if(Self::hasContent($link_node)){
                    //array union, create ief_link fields for paragraph.
                    $field_array = ['type' => 'ief_link']

                    //base link node for the uri, original link node for other field values
                    + (isset($base_link_node->field_links_link)?['field_internal_or_external_link' => $base_link_node->field_links_link]:[])
                    + (isset($link_node->field_links_link)?['field_link_text' => $link_node->field_links_link_text]:[])
                    + (isset($link_node->field_links_link)?['field_open_in_new_window' => $link_node->field_links_open_in_new_window]:[])
                    + (isset($link_node->field_links_link)?['field_file_link' => $link_node->field_links_file_link_upload]:[]);
                    //use field array to create the paragraph
                    $paragraph = Paragraph::create($field_array);
                    //save the paragraph
                    $paragraph->save();
                    //add the paragraph info needed to reference the content type to the paragraph
                    //for each link
                    array_push($paragraph_array ,
                      [
                        'target_id' => $paragraph->id(),
                        'target_revision_id' => $paragraph->getRevisionId(),
                      ]);
                    //log all the other things!
                    $log.='<br>Paragraph Link id: '. $paragraph->id();
                    $log.='<br>Paragraph Link Revision id: '.$paragraph->getRevisionId().'<br>';
                    $c_cnt++;
                  }else{
                    //just in case there's a link ref with no content(uri,text,file...)
                    $log .= '<br>Link has no content.<br>';
                    //$log .= $base_link_node->toLink()->toString().'<br>';
                    $nc_cnt++;
                  }
                }else{
                  $log .= '<br>Link does not exist.<br>';
                  $nl_cnt++;
                }
              }
              //add the paragraph to the entity and save
              if(!empty($paragraph_array))
                $entity->{$field['new']} = $paragraph_array;
              $entity->save();
            }else{
              //just in case there are no links to convert
              $log .= '<br>No Links to transform.<br>';
            }
          }
        }
        //add some new breaks
        $log .= '<br><br>';
      }
    // purge all links from db
    Self::purgeLinks();
    $log .= '<br>Links Transformed: '.$c_cnt.'<br>';
    $log .= '<br>Links Without Content: '.$nc_cnt.'<br>';
    $log .= '<br>Null Links: '.$nl_cnt.'<br>';
    //actually log the log
    \Drupal::logger('paragraph_link_transformation')->info($log);
    return $c_cnt.' Links Transformed';
  }
  //check if there is any content
  static private function hasContent(Node $link_node){
    return (!empty($link_node->field_links_link->uri)
            ||!empty($link_node->field_links_link_text->value)
            ||!empty($link_node->field_links_open_in_new_window->value)
            ||!empty($link_node->field_links_file_link_upload->value));
  }

  //return the base link for a links content type
  static private function getBaseLinkNode(Node $link_node){
    //no uri, base link node
    if(!empty($link_node->field_links_link->uri)){
      $url = Url::fromUri($link_node->field_links_link->uri);
      //if external, it is the base link
      if(!$url->isExternal()&&$url->isRouted()){
        $new_link = Node::load($url->getRouteParameters()['node']);
        //if the new_link isn't of type links, then link_node is base link
        if(!is_null($new_link)&&$new_link->getType() == 'links')
          $link_node = Self::getBaseLinkNode($new_link);
      }
    }
    //return base link
    return $link_node;
  }

  //purge links
  static private function purgeLinks(){
    $links = \Drupal::entityQuery('node')
        ->condition('type', 'links')
        ->execute();
    entity_delete_multiple('node', $links);
  }

  //transformation filter callback
  static private function isTransformation($string){
    return strpos($string, Self::METHOD_FILTER)!==false;
  }

  //returns transformation methods
  static function getTransformations(){
    return array_filter(get_class_methods(new static),__CLASS__ .'::isTransformation'); 
  }
  //fixes missing sp_entity_id for sso config
  static function sso_config_transformation(){
    $message = "SSO Config Fix SUCCESS";
    //get site and env variables all set up
    $site = getenv('AH_SITE_GROUP');
    $env = getenv('AH_SITE_ENVIRONMENT');
    $domain = $_SERVER['HTTP_HOST'];
    $domain_fragments = explode('.', $domain);
    $site_name = array_shift($domain_fragments);

    //testing on local
    if(empty($site))
      $site = 'creighton';
    if(empty($env))
      $env = '01local';

    $uri_prefix = $env;
    $env_prefix = '01';

    //if uri_prefix contains env_prefix, remove it
    if((strpos($env, $env_prefix) !== false))
      $uri_prefix = str_replace($env_prefix,"",$uri_prefix);

    //get acquias internal site id
    preg_match("/(?<=indaly)(.*)(?=$uri_prefix)/",\Drupal::service('settings')->get('file_public_path'),$matches,PREG_UNMATCHED_AS_NULL);
    //if there are no matches then something went wrong
    if(empty($matches)){
      $message = "Something Went Wrong: No Site ID found.";
      return $message;
    }

    //get the acquia site id
    $site_id = $matches[0];

    $sp_entity_id = "urn:acquia:acsf:saml:sp:creighton:$env:$site_id";
    $idp_entity_id = "https://www.{$uri_prefix}-creighton.acsitefactory.com/sso/saml2/idp/metadata.php";
    $idp_single_sign_on_service = "https://www.{$uri_prefix}-creighton.acsitefactory.com/sso/saml2/idp/SSOService.php";
    $idp_single_log_out_service = "https://www.{$uri_prefix}-creighton.acsitefactory.com/sso/saml2/idp/SingleLogoutService.php";

    //get the config factory
    $config = \Drupal::service('config.factory')->getEditable('samlauth.authentication');
    # Sets sp_entity_id, idp_entity_id, idp_single_sign_on_service, and idp_single_log_out_service for ACSF SSO into the selected site
    $config->set("sp_entity_id",$sp_entity_id)->save();
    $config->set("idp_entity_id",$idp_entity_id)->save();
    $config->set("idp_single_sign_on_service",$idp_single_sign_on_service)->save();
    $config->set("idp_single_log_out_service",$idp_single_log_out_service)->save();

    //log it
    \Drupal::logger('sso_config_transformation')->info("$message
    sp_entity_id $sp_entity_id
    idp_entity_id $idp_entity_id
    idp_single_sign_on_service $idp_single_sign_on_service
    idp_single_log_out_service $idp_single_log_out_service");

    //return a message
    return "$message
    <br>sp_entity_id $sp_entity_id
    <br>idp_entity_id $idp_entity_id
    <br>idp_single_sign_on_service $idp_single_sign_on_service
    <br>idp_single_log_out_service $idp_single_log_out_service";
  }


}