<template>
    <div class="w-96 m-auto pt-20"  v-if="!this.loadingProfile">

        <div class="bg-white p-3 border-t-4 border-indigo-400 shadow">
            <div class="image overflow-hidden">
                <img data-v-bb806982="" :src="this.initialsImageLink" alt="Avatar" class="rounded-full border-0 border-white border m-auto">
            </div>
            <h1 class="text-gray-900 font-bold text-xl leading-8 mt-2 mb-3 text-center"> {{this.userName}}</h1>
            <h3 class="text-gray-600 leading-6 px-1 ">General</h3>
            <ul class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:shadow py-2 px-3 mt-3 divide-y rounded shadow-sm">
                <li class="flex items-center py-3">
                    <span>Status</span>
                    <span class="ml-auto"><span class="bg-green-500 py-1 px-2 rounded text-white text-sm">{{this.status}}</span></span>
                </li>
                <li class="flex items-center py-3">
                    <span>Member since</span>

                    <span class="ml-auto">{{this.memberSince}}</span>
                </li>
            </ul>
            
            <hr class=my-4>
            <h3 class="text-gray-600 leading-6 px-1">History</h3>
            <ul class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:shadow py-2 px-3 mt-3 divide-y rounded shadow-sm">
                <li class="flex items-center py-3">
                    <span>Boards</span>
                    <span class="ml-auto">{{this.boardsCount}}</span>
                </li>
                <li class="flex items-center py-3">
                    <span>Tasks assigned to</span>
                    <span class="ml-auto">{{this.taskAssignedToCount}}</span>
                </li>
                <li class="flex items-center py-3">
                    <span>Tasks completed</span>
                    <span class="ml-auto">{{this.taskCompleted}}</span>
                </li>
                <li class="flex items-center py-3">
                    <span>Tasks created</span>
                    <span class="ml-auto">{{this.taskCreated}}</span>
                </li>
            </ul>
            
                <hr class=my-4>
                <h3 class="text-gray-600 leading-6 px-1">Preferences</h3>
                <ul class="bg-gray-100 text-gray-600 hover:text-gray-700 hover:shadow py-2 px-3 mt-3 divide-y rounded shadow-sm">
                    <li class="flex items-center py-3">
                        <span>Language</span>
                        <span class="ml-auto">{{language}}</span>
                    </li>
                </ul>
        </div>
    </div>
</template>

<script>
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";
export default {
    inject: ["eventHub"],
    data() {
        return {
            userName: '',
            status: '',
            memberSince: '',
            boardsCount: null,
            taskAssignedToCount: null,
            taskCompleted: null,
            taskCreated: null,
            language: '',
            loadingProfile: true,
            initialsImageLink: null
        }
    },

    mixins: [ajaxCalls],

    mounted() {
        this.getUserProfileData();
    },

    methods: {
        getUserProfileData() {
            this.eventHub.$emit("set-loading-state", true);
            this.asyncGetUserProfile().then((data) => {
                this.userName = data.data.data.userName;
                this.status = data.data.data.userStatus;
                this.memberSince = data.data.data.userCreatedAt;
                this.boardsCount = data.data.data.boardsCount;
                this.taskAssignedToCount = data.data.data.taskAssignedTo;
                this.taskCompleted = data.data.data.taskComplete;
                this.taskCreated = data.data.data.taskCreated;
                this.language = data.data.data.language;
                this.loadingProfile = false;
                this.initialsImageLink = this.getInitialsLink(data.data.data.userName)
                this.eventHub.$emit("set-loading-state", false);
            }).catch(res => {
                console.log(res)
            });
        },
        getInitialsLink(name) {
            return "https://ui-avatars.com/api/?name=" + name + "&amp;background=748A52&amp;color=fff";
        }
    },
};
</script>
