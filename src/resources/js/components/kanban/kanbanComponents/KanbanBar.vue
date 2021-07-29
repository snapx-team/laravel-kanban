<template>
    <div>
        <div class="flex flex-wrap p-4 pl-10">
            <h3 class="text-3xl text-gray-800 font-bold py-1 pr-8">{{ kanbanName }}</h3>
            <div :class="{'animate-pulse' : loadingMembers.isLoading }" class="flex items-center py-1 pr-8 h-16">
                <p class="px-2 text-gray-500 pr-4">{{ kanbanMembers.length }} members</p>

                <template v-for="(member, memberIndex) in kanbanMembers">
                    <template v-if="memberIndex < 5">
                        <avatar :borderSize="0"
                                :class="{ '-ml-3': memberIndex > 0}"
                                :key="memberIndex"
                                :name="member.employee.user.full_name"
                                :user_id="member.employee_id"
                                :size="10"
                                :tooltip="true"
                                @click.native="clickMember(member)"
                                class="cursor-pointer transform hover:-translate-y-1 transition duration-300"
                                >
                        </avatar>
                    </template>
                </template>

                <span class="z-10 flex items-center justify-center font-semibold text-gray-800 text-sm w-10 h-10 rounded-full bg-gray-300 border-2 border-white -ml-3 pr-2"
                    v-if="kanbanMembers.length > 5">+{{ kanbanMembers.length - 5 }}</span>
                <button @click="createMember()"
                        class="z-20 items-center justify-center w-10 h-10 mr-2 text-indigo-100 transition-colors duration-150 bg-indigo-700 rounded-full focus:outline-none hover:bg-indigo-800 border-2 border-white -ml-3">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
        </div>
    </div>
</template>
<script>
import Avatar from "../../global/Avatar.vue";

export default {
    inject: ["eventHub"],
    components: {
        Avatar,
    },
    data() {
        return {
            selected : [],
        }
    },
    props: {
        kanbanName: {
            type: String,
            default: "no title",
        },
        kanbanID: {
            type: Number,
            default: null,
        },
        kanbanMembers: {
            type: Array,
            default: () => [],
        },
        kanbanInfo: {
            type: Object,
            assignedToMe: Number,
            createdByMe: String,
            default: () => ({}),
        },
        loadingMembers: {
            type: Object,
        },
    },
    methods: {
        createMember() {
            this.eventHub.$emit("add-member");
        },
        clickMember(option) {
            if (this.selected.includes(option.employee_id)) {
                const index = this.selected.indexOf(option.employee_id);
                if (index > -1) {
                    this.selected.splice(index, 1);
                }
            } else {
                this.selected.push(option.employee_id);
            }
            this.eventHub.$emit("show-employee-tasks", this.selected); //user id, not erp id
        }
    },
};
</script>

<style scoped>
.rotate-45 {
    --transform-rotate: 45deg;
    transform: rotate(45deg);
}

.group:hover .group-hover\:flex {
    display: flex;
}
</style>
