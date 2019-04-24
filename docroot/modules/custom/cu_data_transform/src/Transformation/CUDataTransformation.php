<?php
namespace Drupal\cu_data_transform\Transformation;

use Drupal\field\Entity\FieldStorageConfig;

class CUDataTransformation {
  //methods/functions must follow the <transform_type>_transformation
  CONST METHOD_FILTER = '_transformation';

  //transformation filter callback
  static function isTransformation($string){
    return strpos($string, Self::METHOD_FILTER)!==false;
  }
  //returns transformation methods
  static function getTransformations(){
    return array_filter(get_class_methods(new static),__CLASS__ .'::isTransformation'); 
  }
  static function paragraph_links_transformation(){
    //content types to convert links from
    //each contains the old and new field names
    $content_types = [
      'related_links' => [
        'old' =>'field_related_link',
        'new' =>'field_related_p_link'
      ],
      'featured_links'=> [
        'old' =>'field_featured_link',
        'new' =>'field_featured_p_link'
      ],
      'header_alert'=> [
        'old' =>'field_alert_link',
        'new' =>'field_header_alert_p_link'
      ],
      'news_spotlight'=> [
        'old' =>'field_new',
        'new' =>'field_news_spotlight_p_link'
      ],
      'profile'=> [
        'old' =>'field_profile_link',
        'new' =>'field_profile_p_link'
      ],
      'promo_box'=> [
        'old' =>'field_promo_link',
        'new' =>'field_promo_p_link'
      ],
      'quote_box'=> [
        'old' =>'field_quote_link',
        'new' =>'field_quote_p_link'
      ],
      'slide'=> [
        'old' =>'field_slide_link',
        'new' =>'field_slide_p_link'
      ],
    ];

    $log = '';
    $link_nodes = [];
    //iterate over content types
    foreach($content_types as $type => $field_names){
      //get all the node ids for this content type
      $nids = \Drupal::entityQuery('node')->condition('type',$type)->execute();
      //load all of those nodes
      $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
      //log the content type we are converting
      $log .= '<strong>Type: '.$type.'</strong>';
      //iterate over each of these nodes
      foreach($nodes as $entity ){
        //log all the things
        $log .= '<br><strong>Node: '.$entity->nid->value.' title: '.$entity->title->value.'</strong>';
        $log .= '<br><a href="'.$entity->url().'/edit" target="_blank">Node Link</a>';
        //if the field name exists for this entity
        if(count($entity->{$field_names['old']})>0){
          //paragraph array used to create a new paragraph 
          $paragraph_array = [];
          //for each link in the entity
          foreach($entity->{$field_names['old']} as $link){
            //load the link node
            $link_node = \Drupal\node\Entity\Node::load($link->target_id);
            //check that the link is not null
            if(!empty($link_node)){
              //array union, create ief_link fields for paragraph.
              $field_array = ['type' => 'ief_link']
              + (isset($link_node->field_links_link)?['field_internal_or_external_link' => $link_node->field_links_link]:[])
              + (isset($link_node->field_links_link)?['field_link_text' => $link_node->field_links_link_text]:[])
              + (isset($link_node->field_links_link)?['field_open_in_new_window' => $link_node->field_links_open_in_new_window]:[])
              + (isset($link_node->field_links_link)?['field_file_link' => $link_node->field_links_file_link_upload]:[]);
              //use field array to create the paragraph
              $paragraph = \Drupal\paragraphs\Entity\Paragraph::create($field_array);
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
              $log.='<br>Original Link nid: '.$link_node->nid->value;
              $log.=' Paragraph Link id: '. $paragraph->id();
              $log.=' Paragraph Link Revision id: '.$paragraph->getRevisionId().'<br>';
              //in an array for later deletion
              array_push($link_nodes,$link_node);
            }else{
              //just in case there's a link ref and somehow the link doesn;t exist
              $log .= '<br>Link does not Exist.<br>';
            }
          }
          if(!empty($paragraph_array))
            $entity->{$field_names['new']} = $paragraph_array;
          $entity->save();
        }else{
          //just in case there are no links to convert
          $log .= '<br>No Links to convert.<br>';
        }
      }
      //add some new breaks
      $log .= '<br><br>';
    }

    /** 
     * delete the original link content
    */
    foreach($link_nodes as $link_node)
      $link_node->delete();

    $log .= '<br>Links Converted: '.count($link_nodes).'<br><br>';;
    //actually log the log
    \Drupal::logger('paragraph_link_transformation')->info($log);
    return true;
  }
}