<template>
    <div class="modal fade" id="upload-files" role="dialog">
        <div
            class="modal-dialog modal-dialog-centered modal-lg"
            role="document"
        >
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Files</h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger" v-if="errors">
                        <ul>
                            <li v-for="error in errors" :key="error">
                                {{ error[0] }}
                            </li>
                        </ul>
                    </div>
                    <form>
                        <div class="row">
                            <div class="col-md-12">
                                <vue-dropzone
                                    ref="myVueDropzone"
                                    v-on:vdropzone-sending="sendingEvent"
                                    v-on:vdropzone-success="uploadSuccess"
                                    v-on:vdropzone-error="uploadError"
                                    id="dropzone"
                                    :options="dropzoneOptions"
                                ></vue-dropzone>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button
                        @click="triggerSend"
                        type="button"
                        class="btn btn-primary submit-btn"
                    >
                        Submit
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import vue2Dropzone from "vue2-dropzone";
import "vue2-dropzone/dist/vue2Dropzone.min.css";
import { EventBus } from "../vue-asset"

export default {
    components: {
        vueDropzone: vue2Dropzone,
    },
    props: ["activeFriend", "allMessages"],
    data() {
        return {
            active_friend_id: "",
            errors: null,
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
            dropzoneOptions: {
                autoProcessQueue: false,
                url:
                    base_url +
                    "messenger/send-media",
                thumbnailWidth: 150,
                maxFilesize: 5,
                addRemoveLinks: true,
                uploadMultiple: true,
                headers: {
                    "X-CSRF-TOKEN":
                        document.head.querySelector("[name=csrf-token]")
                            .content,
                },
            },
        };
    },
    mounted() {
        var vm = this;
        vm.active_friend_id = vm.activeFriend;
        EventBus.$on('user-clicked', function(id) {
            if(id) {
                vm.active_friend_id = id;
            }
            else {
                vm.active_friend_id = vm.activeFriend;
            }
        })
    },

    watch: {
        activeFriend(val) {
            if(val)
            {
                this.active_friend_id = val;
            }
        }
    },

    methods: {
        triggerSend() {
            this.$refs.myVueDropzone.processQueue();
        },
        sendingEvent(file, xhr, formData) {
            formData.append("activeFriend", this.active_friend_id);
        },
        uploadSuccess: function (file, response) {
            this.allMessages.push(response.data);
            $("#upload-files .close").click()
            this.$refs.myVueDropzone.removeAllFiles();
            this.showMessage(response);
        },
        uploadError(file, message, xhr) {
            this.errors = message.errors;
            this.$refs.myVueDropzone.removeAllFiles();
            this.$toast.error(
                "Something went wrong!",
                "Error Alert",
                this.notificationSystem.options.error
            );
        },
        showMessage(data) {
            if (data.status == "success") {
                this.$toast.success(
                    data.message,
                    "Success Alert",
                    this.notificationSystem.options.success
                );
            } else {
                this.$toast.error(
                    data.message,
                    "Error Alert",
                    this.notificationSystem.options.error
                );
            }
        },
    },
};
</script>
