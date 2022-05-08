/**
 * @file
 * Like and dislike icons behavior.
 */
(function ($, Drupal, drupalSettings) {

  'use strict';

  Drupal.behaviors.likeAndDislike = {
    attach: function(context, settings) {
      $('.vote-widget--like-and-dislike', context).once('like-and-dislike').each(function () {
        var $widget = $(this);
        $widget.find('.vote-like a').click(function() {
          var entity_id, entity_type;
          if (!$(this).hasClass('disable-status')) {
            entity_id = $(this).data('entity-id');
            entity_type = $(this).data('entity-type');
            likeAndDislikeService.vote(entity_id, entity_type, 'like', drupalSettings.likeLabel, drupalSettings.hide_like_label, drupalSettings.hide_like_count);
          }
        });
        $widget.find('.vote-dislike a').click(function() {
          var entity_id, entity_type;
          if (!$(this).hasClass('disable-status')) {
            entity_id = $(this).data('entity-id');
            entity_type = $(this).data('entity-type');
            likeAndDislikeService.vote(entity_id, entity_type, 'dislike', drupalSettings.dislikeLabel, drupalSettings.hide_like_label, drupalSettings.hide_like_count);
          }
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
