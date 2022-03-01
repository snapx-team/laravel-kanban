<template>
    <div class="p-4 pl-10 ">
        <div class="flex flex-wrap items-center">
            <div :class="{'animate-pulse' : loadingMembers.isLoading }" class="flex items-center py-1 pr-8">

                <p class="text-sm font-semibold text-gray-700 pr-4">
                    <i class="fas fa-users"></i> {{ kanbanMembers.length }} members:
                </p>

                <template v-for="(member, memberIndex) in computedMembers">
                    <template v-if="memberIndex < membersOnKanbanBarNumber">
                        <avatar :borderSize="0"
                                :class="{ '-ml-3': memberIndex > 0}"
                                :key="memberIndex"
                                :name="member.employee.user.full_name"
                                :user_id="member.employee_id"
                                :size="10"
                                :tooltip="true"
                                @click.native="clickMember(member)"
                                class="cursor-pointer transform hover:-translate-y-1 transition duration-300"
                        ></avatar>
                    </template>
                </template>

                <div @click="expandMembers()"
                     class="z-0 flex items-center justify-center font-semibold text-gray-800 text-sm w-10 h-10 rounded-full bg-gray-300 border-2 border-white -ml-3 pr-2 cursor-pointer"
                     v-if="kanbanMembers.length > membersOnKanbanBarNumber">
                    +{{ kanbanMembers.length - membersOnKanbanBarNumber }}
                </div>
                <button v-if="$role === 'admin'"
                        @click="createMember()"
                        class="z-0 items-center justify-center w-10 h-10 mr-2 text-indigo-100 transition-colors duration-150 bg-indigo-700 rounded-full focus:outline-none hover:bg-indigo-800 border-2 border-white -ml-3">
                    <i class="fas fa-plus"></i>
                </button>
            </div>

            <div class="flex items-center order-first">
                <h3 class="text-3xl text-gray-800 font-bold">{{ kanbanName }}</h3>
                <hsc-menu-style-white :menuZIndex="3">
                    <hsc-menu-button-menu>
                        <div
                            class="pl-1 pr-8 hover:text-gray-600 transition duration-150 ease-in transform focus:outline-none cursor-pointer text-xl ">
                            <i class="fas fa-sort-amount-down"></i>
                        </div>
                        <template slot="contextmenu">
                            <hsc-menu-item label="Sort By:">
                                <hsc-menu-item label="Index (Default)"
                                               @click="setSortMethod({'id': kanbanId, 'name':'Index', 'value': 'index'})"/>
                                <hsc-menu-item label="Deadline Soonest"
                                               @click="setSortMethod({'id': kanbanId, 'name':'Deadline Soonest', 'value': 'deadline_desc'})"/>
                                <hsc-menu-item label="Recently Created"
                                               @click="setSortMethod({'id': kanbanId, 'name':'Recently Created', 'value': 'created_desc'})"/>
                                <hsc-menu-item label="Highest Time Estimate"
                                               @click="setSortMethod({'id': kanbanId, 'name':'Highest Time Estimate', 'value': 'time_estimate_desc'})"/>
                            </hsc-menu-item>
                        </template>
                    </hsc-menu-button-menu>
                </hsc-menu-style-white>
            </div>

        </div>
        <small class="font-semibold tracking-wide">sorted by: {{ sortMethod.name }}</small>
    </div>
</template>
<script>

import Avatar from "../../global/Avatar.vue";
import vSelect from "vue-select";

export default {
    inject: ["eventHub"],

    components: {
        Avatar,
        vSelect
    },

    watch: {
        kanbanID: function () {
            this.eventHub.$emit("reset-show-employee-tasks");
            this.selected = [];
            this.membersOnKanbanBarNumber = 5;
        },
        kanbanMembers: function () {
            this.eventHub.$emit("reset-show-employee-tasks");
            this.selected = [];
            this.membersOnKanbanBarNumber = 5;
        }
    },

    data() {
        return {
            selected: [],
            membersOnKanbanBarNumber: 5,
        }
    },

    props: {
        kanbanName: {
            type: String,
            default: "no title",
        },
        sortMethod: {
            type: Object,
            default: () => ({'name': 'index', 'value': 'index'}),
        },
        kanbanId: {
            type: Number,
            default: null,
        },
        kanbanMembers: {
            type: Array,
            default: () => [],
        },
        loadingMembers: {
            type: Object,
        },
    },

    methods: {
        createMember() {
            this.eventHub.$emit("add-member");
        },
        expandMembers() {
            this.membersOnKanbanBarNumber = this.membersOnKanbanBarNumber + 10
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
            this.eventHub.$emit("show-employee-tasks", this.selected);
        },

        setSortMethod(selectedSortMethod) {
            this.eventHub.$emit("set-kanban-sort-method", selectedSortMethod);
        },
    },

    computed: {
        computedMembers() {
            return this.kanbanMembers.sort((x, y) => {
                return x.employee_id === this.$employeeIdSession ? -1 : y.employee_id === this.$employeeIdSession ? 1 : 0;
            });
        },
    },
};

</script>
