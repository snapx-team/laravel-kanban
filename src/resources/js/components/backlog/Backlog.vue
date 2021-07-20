<template>
  <div v-if="backlogData !== null">
    <backlog-bar :name="'Backlog'"></backlog-bar>
    <div class="flex">

      <div class="flex block relative my-2">
        <span class="absolute inset-y-0 left-0 flex items-center p-2">
          <i class="text-gray-400 fas fa-search"></i>
        </span>
        <input class="w-72 px-7 py-3 placeholder-gray-400 text-gray-700 rounded border border-gray-400 pr-10 outline-none text-md leading-4"
          placeholder="John Doe"
          type="text"
          v-model="filterText"/>
      </div>

    </div>
    <div class="flex">

      <div class="flex block mr-2">
        <vSelect
          v-model="filterBadge"
          multiple
          :options="backlogData.badges"
          label="name"
          placeholder="Filter By Badge"
          class="w-72 flex-grow text-gray-400"
        >
          <template slot="option" slot-scope="option">
            <p class="inline">{{ option.name }}</p>
          </template>
          <template #no-options="{ search, searching, loading }">
            No result .
          </template>
        </vSelect>
      </div>

      <div class="flex block mx-2">
        <vSelect
          v-model="filterBadge"
          multiple
          :options="backlogData.badges"
          label="name"
          placeholder="Filter By Assigned Employee"
          class="w-72 flex-grow text-gray-400"
        >
          <template slot="option" slot-scope="option">
            <p class="inline">{{ option.name }}</p>
          </template>
          <template #no-options="{ search, searching, loading }">
            No result .
          </template>
        </vSelect>
      </div>

      <div class="flex block mx-2">
        <vSelect
          v-model="filterBadge"
          multiple
          :options="backlogData.badges"
          label="name"
          placeholder="Filter By Reporter"
          class="w-72 flex-grow text-gray-400"
        >
          <template slot="option" slot-scope="option">
            <p class="inline">{{ option.name }}</p>
          </template>
          <template #no-options="{ search, searching, loading }">
            No result .
          </template>
        </vSelect>
      </div>

    </div>
    <div class="flex">

      <div class="flex py-3 h-12 border mt-3 mb-2">
        <label class="flex mx-3">
            <input
                class="mt-2 form-radio text-indigo-600"
                name="task-options"
                type="checkbox"
                v-model="activeBool">
            <div class="ml-1 text-gray-700 font-medium">
                <p>Active</p>
            </div>
        </label>
        <label class="flex mx-3">
            <input
                class="mt-2 form-radio text-indigo-600"
                name="task-options"
                type="checkbox"
                v-model="completedBool">
            <div class="ml-1 text-gray-700 font-medium">
                <p>Completed</p>
            </div>
        </label>
        <label class="flex mx-3">
            <input
                class="mt-2 form-radio text-indigo-600"
                name="task-options"
                type="checkbox"
                v-model="canceledBool">
            <div class="ml-1 text-gray-700 font-medium">
                <p>Canceled</p>
            </div>
        </label>
      </div>

      <div class="flex py-3 h-12 border mt-3 mb-2 mx-2">
        <label class="flex mx-3">
            <input
                class="mt-2 form-radio text-indigo-600"
                name="task-options"
                type="checkbox"
                v-model="assignedToBoard">
            <div class="ml-1 text-gray-700 font-medium">
                <p>Assigned To a Board</p>
            </div>
        </label>
        <label class="flex mx-3">
            <input
                class="mt-2 form-radio text-indigo-600"
                name="task-options"
                type="checkbox"
                v-model="notAssignedToBoard">
            <div class="ml-1 text-gray-700 font-medium">
                <p>Not Assigned To a Board</p>
            </div>
        </label>
      </div>
    </div>
    
    <hr />
    <div>
      <div class="flex">
        <div class="w-38 h-8" style="">
          <button
            v-if="!hideBoardsPane"
            @click="hideBoardsPane = !hideBoardsPane"
            class="outline-none w-full text-gray-700 font-semibold font-sans tracking-wide text-sm"
          >
            {{ hideBoardsPane ? "Hide" : "Show" }} Board Stats
          </button>
        </div>
      </div>
      <div>
        <splitpanes class="default-theme">
          <pane :size="20" :max-size="25" v-if="hideBoardsPane">
            <board-pane :boards="backlogData.boards"></board-pane>
          </pane>
          <pane :size="50" class="flex-grow">
            <draggable
              @start="drag = true"
              @end="drag = false"
              :list="backlogData.backlogTasks"
              :animation="200"
              ghost-class="ghost-card"
              group="tasks"
              class="h-full list-group"
              @change="getChangeData($event, columnIndex, rowIndex)"
            >
              <backlog-card
                v-for="task in filtered"
                :key="task.id"
                :task="task"
                class="cursor-move"
              >
              </backlog-card>
            </draggable>
          </pane>
          <pane v-if="hideTaskPane">
            <task-pane :task="taskPaneInfo" :badges="backlogData.badges" :boards="backlogData.allBoards"></task-pane>
          </pane>
        </splitpanes>
      </div>
    </div>
  </div>
</template>

<script>
import draggable from "vuedraggable";
import BacklogCard from "./backlogComponents/BacklogCard.vue";
import TaskPane from "./backlogComponents/TaskPane.vue";
import BoardPane from "./backlogComponents/BoardPane.vue";
import { Splitpanes, Pane } from "splitpanes";
import "splitpanes/dist/splitpanes.css";
import BacklogBar from "./backlogComponents/BacklogBar.vue";
import vSelect from "vue-select";
import {ajaxCalls} from "../../mixins/ajaxCallsMixin";

export default {
  inject: ["eventHub"],
  components: {
    BacklogCard,
    draggable,
    Splitpanes,
    Pane,
    TaskPane,
    BoardPane,
    BacklogBar,
    vSelect,
  },
  data() {
    return {
      backlogData: null,
      filterText: "",
      filterBadge: "",
      filterBoard: [],
      currentTask: null,
      hideBoardsPane: true,
      hideTaskPane: false,
      activeBool: true,
      canceledBool: true,
      completedBool: true,
      assignedToBoard: true,
      notAssignedToBoard: true,
      taskPaneInfo: {
        name: "default",
        reporter: {
          full_name: "john doe"
        },
        assigned_to: []
      },
    };
  },

  mixins: [ajaxCalls],

  mounted() {
    this.getBacklogData();
  },

  created() {
    this.eventHub.$on("open-task-view", (currentTask) => {
      this.setSideInfo(currentTask);
    });
    this.eventHub.$on("close-task-view", () => {
      this.closeTaskView();
    });
    this.eventHub.$on("close-board-view", () => {
      this.closeBoardView();
    });
    this.eventHub.$on("filter-by-board", (board) => {
      this.filterByBoard(board);
    });
  },
  computed: {
    filtered() {
      const regex = new RegExp(this.filterText, "i");
      let newArray = [];
      this.filteredWithOptions.forEach(function (value) {
        if (
          value.name.match(regex) ||
          value.badge.name.match(regex)
        ) {
          newArray.push(value);
        }
      });
      return newArray;
    },
    filteredWithOptions() {
      if(this.backlogData != null){
        let regex = new RegExp("", "i");
        return this.backlogData.backlogTasks.filter((t) => {
          let badgeMatch = this.isBadgeMatch(t, regex);
          let boardMatch = this.isBoardMatch(t, regex);
          let statusMatch = this.isStatusMatch(t);
          let assignedMatch = this.isAssignedToMatch(t);

          return badgeMatch && boardMatch && statusMatch && assignedMatch;
        });
      } else {
        return [];
      }
    },
  },
  methods: {
    isBadgeMatch(t, regex){
      let badgeMatch = false;
      if (this.filterBadge.length > 0) {
        this.filterBadge.forEach(function (value1) {
          regex = new RegExp(value1, "i");
          if (t.badge.name.match(regex)) {
            badgeMatch = true;
          }
        });
      } else {
        badgeMatch = true;
      }
      return badgeMatch;
    },
    isBoardMatch(t, regex) {
      let boardMatch = false;
      if (this.filterBoard.length > 0) {
        this.filterBoard.forEach(function (value2) {
          regex = new RegExp(value2, "i");
          if (t.board.name.match(regex)) {
            boardMatch = true;
          }
        });
      } else {
        boardMatch = true;
      }
      return boardMatch;
    },
    isStatusMatch(t) {
      if (this.activeBool && t.status === "active") {
        return true;
      }
      if (this.canceledBool && t.status === "canceled") {
        return true;
      }
      if (this.completedBool && t.status === "completed") {
        return true;
      }
      return false;
    },
    isAssignedToMatch(t) {
      if (this.assignedToBoard && t.column_id != null) {
        return true;
      }
      if (this.notAssignedToBoard && t.column_id == null) {
        return true;
      }
      return false;
    },
    setSideInfo(currentTask) {
      this.hideTaskPane = true;
      this.taskPaneInfo = currentTask;
    },
    closeTaskView() {
      this.hideTaskPane = false;
    },
    closeBoardView() {
      this.hideBoardsPane = false;
    },
    filterByBoard(board) {
      if (this.filterBoard.includes(board)) {
        // remove it from array
        let newArray = this.filterBoard.filter((t) => {
          return t !== board;
        });
        this.filterBoard = newArray;
      } else {
        // add to array
        this.filterBoard.push(board);
      }
    },
    getBacklogData() {
      this.eventHub.$emit("set-loading-state", true);
      this.asyncGetBacklogData().then((data) => {
          this.backlogData = data.data;
          this.eventHub.$emit("set-loading-state", false);
      }).catch(res => {console.log(res)});
    },
  },
};
</script>

<style scoped>
.column-width {
  min-width: 230px;
}

.splitpanes.default-theme .splitpanes__pane {
  background-color: #ffffff;
}

.ghost-card {
  opacity: 0.5;
  background: #F7FAFC;
  border: 1px solid #4299e1;
}
</style>
