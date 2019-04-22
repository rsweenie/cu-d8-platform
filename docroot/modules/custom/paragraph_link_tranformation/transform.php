<?php

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
$log;
foreach($content_types as $type => $field_names){
  $nids = \Drupal::entityQuery('node')->condition('type',$type)->execute();
  $nodes =  \Drupal\node\Entity\Node::loadMultiple($nids);
  foreach($nodes as $entity ){
    $log = '<strong>Type: '.$type.'</strong>';
    $log .= '<br><a href="'.$entity->url().'">Verify Node</a>';
    $log .= '<br>Node: '.$entity->nid->value.' title: '.$entity->title->value;

    $paragraph_array = [];
    foreach($entity->{$field_names['old']} as $link){
      
      $link_node = \Drupal\node\Entity\Node::load($link->target_id);

      $paragraph = \Drupal\paragraphs\Entity\Paragraph::create([
        'type' => 'ief_link',   // paragraph type machine name
        'field_internal_or_external_link' => $link_node->field_links_link,
        'field_link_text' => $link_node->field_links_link_text,
        'field_open_in_new_window' => $link_node->field_links_open_in_new_window,
        'field_file_link' => $link_node->field_links_file_link_upload,
      ]);
      $paragraph->save();
      array_push($paragraph_array ,
        [
            'target_id' => $paragraph->id(),
            'target_revision_id' => $paragraph->getRevisionId(),
        ]);
      $log.='<br>Original Link nid: '.$link_node->nid->value;
      $log.=' Paragraph Link id: '. $paragraph->id();
      $log.=' Paragraph Link Revision id: '.$paragraph->getRevisionId().'<br>';
    }

   if(!empty($paragraph_array))
      $entity->{$field_names['new']} = $paragraph_array;
    $entity->save();
  }
}

\Drupal::logger('paragraph_link_translation')->info($log);
die();
