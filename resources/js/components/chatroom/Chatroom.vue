<template>
  <div class="container">
    <!-- ALERT -->
    <div class="alert-container position-absolute" style="top: 10px; right: 10px;">
      <div v-if="status.length > 0">
        <div
        v-for="(status, index) in status"
        :key="index"
          class="alert alert-light text-light bg-dark border-0 alert-dismissible fade show"
          role="alert">
            {{ status.message }}
          <button type="button" class="close text-light" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
      </div>
    </div>

    <div id="app" class="app">

      <!-- LEFT SECTION -->
      <section id="main-left" class="main-left">
        <!-- header -->
        <div id="header-left" class="header-left">
          <div class="header-left-icon-left">
            <b-icon @click="openTab('recent-chat')" icon="chat-square-text-fill" class="h4" variant="light" title="Recent chat"></b-icon>
            <b-icon @click="openTab('friend-list')" icon="people-fill" class="h4" variant="secondary" title="Friend list"></b-icon>
            <b-icon @click="openTab('add-friend')" icon="person-plus-fill" class="h4" variant="secondary" title="Add a friend"></b-icon>
          </div>
          <div class="header-left-icon-right">
            <div class="position-relative">
              <span 
                v-if="notifications.length > 0"
                class="badge badge-danger position-absolute notification-badge">{{ notifications.length }}</span>
              <a id="notification-list" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon icon="bell-fill" class="h5 my-0" variant="secondary"></b-icon></a>
              <div class="dropdown-menu bg-dark notification-list" aria-labelledby="notification-list">
                <!-- Friend request -->
                <div v-if="notifications.length > 0">
                  <div
                    class="list-group-item bg-dark py-2"
                    v-for="(notification, index) in notifications"
                    :key="index">
                    <div v-if="notification.notif.type == 'friend-request'">
                      <h6 class="text-secondary">Friend request</h6>
                      <div class="friend-request">
                        <div class="profile friend-request-photo">
                          <img :src="`avatar/${notification.sender.photo}`" alt="">
                        </div>
                        <span class="text-light">{{ notification.sender.name }}</span>
                        <a @click="addFriend(notification.sender.id, notification.notif.id, true)" class="badge badge-success">Accept</a>
                        <a @click="addFriend(notification.sender.id, notification.notif.id, false)" class="badge badge-danger">Decline</a>
                      </div>
                    </div>

                    <div v-if="notification.notif.type == 'friend-request-accepted'">
                      <h6 class="text-secondary"><a @click="markAsRead(notification.notif.id, 'notification')" class="badge badge-dark bg-dark">&times;</a> {{ notification.notif.message }}</h6>
                    </div>

                  </div>
                </div>

                <div v-else>
                  <h6 class="text-secondary list-group-item bg-dark p-2">You don't have any notification.</h6>
                </div>
              </div>
            </div>

            <div class="position-relative">
              <a class="dropdown-toggle" id="dropdownBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon icon="three-dots-vertical" class="h4 my-0" variant="secondary"></b-icon></a>
              <div id="recent-chat-option" class="dropdown-menu bg-dark recent-chat-option" aria-labelledby="dropdownBtn">
                <a @click="deleteAllChats()" class="list-group-item bg-dark text-light text-decoration-none">Delete all chats</a>
              </div>
            </div>
          </div>
        </div>


        <!-- chat list -->
        <div id="chat-list" class="chat-list">
          <!-- search bar -->
          <div class="search-bar">
            <input
              @keyup="search()"
              v-model="query"
              type="text"
              class="form-control"
              :placeholder="placeholder">
          </div>

          <!-- 1st Tab - Recent chat -->
          <div id="recent-chat" v-if="tab === 'recent-chat'">
            <div v-if="recent_chats && this.query_recent_chats.length == 0">
              <div
                class="recent-chat"
                v-for="(friend, index) in recent_chats"
                :key="index"
                @click="startChat(friend.friend.id)">
                <!-- photo -->
                <div class="profile friends-photo">
                  <img :src="`avatar/${friend.friend.photo}`" alt="">
                </div>
                
                <div class="friends-credent">
                  <!-- name -->
                  <span class="friends-name">{{ friend.friend.name }}</span>
                  <!-- last message -->
                  <span class="friends-message">{{ friend.chat.message }}</span>
                </div>
                <!-- notification badge -->
                <span class="badge notif-badge" v-show="friend.unread > 0">{{ friend.unread }}</span>
              </div>
            </div>

            <div v-if="recent_chats && this.query_recent_chats.length > 0">
              <div
                class="recent-chat"
                v-for="(friend, index) in query_recent_chats"
                :key="index"
                @click="startChat(friend.friend.id)">
                <!-- photo -->
                <div class="profile friends-photo">
                  <img :src="`avatar/${friend.friend.photo}`" alt="">
                </div>
                
                <div class="friends-credent">
                  <!-- name -->
                  <span class="friends-name">{{ friend.friend.name }}</span>
                  <!-- last message -->
                  <span class="friends-message">{{ friend.chat.message }}</span>
                </div>
                <!-- notification badge -->
                <span class="badge notif-badge" v-show="friend.unread > 0">{{ friend.unread }}</span>
              </div>
            </div>
          </div>

          <!-- 2nd tab - friend list -->
          <div id="add-friend" v-if="tab === 'friend-list'">
            <div v-if="friends && query_friend_list.length == 0">
              <div
                class="add-friend"
                v-for="(friend, index) in friends"
                :key="index">
                <!-- photo -->
                <div class="profile friend-list-photo">
                  <img :src="`avatar/${friend.photo}`" alt="">
                </div>

                <h6 class="text-light m-0">{{ friend.name }}</h6>
                <div class="position-relative">
                  <a id="toggleFriendOpt" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon icon="three-dots-vertical" class="h5 my-0" variant="secondary"></b-icon></a>
                  <div class="dropdown-menu bg-dark" aria-labelledby="toggleFriendOpt">
                    <a @click="startChat(friend.id)" class="list-group-item bg-dark text-light text-decoration-none">Chat</a>
                    <a @click="unfriend(friend.id)" class="list-group-item bg-dark text-danger text-decoration-none">Unfriend</a>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="friends && query_friend_list.length > 0">
              <div
                class="add-friend"
                v-for="(friend, index) in query_friend_list"
                :key="index">
                <!-- photo -->
                <div class="profile friend-list-photo">
                  <img :src="`avatar/${friend.photo}`" alt="">
                </div>

                <h6 class="text-light m-0">{{ friend.name }}</h6>
                <div class="friend-list-option-btn position-relative">
                  <a id="toggleFriendOpt" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon icon="three-dots-vertical" class="h5 my-0" variant="secondary"></b-icon></a>
                  <div class="dropdown-menu bg-dark" aria-labelledby="toggleFriendOpt">
                    <a @click="startChat(friend.id)" class="list-group-item bg-dark text-light text-decoration-none">Chat</a>
                    <a @click="unfriend(friend.id)" class="list-group-item bg-dark text-danger text-decoration-none">Unfriend</a>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- 3rd tab - Add a friend -->
          <div v-if="tab === 'add-friend'">
            <div id="add-friend" class="add-friend" v-if="search_result && search_result.status">
              <!-- photo -->
              <div class="profile friend-list-photo">
                <img :src="`avatar/${search_result.user.photo}`" alt="">
              </div>

              <h6 class="text-light m-0">{{ search_result.user.name }}</h6>
              <b-icon @click="sendFriendRequest(search_result.user.id)" icon="person-plus" id="add-friend-btn" class="h5 my-0" variant="light"></b-icon>
            </div>

            <div v-if="search_result && !search_result.status">
              <h6 class="text-light text-center my-5">User not found.</h6>
            </div>
          </div>
        </div>

        <!-- self-profile -->
        <div id="self-info" class="self-info">
          <!-- photo -->
          <div class="profile your-photo">
            <img :src="`avatar/${user.photo}`" alt="">
          </div>
          <!-- name -->
          <h4 class="name your-name my-0">{{ user.name }}</h4>
          <!-- setting btn -->
          <div class="position-relative dropup">
            <a id="toggleSettingOpt" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon style="width: 1.75rem; height: 1.75rem;" icon="gear-fill" class="h1 gear my-0" variant="secondary"></b-icon></a>
            <div class="dropdown-menu bg-dark" aria-labelledby="toggleSettingOpt">
              <a class="list-group-item bg-dark text-light text-decoration-none" data-toggle="modal" data-whatever="@mdo" data-target="#editProfile">Edit Profile</a>
              <a 
                href="/logout"
                class="list-group-item bg-dark text-light text-decoration-none"
                onclick="event.preventDefault();
                        document.getElementById('logout-form').submit();">
              Logout</a>
            </div>
          </div>

          <!-- POP UP MODAL FOR EDITING PROFILE -->
          <div class="modal fade" id="editProfile" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Profile</h5>
                  <button type="button" class="close text-danger" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                  </button>
                </div>
                <form @submit.prevent="updateProfile()" action="" method="POST" enctype="multipart/form-data" id="form-modal">
                <div class="modal-body">
                    <div class="form-group">
                      <label for="recipient-name" class="col-form-label">Your name</label>
                      <input type="text" class="form-control" name="user_name" id="user_name" :value="user.name">
                    </div>
                    <div class="form-group">
                      <label for="message-text" class="col-form-label">Your Profile Picture</label>
                      <input type="hidden" name="old_photo" :value="user.photo">
                      <input type="file" name="new_photo" class="d-block" id="new_user_photo">
                    </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  <button type="submit" class="btn btn-success">Save changes</button>
                </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </section>
      
      


      <!-- RIGHT SECTION -->

      <section id="main-right" class="main-right">
        <!-- header -->
        <div id="header-right" class="header-right">
          <!-- profile pict -->
          <div id="header-img" class="profile header-img" v-if="chatroom_data">
            <img :src="`avatar/${chatroom_data.user.photo}`" alt="">
          </div>

          <!-- name -->
          <h4 class="name friend-name my-0" v-if="chatroom_data">{{ chatroom_data.user.name }}</h4>

          <!-- some btn -->
          <div class="some-btn" v-if="chatroom_data">
            <b-icon icon="camera-video-fill" class="h5 text-disabled" variant="dark" title="Not available."></b-icon>
            <b-icon icon="telephone-fill" class="h5 text-disabled" variant="dark" title="Not available."></b-icon>
            <div class="position-relative dropleft">
              <a class="dropdown-toggle" id="toggleChatBtn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><b-icon icon="three-dots-vertical" class="h4 my-0" variant="dark"></b-icon></a>
              <div class="dropdown-menu bg-dark" aria-labelledby="toggleChatBtn">
                <a @click="clearChat(chatroom_data.room_id)" class="list-group-item bg-dark text-light text-decoration-none">Clear chat</a>
                <a @click="deleteChatroom(chatroom_data.room_id)" class="list-group-item bg-dark text-light text-decoration-none">Delete chatroom</a>
              </div>
            </div>
          </div>
        </div>

        <!-- chat area -->
        <div v-if="!chatroom_data">
          <div class="jumbotron text-center" style="height: 100%;">
            <h1 class="text-secondary">Start a new chat</h1>
          </div>
        </div>

        <div v-if="chatroom_data" id="chat-area" class="chat-area" >
          <div v-for="(chat, index) in chatroom_data.messages" :key="index">

            <!-- chat content -->
            <!-- FRIENDS CHAT TEMPLATE -->
            <div class="friends-chat" v-if="chat.sender.id != user.id">
              <div class="profile friends-chat-photo">
                <img :src="`avatar/${chat.sender.photo}`" alt="">
              </div>
              <div class="friends-chat-content">
                <p class="friends-chat-name">{{ chat.sender.name }}</p>
                <p class="friends-chat-balloon text-break">{{ chat.message }}</p>
                <h5 class="chat-datetime">{{ formattedTime(chat.time) }}</h5>
              </div>
            </div>

            
            <!-- YOUR CHAT TEMPLATE -->
            <div class="your-chat" v-if="chat.sender.id == user.id">
              <p class="your-chat-balloon text-break">{{ chat.message }}</p>
              <p class="chat-datetime"><b-icon icon="check-all"></b-icon> {{ formattedTime(chat.time) }}</p>
            </div>

          </div>
        </div>

        <!-- typing area -->
        <div id="typing-area" class="typing-area">
          <!-- input form -->
          <textarea
            id="type-area"
            class="type-area"
            rows="1"
            v-model="message"
            @keyup="sendMessage()"
            v-if="chatroom_data"
            placeholder="Type something...">
          </textarea>
          
          <!-- send btn -->
          <b-icon @click="sendMessage()" icon="reply-fill" class="h1 send-btn" variant="primary" v-if="chatroom_data"></b-icon>
        </div>
      </section>
    </div>

    <div id="creator" class="creator">
      <p>&copy; {{ new Date().getFullYear() }} | Created by <a href="https://github.com/iqbaltaufiq" class="text-secondary font-weight-bold text-decoration-none">Iqbal Taufiq</a> </p>
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      user: this.userprop,
      tab: 'recent-chat',
      message: '',
      query: null,
      search_result: null,
      placeholder: 'Search in recent chats...',
      friends: null,
      recent_chats: [],
      query_recent_chats: [],
      query_friend_list: [],
      notifications: [],
      chatroom_data: null,
      status: []
    }
  },
  props: {
    userprop: Object
  },
  methods: {
    runThisUserEchoListener () {
      Echo.private(`user.${this.user.id}`)
      .listen('ReceiveMessage', (e) => {

        // cek apakah ada di recent chats
        const isExist = this.recent_chats.filter(recent => recent.friend.id == e.payload.user.id);

        if (!isExist.length) {
          this.recent_chats.unshift({
            chat: { message: e.payload.message },
            friend: e.payload.user,
            unread: 1
          });
        } else {
          // put this user into the top in recent chat list
          this.addToFirstIndexInRecentChats(e.payload.user, e.payload.message);
  
          // increment the unread property value for the badge
          this.incrementUnreadMessages(e.payload.user.id);
        }


        console.log('Ini dari private channel');
        console.log(e);
      })
      .listen('SendNotification', (e) => {
        if (e.payload.notification.type == 'friend-request') {
          this.notifications.push({
            notif: e.payload.notification,
            sender: e.payload.sender
          });
        }

        if (e.payload.notification.type == 'friend-request-accepted') {
          this.notifications.push({
            notif: e.payload.notification,
            sender: e.payload.sender
          });
        }

        console.log('this is from sendnotification event.');
        console.log(e);
      });
    },
    openTab (tab) {
      const leftHeaderButton = document.querySelectorAll('.header-left-icon-left > .b-icon');
      leftHeaderButton.forEach(btn => {
        btn.classList.replace('text-light', 'text-secondary');
      });

      if (tab == 'recent-chat') {
        this.placeholder = 'Search in recent chats...';
        leftHeaderButton[0].classList.replace('text-secondary', 'text-light');
        // reset the query data for other tabs
        this.query_friend_list = [];
        this.search_result = null;
      }

      if (tab == 'friend-list') {
        this.placeholder = 'Search in friend list...';
        // reset the query data for other tabs
        leftHeaderButton[1].classList.replace('text-secondary', 'text-light');
        this.query_recent_chats = [];
        this.search_result = null;
      }

      if (tab == 'add-friend') {
        this.placeholder = 'Press enter to search...';
        // reset the query data for other tabs
        leftHeaderButton[2].classList.replace('text-secondary', 'text-light');
        this.query_recent_chats = [];
        this.query_friend_list = [];
      }

      this.query = null;
      return this.tab = tab
    },
    search () {
      switch (this.tab) {
        case 'recent-chat':
          this.query_recent_chats = this.recent_chats.filter(recent => {
            return recent.friend.name.search(new RegExp(this.query, "i")) + 1;
          });
          break;
        case 'friend-list':
          this.query_friend_list = this.friends.filter(friend => {
            return friend.name.search(new RegExp(this.query, "i")) + 1;
          });
          break;
        case 'add-friend':
          if (event.key == 'Enter') {
            // chech wheter if the input has any non-digit value (Not A Number)
            const hasNaN = this.query.match(/[^0-9]/);
            if (hasNaN) {
              this.setAlert('User ID does not support non-digit character.');
              return false;
            }

            if (this.query && !hasNaN) {
              this.findUser(this.query);
            }
          }
          break;
        default:
          console.log('Search error.');
      }
    },
    findUser (id) {
      axios.get(`/find/${id}`)
      .then((res) => {
        this.search_result = res.data;
      }).catch((err) => {
        console.log(err);
      });
    },
    sendFriendRequest (id) {
      axios.post(`/friendreq/${id}`, {
        sender_id: this.user.id
      })
      .then((res) => {
        this.setAlert(res.data.status);
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    addFriend (id, notification_id, acceptOrDecline) {
      axios.post('/friend', {
        target_id: id,
        notification_id: notification_id,
        accept: acceptOrDecline
      })
      .then((res) => {
        if (!res.data.error) {
          this.friends.push(res.data);
        }
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });

      this.notifications = this.notifications.filter(notif => notif.notif.id != notification_id);
    },
    fetchAllNotifications () {
      axios.get('/notif')
      .then((res) => {
        this.notifications = res.data.error ? [] : res.data;

        console.log('this is from fetchAllNotifications()');
        console.log(this.notifications);
      }).catch((err) => {
        console.log(err);
      });
    },
    fetchAllFriends () {
      axios.get('/friend')
      .then((res) => {
        this.friends = res.data;
        // console.log(this.friends);
      }).catch((err) => {
        console.log(res);
      });
    },
    fetchAllRecentChats () {
      axios.get('/chats')
      .then((res) => {
        res.data.forEach(friend => {
          this.recent_chats.push(friend);
        });

        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    startChat (id) {
      if (this.chatroom_data) {
        Echo.leave(`chat.${this.chatroom_data.room_id}`);
        this.chatroom_data = null;
      }

      this.resetUnreadMessages(id);

      axios.post(`/chat/${id}`, {
        currentISOtime: new Date().toISOString()
      })
      .then((res) => {
        this.chatroom_data = {
          room_id: res.data.room_id,
          user: res.data.user,
          messages: res.data.messages
        }

        if (!res.data.exist) {
          this.recent_chats.unshift({
            chat: {
              room_id: res.data.room_id,
              message: ''
            },
            friend: res.data.user,
            unread: 0
          });
        } 
        console.log(res);

        Echo.join(`chat.${res.data.room_id}`)
        .here((users) => {
          console.log(users);
        })
        .joining((user) => {
          console.log(user.name + ' has joined.');
        })
        .leaving((user) => {
          console.log(user.name + ' has left.');
        })
        .listen('SendChat', (e) => {
          this.chatroom_data.messages.push({
            message: e.payload.message,
            sender: e.payload.user,
            time: e.payload.created_at
          });

          this.addToFirstIndexInRecentChats(e.payload.user, e.payload.message);

          setTimeout(() => {
            this.resetUnreadMessages(e.payload.user.id);
          }, 500);

          this.markAsRead(e.payload.id, 'chat');

          console.log(e);
        });

        console.log(this.chatroom_data);

        return res;
      })
      .catch((err) => {
        console.log(err);
      });
    },
    sendMessage () {

      if (!event.shiftKey && event.key == 'Enter') {
        // UNTUK SEMENTARA
        if (this.message == '' || this.message == null) {
          alert('Pesan tidak boleh kosong!!');
          return false;
        }
  
        this.chatroom_data.messages.push({
          sender: this.user,
          message: this.message,
          time: new Date().toISOString()
        });
  
        this.addToFirstIndexInRecentChats(this.chatroom_data.user, this.message);
  
        console.log(this.recent_chats);
  
        axios.post('/send', {
          message: this.message,
          room_id: this.chatroom_data.room_id
        })
        .then((res) => {
          console.log(res);
        }).catch((err) => {
          console.log(err);        
        });
  
        this.message = '';
      }
    },
    addToFirstIndexInRecentChats (sender, message) {
      
      this.recent_chats.map(recent => {
        if (recent.friend.id == sender.id) {
          recent.chat.message = message;
          recent.chat.created_at = new Date().toISOString();
          recent.chat.updated_at = new Date().toISOString();
        }
      });

      // user yang mengirim pesan
      const recentlyOpenedUser = this.recent_chats.filter(recentChat => {
        return recentChat.friend.id == sender.id;
      });

      // hilangkan sender dari daftar recent_chats
      this.recent_chats = this.recent_chats.filter(recentChat => {
        return recentChat.friend.id != sender.id;
      });

      // tambahkan chat baru ke recent_chats di index pertama
      this.recent_chats.unshift(recentlyOpenedUser[0]);
    },
    incrementUnreadMessages (user_id) {
      /**
       * increment the number of unread messages this user has.
       * The number will be shown on the badge in the recent chat list.
       */

      this.recent_chats.map(recent => {
        if (recent.friend.id == user_id) recent.unread++;
      });
    },
    resetUnreadMessages (user_id) {
      this.recent_chats.forEach(recent => {
        if (recent.friend.id == user_id) recent.unread = 0;
      });
    },
    markAsRead (id, model) {
      axios.put('/read', {
        target_id: id,
        target_model: model
      })
      .then((res) => {
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });

      if (model == 'notification') {
        this.notifications = this.notifications.filter(notif => notif.notif.id != id);
      }
    },
    clearChat (room_id) {
      this.chatroom_data.messages = null;

      axios.post(`/clear`, {
        room_id: room_id,
        csrf_token: document.querySelector("meta[name='csrf-token']").getAttribute('content')
      })
      .then((res) => {
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    deleteChatroom (room_id) {
      this.recent_chats = this.recent_chats.filter(recent => recent.chat.room_id != room_id);
      Echo.leave(`chat.${room_id}`);
      this.chatroom_data = null;

      axios.delete('/deletechatroom', {
        data: {
          room_id: room_id,
          csrf_token: document.querySelector("meta[name='csrf-token']").getAttribute('content')
        }
      })
      .then((res) => {
        this.setAlert(res.data.status);
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    deleteAllChats () {
      this.recent_chats = [];

      if (this.chatroom_data) {
        Echo.leave(`chat.${this.chatroom_data.room_id}`);
        this.chatroom_data = null;
      }
      
      axios.post('/clearall', {
        csrf_token: document.querySelector("meta[name='csrf-token']").getAttribute('content')
      })
      .then((res) => {
        this.setAlert(res.data.status);
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    unfriend (id) {
      this.friends = this.friends.filter(friend => friend.id != id);
      this.recent_chats = this.recent_chats.filter(friend => friend.friend.id != id);

      if (this.chatroom_data) {
        if (this.chatroom_data.user.id = id) {
          this.chatroom_data = null;
        }
      }

      axios.post('/unfriend', {
        target: id,
        csrf_token: document.querySelector("meta[name='csrf-token']").getAttribute('content')
      })
      .then((res) => {
        this.setAlert(res.data.status);
        console.log(res);
      }).catch((err) => {
        console.log(err);
      });
    },
    scrollChat () {
      let element = document.getElementById('chat-area');
      element.scrollTop = element.scrollHeight;
    },
    updateProfile () {
      const form = document.querySelector('#form-modal');

      let data = new FormData(form);
      console.log(data);
      axios({
        method: 'post',
        url: '/updateprofile',
        data: data,
        headers: {
          "Content-Type" : "multipart/form-data"
        }
      })
      .then((res) => {
        this.user = res.data.user;
        this.setAlert(res.data.status);
      })
      .catch((err) => {
        console.log(err);
      });
    },
    setAlert (message) {
      this.status.push({ message });
    },
    formattedTime (currentISOtime) {
      const timestamp = new Date(currentISOtime);
      const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thru', 'Fri', 'Sat'];
      const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

      const addZeroOffset = timeObject => {
        if (timeObject < 10) return '0' + timeObject;

        return timeObject;
      }

      const timeformat = `${days[timestamp.getDay()]}, ${months[timestamp.getMonth()]} ${addZeroOffset(timestamp.getDate())} | ${addZeroOffset(timestamp.getHours())}:${addZeroOffset(timestamp.getMinutes())}`;
      return timeformat;
    }
  },
  mounted () {
    this.fetchAllFriends();
    this.fetchAllRecentChats();
    this.fetchAllNotifications();
    this.runThisUserEchoListener();
  },
  updated () {
    if (this.chatroom_data != null) {
      this.scrollChat();
    }
  }
}
</script>

<style scoped>
.b-icon {
  cursor: pointer;
}

.text-disabled {
  cursor: not-allowed;
}

.dropdown-toggle {
  padding: 0;
  margin: 0;
}
.dropdown-toggle::before, .dropdown-toggle::after {
  content: none;
}

.dropdown-menu {
  padding: 0;
  border: none;
  min-width: unset;
  width: max-content;
}

.alert-container {
  max-height: 250px;
  overflow-y: hidden;
}

.app {
  border: 2px solid #031426;
  width: 95%;
  margin: 5px auto;
  display: grid;
  grid-template-columns: 1fr 2fr;
  grid-auto-rows: minmax(500px, calc(100vh - 100px));
}

.profile {
  /* border: 1px solid gold; */
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  border-radius: 50%;
  cursor: pointer;
}

.profile img {
  object-fit: cover;
  width: inherit;
  height: inherit;
}

.main-left {
  background: #031426;
  display: grid;
  grid-template-rows: 1fr 6fr 1fr;
  grid-auto-flow: row;
}

.header-left {
  /* border: 1px solid salmon; */
  padding: 0px 20px;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 1fr 4fr;
  box-shadow: 0px 2px 2px rgba(0, 0, 0, 0.4);
}

.header-left-icon-left {
  /* border: 1px solid cyan; */
  display: grid;
  grid-template-columns: repeat(3, 1fr);
}

.header-left-icon-left > * {
  /* border: 1px solid maroon; */
  align-self: center;
  margin: 0px 5px;
}

.header-left-icon-left > .h4:last-child {
  margin-right: 0px;
}

.header-left-icon-right {
  /* border: 1px solid yellow; */
  display: grid;
  grid-template-columns: 1fr 1fr;
  place-self: center end;
}

.header-left-icon-right > * {
  /* border: 1px solid maroon; */
  place-self: center end;
  margin: 0px 5px;
}

.header-left-icon-right > div > .list-group {
  width: max-content;
}

.recent-chat-option {
  right: 50%;
}

.notification-badge {
  display: inline;
  bottom: calc(50% + 5px);
  left: 50%;
}

.notification-list {
  left: 50%;
  max-height: 350px;
  overflow-y: auto;
}

.friend-request {
  display: grid;
  grid-template-columns: 30px 2fr 1fr 1fr;
  gap: 0px 5px;
  align-items: center;
}

.friend-request-photo {
  place-self: center;
  width: 25px;
  height: 25px;
}

.search-bar {
  padding: 5px 10px;
}

.chat-list {
  /* border: 1px solid cyan; */
  overflow-y: auto;
}

.chat-list::-webkit-scrollbar {
  width: 0px;
}

.recent-chat {
  /* border: 1px solid yellow; */
  padding: 5px 10px;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 1fr 4fr 1fr;
  gap: 0px 5px;
  transition: 0.2s linear;
  cursor: pointer;
}

.recent-chat:hover, .friend-list:hover, .add-friend:hover {
  background: #041a31;
}

.friend-list > *, .add-friend > * {
  cursor: pointer;
}

.list-group-item {
  padding: 3px 10px;
  cursor: pointer;
  /* font-size: 0.8em; */
}

.friends-photo {
  width: 45px;
  height: 45px;
  place-self: center;
}

.friends-credent {
  /* border: 1px solid magenta; */
  /* padding: 5px; */
  display: grid;
  grid-auto-flow: row;
  grid-template-rows: 1fr 1fr;
  place-items: center start;
}

.friends-name, .friends-message {
  width: 100%;
  font-weight: 600;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;;
}

.friends-name {
  color: white;
  place-self: end start;
  font-size: 1.25em;
  /* border: 1px solid magenta; */
}

.friends-message {
  /* border: 1px solid blanchedalmond; */
  color: #777;
  font-size: 0.85em;
  place-self: start;
}

.friend-list-photo {
  height: 45px;
  width: 45px;
}

.add-friend {
  display: grid;
  padding: 5px 20px;
  grid-template-columns: 1fr 4fr 1fr;
  gap: 0px 5px;
  align-items: center;
}

.add-friend > :last-child {
  place-self: center;
}

.notif-badge {
  place-self: center;
  background: white;
  color: #555;
}

.self-info {
  /* border: 1px solid magenta; */
  padding: 5px 10px;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 1fr 4fr 1fr;
  align-items: center;
  background: #041b33;
  box-shadow: 0px -3px 5px rgba(0, 0, 0, 0.4);
}

.your-photo {
  height: 45px;
  width: 45px;
  place-self: center end;
}

.your-name {
  /* border: 5px solid bisque; */
  color: white;
  margin-left: 20px;
  place-self: center start;
}

.gear {
  transition: 0.2s linear;
}

.gear:hover {
  transform: rotate(30deg);
}

/* END OF LEFT SECTION */
/* ################################################## */
/* START OF RIGHT SECTION */

.main-right {
  background: #efefef;
  display: grid;
  grid-auto-flow: row;
  grid-template-rows: 1fr 6fr 1fr;
}

.header-right {
  /* border: 5px solid greenyellow; */
  padding: 5px;
  background: white;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 1fr 6fr 2fr;
  place-items: center;
  box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.4);
}

.header-img {
  height: 45px;
  width: 45px;
  place-content: center start;
  place-self: center end;
}

.name {
  font-size: 1.2em;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.friend-name {
  /* border: 5px solid violet; */
  place-self: center start;
  margin-left: 20px;
  color: #485373;
}

.some-btn > * {
  place-items: center end;
  display: inline-block;
  margin: 0px 10px;
}

.chat-area {
  /* border: 5px solid lightseagreen; */
  overflow-y: auto;
  padding-top: 10px;
  padding-bottom: 10px;
}

.chat-area::-webkit-scrollbar {
  width: 0;
}

.friends-chat {
  /* border: 3px solid beige; */
  padding-top: 5px;
  padding-bottom: 5px;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 1fr 9fr;
}

.friends-chat-photo {
  /* border: 3px solid orchid; */
  height: 40px;
  width: 40px;
  place-self: start end;
  margin: 0px 5px;
}

.friends-chat-content {
  /* border: 3px solid red; */
  display: grid;
  grid-auto-flow: row;
  grid-template-rows: 1fr min-content min-content;
  place-items: start;
}

.friends-chat-name {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #485373;
  font-size: 1em;
  margin: 0px 0px 3px 0px;
  font-weight: bold;
}


.friends-chat-balloon{
  /* border: 3px solid navy; */
  max-width: 90%;
  padding: 10px 20px;
  margin: 0;
  min-width: min-content;
  border-radius: 0px 20px 20px 20px; /* TL-TR-BR-BL*/
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #485373;
  background: white;
  font-size: 0.9em;
  word-break: break-word;
  white-space: pre-line;
}

.chat-datetime {
  /* border: 3px solid yellow; */
  margin: 3px 10px;
  font-size: 0.75em;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: #aaa;
}

.your-chat {
  /* border: 3px solid chocolate; */
  display: grid;
  padding-top: 5px;
  padding-bottom: 5px;
  padding-right: 10px;
  grid-auto-flow: row;
  grid-template-rows: min-content min-content;
  place-items: start end;
}

.your-chat-balloon {
  /* border: 3px solid blueviolet; */
  max-width: 80%;
  padding: 10px 20px;
  margin: 0;
  min-width: min-content;
  border-radius: 20px 20px 0px 20px; /* TL-TR-BR-BL*/
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  color: white;
  background: darkslateblue;
  font-size: 0.9em;
  word-break: break-word;
  white-space: pre-line;
}

.your-chat-datetime .icon {
  margin-right: 5px;
  color: green;
}

.typing-area {
  /* border: 5px solid goldenrod; */
  background: white;
  display: grid;
  grid-auto-flow: column;
  grid-template-columns: 9fr 1fr;
  column-gap: 5px;
  align-items: center;
  overflow-x: hidden;
  box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.2);
}

.type-area {
  /* border: 5px solid blueviolet; */
  border: none;
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  outline: none;
  background: none;
  padding: 10px 20px;
  color: #333;
  font-size: 1.1em;
}

input::placeholder {
  color: #777;
}

.creator {
  text-align: center;
  color: #777;
  font-family: 'Courier New', Courier, monospace;
}
</style>