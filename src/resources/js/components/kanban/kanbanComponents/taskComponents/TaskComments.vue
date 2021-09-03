<template>

    <div class="space-y-3" :class="{'animate-pulse': loadingComments}">
        <div>
            <div class="flex-grow space-y-2">

                <div v-if="!isCommentFocus">
                    <input ref="myQuillEditortest" @click="displayQuill" placeholder="Write Comment" type="text"
                           class="px-3 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 w-full outline-none text-md leading-4">
                </div>


                <div :class="{'absolute h-0 opacity-0 overflow-hidden': !isCommentFocus}">
                    <quill-editor
                        ref="myQuillEditor"
                        v-model="commentData.comment"
                        :options="config"
                        output="html"
                        @blur="isCommentFocus = false"
                    ></quill-editor>

                    <button
                        @click="saveComment()"
                        class="w-full bg-indigo-500 text-white active:bg-indigo-600 font-bold uppercase text-xs px-4 py-2 rounded shadow hover:shadow-md outline-none focus:outline-none mr-1 mb-1 ease-linear transition-all duration-150"
                        type="button"
                    ><span v-if="loadingComments">submitting</span><span v-else>submit</span> comment <i
                        class="fa fa-paper-plane ml-1"></i>
                    </button>
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
                    <div class="flex border-b" :key="commentIndex">
                        <div class="p-3 text-sm">
                            <div class="flex items-center w-38">
                                <div>
                                    <avatar :name="comment.employee.user.full_name" :size="6"></avatar>
                                </div>
                                <div class="ml-3">
                                    <p class="text-gray-800 font-semibold whitespace-no-wrap">
                                        {{ comment.employee.user.full_name }} </p>
                                    <small>{{ comment.created_at | moment("DD MMM, YYYY") }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 text-sm">
                            <p class="text-gray-800 whitespace-no-wrap" v-html="comment.comment"></p>
                        </div>

                    </div>
                </template>
            </template>

            <div
                class="p-3 border-t flex flex-col xs:flex-row items-center xs:justify-between">
                <div class="text-xs xs:text-sm text-gray-900">
                    Showing {{ Number(paginationIndex) + 1 }} to
                    <span v-if="paginationIndex + paginationStep <= filtered.length"> {{ Number(paginationIndex) + Number(paginationStep) }} </span>
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
                isCommentFocus: false,
                comments: [],
                filter: "",
                paginationIndex: 0,
                paginationStep: 5,
                commentData: {id: null, comment: null, taskId: null},
                config: {
                    readOnly: false,
                    placeholder: 'Describe your task in greater detail',
                    theme: 'snow',
                    modules: {
                        toolbar: [['bold', 'italic', 'underline', 'strike'],
                            ['code-block'],
                            [{'list': 'ordered'}, {'list': 'bullet'}],
                            [{'script': 'sub'}, {'script': 'super'}],
                        ]
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
            },
        },
        mounted() {
            this.commentData.taskId = this.cardData.id;
            this.getComments();
        },
        methods: {

            getComments() {
                this.loadingComments = true;

                this.asyncGetComments(this.commentData.taskId).then((data) => {
                    this.comments = data.data;
                    this.loadingComments = false;
                }).catch(res => {
                    console.log(res)
                });
            },

            saveComment() {
                this.loadingComments = true;
                this.asyncCreateComment(this.commentData).then(res => {
                    this.commentData.comment= null;
                    this.getComments();
                    this.eventHub.$emit("reload-logs");
                });
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

