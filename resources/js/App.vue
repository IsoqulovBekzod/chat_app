<script>
import axios from 'axios';

export default {
    data() {
        return {
            users: [], // All users
            selectedUser: null, // Selected user
            messages: [],
            newMessage: "",
            currentUser:null, // Current user
        };
    },
    methods: {
        // Fetch all users
        async fetchUsers() {
            try {
                const response = await axios.get('/users');
                this.users = response.data;
            } catch (err) {
                console.error('Error fetching users:', err);
            }
        },

        // Fetch current user details
        async fetchCurrentUser() {
            try {
                const response = await axios.get('/messages');
                this.currentUser = response.data;
            } catch (err) {
                console.error('Error fetching current user:', err);
            }
        },

        // Send message
        async postMessage(text) {
            try {
                if (this.selectedUser) {
                    await axios.post('/message', {
                        text,
                        receiver_id: this.selectedUser.id
                    });
                    this.getMessages(); // Fetch messages after sending
                }
            } catch (err) {
                console.log(err.message);
            }
        },

        // Get messages
        async getMessages() {
            try {
                const response = await axios.get('/messages');
                this.messages = response.data;
                this.scrollToBottom(); // Scroll to the bottom of chat
            } catch (err) {
                console.log(err);
            }
        },

        // Send a new message
        sendMessage() {
            if (this.newMessage.trim() !== "" && this.selectedUser) {
                this.postMessage(this.newMessage.trim());
                this.newMessage = ""; // Clear input after sending
            }
        },

        // Select a user
        selectUser(user) {
            this.selectedUser = user;
            this.getMessages(); // Fetch messages for selected user
        },

        // Scroll to bottom after new messages
        scrollToBottom() {
            this.$nextTick(() => {
                const messageList = document.getElementById('messagelist');
                if (messageList) {
                    messageList.scrollTop = messageList.scrollHeight;
                }
            });
        }
    },
    created() {
        this.fetchUsers(); // Get all users
        this.fetchCurrentUser(); // Get current user details
        this.getMessages(); // Get messages
        window.Echo.private("channel_for_everyone")
            .listen('GotMessage', () => {
                this.getMessages(); // Get new messages when they arrive
            });
    },
};
</script>

<template>
    <div class="container">
        <!-- Sidebar with user list -->
        <div class="sidebar">
            <h3>Users</h3>
            <ul>
                <li
                    v-for="user in users"
                    :key="user.id"
                    :class="{ selected: selectedUser && selectedUser.id === user.id }"
                    @click="selectUser(user)">
                    <img :src="user.profilePicture" alt="User Avatar" class="user-avatar" />
                    {{ user.name }}
                </li>
            </ul>
        </div>

        <!-- Chat Box -->
        <div class="chat-box-wrapper">
            <!-- Chat messages -->
            <div class="chat-box" id="messagelist">
                <div
                    v-for="(message, index) in messages"
                    :key="index"
                    :class="{'message': true, 'sent': message.sender_id === currentUser.id, 'received': message.sender_id !== currentUser.id}">
                    <div class="message-header">
                        <strong>{{ message.user ? message.user.name : messages.user }}</strong>
                        <small>{{ message.time }}</small>
                    </div>
                    <p>{{ message.text }}</p>
                </div>
            </div>

            <!-- Message Input Area -->
            <div class="input-area">
                <input
                    v-model="newMessage"
                    @keyup.enter="sendMessage"
                    type="text"
                    placeholder="Type a message"
                    :disabled="!selectedUser" />
                <button @click="sendMessage" :disabled="!selectedUser">Send</button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.container {
    display: flex;
    height: 100vh;
}

/* Sidebar */
.sidebar {
    width: 280px;
    background-color: #f4f7fc;
    padding: 20px;
    border-right: 1px solid #ddd;
    overflow-y: auto;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
}

.sidebar h3 {
    text-align: center;
    font-size: 1.3rem;
    color: #4b8df8;
    margin-bottom: 20px;
}

.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar li {
    padding: 15px;
    margin-bottom: 15px;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 15px;
    border-radius: 8px;
    transition: background-color 0.3s ease;
}

.sidebar li:hover {
    background-color: #d6e8f7;
}

.sidebar li.selected {
    background-color: #4b8df8;
    color: white;
}

.user-avatar {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    object-fit: cover;
}

/* Chat Box */
.chat-box-wrapper {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    background-color: #ffffff;
}

.chat-box {
    flex-grow: 1;
    padding: 20px;
    background-color: #f2f6fc;
    overflow-y: auto;
    box-shadow: inset 0 1px 3px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
}

.message {
    max-width: 70%;
    margin-bottom: 20px;
    padding: 12px;
    border-radius: 20px;
    word-wrap: break-word;
    position: relative;
    font-size: 1rem;
    line-height: 1.4;
}

.sent {
    align-self: flex-end;
    background-color: #c8f6c8; /* Soft green for sent messages */
    color: #007600;
}

.received {
    align-self: flex-start;
    background-color: #c8e4f8; /* Soft blue for received messages */
    color: #005b8b;
}

.message-header {
    display: flex;
    justify-content: space-between;
    font-size: 0.9rem;
    margin-bottom: 5px;
    color: #777;
}

.input-area {
    display: flex;
    padding: 15px;
    border-top: 1px solid #ddd;
    background-color: #f9f9f9;
    border-radius: 0 0 15px 15px;
}

input {
    flex-grow: 1;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 25px;
    outline: none;
    font-size: 1.1rem;
    background-color: #f9f9f9;
}

input:disabled {
    background-color: #e1e1e1;
}

button {
    padding: 12px 18px;
    border: none;
    background-color: #4b8df8;
    color: white;
    border-radius: 25px;
    cursor: pointer;
    font-size: 1rem;
    margin-left: 15px;
    transition: background-color 0.3s ease;
}

button:disabled {
    background-color: #cccccc;
    cursor: not-allowed;
}

button:hover {
    background-color: #3074d9;
}
</style>
