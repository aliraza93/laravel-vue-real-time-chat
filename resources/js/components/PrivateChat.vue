<template>
    <div class="card chat-app">
        <div id="plist" class="people-list" v-if="users.length > 0">
            <ul class="list-unstyled chat-list mt-2 mb-0">
                <li
                    v-for="(value, index) in users"
                    :key="index"
                    :class="
                        value.id == activeFriend
                            ? 'clearfix active'
                            : 'clearfix'
                    "
                >
                    <a
                        href="#"
                        @click="userClicked(value.id)"
                        v-if="user.id != value.id"
                        style="display: flex"
                    >
                        <avatar :username="value.name" :size="20"></avatar>
                        <div class="about" style="margin-top: -3px">
                            <div class="name">
                                {{ value.name }}
                                <i
                                    v-if="
                                        unseen_messages.find(
                                            (user) =>
                                                user.user_id === value.id &&
                                                user.seen === 0 &&
                                                user.user_id != activeFriend
                                        )
                                    "
                                    class="fa fa-circle ml-2"
                                    style="font-size: 12px"
                                ></i>
                            </div>
                            <div class="status">
                                <i
                                    :class="
                                        onlineFriends.find(
                                            (onlineFriend) =>
                                                onlineFriend.id === value.id
                                        )
                                            ? 'fa fa-circle online'
                                            : 'fa fa-circle offline'
                                    "
                                ></i>
                                {{
                                    onlineFriends.find(
                                        (onlineFriend) =>
                                            onlineFriend.id === value.id
                                    )
                                        ? "Online"
                                        : "Offline"
                                }}
                            </div>
                        </div>
                    </a>
                </li>
            </ul>
        </div>
        <div id="plist" class="people-list" v-else>
            <ul class="list-unstyled chat-list mt-2 mb-0">
                <li class="clearfix ml-5 mt-5">No Users found to chat with.</li>
            </ul>
        </div>
        <div class="chat" v-if="activeFriend">
            <div class="chat-header clearfix">
                <div class="row">
                    <div class="col-lg-6 d-flex">
                        <a
                            href="javascript:void(0);"
                            data-toggle="modal"
                            data-target="#view_info"
                        >
                            <avatar
                                v-if="friend.name"
                                :username="friend.name"
                                :size="40"
                            ></avatar>
                        </a>
                        <div class="chat-about">
                            <h6 class="m-b-0">{{ friend.name }}</h6>
                            <small
                                v-if="
                                    onlineFriends.find(
                                        (onlineFriend) =>
                                            onlineFriend.id == activeFriend
                                    )
                                "
                                >Active now</small
                            >
                            <small v-else>
                                {{ friend.last_seen_at | moment }}
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-6 hidden-sm text-right">
                        <a
                            href="javascript:void(0);"
                            data-toggle="modal"
                            data-target="#delete-chat"
                            class="btn btn-outline-warning"
                            ><i class="fa fa-trash"></i
                        ></a>
                    </div>
                </div>
            </div>
            <div class="chat-history" id="privateMessageBox">
                <ul
                    class="m-b-0"
                    v-for="(value, index) in allMessages"
                    :key="index"
                >
                    <div>
                        <li class="clearfix" v-if="user.id == value.user_id">
                            <div class="message-data text-right">
                                <span class="message-data-time">{{
                                    value.SentMessageOnHumanReadable
                                }}</span>
                                <!-- <avatar v-if="user.name" :username="user.name" :size="40"></avatar> -->
                            </div>
                            <div
                                class="message other-message float-right"
                                v-if="value.message"
                            >
                                {{ value.message }}
                            </div>
                            <div
                                class="float-right"
                                v-if="!value.message && value.file != null"
                            >
                                <a
                                    :href="
                                        'messenger/download-message-file/' +
                                        value.file.id
                                    "
                                    data-target="tooltip"
                                    title="Click to download"
                                >
                                    <img
                                        width="182"
                                        height="137"
                                        v-if="value.file.thumbnail"
                                        :src="value.file.thumbnail"
                                        alt=""
                                    />
                                    <span v-else
                                        ><i
                                            class="fa fa-file"
                                            style="font-size: 80px"
                                        ></i
                                    ></span>
                                </a>
                            </div>
                        </li>
                        <li class="clearfix" v-if="user.id != value.user_id">
                            <div class="message-data">
                                <span class="message-data-time">{{
                                    value.SentMessageOnHumanReadable
                                }}</span>
                            </div>
                            <div
                                class="message my-message"
                                v-if="value.message"
                            >
                                {{ value.message }}
                            </div>
                            <div v-if="!value.message && value.file != null">
                                <a
                                    :href="
                                        'messenger/download-message-file/' +
                                        value.file.id
                                    "
                                    data-target="tooltip"
                                    title="Click to download"
                                >
                                    <img
                                        width="182"
                                        height="137"
                                        v-if="value.file.thumbnail"
                                        :src="value.file.thumbnail"
                                        alt=""
                                    />
                                    <span v-else
                                        ><i
                                            class="fa fa-file"
                                            style="font-size: 80px"
                                        ></i
                                    ></span>
                                </a>
                            </div>
                            <span class="mt-1" v-if="value.file != null">
                                {{ value.file.file_name }}
                            </span>
                        </li>
                    </div>
                </ul>
                <ul class="m-b-0" v-if="typingFriend.name">
                    <li class="clearfix">typing...</li>
                </ul>
            </div>
            <div class="floating-div">
                <picker
                    v-if="emoStatus"
                    set="emojione"
                    @select="onInput"
                    title="Pick your emoji…"
                />
            </div>
            <div class="chat-message clearfix">
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <a
                                href="#"
                                class="btn"
                                data-toggle="modal"
                                data-target="#upload-files"
                                ><i class="fa fa-upload"></i
                            ></a>
                        </span>
                    </div>
                    <div class="input-group-prepend">
                        <span
                            class="input-group-text"
                            @click="toggleEmo"
                            style="cursor: pointer"
                            data-target="toggle"
                            title="Emojis"
                        >
                            <i class="fa fa-smile-o"></i>
                        </span>
                    </div>
                    <textarea
                        v-model="message"
                        @keydown="onTyping"
                        @keyup.enter="sendMessage"
                        class="form-control"
                        placeholder="Type message..."
                    ></textarea>
                    <span class="input-group-append">
                        <button
                            @click="sendMessage"
                            :disabled="disableSubmitButton"
                            class="btn btn-primary"
                            type="button"
                        >
                            <i class="fa fa-paper-plane"></i>
                        </button>
                    </span>
                </div>
            </div>
        </div>
        <div class="chat" v-else>
            <div class="row" style="margin-top: 300px; margin-bottom: 300px">
                <div class="col-md-12 text-center">
                    <h2>Welcome to Messenger.</h2>
                    <span>Please select a friend to talk with.</span>
                </div>
            </div>
        </div>
        <upload-files
            v-if="activeFriend"
            :activeFriend="activeFriend"
            :allMessages="allMessages"
        ></upload-files>

        <!-- Delete Conversation Modal -->
        <div class="modal fade" id="delete-chat" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="form-header">
                            <h3>Delete Conversation</h3>
                            <p>Are you sure want to delete?</p>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a
                            @click="deleteMessages()"
                            href="javascript:void(0);"
                            class="btn btn-primary continue-btn"
                            data-dismiss="modal"
                            >Delete</a
                        >
                        <a
                            href="javascript:void(0);"
                            data-dismiss="modal"
                            class="btn btn-primary cancel-btn"
                            >Cancel</a
                        >
                    </div>
                </div>
            </div>
        </div>
        <!-- /Delete Conversation Modal -->
    </div>
</template>

<script>
import moment from "moment";
import { Picker } from "emoji-mart-vue";
import UploadFiles from "./UploadFiles.vue";
import { EventBus } from "../vue-asset";

export default {
    props: ["user", "activeid"],
    components: { Picker, UploadFiles },
    data() {
        return {
            message: null,
            files: [],
            activeFriend: null,
            typingFriend: {},
            onlineFriends: [],
            allMessages: [],
            typingClock: null,
            emoStatus: false,
            users: [],
            unseen_messages: [],
            friend: [],
            saving: false,
            token: document.head.querySelector('meta[name="csrf-token"]')
                .content,
            notificationSystem: {
                options: {
                    success: {
                        position: "topRight",
                        timeout: 3000,
                        class: "success_notification",
                    },
                    error: {
                        position: "topRight",
                        timeout: 3000,
                        class: "error_notification",
                    },
                    completed: {
                        position: "topRight",
                        timeout: 1000,
                        class: "complete_notification",
                    },
                    info: {
                        position: "topRight",
                        timeout: 3000,
                        class: "info_notification",
                    },
                },
            },
        };
    },
    computed: {
        friends() {
            return this.users.filter((user) => {
                return user.id !== this.user.id;
            });
        },
        disableSubmitButton() {
            return this.saving ? true : false;
        },
    },
    watch: {
        files: {
            deep: true,
            handler() {
                let success = this.files[0].success;
                if (success) {
                    this.fetchMessages();
                }
            },
        },
        activeFriend(val) {
            if (val) {
                this.fetchMessages();
                this.fetchUser();
            }
        },
        "$refs.upload"(val) {
            console.log(val);
        },
    },
    methods: {
        userClicked(id) {
            this.activeFriend = id;
            EventBus.$emit("user-clicked", id);
        },
        onTyping() {
            Echo.private("privatechat." + this.activeFriend).whisper("typing", {
                user: this.user,
            });
        },
        sendMessage() {
            //check if there message
            if (!this.message) {
                return alert("Please enter message");
            }
            if (!this.activeFriend) {
                return alert("Please select friend");
            }
            axios
                .post(
                    base_url +
                        "messenger/private-messages/" +
                        this.activeFriend,
                    { message: this.message }
                )
                .then((response) => {
                    this.message = null;
                    this.allMessages.push(response.data.message);
                    setTimeout(this.scrollToEnd, 100);
                });
        },
        fetchMessages() {
            if (!this.activeFriend) {
                return alert("Please select friend");
            }
            axios
                .get(
                    base_url + "messenger/private-messages/" + this.activeFriend
                )
                .then((response) => {
                    this.allMessages = response.data;
                    setTimeout(this.scrollToEnd, 100);
                    this.makeSeen();
                });
        },
        fetchUsers() {
            axios.get(base_url + "messenger/users").then((response) => {
                this.users = response.data.users;
                this.unseen_messages = response.data.unseen_messages;
                if (this.friends.length > 0) {
                    // this.activeFriend = this.friends[0].id;
                }
            });
        },

        fetchUser() {
            axios
                .get(base_url + "messenger/user/" + this.activeFriend)
                .then((response) => {
                    this.friend = response.data;
                });
        },

        makeSeen() {
            if (!this.activeFriend) {
                return alert("Please select friend");
            }
            axios
                .post(base_url + "messenger/seen-messages/" + this.activeFriend)
                .then((response) => {
                    setTimeout(this.scrollToEnd, 100);
                });
        },

        updateLastSeen(user) {
            axios
                .get(base_url + "messenger/update-last-seen/" + user.id)
                .then((response) => {
                    this.fetchUser();
                });
        },

        deleteMessages() {
            if (!this.activeFriend) {
                return alert("Please select friend");
            }
            $("#delete-chat .close").click()
            axios
                .delete(
                    base_url +
                        "messenger/delete-private-messages/" +
                        this.activeFriend
                )
                .then(({ data }) => {
                    
                    this.fetchMessages();
                    this.$toast.success(
                        "Conversation Deleted Successfully!",
                        "Success",
                        this.notificationSystem.options.success
                    );
                });
        },

        scrollToEnd() {
            document.getElementById("privateMessageBox").scrollTo(0, 99999);
        },
        toggleEmo() {
            this.emoStatus = !this.emoStatus;
        },
        onInput(e) {
            if (!e) {
                return false;
            }
            if (!this.message) {
                this.message = e.native;
            } else {
                this.message = this.message + e.native;
            }
            this.emoStatus = false;
        },
        onResponse(e) {
            console.log("onrespnse file up", e);
        },

        listen() {
            Echo.private("privatechat." + this.user.id)
                .listenForWhisper("typing", (e) => {
                    if (this.activeFriend === e.user.id) {
                        this.typingFriend = e.user;
                        setTimeout(this.scrollToEnd, 100);
                        if (this.typingClock) clearTimeout();
                        this.typingClock = setTimeout(() => {
                            this.typingFriend = {};
                        }, 3000);
                    }
                })
                .listen("PrivateMessageSent", (e) => {
                    this.activeFriend = e.message.user_id;
                    this.allMessages.push(e.message);
                    setTimeout(this.scrollToEnd, 100);
                    axios
                        .post(
                            base_url +
                                "messenger/message-delivered/" +
                                e.message.id
                        )
                        .then((response) => {});
                });
            Echo.private("messagedelivered").listen("MessageDelivered", (e) => {
                // this.allMessages.find(message=>message.id == e.id).seen = e.seen
                this.fetchMessages();
            });
        },
    },
    mounted() {
        this.listen();
    },
    created() {
        this.fetchUsers();
        Echo.join("chat")
            .here((users) => {
                this.onlineFriends = users;
            })
            .joining((user) => {
                this.onlineFriends.push(user);
            })
            .leaving((user) => {
                console.log("leaving");
                this.onlineFriends.splice(this.onlineFriends.indexOf(user), 1);
                this.updateLastSeen(user);
            });
        if (this.activeid !== -1) {
            this.activeFriend = this.activeid;
        }
    },
    filters: {
        moment: function (date) {
            if (date == undefined) {
                return "";
            }
            return "Active " + moment(date).fromNow();
        },
    },
};
</script>

<style scoped></style>
