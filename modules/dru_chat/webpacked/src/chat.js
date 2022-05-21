import './scss/mainer.scss'
(function ($, Drupal, drupalSettings) {
  /**
   * @todo:: paginated scroll update for message div and userlist, update pagination
   * @type {Pusher | {Runtime, Options: Options, PresenceChannel, AuthorizerCallback: AuthorizerCallback, AuthorizerGenerator: AuthorizerGenerator, ConnectionManager, Members, Channel, Authorizer: Authorizer, AuthOptions: AuthOptions, readonly default: any}}
   */
  const Pusher = require('pusher-js')

  $(document).ready(function () {
    druChatInit()
  })


  function druChatInit() {

    const notifications = document.querySelector('.chat-controls .notifications')
    const chat_view = document.querySelector('.chat-controls .chat-state')
    const chat_controls = document.querySelector('#chat_block .chat-controls')
    const chat_block_view = document.querySelector('#chat_block #dru-chat-block')
    const user_list = document.querySelector('#dru-chat-block .user-list')
    const notification_info = document.querySelector('.chat-controls')
    const backBtn = chat_controls.querySelector('.user-list-back-btn')
    let receiver_id = '',
      pusher_cluster = '',
      pusher_app_key = '',
      current_id = '',
      userList = null

    backBtn.addEventListener('click', resetChatView)

    /**
     * Resets the chat view,
     * hides back button and shows
     * users list as it hides the
     * message listing too
     */
    function resetChatView(e) {
      e.stopPropagation()
      //toggleForResetChatView()
      let messageWrapper = chat_block_view.querySelector('.message-wrapper'),
        messageBox = chat_block_view.querySelector('.dru-chat-message-box'),
        userList = chat_block_view.querySelector('.user-list')

      // TODO from here!!

      // if userList is locked remove locked
      if (userList.classList.contains('locked')) {
        userList.classList.remove('locked')
      }

      messageWrapper.classList.toggle('no-show')
      messageBox.classList.toggle('no-show')
      userList.classList.toggle('no-show')
      backBtn.classList.toggle('no-show')
    }


    function toggleChatView(){
      // if for minimize view or maximize
      if (chat_view.classList.contains('chat-state')){

        chat_view.classList.toggle('closed')
        chat_view.classList.toggle('opened')
        chat_controls.classList.toggle('opened')
        // TODO:: control others from master div and remove these
        chat_block_view.classList.toggle('open-view')
      }
    }

    function notificationToolTip(){

    }

    chat_controls.addEventListener('click', toggleChatView)
    notifications.addEventListener('mouseover', notificationToolTip)

    const usersList = document.querySelector('#dru-chat-block .users')

    const messages = document.querySelector('#messages')
    const users = usersList.children

    Array.from(users).forEach(user => {
      user.addEventListener('click', showChats)
    })

    let totalUnread = document.querySelector('.chat-controls .notifications .total')

    let xhr = new XMLHttpRequest()


    pusher_app_key = drupalSettings.dru_chat.pusher_app_key
    pusher_cluster = drupalSettings.dru_chat.pusher_cluster

    //Pusher.logToConsole = true
    let pusher = new Pusher( pusher_app_key, {
      cluster: drupalSettings.dru_chat.pusher_cluster,
      authEndpoint: `${drupalSettings.dru_chat.presence_url}`,
      //auth: { headers: { "csrf_token": drupalSettings.dru_chat.token } },
    })

    let channel = pusher.subscribe('my-channel')
    // Presence


    // @todo:: https://github.com/pusher/pusher-js/issues/485
    let presenceChannel = pusher.subscribe("presence-my-channel")
    presenceChannel.bind("pusher:subscription_succeeded", () => {
      // For example
      //update_member_count(members.count);
      let members = presenceChannel.members

      members.each((member) => {
        // For example
        /*console.log('+++++++++++++++++++++++++++++++++++++++++++')
        console.log('+++++++++++++++++++++++++++++++++++++++++++')
        console.log(members.length)
        console.log(member)
        console.log('+++++++++++++++++++++++++++++++++++++++++++')
        console.log('+++++++++++++++++++++++++++++++++++++++++++')*/
        //add_member(member.id, member.info);
      });
    });
    // let count = presenceChannel.members.count
    presenceChannel.bind("pusher:subscription_error" , (error, data) => {
      /*console.log(error)
      console.log('++++++++++++++++ ERROR ++++++++++++++++++')
      console.log(data)*/
    });
    // end of presence

    channel.bind('dru-chat-event', function(data) {
      current_id = drupalSettings.dru_chat.current_id
      if (current_id === data.from) {
        document.querySelector(`[id="${data.to}"]`).click()
      } else if (current_id === data.to) {

        // update my view
        if (receiver_id === data.from) {
          document.querySelector(`[id="${data.from}"]`).click()
          // if receiver is selected reload the selected user..
        } else {

          // update total unread if this user is not active state
          totalUnread.innerText = parseInt(totalUnread.innerText) + 1

          let pending = document.querySelector(`[id="${data.from}"]`)
          // if receiver not selected, add unread notification for that user
          if (pending.querySelector('.pending') && pending.querySelector('.pending').innerText) {
            pending.querySelector('.pending').innerText = parseInt(pending.querySelector('.pending').innerText) + 1
          } else {
            const item = document.createElement('span')
            item.classList.add('pending')
            item.innerText = 1
            pending.prepend(item)
          }
        }
      }

    })


    function sendNewMessage(e) {
      //console.log(this)

      // We lock the user_list if not locked,
      // so it's not loaded when the no-show class
      // is toggled in showMessages(), will do better.
      userList = chat_block_view.querySelector('.user-list')
      if (!userList.classList.contains('locked')) {
        userList.classList.add('locked')
      }


      let msg = this.value
      // check if enter key is pressed and message and recever id not null
      if (e.keyCode === 13 && msg !== '' && receiver_id !== ''){

        // clear txt box
        this.value = ''
        let params = 'receiver_id=' + receiver_id + '&message=' + Drupal.checkPlain(msg)



        const url = drupalSettings.dru_chat.new_msg_url
        xhr.open('POST', url, true)
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
        xhr.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0")

        xhr.onprogress = function () {

        }

        xhr.onload = function () {

          if (this.status === 200) {
            scrollMessageList()
          }
        }

        xhr.onerror = function () {
        }

        xhr.send(params)
      }
    }

    function showChats() {
      receiver_id = this.id
      current_id = drupalSettings.dru_chat.current_id

      // ----------- update #chat-user-name ----------------
      document.querySelector('.chat-controls .chat-user-name')
        .innerText = this.querySelector('.name').innerText
      // ----------- end of update #chat-user-name ----------------
      // remove active class from prev if any
      let prev_active = document.querySelector('.users .active')
      if (prev_active) prev_active.classList.remove('active')

      let messages_div_hidden = document.querySelector('#dru-chat-block #messages')
      if (messages_div_hidden) messages_div_hidden.style.display = 'inline-block'

      // toggle messages display: none
      //this.classList.remove('active')
      // remove unread notification
      let notifications = document.querySelector(`[id="${this.id}"]`)
      if (notifications && notifications.querySelector('.pending')) {
        let item = notifications.querySelector('.pending')
        // update total unread before removing unread
        // update total unread if this user is not active state
        totalUnread.innerText = parseInt(totalUnread.innerText) - parseInt(item.innerText)

        item.remove()
      }
      this.classList.add('active')

      // OPEN - type, url/file, async
      // /dru-chat/messages/{user}
      //const url = 'https://api.github.com/users?name=mimi'
      const url = drupalSettings.dru_chat.msgs_url + receiver_id

      xhr.open('GET', url, true)
      xhr.setRequestHeader("Cache-Control", "no-cache, no-store, max-age=0")
      //xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded')
      // https://developer.mozilla.org/en-US/docs/Web/API/XMLHttpRequest/Using_XMLHttpRequest

      xhr.onprogress = function () {
        // console.log(xhr.readyState) // === 3
      }

      // readyState === 4 onload
      xhr.onload = function () {

        if (this.status === 200) {
          // 403: forbidden
          // 404: Not found
          messages.innerHTML = this.responseText

          // For mobile responsiveness
          // we need to hide the user list div, and
          // replace that with the message list,
          // also create a DOM element to undo this, ie
          // hide the message list and show the user listing

          let messageWrapper = chat_block_view.querySelector('.message-wrapper'),
            messageBox = chat_block_view.querySelector('.dru-chat-message-box'),
            userList = chat_block_view.querySelector('.user-list')

          // We don't need to toggle these everytime, remember this code,
          // is also fired when a user sends a new message, so we
          // check if user list is hidden/locked, and if it is,
          // do nothing to these classes

          messageWrapper.classList.toggle('no-show')
          messageBox.classList.toggle('no-show')

          // if userList is locked don't toggle backBtn and
          // userList.
          if (!userList.classList.contains('locked')) {
            userList.classList.toggle('no-show')
            backBtn.classList.toggle('no-show')
          }


          // end of

          // listener for message input
          const message = document.querySelector('[type="input"], [name="message"]')
          message.addEventListener('keyup', sendNewMessage)
          scrollMessageList()
        }
      }

      xhr.onerror = function () {
        //console.log('ERRor')
      }

      xhr.send()
    }
  }

  if (document.readyState === 'complete'){

  }

  // scroll message list
  function scrollMessageList(){
    const msgList = document.querySelector('.message-wrapper')
    // don't scroll if message-wrapper is hidden, ie
    // contains a class of no-show
    // todo debug essie
    //if (msgList.classList.contains('no-show')) return

    $('.message-wrapper').animate({
      scrollTop: msgList.scrollHeight
    }, 50)
  }
})(jQuery, Drupal, drupalSettings)
