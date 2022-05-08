CONTENTS OF THIS FILE
---------------------

* Introduction
* Recommended modules
* Installation
* Configuration
* Extending


INTRODUCTION
------------
This module provides a live chat block, and uses the pusher api
https://pusher.com/channels/pricing

INSTALLATION
------------
Install this module via composer, to have the libraries it relies on
installed together.

CONFIGURATION
------------
After enabling the module, head to `<front>/admin/dru-chat/pusher-configs` and enter your Pusher
app (Channel) details as provided here `https://dashboard.pusher.com/apps/` ie app_id, key, secret,
cluster.
After this, place the dru chat block at the footer of your site.

EXTENDING
---------
* You can choose not to send certain messages via the DruChatEvent, see example
at Drupal\dru_chat\Examples\DruChatExampleSubscriber::druChatNewMessage;

* You can mutate the user list passed to the chat block too via event listener
see at Drupal\dru_chat\Examples\DruChatExampleSubscriber::druChatUserList;(Coming soon)

* Help contribute to guest user guest chat functionality see `modules/dru_chat_guest`
at the root of this folder
