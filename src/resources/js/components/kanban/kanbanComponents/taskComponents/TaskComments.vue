<template>

    <div class="space-y-3" :class="{'animate-pulse': loadingComments}">
        <div>
            <div class="flex-grow space-y-2">

                <div v-if="!isCommentFocus">
                    <input @click="displayQuill" placeholder="Write Comment" type="text"
                           class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full outline-none text-md leading-4">
                </div>

                <div :class="{'absolute h-0 opacity-0 overflow-hidden': !isCommentFocus}">
                    <quill-editor
                        ref="myQuillEditor"
                        v-model="commentData.comment"
                        :options="config"
                        output="html"
                        @blur="clickOutsideQuill()"
                    ></quill-editor>

                    <div v-if="isEditingComment.visible" class="flex">
                        <button
                            @click="saveComment()"
                            class="w-full bg-yellow-500 text-white active:bg-yellow-600 font-bold uppercase text-xs px-4
                            py-2 rounded shadow hover:shadow-md outline-none focus:outline-none mr-1 mb-1 ease-linear
                            transition-all duration-150"
                            type="button">
                            <span v-if="loadingComments">loading...</span>
                            <span v-else>edit comment</span>
                            <i class="fa fa-paper-plane ml-1"></i>
                        </button>
                        <button
                            @click="cancelEditComment()"
                            class="w-full bg-red-500 text-white active:bg-red-600 font-bold uppercase text-xs px-4
                            py-2 rounded shadow hover:shadow-md outline-none focus:outline-none mr-1 mb-1 ease-linear
                            transition-all duration-150"
                            type="button">
                            <span>cancel</span>
                            <i class="fa fa-paper-plane ml-1"></i>
                        </button>
                    </div>

                    <div v-else>
                        <button
                            @click="saveComment()"
                            class="w-full bg-indigo-500 text-white active:bg-indigo-600 font-bold uppercase text-xs px-4
                        py-2 rounded shadow hover:shadow-md outline-none focus:outline-none mr-1 mb-1 ease-linear
                        transition-all duration-150"
                            type="button">
                            <span v-if="loadingComments">loading...</span>
                            <span v-else>submit comment</span>
                            <i class="fa fa-paper-plane ml-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <div class="my-2 flex sm:flex-row flex-col">
            <div class="flex flex-row mb-1 sm:mb-0">
                <div class="relative">
                    <select @change="paginationIndex = 0"
                            class="appearance-none h-full rounded-l border block appearance-none w-full bg-white border-gray-400 text-gray-700 py-2 px-4 pr-8 leading-tight focus:outline-none focus:bg-white focus:border-gray-500"
                            v-model="paginationStep">
                        <option>5</option>
                        <option>10</option>
                        <option>20</option>
                    </select>
                </div>
            </div>
            <div class="block relative">
                        <span class="h-full absolute inset-y-0 left-0 flex items-center pl-2">
                          <i class="text-gray-400 fas fa-search"></i>
                        </span>
                <input
                    class="appearance-none rounded-r rounded-l sm:rounded-l-none border border-gray-400 border-b block pl-8 pr-6 py-2 w-full bg-white text-sm placeholder-gray-400 text-gray-700 focus:bg-white focus:placeholder-gray-600 focus:text-gray-700 focus:outline-none"
                    placeholder="Search"
                    v-model="filter"/>
            </div>
        </div>

        <div class="inline-block min-w-full bg-gray-50 rounded-lg overflow-hidden">

            <template v-for="(comment, commentIndex) in filtered">
                <template v-if="commentIndex >= paginationIndex &&commentIndex < paginationIndex + paginationStep">
                    <div class="flex border-b group" :key="commentIndex"
                         :class=" isEditingComment.id === comment.id ? 'bg-yellow-200': '' ">
                        <div class="p-3 text-sm">
                            <div class="flex items-center w-38">
                                <div>
                                    <avatar :name="comment.employee.user.full_name" :size="8"></avatar>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-800 font-semibold">
                                        {{ comment.employee.user.full_name }} </p>
                                    <small class="whitespace-nowrap">
                                        {{ comment.created_at | moment("DD MMM, YYYY") }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 text-sm w-full">
                            <p class="text-gray-800" v-html="comment.comment"></p>
                            <small class="text-gray-500" v-if="comment.created_at !== comment.updated_at">(edited)</small>

                            <div class=" mt-2" v-if="cardData.id !== comment.task_id">
                                <div class="inline-flex space-x-2 rounded border px-2 py-1">
                                    <small>Origin: </small>
                                    <avatar :name="comment.task.board.name" :size="5" :tooltip="true"></avatar>
                                    <p class="text-gray-800 text-sm font-semibold whitespace-nowrap">
                                        [{{ comment.task.task_simple_name }}]</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col justify-around text-sm opacity-0 group-hover:opacity-100 transition duration-300 ease-in-out">
                            <a v-if="comment.employee_id === $employeeIdSession"
                               @click="editComment(comment)"
                               class="cursor-pointer px-2 text-gray-400 hover:text-gray-600 transition duration-300 ease-in-out focus:outline-none">
                                <i class="fas fa-edit"></i>
                            </a>

                            <a v-if="comment.employee_id === $employeeIdSession"
                               @click="deleteComment(comment)"
                               class="cursor-pointer px-2 text-gray-400 hover:text-red-600 transition duration-300 ease-in-out focus:outline-none">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </template>
            </template>

            <div
                class="p-3 border-t flex flex-col xs:flex-row items-center xs:justify-between">
                <div class="text-xs xs:text-sm text-gray-900">
                    Showing {{ Number(paginationIndex) + 1 }} to
                    <span v-if="paginationIndex + paginationStep <= filtered.length">
                        {{ Number(paginationIndex) + Number(paginationStep) }}
                    </span>
                    <span v-else>{{ filtered.length }} </span> of {{ filtered.length }} Entries
                </div>
                <div class="inline-flex mt-2 xs:mt-0">
                    <button @click="updatePaginationIndex(paginationIndex - paginationStep)"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l">
                        Prev
                    </button>
                    <button @click="updatePaginationIndex(paginationIndex + paginationStep)"
                            class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

</template>


<script>
import vSelect from "vue-select";
import Avatar from "../../../global/Avatar.vue";
import {ajaxCalls} from "../../../../mixins/ajaxCallsMixin";

export default {
    inject: ["eventHub"],
    components: {
        vSelect,
        Avatar,
    },
    mixins: [ajaxCalls],

    props: {
        cardData: Object,
    },

    data() {
        return {
            loadingComments: false,
            isEditingComment: {visible: false, id: null},
            isCommentFocus: false,
            comments: [],
            filter: "",
            paginationIndex: 0,
            paginationStep: 5,
            commentData: {
                id: null,
                comment: null,
                task_id: null,
                employee_id: null,
                mentions: [],
            },
            boardMembers: [],
            config: {
                readOnly: false,
                placeholder: 'Describe your task in greater detail',
                theme: 'snow',
                modules: {
                    toolbar: [['bold', 'italic', 'underline', 'strike'],
                        ['code-block'],
                        [{'list': 'ordered'}, {'list': 'bullet'}],
                        [{'script': 'sub'}, {'script': 'super'}],
                    ],
                    mention: {
                        allowedChars: /^[A-Za-z\sÅÄÖåäö]*$/,
                        mentionDenotationChars: ["@"],
                        selectKeys: [13],
                        source: (searchTerm, renderList) => {

                            if (searchTerm.length === 0) {
                                renderList(this.boardMembers, searchTerm);
                            } else {
                                const matches = [];
                                for (let i = 0; i < this.boardMembers.length; i++)
                                    if (~this.boardMembers[i].value.toLowerCase().indexOf(searchTerm.toLowerCase()))
                                        matches.push(this.boardMembers[i]);
                                renderList(matches, searchTerm);
                            }
                        }
                    }
                }
            },
        };
    },

    computed: {
        filtered() {
            const regex = new RegExp(this.filter, "i");
            return this.comments.filter((e) => {
                this.paginationIndex = 0;
                return !this.filter || e.employee.user.full_name.match(regex) || e.comment.match(regex);
            });
        }
    },
    mounted() {
        this.getMembers();
        this.getComments();
    },
    methods: {

        getMembers() {
            this.asyncGetMembersFormattedForQuill(this.cardData.board_id).then((members) => {
                this.boardMembers = members.data;
            });
        },
        getComments() {
            this.loadingComments = true;

            this.asyncGetComments(this.cardData.id).then((data) => {
                this.comments = data.data;
                this.loadingComments = false;
            }).catch(res => {
                console.log(res)
            });
        },

        saveComment() {
            this.loadingComments = true;
            this.commentData.task_id = this.cardData.id;
            this.commentData.mentions = [];

            let parser = new DOMParser();
            let doc = parser.parseFromString(this.commentData.comment, 'text/html');
            let matches = doc.getElementsByClassName('mention');

            for (let i = 0; i < matches.length; i++) {

                let dataAttribute = matches[i].getAttribute('data-id');
                this.commentData.mentions.push(dataAttribute);
            }

            this.asyncCreateOrEditComment(this.commentData).then(res => {
                this.commentData.comment = null;
                this.isEditingComment = {visible: false, id: null};
                this.getComments();
                this.eventHub.$emit("reload-logs");
            });
        },

        editComment(commentData) {
            this.isEditingComment = {visible: true, id: commentData.id};
            this.isCommentFocus = true;
            this.commentData = Object.assign({}, this.commentData, commentData);
        },

        cancelEditComment() {
            this.isEditingComment = {visible: false, id: null};
            this.commentData = {
                id: null,
                comment: null,
                task_id: null,
                employee_id: null,
                mentions: [],
            }
        },

        deleteComment(commentData) {


            this.$swal({
                icon: 'warning',
                title: 'Confirm Delete Comment',
                text: 'Are you sure you want to delete this comment?',
                showCancelButton: true,
                confirmButtonText: `Yes, delete it`,
            }).then((result) => {
                if (result.isConfirmed) {
                    this.loadingComments = true;
                    this.asyncDeleteComment(commentData).then(() => {
                        this.getComments();
                        this.eventHub.$emit("reload-logs");
                    });
                }
            })
        },

        clickOutsideQuill(){
            this.isCommentFocus = false;
            this.cancelEditComment();
        },

        updatePaginationIndex(newIndex) {
            if (newIndex < 0) newIndex = 0;
            else if (newIndex < this.filtered.length) this.paginationIndex = newIndex;
        },
        displayQuill() {
            this.isCommentFocus = true;
            this.$refs.myQuillEditor.quill.focus();
        },
    },
};
</script>

