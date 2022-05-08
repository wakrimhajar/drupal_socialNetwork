<?php

namespace Drupal\simple_like_button\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'SimpleLikeButton' block.
 *
 * @Block(
 *  id = "simple_like_button",
 *  admin_label = @Translation("Simple Like Button"),
 * )
 */
class SimpleLikeButtonBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    // Get the like button.
    $like_button = \Drupal::formBuilder()->getForm(\Drupal\simple_like_button\Form\LikeForm::class);

    // Build block.
    $theme_vars = [
      'like_button' => $like_button,
    ];
    return [
      '#theme' => 'block_like_button',
      '#vars' => $theme_vars,
    ];
  }

}
