<?php

namespace Drupal\simple_like_button\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CssCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\Html;
use Drupal\simple_like_button\Entity\SimpleLike;


/**
 * Class LikeForm.
 */
class LikeForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'like_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $current_entity = $this->getCurrentEntity();
    $logged_in = \Drupal::currentUser()->isAuthenticated();
    // If user is not logged in, or current page is no entity, return null.
    if(!$logged_in || !$current_entity){
      return null;
    }
    $entity = $current_entity->getEntityTypeId();
    $bundle = $current_entity->bundle();
    $entity_id = $current_entity->id();
    $uid = \Drupal::currentUser()->id();
    $like_status = $this->getLikeStatus($entity, $entity_id, $bundle, $uid);
    $like_count = $this->getLikeCount($entity, $entity_id, $bundle);
    $liked_class = is_numeric($like_status) ? t('J\'aime') : '';
    $button_text = is_numeric($like_status) ? $like_count. t(' . J\'aime')  : $like_count. t(' . J\'aime');
    $users_that_liked = ($like_count > 0) ? $this->getUsersThatLiked($entity, $entity_id, $bundle, $uid) : '';

    $form['entity'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $entity,
    ];
    $form['entity_id'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $entity_id,
    ];
    $form['bundle'] = [
      '#type' => 'hidden',
      '#required' => true,
      '#default_value' => $bundle,
    ];
    $form['actions'] = [
      '#prefix' => '<span class="'.$entity.$entity_id .' '.$liked_class.'"> ',
      '#type' => 'button',
      '#attributes' => array('class' => array($entity.$entity_id)),
      '#value' => $button_text,
      '#ajax' => [
        'callback' => '::submitLikeAjax',
        'disable-refocus' => true,
        'progress' => [
          'type' => 'none',
          ]
      ],
      '#suffix' => '</span>'
    ];

    if(!empty($users_that_liked)) {
      $form['users_that_liked'] = [
        '#type' => 'markup',
        '#weight' => '50',
        '#markup' => '<span class="users_that_liked">' . t('Aimé par: ') .$users_that_liked . '</span>',
      ];
    }
    $form['#attached']['library'][] = 'simple_like_button/like';
    return $form;
  }


  /**
   * Ajax callback to validate the email field.
   */
  public function submitLikeAjax(array &$form, FormStateInterface $form_state) {

    // Initiate.
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $entity = $form_state->getValue('entity');
    $entity_id = $form_state->getValue('entity_id');
    $bundle = $form_state->getValue('bundle');
    $like_count = $this->getLikeCount($entity, $entity_id, $bundle);
    $like_count = (empty($like_count)) ? 0 : $like_count;
    $like_status = $this->getLikeStatus($entity, $entity_id, $bundle, $uid);

    // Record exists, unlike, remove record.
    if(is_numeric($like_status)){
      \Drupal::database()->delete('simple_like')
        ->condition('entity', $entity, '=')
        ->condition('entity_id', $entity_id, '=')
        ->condition('bundle', $bundle, '=')
        ->condition('user_id', $uid, '=')
        ->execute();
      // Add ajax commands
      $css_you_liked = ['display' => 'none'];
      $response->addCommand(new CssCommand('.you_liked_' .$entity .$entity_id, $css_you_liked));
      $css = ['color' => '#9f9c9c'];
      $response->addCommand(new CssCommand('.' . $entity.$entity_id, $css));
      $new_count = strval($like_count - 1);
      $response->addCommand(new InvokeCommand('.' .$entity .$entity_id, 'val', [t('Like · ').$new_count]));
      $response->addCommand(new HtmlCommand('#like_count_'.$entity.$entity_id, $new_count));

    }
    // Record doesn't exists, Like, add record.
    elseif (empty($like_status)) {
      $simple_like = SimpleLike::create([
        'entity' => Html::escape($entity),
        'entity_id' => $entity_id,
        'bundle' => $bundle,
      ]);
      $simple_like->save();
      // Add ajax commands
      $response->addCommand(new HtmlCommand('.you_liked_'.$entity.$entity_id, 'Vous, '));
      $css = ['color' => '#0037fd'];
      $response->addCommand(new CssCommand('.' .$entity .$entity_id, $css));
      $css_you_liked = ['display' => 'initial'];
      $response->addCommand(new CssCommand('.you_liked_' .$entity .$entity_id, $css_you_liked));
      $new_count = strval($like_count + 1);
      $response->addCommand(new InvokeCommand('.' .$entity .$entity_id, 'val', [$new_count. t(' . J\'aime')]));
      $response->addCommand(new HtmlCommand('#like_count_'.$entity.$entity_id, $new_count));
    }
    // Wipe all messages, so on page refresh nothing comes up.
    \Drupal::messenger()->deleteAll();
    return $response;
  }

  /**
   * @param $entity
   * @param $entity_id
   * @param $bundle
   * @param $uid
   *
   * @return string
   */
  private function getUsersThatLiked($entity, $entity_id, $bundle, $uid){
    // Query users that liked.
    $query = \Drupal::database()->select('simple_like', 'sil');
    $query->addField('sil', 'user_id');
    $query->addField('ufd', 'name');
    $query->condition('sil.entity', $entity);
    $query->condition('sil.bundle', $bundle);
    $query->condition('sil.entity_id', $entity_id);
    $query->join('users_field_data','ufd','ufd.uid = sil.user_id');
    $users = $query->execute()->fetchAll();
    $total = count((array)$users);
    // Build users string
    $users_string = '' ;
    $liked_by_current = '';
    $i = 0;
    foreach ($users as $user){
      if($user->user_id == $uid){
        $liked_by_current = t('Vous, ');
        continue;
      }
      $users_string .= $user->name;
      if(++$i === $total) {
        $users_string .= '.';
      }else {
        $users_string .= ', ';
      }
    }
    return '<span class="you_liked you_liked_'.$entity.$entity_id.'">'.$liked_by_current .'</span>' .$users_string;
  }

  /**
   * @param $entity
   * @param $entity_id
   * @param $bundle
   * @param $uid
   * @return mixed
   */
  private function getLikeStatus($entity, $entity_id, $bundle, $uid){
    $query = \Drupal::database()->select('simple_like', 'sil');
    $query->addField('sil', 'id');
    $query->condition('sil.entity', $entity);
    $query->condition('sil.entity_id', $entity_id);
    $query->condition('sil.bundle', $bundle);
    $query->condition('sil.user_id', $uid);
    return $query->execute()->fetchField();
  }

  /**
   * @param $entity
   * @param $entity_id
   * @param $bundle
   * @return mixed
   */
  private function getLikeCount($entity, $entity_id, $bundle){
    $query = \Drupal::database()->select('simple_like', 'sil');
    $query->addField('sil', 'id');
    $query->condition('sil.entity', $entity);
    $query->condition('sil.entity_id', $entity_id);
    $query->condition('sil.bundle', $bundle);
    $query->condition('sil.status', 1);
    return $query->countQuery()->execute()->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We need this function, because interface requires it.
    // But nothing is needed here, it's all ajax above.
  }

  /**
   * Get current entity, if any.
   */
  private function getCurrentEntity(){
    $currentRouteParameters = \Drupal::routeMatch()->getParameters();
    foreach ($currentRouteParameters as $param) {
      if ($param instanceof \Drupal\Core\Entity\EntityInterface) {
        $entity = $param;
        return $entity;
      }
    }
    return NULL;
  }
}
